<?php

namespace App\Http\Controllers;

use App\Models\Campania;
use App\Models\User;
use App\Models\Segmento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Services\MensajeriaService;

class CampaniaController extends Controller
{
   public function __construct()
{
    $this->baseUrl = config('services.mensajeria.url')
        ?? env('MENSAJERIA_API_URL')
        ?? 'https://hrc-mensajeria.sanluis.gob.ar:8081';
}
    public function index(MensajeriaService $mensajeriaService)
    {
        $campaniasActivas = Campania::whereIn('estado', ['borrador', 'programada'])
            ->count();

        $campaniasProgramadas = Campania::where('estado', 'programada')
            ->count();

        $resumenMensajeria = [
            'mensajes_enviados' => 0,
            'tasa_lectura' => 0,
            'aceptadas_meta' => 0,
            'enviados' => 0,
            'recibidos' => 0,
            'leidos' => 0,
            'fallos' => 0,
        ];

        $campaniasConMensajeria = Campania::whereNotNull('mensajeria_campaign_id')
            ->get();

        foreach ($campaniasConMensajeria as $campania) {
            try {
                $stats = $mensajeriaService->obtenerEstadisticas(
                    $campania->mensajeria_campaign_id
                );

                $resumenMensajeria['mensajes_enviados'] += $stats['total'] ?? 0;
                $resumenMensajeria['aceptadas_meta'] += $stats['accepted'] ?? 0;
                $resumenMensajeria['enviados'] += $stats['sent'] ?? 0;
                $resumenMensajeria['recibidos'] += $stats['received'] ?? 0;
                $resumenMensajeria['leidos'] += $stats['read'] ?? 0;
                $resumenMensajeria['fallos'] += $stats['failed'] ?? 0;

            } catch (\Throwable $e) {
                logger()->error('Error obteniendo estadísticas de campaña', [
                    'campania_id' => $campania->id,
                    'mensajeria_campaign_id' => $campania->mensajeria_campaign_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $resumenMensajeria['tasa_lectura'] =
            $resumenMensajeria['mensajes_enviados'] > 0
                ? round(($resumenMensajeria['leidos'] / $resumenMensajeria['mensajes_enviados']) * 100, 2)
                : 0;

        $campanias = Campania::latest()->paginate(10);

        return view('campanias.index', compact(
            'campanias',
            'campaniasActivas',
            'campaniasProgramadas',
            'resumenMensajeria'
        ));
    }

    public function create()
    {
        $usuarios = User::all();

        return view('campanias.create', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $data = $this->validateCampania($request);

        Campania::create([
            'titulo' => $data['nombre'],
            'descripcion' => $data['descripcion'],
            'solicitante' => $data['solicitante'],
            'responsable_id' => auth()->id(),
            'segmentacion_tipo' => $data['segmentacion_tipo'],
            'edad_min' => $data['segmentacion_tipo'] === 'filtros' ? ($data['edad_min'] ?? null) : null,
            'edad_max' => $data['segmentacion_tipo'] === 'filtros' ? ($data['edad_max'] ?? null) : null,
            'sexo' => $data['segmentacion_tipo'] === 'filtros' ? ($data['sexo'] ?? null) : null,
            'localidad' => $data['segmentacion_tipo'] === 'filtros' ? ($data['localidad'] ?? null) : null,
            'diagnostico' => $data['segmentacion_tipo'] === 'filtros' ? ($data['diagnostico'] ?? null) : null,
              'ultima_atencion_desde' => $data['segmentacion_tipo'] === 'filtros'
                ? ($data['ultima_atencion_desde'] ?? null)
                : null,
            'ultima_atencion_hasta' => $data['segmentacion_tipo'] === 'filtros'
                ? ($data['ultima_atencion_hasta'] ?? null)
                : null,
            'segmentacion_sql' => $data['segmentacion_tipo'] === 'sql'
                ? trim($data['segmentacion_sql'])
                : null,
        ]);

        return redirect()
            ->route('campanias.index')
            ->with('success', 'Campaña creada correctamente');
    }
   protected function armarSqlDesdeFiltros(Request $request): string
    {
        $where = [];

        if ($request->filled('sexo')) {
            $sexo = str_replace("'", "''", trim($request->sexo));
            $where[] = "pf.ie_sexo = '{$sexo}'";
        }

        if ($request->filled('edad_min')) {
            $where[] = "OBTER_IDADE(pf.dt_nascimento, SYSDATE, 'A') >= " . (int) $request->edad_min;
        }

        if ($request->filled('edad_max')) {
            $where[] = "OBTER_IDADE(pf.dt_nascimento, SYSDATE, 'A') <= " . (int) $request->edad_max;
        }

        if ($request->filled('ultima_atencion_desde')) {
            $fechaDesde = trim($request->ultima_atencion_desde);
            $where[] = "ate.dt_entrada >= TO_DATE('{$fechaDesde}', 'YYYY-MM-DD')";
        }

        if ($request->filled('ultima_atencion_hasta')) {
            $fechaHasta = trim($request->ultima_atencion_hasta);
            $where[] = "ate.dt_entrada < TO_DATE('{$fechaHasta}', 'YYYY-MM-DD') + 1";
        }

        if ($request->filled('localidad')) {
            $localidad = str_replace("'", "''", trim($request->localidad));
           $where[] = "UPPER(Obter_Municipio_pf(ate.cd_pessoa_fisica)) LIKE UPPER('%{$localidad}%')";
        }

        if ($request->filled('diagnostico')) {
            $diagnostico = str_replace("'", "''", trim($request->diagnostico));

            $where[] = "EXISTS (
                SELECT 1
                FROM atendimento_paciente ate_dx
                JOIN diagnostico_doenca dd
                ON dd.nr_atendimento = ate_dx.nr_atendimento
                JOIN cid_doenca cd
                ON cd.cd_doenca_cid = dd.cd_doenca
                WHERE ate_dx.cd_pessoa_fisica = ate.cd_pessoa_fisica
                AND dd.ie_situacao = 'A'
                AND dd.ie_classificacao_doenca IN ('P', 'S')
                AND (
                        UPPER(dd.cd_doenca) LIKE '%' || UPPER(TRIM('{$diagnostico}')) || '%'
                    OR UPPER(cd.ds_doenca_cid) LIKE '%' || UPPER(TRIM('{$diagnostico}')) || '%'
                )
            )";
        }

        $whereSql = '';
        if (!empty($where)) {
            $whereSql = "WHERE " . implode("\n      AND ", $where);
        }

        return "
    SELECT
        cd_pessoa_fisica AS codigoPersona,
        paciente AS nombrePaciente,
        dni_paciente AS dniPaciente,
        telefono AS telefono
    FROM (
        SELECT
            ate.nr_atendimento,
            ate.cd_pessoa_fisica,
            Obter_nr_identidade_pf(ate.cd_pessoa_fisica) AS dni_paciente,
            UPPER(obter_nome_paciente(ate.nr_atendimento)) AS paciente,
            NVL(
                (SELECT MAX(ac.nr_telefone)
                FROM agenda_consulta ac
                WHERE ac.nr_atendimento = ate.nr_atendimento),
                Obter_Telefone_PF(ate.cd_pessoa_fisica,1)
            ) AS telefono,
            pf.ie_sexo AS sexo,
            Obter_Municipio_pf(ate.cd_pessoa_fisica) AS localidad,
            OBTER_IDADE(pf.dt_nascimento, SYSDATE, 'A') AS edad,
            ate.dt_entrada AS ultima_consulta,
            ROW_NUMBER() OVER (
                PARTITION BY ate.cd_pessoa_fisica
                ORDER BY ate.dt_entrada DESC
            ) AS rn
        FROM atendimento_paciente ate
        JOIN pessoa_fisica pf
        ON ate.cd_pessoa_fisica = pf.cd_pessoa_fisica
        {$whereSql}
    )
    WHERE rn = 1
    AND telefono IS NOT NULL
    AND LENGTH(TRIM(telefono)) >= 8
    ORDER BY paciente
    ";
    }
    protected function validarSqlSegmentacion(string $sql): void
    {
        $sqlNormalizado = trim($sql);
        $sqlLower = strtolower($sqlNormalizado);

        if (!str_starts_with($sqlLower, 'select')) {
            throw ValidationException::withMessages([
                'segmentacion_sql' => ['La consulta debe comenzar con SELECT']
            ]);
        }

        if (!str_contains($sqlLower, ' from ')) {
            throw ValidationException::withMessages([
                'segmentacion_sql' => ['La consulta debe incluir FROM']
            ]);
        }
        if (!preg_match('/\bwhere\b/i', $sqlLower)) {
            throw ValidationException::withMessages([
                'segmentacion_sql' => ['La consulta debe incluir una cláusula WHERE para limitar los resultados']
            ]);
        }
        if (preg_match('/where\s+1\s*=\s*1/i', $sqlLower)) {
            throw ValidationException::withMessages([
                'segmentacion_sql' => ['La cláusula WHERE no puede ser trivial (ej: 1=1)']
            ]);
        }

        $bloqueadas = ['insert', 'update', 'delete', 'drop', 'truncate', 'alter'];

        foreach ($bloqueadas as $palabra) {
            if (preg_match('/\b' . preg_quote($palabra, '/') . '\b/i', $sqlNormalizado)) {
                throw ValidationException::withMessages([
                    'segmentacion_sql' => ["No se permite usar {$palabra} en la consulta"]
                ]);
            }
        }

        preg_match('/select(.*?)from/is', $sqlNormalizado, $matches);
        $selectPart = strtolower($matches[1] ?? '');

        $aliasesRequeridos = [
            'codigopersona',
            'nombrepaciente',
            'dnipaciente',
            'telefono',
        ];

        foreach ($aliasesRequeridos as $alias) {
            if (!preg_match('/\bas\s+' . preg_quote($alias, '/') . '\b/i', $selectPart)
                && !preg_match('/\b' . preg_quote($alias, '/') . '\b/i', $selectPart)) {
                throw ValidationException::withMessages([
                    'segmentacion_sql' => ["Falta el alias obligatorio: {$alias}"]
                ]);
            }
        }
    }
public function dashboard(
    Campania $campania,
    MensajeriaService $mensajeriaService
) {
    $estadisticas = $mensajeriaService->obtenerEstadisticas(
        $campania->mensajeria_campaign_id
    );

    $detallePacientes = $mensajeriaService->obtenerDetalleMensajes(
        $campania->mensajeria_campaign_id
    );

    return view(
        'campanias.dashboard',
        compact('campania', 'estadisticas', 'detallePacientes')
    );
}
  public function exportarDashboardExcel(
    Campania $campania,
    MensajeriaService $mensajeriaService
) {
    $detallePacientes = $mensajeriaService->obtenerDetalleMensajes(
        $campania->mensajeria_campaign_id
    );

    $filename = 'dashboard_campania_' . $campania->id . '.xls';

    $headers = [
        'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ];

    return response()->stream(function () use ($campania, $detallePacientes) {
        echo "\xEF\xBB\xBF";

        echo "<table border='1'>";
        echo "<tr><th colspan='5'>Dashboard campaña: {$campania->nombre}</th></tr>";
        echo "<tr>";
        echo "<th>Nombre</th>";
        echo "<th>Teléfono</th>";
        echo "<th>Estado</th>";
        echo "<th>Leído</th>";
        echo "<th>Confirmado</th>";
        echo "</tr>";

        foreach ($detallePacientes as $paciente) {
            $leido = ($paciente['leido'] ?? null) === null
                ? '-'
                : (($paciente['leido']) ? 'Sí' : 'No');

            $confirmado = ($paciente['confirmado'] ?? null) === null
                ? '-'
                : (($paciente['confirmado']) ? 'Sí' : 'No');

            echo "<tr>";
            echo "<td>" . e($paciente['nombre'] ?? '-') . "</td>";
            echo "<td>" . e($paciente['telefono'] ?? '-') . "</td>";
            echo "<td>" . e($paciente['estado'] ?? '-') . "</td>";
            echo "<td>" . e($leido) . "</td>";
            echo "<td>" . e($confirmado) . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    }, 200, $headers);
}
    /*  public function dashboard(
    Campania $campania,
    MensajeriaService $mensajeriaService
) {
    if ($campania->estado !== 'finalizada') {
        return redirect()
            ->route('campanias.index')
            ->withErrors([
                'general' => 'Solo se puede ver el dashboard de campañas finalizadas.'
            ]);
    }

    if (!$campania->mensajeria_campaign_id) {
        return redirect()
            ->route('campanias.index')
            ->withErrors([
                'general' => 'La campaña no tiene ID asociado en Mensajería.'
            ]);
    }

    $response = $mensajeriaService->obtenerEstadisticas(
        $campania->mensajeria_campaign_id
    );

    if (!$response->successful()) {
        return redirect()
            ->route('campanias.index')
            ->withErrors([
                'general' => 'No se pudieron obtener las estadísticas de Mensajería.'
            ]);
    }

    $estadisticas = $response->json();

    return view('campanias.dashboard', compact('campania', 'estadisticas'));
}*/
protected function resolverSegmentacion(Request $request,MensajeriaService $mensajeriaService): array
{
    if ($request->segmentacion_tipo === 'sql') {
        $sql = trim($request->segmentacion_sql ?? '');

        if ($sql === '') {
            throw ValidationException::withMessages([
                'segmentacion_sql' => ['Debe ingresar una consulta SQL']
            ]);
        }

        $this->validarSqlSegmentacion($sql);
    } else {
        $sql = $this->armarSqlDesdeFiltros($request);
        $this->validarSqlSegmentacion($sql);
    }

   $data = $this->consultarCantidadSegmentacionEnApi($sql, $mensajeriaService);

   
    return [
        'sql' => $sql,
        'cantidad' => (int) ($data['count'] ?? 0),
    ];
}
protected function consultarCantidadSegmentacionEnApi(
    string $sql,
    MensajeriaService $mensajeriaService
): array {
    $response = $mensajeriaService->contarPacientes($sql);
//dd($response);
    if (!$response->successful()) {
        throw ValidationException::withMessages([
            'segmentacion_sql' => [
                'Error al consultar mensajería: ' .
                ($response->json('message') ?? $response->body())
            ]
        ]);
    }

    $data = $response->json();

    return is_array($data) ? $data : [];
}
 
public function probarSegmentacion(Request $request,MensajeriaService $mensajeriaService) {
    try {
        $segmentacion = $this->resolverSegmentacion($request,$mensajeriaService);

        $sqlQuery = $segmentacion['sql'];

        $response = $mensajeriaService->contarPacientes($sqlQuery);

        if (!$response->successful()) {
            return response()->json([
                'message' => 'No se pudo obtener la cantidad de pacientes desde mensajería.',
                'error_real' => $response->body(),
            ], 500);
        }

        $data = $response->json();

        $cantidad = $data['cantidad']
            ?? $data['count']
            ?? $data['total']
            ?? 0;

        $advertencia = '';

        if ($cantidad === 0) {
            $advertencia = 'La segmentación no devolvió pacientes.';
        }

        $maxPacientes = (int) config('app.campanias_max_pacientes', 5000);

        if ($cantidad > $maxPacientes) {
            $advertencia = 'La segmentación supera el máximo permitido de pacientes.';
        }

        return response()->json([
            'cantidad' => $cantidad,
            'advertencia' => $advertencia,
            'supera_maximo' => $cantidad > $maxPacientes,
            'sql_generada' => $sqlQuery,
            'respuesta_api' => $data,
        ]);

    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Error de validación',
            'errors' => $e->errors(),
        ], 422);

    } catch (\Throwable $e) {
        report($e);

        return response()->json([
            'message' => 'Error interno al probar la segmentación',
            'error_real' => $e->getMessage(),
        ], 500);
    }
}

    protected function validateCampania(Request $request): array
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'solicitante' => 'required|string|max:255',
            'segmentacion_tipo' => ['required', 'in:filtros,sql'],
            'edad_min' => ['nullable', 'integer', 'min:0', 'max:120'],
            'edad_max' => ['nullable', 'integer', 'min:0', 'max:120'],
            'sexo' => ['nullable', 'in:F,M,O'],
            'localidad' => ['nullable', 'string', 'max:255'],
            'diagnostico' => ['nullable', 'string', 'max:255'],
            'ultima_atencion_desde' => ['nullable', 'date'],
            'ultima_atencion_hasta' => ['nullable', 'date'],
            'segmentacion_sql' => ['nullable', 'string'],
            'mensaje' => ['nullable', 'string'],
        ]);

        if (($data['segmentacion_tipo'] ?? null) === 'filtros') {
            if (
                isset($data['edad_min'], $data['edad_max']) &&
                (int) $data['edad_min'] > (int) $data['edad_max']
            ) {
                throw ValidationException::withMessages([
                    'edad_min' => 'La edad mínima no puede ser mayor a la edad máxima.',
                ]);
            }
        }

        if (($data['segmentacion_tipo'] ?? null) === 'sql') {
            if (blank($data['segmentacion_sql'] ?? null)) {
                throw ValidationException::withMessages([
                    'segmentacion_sql' => 'Debe ingresar una consulta SQL.',
                ]);
            }

            $this->validateSafeSelectSql($data['segmentacion_sql']);
        }

        return $data;
    }

    protected function validateSegmentacion(Request $request): array
    {
        $data = $request->validate([
            'segmentacion_tipo' => ['required', 'in:filtros,sql'],
            'edad_min' => ['nullable', 'integer', 'min:0', 'max:120'],
            'edad_max' => ['nullable', 'integer', 'min:0', 'max:120'],
            'sexo' => ['nullable', 'in:F,M,O'],
            'localidad' => ['nullable', 'string', 'max:255'],
            'diagnostico' => 'nullable|string|max:255',
            'ultima_atencion_desde' => ['nullable', 'date'],
            'ultima_atencion_hasta' => ['nullable', 'date'],
            'segmentacion_sql' => ['nullable', 'string'],
        ]);

        if (($data['segmentacion_tipo'] ?? null) === 'filtros') {
            if (
                isset($data['edad_min'], $data['edad_max']) &&
                (int) $data['edad_min'] > (int) $data['edad_max']
            ) {
                throw ValidationException::withMessages([
                    'edad_min' => 'La edad mínima no puede ser mayor a la edad máxima.',
                ]);
            }
        }

        if (($data['segmentacion_tipo'] ?? null) === 'sql') {
            if (blank($data['segmentacion_sql'] ?? null)) {
                throw ValidationException::withMessages([
                    'segmentacion_sql' => 'Debe ingresar una consulta SQL.',
                ]);
            }
        }
        if (
            $request->filled('ultima_atencion_desde') &&
            $request->filled('ultima_atencion_hasta') &&
            $request->ultima_atencion_desde > $request->ultima_atencion_hasta
        ) {
            throw ValidationException::withMessages([
                'ultima_atencion_desde' => ['La fecha desde no puede ser mayor que la fecha hasta.']
            ]);
        }

        return $data;
    }

    protected function validateSafeSelectSql(string $sql): void
    {
        $normalized = trim(mb_strtolower($sql));

        if (!str_starts_with($normalized, 'select')) {
            throw ValidationException::withMessages([
                'segmentacion_sql' => 'Solo se permiten consultas SELECT.',
            ]);
        }

        $forbiddenPatterns = [
            '/;/',                    // evita múltiples sentencias
            '/--/',                   // comentarios inline
            '/\/\*/',                 // comentarios block
            '/\b(insert|update|delete|drop|alter|truncate|create|replace|merge|call|exec|execute|grant|revoke)\b/',
        ];

        foreach ($forbiddenPatterns as $pattern) {
            if (preg_match($pattern, $normalized)) {
                throw ValidationException::withMessages([
                    'segmentacion_sql' => 'La consulta contiene instrucciones no permitidas.',
                ]);
            }
        }

        if (!preg_match('/\bfrom\b/', $normalized)) {
            throw ValidationException::withMessages([
                'segmentacion_sql' => 'La consulta debe incluir una cláusula FROM válida.',
            ]);
        }

        // opcional: forzar uso de una vista segura
        if (!preg_match('/\bfrom\s+vw_pacientes_campanias\b/', $normalized)) {
            throw ValidationException::withMessages([
                'segmentacion_sql' => 'La consulta debe ejecutarse sobre la vista autorizada vw_pacientes_campanias.',
            ]);
        }

        // opcional: exigir columnas mínimas
        foreach (['nombre', 'dni', 'telefono'] as $requiredAlias) {
            if (!preg_match('/\b' . preg_quote($requiredAlias, '/') . '\b/', $normalized)) {
                throw ValidationException::withMessages([
                    'segmentacion_sql' => 'La consulta debe devolver al menos nombre, dni y telefono.',
                ]);
            }
        }
    }

    protected function buildSegmentacionPayload(array $data): array
    {
        if ($data['segmentacion_tipo'] === 'sql') {
            return [
                'tipo' => 'sql',
                'sql' => trim($data['segmentacion_sql']),
            ];
        }

        return [
            'tipo' => 'filtros',
            'filtros' => [
                'edad_min' => $data['edad_min'] ?? null,
                'edad_max' => $data['edad_max'] ?? null,
                'sexo' => $data['sexo'] ?? null,
                'localidad' => $data['localidad'] ?? null,
                 'diagnostico' => $data['diagnostico'] ?? null,
                 'ultima_atencion_desde' => $data['ultima_atencion_desde'] ?? null,
                'ultima_atencion_hasta' => $data['ultima_atencion_hasta'] ?? null,
            ],
        ];
    }

public function guardarBorrador(Request $request,MensajeriaService $mensajeriaService)
{
    $usuario = auth()->user();

    if (!$usuario) {
        return response()->json([
            'message' => 'Usuario no autenticado'
        ], 401);
    }

    $request->validate([
        'id' => 'nullable|integer|exists:campanias,id',
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'solicitante' => 'nullable|string|max:255',

        'segmentacion_tipo' => 'required|in:filtros,sql',
        'edad_min' => 'nullable|integer|min:0',
        'edad_max' => 'nullable|integer|min:0',
        'sexo' => 'nullable|string|max:20',
        'localidad' => 'nullable|string|max:255',
        'diagnostico' => 'nullable|string|max:255',
        'ultima_atencion_desde' => 'nullable|date',
        'ultima_atencion_hasta' => 'nullable|date',
        'segmentacion_sql' => 'nullable|string',
        'segmentacion_modificada' => 'nullable|boolean',

        'mensaje' => 'nullable|string',
        'adjunto' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
    ]);

    if (
        $request->filled('edad_min') &&
        $request->filled('edad_max') &&
        (int) $request->edad_min > (int) $request->edad_max
    ) {
        throw ValidationException::withMessages([
            'edad_min' => ['La edad mínima no puede ser mayor que la edad máxima.']
        ]);
    }

    if (
        $request->filled('ultima_atencion_desde') &&
        $request->filled('ultima_atencion_hasta') &&
        $request->ultima_atencion_desde > $request->ultima_atencion_hasta
    ) {
        throw ValidationException::withMessages([
            'ultima_atencion_desde' => ['La fecha desde no puede ser mayor que la fecha hasta.']
        ]);
    }

    DB::beginTransaction();

    try {
        $campania = $request->filled('id')
            ? Campania::findOrFail($request->id)
            : new Campania();

        $campania->titulo = $request->nombre;
        $campania->descripcion = $request->descripcion;
        $campania->responsable_id = $usuario->id;
        $campania->solicitante = $request->solicitante;

        $campania->segmentacion_tipo = $request->segmentacion_tipo;
        $campania->sexo = $request->sexo;
        $campania->localidad = $request->localidad;
        $campania->diagnostico = $request->diagnostico;
        $campania->edad_min = $request->edad_min;
        $campania->edad_max = $request->edad_max;
        $campania->ultima_atencion_desde = $request->ultima_atencion_desde ?: null;
        $campania->ultima_atencion_hasta = $request->ultima_atencion_hasta ?: null;

       if ($request->segmentacion_tipo === 'filtros') {
            $segmentacion = $this->resolverSegmentacion($request,$mensajeriaService);

            $campania->segmentacion_sql = trim($segmentacion['sql']);
            $campania->cantidad_destinatarios = $segmentacion['cantidad'];
        } else {
            $debeResolverSegmentacion = !$campania->exists
                || !$campania->cantidad_destinatarios
                || $request->boolean('segmentacion_modificada');

            if ($debeResolverSegmentacion) {
                $segmentacion = $this->resolverSegmentacion($request,$mensajeriaService);

                $campania->segmentacion_sql = trim($segmentacion['sql']);
                $campania->cantidad_destinatarios = $segmentacion['cantidad'];
            } else {
                $campania->segmentacion_sql = trim($request->segmentacion_sql);
            }
        }
        $campania->mensaje = $request->mensaje;

        if (!$campania->exists || $campania->estado === 'borrador') {
            $campania->estado = 'borrador';
        }

        if ($request->hasFile('adjunto')) {
            if ($campania->adjunto_path) {
                Storage::disk('public')->delete($campania->adjunto_path);
            }

            $archivo = $request->file('adjunto');
            $path = $archivo->store('campanias/adjuntos', 'public');

            $campania->adjunto_path = $path;
            $campania->adjunto_nombre = $archivo->getClientOriginalName();
            $campania->adjunto_tipo_mime = $archivo->getClientMimeType();
            $campania->tipo_adjunto = str_starts_with($archivo->getClientMimeType(), 'image/')
                ? 'imagen'
                : 'documento';
        }
        $campania->save();
        
        DB::commit();
        if ($campania->exists && $campania->estado === 'programada') {

            $mensajeriaService = app(\App\Services\MensajeriaService::class);

            $payload = [
                'sqlQuery' => $campania->segmentacion_sql,
                'message' => $campania->mensaje,
                'scheduledAt' => Carbon::parse($campania->fecha_programada)->format('Y-m-d\TH:i:s'),
            ];
            if ($campania->adjunto_path) {
                $mime = $campania->adjunto_tipo_mime;

                $payload['tipo'] = str_starts_with($mime, 'image/')
                    ? 'image'
                    : 'document';

                $payload['caption'] = $campania->mensaje;
                $payload['nombreArchivo'] = $campania->adjunto_nombre;
                $payload['urlArchivo'] = asset('storage/' . $campania->adjunto_path);
            }
            $response = $mensajeriaService->actualizarCampania($campania->id, $payload);

            if (!$response->successful()) {
                throw new \Exception(
                    'No se pudo actualizar la campaña en mensajería. ' . $response->body()
                );
            }
        }

       

        return response()->json([
            'ok' => true,
            'id' => $campania->id,
            'message' => 'Campaña guardada',
            'cantidad' => $campania->cantidad_destinatarios,
            'sql_generada' => $campania->segmentacion_sql,
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);

        return response()->json([
            'message' => 'No se pudo guardar',
            'error_real' => $e->getMessage(),
        ], 500);
    }
}

 public function show($id)
{
    $campania = Campania::with('responsable')->findOrFail($id);

    return view('campanias.show', compact('campania'));
}
public function programar(
    Request $request,
    Campania $campania,
    MensajeriaService $mensajeriaService
) {
    if (!$campania->puedeEditarse()) {
        return redirect()
            ->back()
            ->withErrors([
                'general' => 'La campaña ya no puede programarse porque llegó la fecha de programación.'
            ]);
    }

    $request->validate([
        'fecha_programada' => ['required', 'date', 'after:now'],
    ], [
        'fecha_programada.required' => 'Debe ingresar una fecha y hora de programación.',
        'fecha_programada.date' => 'La fecha de programación no es válida.',
        'fecha_programada.after' => 'La fecha de programación debe ser futura.',
    ]);

    $sqlQuery = trim((string) $campania->segmentacion_sql);

    if ($sqlQuery === '') {
        return redirect()
            ->back()
            ->withErrors([
                'general' => 'La campaña no tiene una consulta SQL de segmentación guardada.'
            ]);
    }

    $payload = [
        'campaignId' => $campania->mensajeria_campaign_id ?? $campania->id,
        'sqlQuery' => $sqlQuery,
        'message' => $campania->mensaje,
        'scheduledAt' => Carbon::parse($request->fecha_programada)->format('Y-m-d\TH:i:s'),
    ];
    if ($campania->adjunto_path) {
        $mime = $campania->adjunto_tipo_mime;

        $payload['tipo'] = str_starts_with($mime, 'image/')
            ? 'image'
            : 'document';

        $payload['caption'] = $campania->mensaje;
        $payload['nombreArchivo'] = $campania->adjunto_nombre;
        $payload['urlArchivo'] = asset('storage/' . $campania->adjunto_path);
    }

    try {
        if ($campania->estado === 'programada' && $campania->mensajeria_campaign_id) {
            $response = $mensajeriaService->actualizarCampania(
                $campania->mensajeria_campaign_id,
                $payload
            );
        } else {
            $response = $mensajeriaService->crearCampania($payload);
        }

        if (!$response->successful()) {
            return redirect()
                ->back()
                ->withErrors([
                    'general' => 'No se pudo programar la campaña en la API de mensajería. ' . $response->body()
                ]);
        }

        $data = $response->json();

        $mensajeriaCampaignId =
            $data['id']
            ?? $data['campaignId']
            ?? $data['campaign_id']
            ?? $data['data']['id']
            ?? $data['data']['campaignId']
            ?? $campania->mensajeria_campaign_id;

        $campania->fecha_programada = $request->fecha_programada;
        $campania->estado = 'programada';
        $campania->mensajeria_campaign_id = $mensajeriaCampaignId;
        $campania->save();

        return redirect()
            ->route('campanias.show', $campania->id)
            ->with('success', 'Campaña programada correctamente.');

    } catch (\Throwable $e) {
        return redirect()
            ->back()
            ->withErrors([
                'general' => 'Error al conectar con la API de mensajería: ' . $e->getMessage()
            ]);
    }
}
   public function edit(Campania $campania)
{
    if (!$campania->puedeEditarse()) {
       return redirect()
        ->route('campanias.index')
        ->withErrors([
            'general' => 'La campaña ya no puede modificarse porque llegó la fecha de programación.'
        ]);
    }

    return view('campanias.create', compact('campania'));
}

  public function destroy(
        Campania $campania,
        MensajeriaService $mensajeriaService
    ) {
        if (!$campania->puedeEditarse()) {
            return redirect()
                ->back()
                ->withErrors([
                    'general' => 'La campaña no puede eliminarse porque ya pasó la fecha de programación.'
                ]);
        }

        if ($campania->estado === 'programada') {
            try {
                $response = $mensajeriaService->eliminarCampania($campania->id);

                if (!$response->successful()) {
                    return redirect()
                        ->back()
                        ->withErrors([
                            'general' => 'No se pudo eliminar la campaña en la API de mensajería.'
                        ]);
                }

            } catch (\Exception $e) {
                return redirect()
                    ->back()
                    ->withErrors([
                        'general' => 'Error al conectar con la API de mensajería: ' . $e->getMessage()
                    ]);
            }
        }

        $campania->estado = 'eliminada';
        $campania->save();

        $campania->delete();

        return redirect()
            ->route('campanias.index')
            ->with('success', 'Campaña eliminada correctamente.');
    }

    
    
}
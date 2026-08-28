<?php

namespace App\Http\Controllers;

use App\Models\Comunicacion;
use App\Models\User;
use App\Models\Segmento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Services\SegmentacionPersonalService;

class ComunicacionController extends Controller
{
    public function index(SegmentacionPersonalService $segmentacionService)
    {
        $comunicacion = Comunicacion::with('responsable')->latest()->get();

        /*$this->sincronizarComunicacionesConMensajeria($mensajeriaService);

        $comunicacionesActivas = Comunicaciones::whereIn('estado', ['borrador', 'programada'])
            ->count();

        $comunicacionesProgramadas = Comunicaciones::where('estado', 'programada')
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

        $comunicacionesConMensajeria = Comunicaciones::whereNotNull('mensajeria_campaign_id')
            ->get();

        foreach ($comunicacionesConMensajeria as $campania) {
            try {
                $stats = $mensajeriaService->obtenerEstadisticas(
                    $comunicaciones->id
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

        $comunicaciones = Campania::latest()->paginate(10);
*/
// Declaras las variables vacías para que existan
    $comunicacionActivas  = collect(); 
    $comunicacionProgramadas = collect();
    $resumenMensajeria = ['mensajes_enviados' => 0]; // O un array vacío []
    $resumenMensajeria = ['tasa_lectura' => 0]; // O un array vacío []
    $resumenMensajeria = ['aceptadas_meta' => 0]; // O un array vacío []
    $resumenMensajeria = ['enviados' => 0]; // O un array vacío []

    $resumenMensajeria = ['recibidos' => 0]; // O un array vacío []
    $resumenMensajeria = ['leidos' => 0]; // O un array vacío []
    $resumenMensajeria = ['fallos' => 0]; // O un array vacío []

        return view('comunicacion.index', compact(
            'comunicacion',
            'comunicacionActivas',
            'comunicacionProgramadas',
            'resumenMensajeria'
        ));
    }
    

    public function create()
    {
        $segmentos = Segmento::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

      return view('comunicacion.create', compact('segmentos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'responsable_id' => 'required|exists:users,id',
            'mensaje' => 'nullable|string',
            'segmentacion' => 'nullable',
            'botones' => 'nullable',
            'alcance_estimado' => 'nullable|integer',
        ]);

        $data['estado'] = 'borrador';

        Comunicacion::create($data);

        return redirect()
            ->route('comunicacion.index')
            ->with('success', 'Comunicación creada correctamente');
    }
  public function dashboard(Comunicacion $comunicacion)
{
    $comunicacion->load([
        'destinatarios.user'
    ]);

    $total = $comunicacion->destinatarios->count();

    $leidos = $comunicacion->destinatarios
        ->where('estado', 'leido')
        ->count();

    $enviados = $comunicacion->destinatarios
        ->where('estado', 'enviado')
        ->count();

    $recibidos = $comunicacion->destinatarios
        ->where('estado', 'recibido')
        ->count();

    $fallidos = $comunicacion->destinatarios
        ->filter(function ($destinatario) {
            return in_array(
                strtolower($destinatario->estado ?? ''),
                [
                    'fallo',
                    'fallido',
                    'noaceptadometa'
                ]
            );
        })
        ->count();

    return view('comunicacion.dashboard', [
        'com' => $comunicacion,
        'total' => $total,
        'leidos' => $leidos,
        'enviados' => $enviados,
        'recibidos' => $recibidos,
        'fallidos' => $fallidos,
    ]);
}
    public function probarSegmentacion(
    Request $request,
    SegmentacionPersonalService $segmentacionService
) {
    $data = $request->validate([
        'tipo_segmentacion' => [
            'required',
            'in:segmentos,todo_personal,sql',
        ],

        'segmentos' => [
            'nullable',
            'array',
        ],

        'segmentos.*' => [
            'integer',
            'exists:segmentos,id',
        ],

        'segmentacion_sql' => [
            'nullable',
            'string',
        ],
    ]);

    try {

        // 1. Generamos/resolvemos el SQL
        $sql = $segmentacionService->resolverConsulta(
            $data['tipo_segmentacion'],
            $data['segmentos'] ?? [],
            $data['segmentacion_sql'] ?? null
        );

      $response = $segmentacionService->contarPersonal($sql);

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo consultar la cantidad de personas.',
                'error_api' => $response->body(),
            ], 500);
        }

        $cantidad = $response->json('count');

        return response()->json([
            'success' => true,
            'cantidad' => $cantidad,
            'sql_generada' => $sql,
        ]);

    } catch (\Throwable $e) {

        report($e);

        return response()->json([
            'success' => false,
            'message' => 'No se pudo probar la segmentación.',
            'error_real' => $e->getMessage(),
        ], 500);
    }
}
public function guardarBorrador(
    Request $request,
    SegmentacionPersonalService $segmentacionService
) {
    $usuario = auth()->user();

    if (!$usuario) {
        return response()->json([
            'message' => 'Usuario no autenticado'
        ], 401);
    }

  $request->validate([
    'id' => 'nullable|integer|exists:comunicaciones,id',

    'nombre' => 'required|string|max:255',
    'tipo' => 'required|string|max:255',
    'descripcion' => 'nullable|string',
    'solicitante' => 'required|string|max:255',

    'tipo_segmentacion' => 'required|in:segmentos,todo_personal,sql',

    'segmentos' => 'nullable|array',
    'segmentos.*' => 'integer|exists:segmentos,id',

    'segmentacion_sql' => 'nullable|string',

    'mensaje' => 'nullable|string',

    'adjunto' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
], [
    'nombre.required' => 'Falta completar el nombre.',
    'nombre.max' => 'El nombre no puede superar los 255 caracteres.',

    'tipo_segmentacion.required' => 'Falta seleccionar el tipo de segmentación.',
    'tipo_segmentacion.in' => 'El tipo de segmentación seleccionado no es válido.',

    'segmentos.array' => 'Los segmentos enviados no tienen un formato válido.',
    'segmentos.*.exists' => 'Uno de los segmentos seleccionados no existe.',

    'solicitante.max' => 'El solicitante no puede superar los 255 caracteres.',

    'adjunto.file' => 'El adjunto enviado no es un archivo válido.',
    'adjunto.mimes' => 'El archivo debe ser JPG, JPEG, PNG, PDF, DOC o DOCX.',
    'adjunto.max' => 'El archivo no puede superar los 10 MB.',
]);
    DB::beginTransaction();

    try {

        $comunicacion = $request->filled('id')
            ? Comunicacion::findOrFail($request->id)
            : new Comunicacion();

        $comunicacion->nombre = $request->nombre;
        $comunicacion->tipo = $request->tipo;
        $comunicacion->descripcion = $request->descripcion;
        $comunicacion->responsable_id = $usuario->id;
        $comunicacion->solicitante = $request->solicitante;

        /*
        |--------------------------------------------------------------------------
        | Segmentación de Comunicaciones
        |--------------------------------------------------------------------------
        */

        $sql = $segmentacionService->resolverConsulta(
            $request->tipo_segmentacion,
            $request->segmentos ?? [],
            $request->segmentacion_sql
        );
       $responseCantidad = $segmentacionService->contarPersonal($sql);

            if (!$responseCantidad->successful()) {
                throw new \Exception(
                    'Error al consultar destinatarios: ' . $responseCantidad->body()
                );
            }

            $cantidad = $responseCantidad->json('count');

            $comunicacion->cantidad_destinatarios = $cantidad;

        $comunicacion->tipo_segmentacion = $request->tipo_segmentacion;
        $comunicacion->segmentacion_sql = trim($sql);
        $comunicacion->cantidad_destinatarios = $cantidad;

  
        /*
        |--------------------------------------------------------------------------
        | Mensaje
        |--------------------------------------------------------------------------
        */

        $comunicacion->mensaje = $request->mensaje;

        if (
            !$comunicacion->exists ||
            $comunicacion->estado === 'borrador'
        ) {
            $comunicacion->estado = 'borrador';
        }

        /*
        |--------------------------------------------------------------------------
        | Adjunto
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('adjunto')) {

            $nombreOriginal = $request->file('adjunto')
                ->getClientOriginalName();

            if (preg_match('/\s/', $nombreOriginal)) {
                throw ValidationException::withMessages([
                    'adjunto' => [
                        'El nombre del archivo adjunto no puede contener espacios.'
                    ]
                ]);
            }

            if ($comunicacion->adjunto_path) {
                Storage::disk('public')
                    ->delete($comunicacion->adjunto_path);
            }

            $archivo = $request->file('adjunto');

            $path = $archivo->store(
                'comunicaciones/adjuntos',
                'public'
            );

            $comunicacion->adjunto_path = $path;
            $comunicacion->adjunto_nombre =
                $archivo->getClientOriginalName();

            $comunicacion->adjunto_tipo_mime =
                $archivo->getClientMimeType();

            $comunicacion->tipo_adjunto =
                str_starts_with(
                    $archivo->getClientMimeType(),
                    'image/'
                )
                    ? 'imagen'
                    : 'documento';
        }

        $comunicacion->save();
        if ($request->tipo_segmentacion === 'segmentos') {
            $comunicacion->segmentos()->sync($request->segmentos ?? []);
        } else {
            $comunicacion->segmentos()->detach();
        }
        DB::commit();

        return response()->json([
            'ok' => true,
            'id' => $comunicacion->id,
            'message' => 'Comunicación guardada',
            'sql_generada' => $comunicacion->segmentacion_sql,
        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        report($e);

        return response()->json([
            'message' => 'No se pudo guardar la comunicación',
            'error_real' => $e->getMessage(),
        ], 500);
    }
}
public function edit(Comunicacion $comunicacion)
{
    if (!$comunicacion->puedeEditarse()) {
        return redirect()
            ->route('comunicacion.index')
            ->withErrors([
                'general' => 'La comunicación ya no puede modificarse porque llegó la fecha de programación.'
            ]);
    }

    $comunicacion->load('segmentos');

    $segmentos = Segmento::all();

    return view('comunicacion.create', compact(
        'comunicacion',
        'segmentos'
    ));
}
public function show(Comunicacion $comunicacion)
{
    $comunicacion->load([
        'responsable',
        'segmentos'
    ]);

    return view('comunicacion.show', compact('comunicacion'));
}
public function programar(
    Request $request,
    Comunicacion $comunicacion,
    SegmentacionPersonalService $segmentacionService
) {
    if (!$comunicacion->puedeEditarse()) {
        return redirect()
            ->back()
            ->withErrors([
                'general' => 'La comunicación ya no puede programarse porque llegó la fecha de programación.'
            ]);
    }

    $request->validate([
        'fecha_programada' => ['required', 'date', 'after:now'],
    ], [
        'fecha_programada.required' => 'Debe ingresar una fecha y hora de programación.',
        'fecha_programada.date' => 'La fecha de programación no es válida.',
        'fecha_programada.after' => 'La fecha de programación debe ser futura.',
    ]);

    $sqlQuery = trim((string) $comunicacion->segmentacion_sql);

    if ($sqlQuery === '') {
        return redirect()
            ->back()
            ->withErrors([
                'general' => 'La comunicación no tiene una consulta SQL de segmentación guardada.'
            ]);
    }

    $fechaProgramada = Carbon::parse(
        $request->fecha_programada
    );

    $payload = [
        'comunicacionId' => $comunicacion->id,
        'sqlQuery' => $sqlQuery,
        'message' => $comunicacion->mensaje,
        'scheduledAt' => $fechaProgramada->format('Y-m-d\TH:i:s'),
    ];

    /*
    |--------------------------------------------------------------------------
    | Adjunto
    |--------------------------------------------------------------------------
    */
    if ($comunicacion->adjunto_path) {
        $mime = $comunicacion->adjunto_tipo_mime ?? '';

        $payload['tipo'] = str_starts_with($mime, 'image/')
            ? 'image'
            : 'document';

        $payload['caption'] = $comunicacion->mensaje;

        $payload['nombreArchivo'] =
            $comunicacion->adjunto_nombre;

        $payload['urlArchivo'] =
            asset('storage/' . $comunicacion->adjunto_path);
    }
$esReprogramacion =
    $comunicacion->estado === 'programada';

try {

    if ($esReprogramacion) {

        $response = $segmentacionService->actualizarComunicacion(
            $comunicacion->id,
            $payload
        );

    } else {

        $response = $segmentacionService->crearComunicacion(
            $payload
        );
    }

    if (!$response->successful()) {
        return redirect()
            ->back()
            ->withErrors([
                'general' =>
                    'No se pudo programar la comunicación en la API de mensajería. '
                    . $response->body()
            ]);
    }

    $comunicacion->update([
        'fecha_programada' => $fechaProgramada,
        'estado' => 'programada',
    ]);

    return redirect()
        ->route('comunicacion.show', $comunicacion->id)
        ->with(
            'success',
            $esReprogramacion
                ? 'Comunicación reprogramada correctamente.'
                : 'Comunicación programada correctamente.'
        );

} catch (\Throwable $e) {

    report($e);

    return redirect()
        ->back()
        ->withErrors([
            'general' =>
                'Error al conectar con la API de mensajería: '
                . $e->getMessage()
        ]);
}
}

public function destroy(
    Comunicacion $comunicacion,
    SegmentacionPersonalService $segmentacionService
) {
    try {

        // Si ya fue programada, primero intentamos eliminarla
        // también de la API de mensajería.
        if ($comunicacion->estado === 'programada') {

            $response = $segmentacionService
                ->eliminarComunicacion($comunicacion->id);

            if (!$response->successful()) {
                return redirect()
                    ->back()
                    ->withErrors([
                        'general' =>
                            'No se pudo eliminar la comunicación de la API de mensajería. '
                            . $response->body()
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Eliminar relaciones
        |--------------------------------------------------------------------------
        */

        $comunicacion->segmentos()->detach();

        /*
        |--------------------------------------------------------------------------
        | Eliminar adjunto
        |--------------------------------------------------------------------------
        */

        if ($comunicacion->adjunto_path) {
            Storage::disk('public')
                ->delete($comunicacion->adjunto_path);
        }

        /*
        |--------------------------------------------------------------------------
        | Eliminar comunicación
        |--------------------------------------------------------------------------
        */

        $comunicacion->delete();

        return redirect()
            ->route('comunicacion.index')
            ->with(
                'success',
                'Comunicación eliminada correctamente.'
            );

    } catch (\Throwable $e) {

        report($e);

        return redirect()
            ->back()
            ->withErrors([
                'general' =>
                    'No se pudo eliminar la comunicación. '
                    . $e->getMessage()
            ]);
    }
}
}

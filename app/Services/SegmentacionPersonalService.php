<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Segmento;
use InvalidArgumentException;


class SegmentacionPersonalService
{
     private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env(
            'MENSAJERIA_API_URL',
            'https://hrc-mensajeria.sanluis.gob.ar:8081'
        );
    }
    private function http()
    {
        return Http::withoutVerifying()->timeout(30);
    }
    public function crearComunicacion(array $payload)
    {
        return $this->http()->post(
            $this->baseUrl . '/comunicaciones',
            $payload
        );
    }

    public function actualizarComunicacion(
        int $comunicacionId,
        array $payload
    ) {
        return $this->http()->put(
            $this->baseUrl . "/comunicaciones/{$comunicacionId}",
            $payload
        );
    }

    public function eliminarComunicacion(int $comunicacionId)
    {
        return $this->http()->delete(
            $this->baseUrl . "/comunicaciones/{$comunicacionId}"
        );
    }

    public function contarPersonal(string $sqlQuery)
    {
        return $this->http()->post(
            $this->baseUrl . '/comunicaciones/count',
            [
                'sqlQuery' => $sqlQuery,
            ]
        );
    }

    public function obtenerComunicacion(int $comunicacionId)
    {
        return $this->http()->get(
            $this->baseUrl . "/comunicaciones/{$comunicacionId}"
        );
    }
   
   public function obtenerDetalleMensajes($comunicacionId)
    {
        $response = Http::withOptions([
                'verify' => false,
            ])
            ->timeout(60)
            ->get($this->baseUrl . '/mensajeria-data/mensajes-campania', [
                'campania' => $comunicacionId,
            ]);

        if (!$response->successful()) {
            return [];
        }

        $mensajes = $response->json() ?? [];

        return collect($mensajes)->map(function ($mensaje) {
            $estadoNumero = (int) ($mensaje['ESTADO'] ?? 0);

            $estadoTexto = match ($estadoNumero) {
                1 => 'Aceptado Meta',
                2 => 'Enviado',
                3 => 'Recibido',
                4 => 'Leído',
                5 => 'Confirmado',
                6 => 'Cancelado por paciente',
                7 => 'Cancelado por sistema',
                8 => 'Revisar',
                9 => 'Fallo',
                10 => 'Eliminado',
                11 => 'No aceptado Meta',
                default => 'Pendiente',
            };

        return [
        'nombre' => $mensaje['NOMBRE_PERSONA'],
        'codigo_persona' => $mensaje['CODIGO_PERSONA'],
        'telefono' => $mensaje['PHONE_NUMBER'] ?? '-',
        'estado' => $estadoTexto,
        'fecha_envio' => $mensaje['FECHA_ENVIO'],
        'fecha_leido' => $mensaje['FECHA_LEIDO'],
    ];
        })->toArray();
    }
    public function obtenerMensajesPorComunicacion($comunicacionId)
    {
        $response = Http::withOptions([
                'verify' => false,
            ])
            ->timeout(60)
            ->get($this->baseUrl . '/mensajeria-data/mensajes', [
                'campania' => $comunicacionId,
            ]);

        if (!$response->successful()) {
            return [];
        }

        return $response->json() ?? [];
    }
    public function obtenerEstadisticas($comunicacionId)
{
    $mensajes = $this->obtenerMensajesPorComunicacion($comunicacionId);

    $totalMensajes = collect($mensajes)
        ->filter(fn ($m) => !in_array((int) ($m['ESTADO'] ?? 0), [7, 8]))
        ->count();

    $aceptados = collect($mensajes)->where('ESTADO', 1)->count();
    $enviados = collect($mensajes)->where('ESTADO', 2)->count();
    $recibidos = collect($mensajes)->where('ESTADO', 3)->count();
    $leidos = collect($mensajes)->where('ESTADO', 4)->count();
    $confirmados = collect($mensajes)->where('ESTADO', 5)->count();

    $cancelados = collect($mensajes)
        ->filter(fn ($m) => in_array((int) ($m['ESTADO'] ?? 0), [6, 7]))
        ->count();

    $fallos = collect($mensajes)
        ->filter(fn ($m) => in_array((int) ($m['ESTADO'] ?? 0), [9, 11]))
        ->count();

    $tasaLectura = $totalMensajes > 0
        ? round(($leidos / $totalMensajes) * 100, 2)
        : 0;

    return [
        'total' => $totalMensajes,
        'accepted' => $aceptados,
        'sent' => $enviados,
        'received' => $recibidos,
        'read' => $leidos,
        'confirmed' => $confirmados,
        'cancelled' => $cancelados,
        'failed' => $fallos,
        'tasa_lectura' => $tasaLectura,
    ];
}
    public function resolverConsulta(
    string $tipo,
    array $segmentos = [],
    ?string $sqlManual = null
    ): string {
    return match ($tipo) {

        'segmentos' =>
            $this->consultaPorSegmentos($segmentos),

        'todo_personal' =>
            $this->consultaTodoPersonal(),

        'sql' =>
            $this->validarSqlManual($sqlManual),

        default =>
            throw new \InvalidArgumentException(
                'Tipo de segmentación inválido.'
            ),
    };
}
private function consultaTodoPersonal(): string
{
    return "
        SELECT DISTINCT
            pf.cd_pessoa_fisica AS codigoPersona,
            pf.nm_pessoa_fisica AS nombrePersona,
            pf.nr_identidade AS dniPersona,
            pf.nr_telefone_celular AS telefono

        FROM pessoa_fisica pf

        WHERE pf.ie_vinculo_profissional = 1

          AND EXISTS (
              SELECT 1
              FROM usuario u
              WHERE u.cd_pessoa_fisica = pf.cd_pessoa_fisica
                AND u.ie_situacao = 'A'
          )

          AND pf.nr_telefone_celular IS NOT NULL
          AND TRIM(pf.nr_telefone_celular) IS NOT NULL

          AND UPPER(pf.nm_pessoa_fisica) NOT LIKE '%PRUEBA%'

        ORDER BY pf.nm_pessoa_fisica
    ";
}
private function consultaPorSegmentos(array $segmentosIds): string
{
    if (empty($segmentosIds)) {
        throw new InvalidArgumentException(
            'Debe seleccionar al menos un segmento.'
        );
    }

    $segmentos = Segmento::query()
        ->whereIn('id', $segmentosIds)
        ->where('activo', true)
        ->get();

    if ($segmentos->isEmpty()) {
        throw new InvalidArgumentException(
            'No se encontraron segmentos válidos.'
        );
    }

    $codigos = $segmentos
        ->pluck('codigo')
        ->map(fn ($codigo) => strtoupper($codigo))
        ->values()
        ->toArray();

    $consultas = [];

    /*
     * SEGMENTOS BASADOS EN ESPECIALIDADES
     */
    $segmentosEspecialidades = array_filter(
        $codigos,
        fn ($codigo) => !in_array(
            $codigo,
            [
                'DIRECTIVOS',
                'ADMINISTRATIVOS',
            ]
        )
    );

    if (!empty($segmentosEspecialidades)) {
        $condiciones = [];

        foreach ($segmentosEspecialidades as $codigo) {
            $condicion = $this->condicionPorSegmento($codigo);

            if ($condicion) {
                $condiciones[] = $condicion;
            }
        }

        if (!empty($condiciones)) {
            $whereSegmentos = implode(
                "\n OR ",
                $condiciones
            );

            $consultas[] = $this->consultaSegmentosProfesionales(
                $whereSegmentos
            );
        }
    }

    /*
     * DIRECTIVOS / JEFES / SUBJEFES
     */
    if (in_array('DIRECTIVOS', $codigos)) {
        $consultas[] = $this->consultaDirectivos();
    }

    /*
     * ADMINISTRATIVOS
     *
     * Todavía falta determinar en Tasy
     * qué clasificación identifica administrativos.
     */
    if (in_array('ADMINISTRATIVOS', $codigos)) {
        throw new InvalidArgumentException(
            'El segmento Administrativos todavía no tiene configurada su consulta.'
        );
    }

    if (empty($consultas)) {
        throw new InvalidArgumentException(
            'Los segmentos seleccionados no tienen una consulta configurada.'
        );
    }

    /*
     * Unimos todas las fuentes.
     *
     * UNION ALL + SELECT DISTINCT externo:
     * si una persona es médico y además jefe,
     * aparecerá una sola vez.
     */
    $union = implode(
        "\n\nUNION ALL\n\n",
        $consultas
    );

    return "
        SELECT 
            resultado.codigoPersona AS codigoPersona,
            resultado.nombrePersona AS nombrePaciente,
            resultado.dniPersona AS dniPaciente,
            resultado.telefono AS telefono
        FROM (
            {$union}
        ) resultado

        ORDER BY resultado.nombrePersona
    ";
}
private function consultaSegmentosProfesionales(
    string $whereSegmentos
): string {
    return "
        WITH profesionales AS (
            SELECT DISTINCT
                pf.cd_pessoa_fisica,
                pf.nm_pessoa_fisica,
                pf.nr_identidade,
                pf.nr_telefone_celular,

                UPPER(
                    TRANSLATE(
                        Obter_Especialidades_medico(
                            pf.cd_pessoa_fisica
                        ),
                        'ÁÉÍÓÚÜÑáéíóúüñ',
                        'AEIOUUNAEIOUUN'
                    )
                ) AS especialidades_normalizadas

            FROM pessoa_fisica pf

            LEFT JOIN medico m
                ON m.cd_pessoa_fisica = pf.cd_pessoa_fisica

            WHERE pf.ie_vinculo_profissional = 1

              AND pf.nr_telefone_celular IS NOT NULL

              AND TRIM(
                    pf.nr_telefone_celular
                  ) IS NOT NULL

              AND EXISTS (
                  SELECT 1
                  FROM usuario u
                  WHERE u.cd_pessoa_fisica =
                        pf.cd_pessoa_fisica
                    AND u.ie_situacao = 'A'
              )

              AND UPPER(
                    pf.nm_pessoa_fisica
                  ) NOT LIKE '%PRUEBA%'
        )

        SELECT DISTINCT
            p.cd_pessoa_fisica AS codigoPersona,
            p.nm_pessoa_fisica AS nombrePersona,
            p.nr_identidade AS dniPersona,
            p.nr_telefone_celular AS telefono

        FROM profesionales p

        WHERE (
            {$whereSegmentos}
        )
    ";
}
private function consultaDirectivos(): string
{
    return "
        SELECT DISTINCT
            pf.cd_pessoa_fisica AS codigoPersona,
            pf.nm_pessoa_fisica AS nombrePersona,
            pf.nr_identidade AS dniPersona,
            pf.nr_telefone_celular AS telefono

        FROM pessoa_fisica pf

        JOIN pessoa_fisica_classif_func pfc
            ON pfc.cd_pessoa_fisica =
               pf.cd_pessoa_fisica

        WHERE pf.ie_vinculo_profissional = 1

          AND pfc.nr_seq_classif IN (9, 10)

          AND pf.nr_telefone_celular IS NOT NULL

          AND TRIM(
                pf.nr_telefone_celular
              ) IS NOT NULL

          AND EXISTS (
              SELECT 1
              FROM usuario u
              WHERE u.cd_pessoa_fisica =
                    pf.cd_pessoa_fisica
                AND u.ie_situacao = 'A'
          )

          AND UPPER(
                pf.nm_pessoa_fisica
              ) NOT LIKE '%PRUEBA%'
    ";
}
private function condicionPorSegmento(string $codigo): ?string
{
    return match (strtoupper($codigo)) {

        'ENFERMERIA' => "
            REGEXP_LIKE(
                p.especialidades_normalizadas,
                '(^|\\\\)ENFERMERIA(\\\\|$)'
            )
        ",

        'KINESIOLOGIA' => "
            REGEXP_LIKE(
                p.especialidades_normalizadas,
                '(^|\\\\)KINESIOLOGIA(\\\\|$)'
            )
        ",

        'LABORATORIO' => "
            REGEXP_LIKE(
                p.especialidades_normalizadas,
                '(^|\\\\)(LABORATORIO|ANATOMIA PATOLOGICA|HEMOTERAPIA|INMUNO HEMATOLOGIA)(\\\\|$)'
            )
        ",

        'FARMACIA' => "
            REGEXP_LIKE(
                p.especialidades_normalizadas,
                '(^|\\\\)FARMACIA(\\\\|$)'
            )
        ",

        'NUTRICION' => "
            REGEXP_LIKE(
                p.especialidades_normalizadas,
                '(^|\\\\)(NUTRICION|MEDICO NUTRICIONISTA|SOPORTE NUTRICIONAL)(\\\\|$)'
            )
        ",

        'PSICOLOGIA' => "
            REGEXP_LIKE(
                p.especialidades_normalizadas,
                '(^|\\\\)(PSICOLOGIA|PSICOLOGIA BARIATRICA)(\\\\|$)'
            )
        ",

        'TRABAJO_SOCIAL' => "
            REGEXP_LIKE(
                p.especialidades_normalizadas,
                '(^|\\\\)(TRABAJO SOCIAL|TRABAJO SOCIAL \\(SALUD MENTAL\\))(\\\\|$)'
            )
        ",

        'TECNICOS' => "
            REGEXP_LIKE(
                p.especialidades_normalizadas,
                '(^|\\\\)(INSTRUMENTACION QUIRURGICA|TOMOGRAFISTA/MAMOGRAFISTA|ECOGRAFISTA|RADIOLOGIA|IMAGENOLOGIA DIAGNOSTICA Y TER)(\\\\|$)'
            )
        ",

        'MEDICOS' => $this->condicionMedicos(),
       

        default => null,
    };
}
private function condicionMedicos(): string
{
    return "
        (
               p.especialidades_normalizadas LIKE '%ALERGOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%ANESTESIOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%CARDIOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%CIRUGIA%'
            OR p.especialidades_normalizadas LIKE '%CLINICA MEDICA%'
            OR p.especialidades_normalizadas LIKE '%COLOPROCTOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%DERMATOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%DIABETOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%EMERGENTOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%ENDOCRINOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%EPIDEMIOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%FISIATRA%'
            OR p.especialidades_normalizadas LIKE '%FLEBOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%GASTROENTEROLOGIA%'
            OR p.especialidades_normalizadas LIKE '%GENETICA%'
            OR p.especialidades_normalizadas LIKE '%GERIATRIA%'
            OR p.especialidades_normalizadas LIKE '%GINECOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%HEMATOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%HEMODINAMIA%'
            OR p.especialidades_normalizadas LIKE '%HEPATOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%INFECTOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%INMUNOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%MEDICINA CRITICA%'
            OR p.especialidades_normalizadas LIKE '%MEDICINA FAMILIAR%'
            OR p.especialidades_normalizadas LIKE '%MEDICINA GENERAL%'
            OR p.especialidades_normalizadas LIKE '%MEDICINA INTENSIVA%'
            OR p.especialidades_normalizadas LIKE '%MEDICINA INTERNA%'
            OR p.especialidades_normalizadas LIKE '%MEDICINA LABORAL%'
            OR p.especialidades_normalizadas LIKE '%MEDICINA PALIATIVA%'
            OR p.especialidades_normalizadas LIKE '%MEDICO CLINICO%'
            OR p.especialidades_normalizadas LIKE '%MEDICO GENERALISTA%'
            OR p.especialidades_normalizadas LIKE '%MEDICO TERAPISTA%'
            OR p.especialidades_normalizadas LIKE '%NEFROLOGIA%'
            OR p.especialidades_normalizadas LIKE '%NEUMONOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%NEUROCIRUGIA%'
            OR p.especialidades_normalizadas LIKE '%NEUROLOGIA%'
            OR p.especialidades_normalizadas LIKE '%OFTALMOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%OTORRINOLARINGOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%PEDIATRIA%'
            OR p.especialidades_normalizadas LIKE '%PSIQUIATRIA%'
            OR p.especialidades_normalizadas LIKE '%REUMATOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%TERAPIA INTENSIVA%'
            OR p.especialidades_normalizadas LIKE '%TRAUMATOLOGIA%'
            OR p.especialidades_normalizadas LIKE '%UROLOGIA%'
        )
    ";
}
}
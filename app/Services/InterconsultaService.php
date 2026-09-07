<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class InterconsultaService
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
        return Http::withoutVerifying()
            ->acceptJson()
            ->timeout(60);
    }

    /*
    |--------------------------------------------------------------------------
    | Interconsultas
    |--------------------------------------------------------------------------
    */

    public function obtenerInterconsultas(): array
    {
        $response = $this->http()
            ->get(
                $this->baseUrl .
                '/mensajeria-data/interconsulta'
            );

        if (!$response->successful()) {
            return [];
        }

        return $response->json() ?? [];
    }

    /*
    |--------------------------------------------------------------------------
    | Mensajes de interconsultas
    |--------------------------------------------------------------------------
    */

    public function obtenerMensajes(): array
    {
        $response = $this->http()
            ->get(
                $this->baseUrl .
                '/mensajeria-data/mensajes-interconsulta'
            );

        if (!$response->successful()) {
            return [];
        }

        return $response->json() ?? [];
    }

    /*
    |--------------------------------------------------------------------------
    | Buscar una interconsulta
    |--------------------------------------------------------------------------
    */

    public function obtenerInterconsulta(int $idInterconsultaReq): ?array
    {
        return collect(
            $this->obtenerInterconsultas()
        )
        ->first(function ($item) use ($idInterconsultaReq) {

            return (int) (
                $item['ID_INTERCONSULTA_REQ'] ?? 0
            ) === $idInterconsultaReq;

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Mensajes correspondientes a una interconsulta
    |--------------------------------------------------------------------------
    */

    public function obtenerMensajesInterconsulta(
        int $idInterconsultaReq
    ): array {

        return collect(
            $this->obtenerMensajes()
        )
        ->filter(function ($mensaje) use ($idInterconsultaReq) {

            return (int) (
                $mensaje['ID_INTERCONSULTA_REQ'] ?? 0
            ) === $idInterconsultaReq;

        })
        ->values()
        ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Estado operativo
    |--------------------------------------------------------------------------
    */

    public function estadoInterconsulta(array $interconsulta): string
    {
        if (!empty($interconsulta['FECHA_RES'])) {
            return 'Respondida';
        }

        if (!empty($interconsulta['FECHA_TOMA'])) {
            return 'En proceso';
        }

        return 'Pendiente';
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard general
    |--------------------------------------------------------------------------
    */

    public function obtenerResumen(): array
    {
        $interconsultas = collect(
            $this->obtenerInterconsultas()
        );

        $mensajes = collect(
            $this->obtenerMensajes()
        );

        $total = $interconsultas->count();

        $pendientes = $interconsultas
            ->filter(fn ($i) =>
                empty($i['FECHA_TOMA'])
                &&
                empty($i['FECHA_RES'])
            )
            ->count();

        $enProceso = $interconsultas
            ->filter(fn ($i) =>
                !empty($i['FECHA_TOMA'])
                &&
                empty($i['FECHA_RES'])
            )
            ->count();

        $respondidas = $interconsultas
            ->filter(fn ($i) =>
                !empty($i['FECHA_RES'])
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Tiempo promedio hasta toma
        |--------------------------------------------------------------------------
        */

        $tiemposToma = $interconsultas
            ->filter(fn ($i) =>
                !empty($i['FECHA_INTERCONSULTA'])
                &&
                !empty($i['FECHA_TOMA'])
            )
            ->map(function ($i) {

                $inicio = strtotime(
                    $i['FECHA_INTERCONSULTA']
                );

                $fin = strtotime(
                    $i['FECHA_TOMA']
                );

                return max(
                    0,
                    ($fin - $inicio) / 60
                );

            });

        $promedioToma = $tiemposToma->count()
            ? round($tiemposToma->avg(), 1)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Tiempo promedio hasta respuesta
        |--------------------------------------------------------------------------
        */

        $tiemposRespuesta = $interconsultas
            ->filter(fn ($i) =>
                !empty($i['FECHA_INTERCONSULTA'])
                &&
                !empty($i['FECHA_RES'])
            )
            ->map(function ($i) {

                $inicio = strtotime(
                    $i['FECHA_INTERCONSULTA']
                );

                $fin = strtotime(
                    $i['FECHA_RES']
                );

                return max(
                    0,
                    ($fin - $inicio) / 60
                );

            });

        $promedioRespuesta =
            $tiemposRespuesta->count()
                ? round(
                    $tiemposRespuesta->avg(),
                    1
                )
                : 0;

        /*
        |--------------------------------------------------------------------------
        | Mensajería
        |--------------------------------------------------------------------------
        */

        $mensajesTotal = $mensajes
            ->filter(fn ($m) =>
                !in_array(
                    (int) ($m['ESTADO'] ?? 0),
                    [7, 8, 10]
                )
            )
            ->count();

        $aceptados = $mensajes
            ->where('ESTADO', 1)
            ->count();

        $enviados = $mensajes
            ->where('ESTADO', 2)
            ->count();

        $recibidos = $mensajes
            ->where('ESTADO', 3)
            ->count();

        $leidos = $mensajes
            ->where('ESTADO', 4)
            ->count();

        $fallidos = $mensajes
            ->filter(fn ($m) =>
                in_array(
                    (int) ($m['ESTADO'] ?? 0),
                    [9, 11, 12]
                )
            )
            ->count();

        $tasaLectura = $mensajesTotal > 0
            ? round(
                ($leidos / $mensajesTotal) * 100,
                1
            )
            : 0;

        return [
            'total' => $total,
            'pendientes' => $pendientes,
            'en_proceso' => $enProceso,
            'respondidas' => $respondidas,

            'promedio_toma' => $promedioToma,
            'promedio_respuesta' =>
                $promedioRespuesta,

            'mensajes_total' => $mensajesTotal,
            'aceptados' => $aceptados,
            'enviados' => $enviados,
            'recibidos' => $recibidos,
            'leidos' => $leidos,
            'fallidos' => $fallidos,
            'tasa_lectura' => $tasaLectura,
        ];
    }
}
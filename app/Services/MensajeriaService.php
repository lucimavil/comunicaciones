<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MensajeriaService
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
    public function crearCampania(array $payload)
    {
             return $this->http()->post($this->baseUrl . '/campaigns', $payload);
    }

    public function actualizarCampania(int $campaignId, array $payload)
    {
        return $this->http()->put($this->baseUrl . "/campaigns/{$campaignId}", $payload);
    }

    public function eliminarCampania(int $campaignId)
    {
         return $this->http()->delete($this->baseUrl . "/campaigns/{$campaignId}");
    }

    public function contarPacientes(string $sqlQuery)
    {
		return $this->http()
        ->acceptJson()
        ->asJson()
        ->post($this->baseUrl . '/campaigns/count', [
            'sqlQuery' => $sqlQuery,
        ]);
		
			/*
		return $this->http()->post($this->baseUrl . '/campaigns/count', [
                'sql' => $sqlQuery,
            ]);
			*/
    }

    public function obtenerCampania(int $campaignId)
    {
         return $this->http()->get($this->baseUrl . "/campaigns/{$campaignId}");
    }

   
   public function obtenerDetalleMensajes($campaniaId)
{
    $response = Http::withOptions([
            'verify' => false,
        ])
        ->timeout(60)
        ->get($this->baseUrl . '/mensajeria-data/mensajes-campania', [
            'campania' => $campaniaId,
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
    public function obtenerMensajesPorCampania($campaniaId)
{
    $response = Http::withOptions([
            'verify' => false,
        ])
        ->timeout(60)
        ->get($this->baseUrl . '/mensajeria-data/mensajes', [
            'campania' => $campaniaId,
        ]);

    if (!$response->successful()) {
        return [];
    }

    return $response->json() ?? [];
}
    public function obtenerEstadisticas($campaniaId)
    {
        $mensajes = $this->obtenerMensajesPorCampania($campaniaId);

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
}
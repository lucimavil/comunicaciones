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

    public function obtenerJobsProgramados()
    {
         return $this->http()->get($this->baseUrl . '/scheduled-jobs');
    }
}
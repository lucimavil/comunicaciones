<?php

namespace App\Console\Commands;

use App\Models\Campania;
use App\Services\MensajeriaService;
use Illuminate\Console\Command;

class VerificarCampaniasEjecutando extends Command
{
    protected $signature = 'campanias:verificar';

    protected $description = 'Verifica campañas en ejecución contra la API de mensajería';

   public function handle(MensajeriaService $mensajeriaService)
{
    $this->info('Iniciando verificación...');

    $campanias = Campania::whereIn('estado', ['programada', 'ejecutando'])
        ->where('fecha_programada', '<=', now())
        ->get();

    $this->info('Campañas encontradas: ' . $campanias->count());

    foreach ($campanias as $campania) {

        $this->info("Consultando campaña {$campania->id}");

        try {
            $this->info("Consultando campaña {$campania->id}");
            $response = $mensajeriaService
                ->obtenerCampania($campania->id);

            if (!$response->successful()) {
                $this->error("Error consultando campaña {$campania->id}");
                continue;
            }

            $data = $response->json();

            $status = $data['status'] ?? null;

            $this->info("Estado API: {$status}");

            if ($status === 'SENT') {
                $campania->update([
                    'estado' => 'finalizada',
                    'fecha_finalizacion' => now(),
                ]);
            } else {
                $campania->update([
                    'estado' => 'ejecutando',
                ]);
            }

        } catch (\Throwable $e) {

            $this->error(
                "Error campaña {$campania->id}: {$e->getMessage()}"
            );
        }
    }

    return self::SUCCESS;
}
}

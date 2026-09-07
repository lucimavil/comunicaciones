<?php

namespace App\Http\Controllers;

use App\Services\InterconsultaService;

class InterconsultaController extends Controller
{
    public function index(
        InterconsultaService $service
    ) {
        $interconsultas = collect(
            $service->obtenerInterconsultas()
        )
        ->map(function ($item) use ($service) {

            $item['ESTADO_TEXTO'] =
                $service->estadoInterconsulta($item);

            return $item;

        })
        ->sortByDesc('FECHA_INTERCONSULTA')
        ->values();

        $resumen = $service->obtenerResumen();

        return view(
            'interconsultas.index',
            compact(
                'interconsultas',
                'resumen'
            )
        );
    }


    public function show(
        int $id,
        InterconsultaService $service
    ) {
        $interconsulta =
            $service->obtenerInterconsulta($id);

        if (!$interconsulta) {
            abort(
                404,
                'Interconsulta no encontrada.'
            );
        }

        $interconsulta['ESTADO_TEXTO'] =
            $service->estadoInterconsulta(
                $interconsulta
            );

        $mensajes = $service
            ->obtenerMensajesInterconsulta($id);

        $stats = $this->estadisticasMensajes(
            $mensajes
        );

        return view(
            'interconsultas.show',
            compact(
                'interconsulta',
                'mensajes',
                'stats'
            )
        );
    }


    private function estadisticasMensajes(
        array $mensajes
    ): array {

        $mensajes = collect($mensajes);

        $total = $mensajes->count();

        $leidos = $mensajes
            ->where('ESTADO', 4)
            ->count();

        $recibidos = $mensajes
            ->where('ESTADO', 3)
            ->count();

        $fallidos = $mensajes
            ->filter(fn ($m) =>
                in_array(
                    (int) ($m['ESTADO'] ?? 0),
                    [9, 11, 12]
                )
            )
            ->count();

        return [
            'total' => $total,
            'leidos' => $leidos,
            'recibidos' => $recibidos,
            'fallidos' => $fallidos,
            'tasa_lectura' =>
                $total > 0
                    ? round(
                        ($leidos / $total) * 100,
                        1
                    )
                    : 0,
        ];
    }
}
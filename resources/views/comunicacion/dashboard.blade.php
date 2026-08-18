@extends('layouts.app')

@section('content')

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                Dashboard de Comunicación
            </h2>

            <p class="text-muted mb-0">
                {{ $com->nombre ?? 'Detalle de resultados de la comunicación' }}
            </p>
        </div>

        <a
            href="{{ route('comunicacion.show', $com->id) }}"
            class="btn btn-outline-secondary"
        >
            Volver al detalle
        </a>
    </div>


    {{-- KPIS --}}
    <div class="row g-3 mb-4">

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">
                        Total mensajes
                    </div>

                    <div class="fs-2 fw-bold">
                        {{ number_format($total ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>


        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">
                        Leídos
                    </div>

                    <div class="fs-2 fw-bold text-success">
                        {{ number_format($leidos ?? 0, 0, ',', '.') }}
                    </div>

                    @php
                        $porcentajeLeidos =
                            ($total ?? 0) > 0
                                ? (($leidos ?? 0) / $total) * 100
                                : 0;
                    @endphp

                    <div class="small text-muted mt-1">
                        {{ number_format($porcentajeLeidos, 1, ',', '.') }}%
                        del total
                    </div>
                </div>
            </div>
        </div>


        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">
                        Enviados
                    </div>

                    <div class="fs-2 fw-bold text-primary">
                        {{ number_format($enviados ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>


        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">
                        Fallidos
                    </div>

                    <div class="fs-2 fw-bold text-danger">
                        {{ number_format($fallidos ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

    </div>


    {{-- GRAFICOS --}}
    <div class="row g-4 mb-4">

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <h5 class="fw-bold mb-4">
                        Estado de lectura
                    </h5>

                    <div style="height: 320px;">
                        <canvas id="lectura"></canvas>
                    </div>

                </div>

            </div>
        </div>


        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <h5 class="fw-bold mb-4">
                        Estados de mensajes
                    </h5>

                    <div style="height: 320px;">
                        <canvas id="estados"></canvas>
                    </div>

                </div>

            </div>
        </div>

    </div>


    {{-- DETALLE DESTINATARIOS --}}
    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h5 class="fw-bold mb-0">
                    Detalle de destinatarios
                </h5>

                <span class="badge bg-secondary">
                    {{ $com->destinatarios->count() }} destinatarios
                </span>

            </div>


            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>Nombre</th>

                            <th>Sector</th>

                            <th>Teléfono</th>

                            <th>Estado</th>

                            <th>Respuesta</th>

                            <th>Hora lectura</th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($com->destinatarios as $d)

                            <tr>

                                <td class="fw-semibold">
                                    {{ $d->user->name ?? '-' }}
                                </td>


                                <td>
                                    {{ $d->user->sector ?? '-' }}
                                </td>


                                <td>
                                    {{ $d->telefono ?? '-' }}
                                </td>


                                <td>

                                    @php
                                        $estado = strtolower(
                                            $d->estado ?? ''
                                        );

                                        $claseEstado = match($estado) {
                                            'leido',
                                            'leído' =>
                                                'bg-success',

                                            'recibido' =>
                                                'bg-info text-dark',

                                            'enviado' =>
                                                'bg-primary',

                                            'fallo',
                                            'fallido',
                                            'noaceptadometa' =>
                                                'bg-danger',

                                            'aceptadometa' =>
                                                'bg-secondary',

                                            default =>
                                                'bg-light text-dark'
                                        };
                                    @endphp

                                    <span class="badge {{ $claseEstado }}">
                                        {{ ucfirst($d->estado ?? '-') }}
                                    </span>

                                </td>


                                <td>
                                    {{ $d->respuesta ?: '-' }}
                                </td>


                                <td>
                                    @if($d->leido_at)

                                        {{ \Carbon\Carbon::parse($d->leido_at)->format('d/m/Y H:i') }}

                                    @else

                                        -

                                    @endif
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="text-center text-muted py-4"
                                >
                                    No hay destinatarios registrados para esta comunicación.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Lectura
    |--------------------------------------------------------------------------
    */

    const lecturaCanvas =
        document.getElementById('lectura');

    if (lecturaCanvas) {

        new Chart(lecturaCanvas, {

            type: 'doughnut',

            data: {

                labels: [
                    'Leídos',
                    'No leídos'
                ],

                datasets: [{
                    data: [
                        {{ (int) ($leidos ?? 0) }},
                        {{ max(0, (int) ($total ?? 0) - (int) ($leidos ?? 0)) }}
                    ]
                }]
            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {
                        position: 'bottom'
                    }

                }

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Estados generales
    |--------------------------------------------------------------------------
    */

    const estadosCanvas =
        document.getElementById('estados');

    if (estadosCanvas) {

        new Chart(estadosCanvas, {

            type: 'bar',

            data: {

                labels: [
                    'Enviados',
                    'Recibidos',
                    'Leídos',
                    'Fallidos'
                ],

                datasets: [{
                    label: 'Cantidad',

                    data: [
                        {{ (int) ($enviados ?? 0) }},
                        {{ (int) ($recibidos ?? 0) }},
                        {{ (int) ($leidos ?? 0) }},
                        {{ (int) ($fallidos ?? 0) }}
                    ]
                }]
            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                scales: {

                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }

                },

                plugins: {

                    legend: {
                        display: false
                    }

                }

            }

        });

    }

});
</script>

@endsection
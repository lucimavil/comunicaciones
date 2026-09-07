@extends('layouts.app')

@section('content')

<div class="container py-4">

    {{-- CABECERA --}}
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

        {{-- TOTAL --}}
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


        {{-- ACEPTADOS META --}}
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="text-muted small mb-1">
                        Aceptados por Meta
                    </div>

                    <div class="fs-2 fw-bold text-secondary">
                        {{ number_format($aceptados ?? 0, 0, ',', '.') }}
                    </div>

                </div>
            </div>
        </div>


        {{-- ENVIADOS --}}
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


        {{-- RECIBIDOS --}}
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="text-muted small mb-1">
                        Recibidos
                    </div>

                    <div class="fs-2 fw-bold text-info">
                        {{ number_format($recibidos ?? 0, 0, ',', '.') }}
                    </div>

                </div>
            </div>
        </div>


        {{-- LEIDOS --}}
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="text-muted small mb-1">
                        Leídos
                    </div>

                    <div class="fs-2 fw-bold text-success">
                        {{ number_format($leidos ?? 0, 0, ',', '.') }}
                    </div>

                    <div class="small text-muted mt-1">
                        {{ number_format($tasaLectura ?? 0, 1, ',', '.') }}%
                        de lectura
                    </div>

                </div>
            </div>
        </div>


        {{-- CONFIRMADOS --}}
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="text-muted small mb-1">
                        Confirmados
                    </div>

                    <div class="fs-2 fw-bold text-success">
                        {{ number_format($confirmados ?? 0, 0, ',', '.') }}
                    </div>

                </div>
            </div>
        </div>


        {{-- CANCELADOS --}}
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="text-muted small mb-1">
                        Cancelados
                    </div>

                    <div class="fs-2 fw-bold text-warning">
                        {{ number_format($cancelados ?? 0, 0, ',', '.') }}
                    </div>

                </div>
            </div>
        </div>


        {{-- FALLIDOS --}}
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

        {{-- GRAFICO LECTURA --}}
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


        {{-- GRAFICO ESTADOS --}}
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


    {{-- DETALLE DE DESTINATARIOS --}}
    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div
                class="d-flex justify-content-between align-items-center mb-4"
            >

                <div>
                    <h5 class="fw-bold mb-1">
                        Detalle de destinatarios
                    </h5>

                    <div class="small text-muted">
                        Estado individual de los mensajes enviados.
                    </div>
                </div>

                <span class="badge bg-secondary">
                    {{ count($detalle ?? []) }} destinatarios
                </span>

            </div>


            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>Nombre</th>

                            <th>Código persona</th>

                            <th>Teléfono</th>

                            <th>Estado</th>

                            <th>Fecha envío</th>

                            <th>Fecha lectura</th>

                        </tr>

                    </thead>


                    <tbody>

                    @forelse($detalle ?? [] as $d)

                        @php

                            $estadoNumero =
                                (int) ($d['ESTADO'] ?? 0);

                            $estadoTexto = match($estadoNumero) {

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


                            $claseEstado = match($estadoNumero) {

                                1 =>
                                    'bg-secondary',

                                2 =>
                                    'bg-primary',

                                3 =>
                                    'bg-info text-dark',

                                4 =>
                                    'bg-success',

                                5 =>
                                    'bg-success',

                                6, 7 =>
                                    'bg-warning text-dark',

                                8 =>
                                    'bg-warning text-dark',

                                9, 11 =>
                                    'bg-danger',

                                10 =>
                                    'bg-dark',

                                default =>
                                    'bg-light text-dark',
                            };

                        @endphp


                        <tr>

                            {{-- NOMBRE --}}
                            <td class="fw-semibold">

                                {{ $d['NOMBRE_PERSONA'] ?? '-' }}

                            </td>


                            {{-- CODIGO PERSONA --}}
                            <td>

                                {{ $d['CODIGO_PERSONA'] ?? '-' }}

                            </td>


                            {{-- TELEFONO --}}
                            <td>

                                {{ $d['PHONE_NUMBER'] ?? '-' }}

                            </td>


                            {{-- ESTADO --}}
                            <td>

                                <span class="badge {{ $claseEstado }}">

                                    {{ $estadoTexto }}

                                </span>

                            </td>


                            {{-- FECHA ENVIO --}}
                            <td>

                                @if(!empty($d['FECHA_ENVIO']))

                                    {{ \Carbon\Carbon::parse(
                                        $d['FECHA_ENVIO']
                                    )->format('d/m/Y H:i') }}

                                @else

                                    -

                                @endif

                            </td>


                            {{-- FECHA LEIDO --}}
                            <td>

                                @if(!empty($d['FECHA_LEIDO']))

                                    {{ \Carbon\Carbon::parse(
                                        $d['FECHA_LEIDO']
                                    )->format('d/m/Y H:i') }}

                                @else

                                    -

                                @endif

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="text-center text-muted py-5"
                            >

                                No hay mensajes registrados para esta comunicación.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


{{-- CHART JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | GRAFICO DE LECTURA
    |--------------------------------------------------------------------------
    */

    const lecturaCanvas =
        document.getElementById('lectura');


    if (lecturaCanvas) {

        const total =
            {{ (int) ($total ?? 0) }};

        const leidos =
            {{ (int) ($leidos ?? 0) }};

        const noLeidos =
            Math.max(
                0,
                total - leidos
            );


        new Chart(
            lecturaCanvas,
            {

                type: 'doughnut',

                data: {

                    labels: [
                        'Leídos',
                        'No leídos'
                    ],

                    datasets: [
                        {
                            data: [
                                leidos,
                                noLeidos
                            ]
                        }
                    ]

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

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | GRAFICO DE ESTADOS
    |--------------------------------------------------------------------------
    */

    const estadosCanvas =
        document.getElementById('estados');


    if (estadosCanvas) {

        new Chart(
            estadosCanvas,
            {

                type: 'bar',

                data: {

                    labels: [
                        'Aceptados',
                        'Enviados',
                        'Recibidos',
                        'Leídos',
                        'Confirmados',
                        'Cancelados',
                        'Fallidos'
                    ],

                    datasets: [
                        {

                            label: 'Cantidad',

                            data: [

                                {{ (int) ($aceptados ?? 0) }},

                                {{ (int) ($enviados ?? 0) }},

                                {{ (int) ($recibidos ?? 0) }},

                                {{ (int) ($leidos ?? 0) }},

                                {{ (int) ($confirmados ?? 0) }},

                                {{ (int) ($cancelados ?? 0) }},

                                {{ (int) ($fallidos ?? 0) }}

                            ]

                        }
                    ]

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

            }
        );

    }

});

</script>

@endsection
@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <span class="text-muted small">
                Mensajería clínica
            </span>

            <h2 class="fw-bold mb-0">
                Dashboard de Interconsultas
            </h2>
        </div>

    </div>


    {{-- KPIS OPERATIVOS --}}
    <div class="row g-3 mb-4">

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="text-muted small">
                        Total interconsultas
                    </div>

                    <div class="fs-2 fw-bold">
                        {{ $resumen['total'] ?? 0 }}
                    </div>

                </div>
            </div>
        </div>


        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="text-muted small">
                        Pendientes
                    </div>

                    <div class="fs-2 fw-bold text-warning">
                        {{ $resumen['pendientes'] ?? 0 }}
                    </div>

                </div>
            </div>
        </div>


        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="text-muted small">
                        En proceso
                    </div>

                    <div class="fs-2 fw-bold text-primary">
                        {{ $resumen['en_proceso'] ?? 0 }}
                    </div>

                </div>
            </div>
        </div>


        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="text-muted small">
                        Respondidas
                    </div>

                    <div class="fs-2 fw-bold text-success">
                        {{ $resumen['respondidas'] ?? 0 }}
                    </div>

                </div>
            </div>
        </div>

    </div>


    {{-- TIEMPOS --}}
    <div class="row g-3 mb-4">

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small">
                        Tiempo promedio hasta toma
                    </div>

                    <div class="fs-3 fw-bold">
                        {{ number_format(
                            $resumen['promedio_toma'] ?? 0,
                            1,
                            ',',
                            '.'
                        ) }}
                        min
                    </div>

                </div>

            </div>
        </div>


        <div class="col-md-6">
            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small">
                        Tiempo promedio hasta respuesta
                    </div>

                    <div class="fs-3 fw-bold">
                        {{ number_format(
                            $resumen['promedio_respuesta'] ?? 0,
                            1,
                            ',',
                            '.'
                        ) }}
                        min
                    </div>

                </div>

            </div>
        </div>

    </div>


    {{-- MENSAJERIA --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <h5 class="fw-bold mb-3">
                Mensajería WhatsApp
            </h5>

            <div class="row text-center g-3">

                <div class="col">
                    <div class="text-muted small">
                        Mensajes
                    </div>

                    <div class="fs-4 fw-bold">
                        {{ $resumen['mensajes_total'] ?? 0 }}
                    </div>
                </div>

                <div class="col">
                    <div class="text-muted small">
                        Recibidos
                    </div>

                    <div class="fs-4 fw-bold text-info">
                        {{ $resumen['recibidos'] ?? 0 }}
                    </div>
                </div>

                <div class="col">
                    <div class="text-muted small">
                        Leídos
                    </div>

                    <div class="fs-4 fw-bold text-success">
                        {{ $resumen['leidos'] ?? 0 }}
                    </div>
                </div>

                <div class="col">
                    <div class="text-muted small">
                        Fallidos
                    </div>

                    <div class="fs-4 fw-bold text-danger">
                        {{ $resumen['fallidos'] ?? 0 }}
                    </div>
                </div>

                <div class="col">
                    <div class="text-muted small">
                        Tasa lectura
                    </div>

                    <div class="fs-4 fw-bold">
                        {{ $resumen['tasa_lectura'] ?? 0 }}%
                    </div>
                </div>

            </div>

        </div>

    </div>


    {{-- TABLA --}}
    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="fw-bold mb-0">
                    Interconsultas
                </h5>

                <span class="badge bg-secondary">
                    {{ $interconsultas->count() }}
                </span>

            </div>


            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">

                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Médico origen</th>
                        <th>Destino</th>
                        <th>Estado</th>
                        <th>Destinatarios</th>
                        <th>Fecha toma</th>
                        <th>Fecha respuesta</th>
                        <th></th>
                    </tr>

                    </thead>

                    <tbody>

                    @forelse($interconsultas as $i)

                        @php

                            $estado =
                                $i['ESTADO_TEXTO'];

                            $claseEstado =
                                match($estado) {
                                    'Respondida' =>
                                        'bg-success',

                                    'En proceso' =>
                                        'bg-primary',

                                    default =>
                                        'bg-warning text-dark',
                                };

                            $destino =
                                $i['ESPECIALIDAD_DESTINO']
                                ??
                                $i['INDIVIDUAL_DESTINO']
                                ??
                                $i['EQUIPO_DESTINO']
                                ??
                                '-';

                        @endphp


                        <tr>

                            <td class="fw-semibold">
                                {{ $i['ID_INTERCONSULTA_REQ'] }}
                            </td>

                            <td>
                                {{ !empty($i['FECHA_INTERCONSULTA'])
                                    ? \Carbon\Carbon::parse(
                                        $i['FECHA_INTERCONSULTA']
                                      )->format('d/m/Y H:i')
                                    : '-'
                                }}
                            </td>

                            <td>
                                {{ $i['MEDICO_ORIGEN'] ?? '-' }}
                            </td>

                            <td>
                                {{ $destino }}
                            </td>

                            <td>
                                <span class="badge {{ $claseEstado }}">
                                    {{ $estado }}
                                </span>
                            </td>

                            <td>
                                {{ $i['CANTIDAD_DESTINATARIOS'] ?? 0 }}
                            </td>

                            <td>
                                {{ !empty($i['FECHA_TOMA'])
                                    ? \Carbon\Carbon::parse(
                                        $i['FECHA_TOMA']
                                      )->format('d/m/Y H:i')
                                    : '-'
                                }}
                            </td>

                            <td>
                                {{ !empty($i['FECHA_RES'])
                                    ? \Carbon\Carbon::parse(
                                        $i['FECHA_RES']
                                      )->format('d/m/Y H:i')
                                    : '-'
                                }}
                            </td>

                            <td>

                                <a
                                    href="{{ route(
                                        'interconsultas.show',
                                        $i['ID_INTERCONSULTA_REQ']
                                    ) }}"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Ver detalle
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="9"
                                class="text-center text-muted py-4"
                            >
                                No hay interconsultas.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection
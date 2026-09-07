@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

    {{-- CABECERA --}}
<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <span class="text-muted small">
            Interconsultas / Detalle
        </span>

        <h2 class="fw-bold mb-0">
            Interconsulta #{{ $interconsulta['ID_INTERCONSULTA_REQ'] }}
        </h2>
    </div>

    <a href="{{ route('interconsultas.index') }}"
       class="btn btn-outline-secondary">

        <i class="bi bi-x-lg me-2"></i>
        Cerrar detalle

    </a>

</div>


    {{-- DATOS GENERALES --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <h5 class="fw-bold mb-4">
                Información de la interconsulta
            </h5>

            <div class="row g-4">

                {{-- FECHA --}}
                <div class="col-md-3">
                    <div class="text-muted small">
                        Fecha de solicitud
                    </div>

                    <div class="fw-semibold">
                        @if(!empty($interconsulta['FECHA_INTERCONSULTA']))
                            {{ \Carbon\Carbon::parse(
                                $interconsulta['FECHA_INTERCONSULTA']
                            )->format('d/m/Y H:i') }}
                        @else
                            -
                        @endif
                    </div>
                </div>


                {{-- MEDICO ORIGEN --}}
                <div class="col-md-3">
                    <div class="text-muted small">
                        Médico origen
                    </div>

                    <div class="fw-semibold">
                        {{ $interconsulta['MEDICO_ORIGEN'] ?? '-' }}
                    </div>
                </div>


                {{-- ESPECIALIDAD ORIGEN --}}
                <div class="col-md-3">
                    <div class="text-muted small">
                        Especialidad origen
                    </div>

                    <div class="fw-semibold">
                        {{ $interconsulta['ESPECIALIDAD_ORIGEN'] ?? '-' }}
                    </div>
                </div>


                {{-- ESTADO --}}
                <div class="col-md-3">

                    <div class="text-muted small">
                        Estado
                    </div>

                    @php
                        $estado = $interconsulta['ESTADO_TEXTO'] ?? 'Pendiente';

                        $claseEstado = match($estado) {
                            'Respondida' => 'bg-success',
                            'En proceso' => 'bg-primary',
                            default => 'bg-warning text-dark',
                        };
                    @endphp

                    <span class="badge {{ $claseEstado }}">
                        {{ $estado }}
                    </span>

                </div>


                {{-- TIPO --}}
                <div class="col-md-3">
                    <div class="text-muted small">
                        Tipo de interconsulta
                    </div>

                    <div class="fw-semibold">
                        {{ $interconsulta['TIPO_INTERCONSULTA'] ?? '-' }}
                    </div>
                </div>


                {{-- ESPECIALIDAD DESTINO --}}
                <div class="col-md-3">
                    <div class="text-muted small">
                        Especialidad destino
                    </div>

                    <div class="fw-semibold">
                        {{ $interconsulta['ESPECIALIDAD_DESTINO'] ?? '-' }}
                    </div>
                </div>


                {{-- INDIVIDUAL --}}
                <div class="col-md-3">
                    <div class="text-muted small">
                        Profesional destino
                    </div>

                    <div class="fw-semibold">
                        {{ $interconsulta['INDIVIDUAL_DESTINO'] ?? '-' }}
                    </div>
                </div>


                {{-- EQUIPO --}}
                <div class="col-md-3">
                    <div class="text-muted small">
                        Equipo destino
                    </div>

                    <div class="fw-semibold">
                        {{ $interconsulta['EQUIPO_DESTINO'] ?? '-' }}
                    </div>
                </div>


                {{-- MEDICO TOMA --}}
                <div class="col-md-3">
                    <div class="text-muted small">
                        Médico que toma
                    </div>

                    <div class="fw-semibold">
                        {{ $interconsulta['MEDICO_TOMA'] ?? '-' }}
                    </div>
                </div>


                {{-- FECHA TOMA --}}
                <div class="col-md-3">
                    <div class="text-muted small">
                        Fecha de toma
                    </div>

                    <div class="fw-semibold">

                        @if(!empty($interconsulta['FECHA_TOMA']))

                            {{ \Carbon\Carbon::parse(
                                $interconsulta['FECHA_TOMA']
                            )->format('d/m/Y H:i') }}

                        @else

                            <span class="text-warning">
                                Pendiente
                            </span>

                        @endif

                    </div>
                </div>


                {{-- MEDICO RESPONDE --}}
                <div class="col-md-3">
                    <div class="text-muted small">
                        Médico que responde
                    </div>

                    <div class="fw-semibold">
                        {{ $interconsulta['MEDICO_RESPONDE'] ?? '-' }}
                    </div>
                </div>


                {{-- FECHA RESPUESTA --}}
                <div class="col-md-3">
                    <div class="text-muted small">
                        Fecha de respuesta
                    </div>

                    <div class="fw-semibold">

                        @if(!empty($interconsulta['FECHA_RES']))

                            {{ \Carbon\Carbon::parse(
                                $interconsulta['FECHA_RES']
                            )->format('d/m/Y H:i') }}

                        @else

                            <span class="text-warning">
                                Pendiente
                            </span>

                        @endif

                    </div>
                </div>


                {{-- DESTINATARIOS --}}
                <div class="col-md-3">
                    <div class="text-muted small">
                        Cantidad de destinatarios
                    </div>

                    <div class="fw-semibold">
                        {{ $interconsulta['CANTIDAD_DESTINATARIOS'] ?? 0 }}
                    </div>
                </div>

            </div>


            {{-- MOTIVO --}}
            <hr class="my-4">

            <div>
                <div class="text-muted small mb-2">
                    Motivo de la interconsulta
                </div>

                <div class="bg-light rounded-3 p-3"
                     style="white-space: pre-line;">
                    {{ $interconsulta['MENSAJERIA_MOTIVO'] ?? '-' }}
                </div>
            </div>

        </div>

    </div>


    {{-- TRAZABILIDAD --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <h5 class="fw-bold mb-4">
                Trazabilidad
            </h5>

            <div class="row text-center g-3">

                {{-- SOLICITADA --}}
                <div class="col-md-4">

                    <div class="border rounded-3 p-3 h-100">

                        <i class="bi bi-file-earmark-medical fs-3 text-primary"></i>

                        <div class="fw-bold mt-2">
                            Solicitada
                        </div>

                        <div class="text-muted small">

                            @if(!empty($interconsulta['FECHA_INTERCONSULTA']))

                                {{ \Carbon\Carbon::parse(
                                    $interconsulta['FECHA_INTERCONSULTA']
                                )->format('d/m/Y H:i') }}

                            @else

                                -

                            @endif

                        </div>

                    </div>

                </div>


                {{-- TOMADA --}}
                <div class="col-md-4">

                    <div class="border rounded-3 p-3 h-100">

                        <i class="bi bi-person-check fs-3 text-primary"></i>

                        <div class="fw-bold mt-2">
                            Tomada
                        </div>

                        <div class="text-muted small">

                            @if(!empty($interconsulta['FECHA_TOMA']))

                                {{ \Carbon\Carbon::parse(
                                    $interconsulta['FECHA_TOMA']
                                )->format('d/m/Y H:i') }}

                            @else

                                Pendiente

                            @endif

                        </div>

                    </div>

                </div>


                {{-- RESPONDIDA --}}
                <div class="col-md-4">

                    <div class="border rounded-3 p-3 h-100">

                        <i class="bi bi-check-circle fs-3 text-success"></i>

                        <div class="fw-bold mt-2">
                            Respondida
                        </div>

                        <div class="text-muted small">

                            @if(!empty($interconsulta['FECHA_RES']))

                                {{ \Carbon\Carbon::parse(
                                    $interconsulta['FECHA_RES']
                                )->format('d/m/Y H:i') }}

                            @else

                                Pendiente

                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- MENSAJES ASOCIADOS --}}
    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>
                    <h5 class="fw-bold mb-1">
                        Mensajes de WhatsApp
                    </h5>

                    <div class="text-muted small">
                        Notificaciones asociadas a esta interconsulta.
                    </div>
                </div>

                <span class="badge bg-secondary">
                    {{ count($mensajes ?? []) }} mensajes
                </span>

            </div>


            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">

                        <tr>
                            <th>Destinatario</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Enviado</th>
                            <th>Recibido</th>
                            <th>Leído</th>
                            <th>Error</th>
                        </tr>

                    </thead>


                    <tbody>

                    @forelse($mensajes ?? [] as $m)

                        @php

                            $estadoNumero =
                                (int) ($m['ESTADO'] ?? 0);

                            $estadoTexto = match($estadoNumero) {
                                1 => 'Aceptado Meta',
                                2 => 'Enviado',
                                3 => 'Recibido',
                                4 => 'Leído',
                                5 => 'Confirmado',
                                6 => 'Cancelado',
                                7 => 'Cancelado sistema',
                                8 => 'Revisar',
                                9 => 'Fallo',
                                10 => 'Eliminado',
                                11 => 'No aceptado Meta',
                                12 => 'Error técnico',
                                default => 'Pendiente',
                            };

                            $estadoClase = match($estadoNumero) {
                                1 => 'bg-secondary',
                                2 => 'bg-primary',
                                3 => 'bg-info text-dark',
                                4 => 'bg-success',
                                5 => 'bg-success',
                                6, 7 => 'bg-warning text-dark',
                                8 => 'bg-warning text-dark',
                                9, 11, 12 => 'bg-danger',
                                10 => 'bg-dark',
                                default => 'bg-light text-dark',
                            };

                        @endphp


                        <tr>

                            <td class="fw-semibold">
                                {{ $m['NOMBRE_PERSONA'] ?? '-' }}
                            </td>

                            <td>
                                {{ $m['PHONE_NUMBER'] ?? '-' }}
                            </td>

                            <td>

                                <span class="badge {{ $estadoClase }}">
                                    {{ $estadoTexto }}
                                </span>

                            </td>

                            <td>
                                @if(!empty($m['FECHA_SEND']))

                                    {{ \Carbon\Carbon::parse(
                                        $m['FECHA_SEND']
                                    )->format('d/m/Y H:i:s') }}

                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                @if(!empty($m['FECHA_RECIBIDO']))

                                    {{ \Carbon\Carbon::parse(
                                        $m['FECHA_RECIBIDO']
                                    )->format('d/m/Y H:i:s') }}

                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                @if(!empty($m['FECHA_LEIDO']))

                                    {{ \Carbon\Carbon::parse(
                                        $m['FECHA_LEIDO']
                                    )->format('d/m/Y H:i:s') }}

                                @else
                                    -
                                @endif
                            </td>

                            <td class="small">

                                @if(!empty($m['MOTIVO_FAILED']))

                                    <span class="text-danger">
                                        {{ $m['MOTIVO_FAILED'] }}
                                    </span>

                                @else
                                    -
                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="text-center text-muted py-4"
                            >
                                No hay mensajes asociados a esta interconsulta.
                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>
<div class="d-flex justify-content-end mt-4">

    <a href="{{ route('interconsultas.index') }}"
       class="btn btn-outline-secondary">

        <i class="bi bi-arrow-left me-2"></i>
        Volver a Interconsultas

    </a>

</div>
</div>

@endsection
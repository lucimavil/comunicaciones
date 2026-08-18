@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
  <div class="hero-card-comunicaciones mb-4">
                <span class="badge text-bg-light text-primary d-inline-flex align-items-center px-3 py-2 rounded-pill mb-3" style="width: fit-content;">
                    <i class="bi bi-whatsapp me-2"></i>Comunicaciones
                </span>
                <h1>Bienvenido al módulo de Comunicaciones</h1>
                <p>Gestioná la comunicación interna, segmentá personal, programá envíos y seguí resultados desde un único tablero.</p>
            </div>

          

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="section-title mb-0">Tablero de control</h3>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary rounded-3">
                        <i class="bi bi-funnel me-2"></i>Filtrar
                    </button>
                    <a  href="{{ route('comunicacion.create') }}">
                    <button class="btn btn-primary rounded-3 px-3">
                        <i class="bi bi-plus-lg me-2"></i>Nueva comunicación
                    </button></a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-label">Comunicaciones activas</div>
                        <div class="stat-value">{{ $comunicacionActivas }}</div>
                        
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-label">Programadas</div>
                        <div class="stat-value">{{ $comunicacionProgramadas }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-label">Mensajes enviados</div>
                        <div class="stat-value">{{ $resumenMensajeria['mensajes_enviados'] ?? 'Sin mensajes enviados' }}
</div>
          
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-label">Tasa de lectura</div>
                        <div class="stat-value">{{ $resumenMensajeria['tasa_lectura'] ?? 'Sin mensajes enviados' }}%</div>
                        <p class="stat-help">Promedio global de comunicaciones finalizadas.</p>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-xl-8">
                    <div class="panel-card h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="fw-bold mb-0">Distribución de mensajes por estado</h4>
                            <span class="badge rounded-pill text-bg-light">Últimos 30 días</span>
                        </div>
                        <div class="chart-placeholder">
                           <div id="graficoGeneralEstados" style="height:320px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="panel-card h-100">
                        <h4 class="fw-bold mb-3">Resumen rápido</h4>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-secondary">Aceptados por Meta</span>
                            <strong>{{ $resumenMensajeria['aceptadas_meta'] ?? 'Sin mensajes enviados' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-secondary">Enviados</span>
                            <strong>{{ $resumenMensajeria['enviados'] ?? 'Sin mensajes enviados' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-secondary">Recibidos</span>
                            <strong>{{ $resumenMensajeria['recibidos'] ?? 'Sin mensajes enviados' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-secondary">Leídos</span>
                            <strong>{{ $resumenMensajeria['leidos'] ?? 'Sin mensajes enviados' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between pt-2">
                            <span class="text-secondary">Fallos</span>
                            <strong class="text-danger">{{ $resumenMensajeria['fallos'] ?? 'Sin mensajes enviados' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

          <div class="panel-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Últimas Comunicaciones</h4>
        <a href="{{ route('comunicacion.index') }}" class="btn btn-sm btn-light border rounded-3">
            Ver todas
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                     <th>Id</th>
                    <th>Comunicación</th>
                    <th>Solicitante</th>
                    <th>Fecha Creación</th>
                    <th>Estado</th>
                    <th>Fecha Programación</th>
                    <th>Responsable</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($comunicacion as $comunicacion)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $comunicacion->id }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $comunicacion->titulo }}</div>
                            <div class="small text-secondary">
                                {{ $comunicacion->descripcion }}
                            </div>
                        </td>

                        <td>
                            {{ $comunicacion->solicitante ?? '-' }}
                        </td>

                        <td>
                            {{ optional($comunicacion->created_at)->format('d/m/Y H:i') }}
                        </td>

                        <td>
                            @php
                                $estado = strtolower($comunicacion->estado);
                            @endphp

                            @if($estado === 'finalizada')
                                <span class="badge bg-success text-dark  rounded-pill">Finalizada</span>
                            @elseif($estado === 'programada')
                                <span class="badge bg-warning text-dark rounded-pill">Programada</span>
                            @elseif($estado === 'borrador')
                                <span class="badge bg-info text-dark rounded-pill">Borrador</span>
                            
                            @else
                                <span class="badge bg-secondary rounded-pill">{{ ucfirst($comunicacion->estado) }}</span>
                            @endif
                        </td>
                          <td>
                            {{ optional($comunicacion->fecha_programada)->format('d/m/Y H:i') }}
                        </td>
                        <td>
                          {{ $comunicacion->responsable->name ?? '-' }}
                        </td>
                        <td class="text-end">
                          
                           @if(in_array($estado, ['programada', 'borrador']) && $comunicacion->puedeEditarse())
                                <a href="{{ route('comunicacion.edit', $comunicacion->id) }}"
                                class="btn btn-sm btn-light border"
                                title="Editar comunicación">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endif
                            @if(in_array($estado, ['programada', 'borrador']) && $comunicacion->puedeEditarse())
                                

                              <button
                                type="button"
                                class="btn btn-sm btn-light border text-danger"
                                title="Eliminar comunicación"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEliminarComunicacion{{ $comunicacion->id }}"
                            >
                                <i class="bi bi-trash"></i>
                            </button>
                               
                            @endif
                             <a href="{{ route('comunicacion.show', $comunicacion->id) }}" class="btn btn-sm btn-light border">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($comunicacion->estado === 'finalizada')
                                    <a href="{{ route('comunicacion.dashboard', $comunicacion->id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                        Ver dashboard
                                    </a>
                                @endif
                        </td>
                    </tr>
                  <div class="modal fade"
     id="modalEliminarComunicacion{{ $comunicacion->id }}"
     tabindex="-1"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar eliminación</h5>
                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-start">
                ¿Estás segura de que querés eliminar la comunicación
                <strong>{{ $comunicacion->titulo }}</strong>?
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-light border"
                        data-bs-dismiss="modal">
                    Cancelar
                </button>

                <form action="{{ route('comunicacion.destroy', $comunicacion->id) }}"
                      method="POST"
                      class="d-inline">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger">
                        Sí, eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">
                            No hay comunicaciones registradas
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.querySelector("#graficoGeneralEstados");

    console.log('Elemento grafico:', el);

    if (!el) {
        console.log('No existe #graficoGeneralEstados');
        return;
    }

    const series = [
        {{ (int) ($resumenMensajeria['aceptadas_meta'] ?? 0) }},
        {{ (int) ($resumenMensajeria['enviados'] ?? 0) }},
        {{ (int) ($resumenMensajeria['recibidos'] ?? 0) }},
        {{ (int) ($resumenMensajeria['leidos'] ?? 0) }},
        {{ (int) ($resumenMensajeria['fallos'] ?? 0) }}
    ];

    console.log('Series:', series);

    const options = {
        chart: {
            type: 'donut',
            height: 320
        },
        series: series,
        labels: [
            'Aceptados por Meta',
            'Enviados',
            'Recibidos',
            'Leídos',
            'Fallos'
        ],
        legend: {
            position: 'bottom'
        },
        noData: {
            text: 'Sin datos disponibles'
        }
    };

    new ApexCharts(el, options).render();
});
</script>
@if($errors->has('general'))
<div class="modal fade" id="modalMensajeComunicaciones" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">No se puede editar</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="mb-0">{{ $errors->first('general') }}</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    Aceptar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = new bootstrap.Modal(document.getElementById('modalMensajeCampania'));
    modal.show();
});
</script>
@endif
<div class="modal fade" id="modalMensajeComunicaciones" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" :class="modalTipoClase">
                <h5 class="modal-title" x-text="modalTitulo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p class="mb-0" x-text="modalMensaje"></p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    Aceptar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
           

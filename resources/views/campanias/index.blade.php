@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
  <div class="hero-card mb-4">
                <span class="badge text-bg-light text-primary d-inline-flex align-items-center px-3 py-2 rounded-pill mb-3" style="width: fit-content;">
                    <i class="bi bi-whatsapp me-2"></i>Campañas
                </span>
                <h1>Bienvenido al módulo de Campañas</h1>
                <p>Gestioná campañas, segmentá pacientes, programá envíos y seguí resultados desde un único tablero.</p>
            </div>

            <!--div class="info-alert mb-4">
                <i class="bi bi-exclamation-triangle fs-4"></i>
                <div>
                    <div class="fw-bold">Atención: campaña pendiente de aprobación</div>
                    <div class="small">La campaña <strong>Control cardiológico abril</strong> quedó en revisión porque supera el límite configurado de pacientes alcanzados.</div>
                </div>
            </div-->

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="section-title mb-0">Tablero de control</h3>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary rounded-3">
                        <i class="bi bi-funnel me-2"></i>Filtrar
                    </button>
                    <a  href="{{ route('campanias.create') }}">
                    <button class="btn btn-primary rounded-3 px-3">
                        <i class="bi bi-plus-lg me-2"></i>Nueva campaña
                    </button></a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-label">Campañas activas</div>
                        <div class="stat-value">{{ $campaniasActivas }}</div>
                        
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-label">Programadas</div>
                        <div class="stat-value">{{ $campaniasProgramadas }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-label">Mensajes enviados</div>
                        <div class="stat-value">{{ $resumenMensajeria['mensajes_enviados'] }}</div>
          
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-label">Tasa de lectura</div>
                        <div class="stat-value">{{ $resumenMensajeria['tasa_lectura'] }}%</div>
                        <p class="stat-help">Promedio global de campañas finalizadas.</p>
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
                            <strong>{{ $resumenMensajeria['aceptadas_meta'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-secondary">Enviados</span>
                            <strong>{{ $resumenMensajeria['enviados'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-secondary">Recibidos</span>
                            <strong>{{ $resumenMensajeria['recibidos'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-secondary">Leídos</span>
                            <strong>{{ $resumenMensajeria['leidos'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between pt-2">
                            <span class="text-secondary">Fallos</span>
                            <strong class="text-danger">{{ $resumenMensajeria['fallos'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>

          <div class="panel-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Últimas campañas</h4>
        <a href="{{ route('campanias.index') }}" class="btn btn-sm btn-light border rounded-3">
            Ver todas
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                     <th>Id</th>
                    <th>Campaña</th>
                    <th>Solicitante</th>
                    <th>Fecha Creación</th>
                    <th>Estado</th>
                    <th>Fecha Programación</th>
                    <th>Responsable</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campanias as $campania)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $campania->id }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $campania->titulo }}</div>
                            <div class="small text-secondary">
                                {{ $campania->descripcion }}
                            </div>
                        </td>

                        <td>
                            {{ $campania->solicitante ?? '-' }}
                        </td>

                        <td>
                            {{ optional($campania->created_at)->format('d/m/Y H:i') }}
                        </td>

                        <td>
                            @php
                                $estado = strtolower($campania->estado);
                            @endphp

                            @if($estado === 'finalizada')
                                <span class="badge bg-success text-dark  rounded-pill">Finalizada</span>
                            @elseif($estado === 'programada')
                                <span class="badge bg-warning text-dark rounded-pill">Programada</span>
                            @elseif($estado === 'borrador')
                                <span class="badge bg-info text-dark rounded-pill">Borrador</span>
                            
                            @else
                                <span class="badge bg-secondary rounded-pill">{{ ucfirst($campania->estado) }}</span>
                            @endif
                        </td>
                          <td>
                            {{ optional($campania->fecha_programada)->format('d/m/Y H:i') }}
                        </td>
                        <td>
                          {{ $campania->responsable->name ?? '-' }}
                        </td>
                        <td class="text-end">
                          
                           @if(in_array($estado, ['programada', 'borrador']) && $campania->puedeEditarse())
                                <a href="{{ route('campanias.edit', $campania->id) }}"
                                class="btn btn-sm btn-light border"
                                title="Editar campaña">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endif
                            @if(in_array($estado, ['programada', 'borrador']) && $campania->puedeEditarse())
                                

                              <button
                                type="button"
                                class="btn btn-sm btn-light border text-danger"
                                title="Eliminar campaña"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEliminarCampania{{ $campania->id }}"
                            >
                                <i class="bi bi-trash"></i>
                            </button>
                               
                            @endif
                             <a href="{{ route('campanias.show', $campania->id) }}" class="btn btn-sm btn-light border">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($campania->estado === 'finalizada')
                                    <a href="{{ route('campanias.dashboard', $campania->id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                        Ver dashboard
                                    </a>
                                @endif
                        </td>
                    </tr>
                  <div class="modal fade"
     id="modalEliminarCampania{{ $campania->id }}"
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
                ¿Estás segura de que querés eliminar la campaña
                <strong>{{ $campania->titulo }}</strong>?
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-light border"
                        data-bs-dismiss="modal">
                    Cancelar
                </button>

                <form action="{{ route('campanias.destroy', $campania->id) }}"
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
                            No hay campañas registradas
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
<div class="modal fade" id="modalMensajeCampania" tabindex="-1" aria-hidden="true">
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
<div class="modal fade" id="modalMensajeCampania" tabindex="-1" aria-hidden="true">
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
           

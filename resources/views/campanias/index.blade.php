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
                            Montar Chart.js / ApexCharts con métricas de campaña
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
                    <th>Campaña</th>
                    <th>Solicitante</th>
                    <th>Fecha</th>
                    <th>Segmento</th>
                    <th>Pacientes</th>
                    <th>Estado</th>
                    <th>Responsable</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campanias as $campania)
                    <tr>
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
                            {{ optional($campania->fecha_programada ?? $campania->created_at)->format('d/m/Y H:i') }}
                        </td>

                        <td>
                            {{ $campania->segmento_descripcion ?? '-' }}
                        </td>

                        <td>
                            {{ number_format($campania->cantidad_pacientes ?? 0, 0, ',', '.') }}
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
                                <span class="badge bg-primary text-dark rounded-pill">Borrador</span>
                            @elseif($estado === 'cancelada')
                                <span class="badge bg-primary text-dark rounded-pill">Cancelada</span>
                            @else
                                <span class="badge bg-secondary rounded-pill">{{ ucfirst($campania->estado) }}</span>
                            @endif
                        </td>
                        <td>
                          {{ $campania->responsable->name ?? '-' }}
                        </td>
                        <td class="text-end">
                            @if($estado === 'finalizada')
                               
                            
                            @elseif($estado === 'programada')
                                <a href="{{ route('campanias.edit', $campania->id) }}" class="btn btn-sm btn-light border">
                                    <i class="bi bi-pencil"></i>
                                </a>
                              
                            @elseif($estado === 'borrador')
                                <a href="{{ route('campanias.edit', $campania->id) }}" class="btn btn-sm btn-light border">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                
                            @endif
                            <button
                                type="button"
                                class="btn btn-outline-danger btn-sm"
                                onclick="cancelarCampania({{ $campania->id }})">
                                Cancelar
                            </button>
                             <a href="{{ route('campanias.show', $campania->id) }}" class="btn btn-sm btn-light border">
                                    <i class="bi bi-eye"></i>
                                </a>
                        </td>
                    </tr>
                  
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

@endsection
           

@extends('layouts.app')

@section('content')
@php
    $total = $estadisticas['total'] ?? 0;
    $aceptados = $estadisticas['accepted'] ?? $estadisticas['aceptados'] ?? 0;
    $enviados = $estadisticas['sent'] ?? $estadisticas['enviados'] ?? 0;
    $recibidos = $estadisticas['received'] ?? $estadisticas['recibidos'] ?? 0;
    $leidos = $estadisticas['read'] ?? $estadisticas['leidos'] ?? 0;
    $fallos = $estadisticas['failed'] ?? $estadisticas['fallos'] ?? 0;

   $tasaLectura = $estadisticas['tasa_lectura'] ?? 0;
@endphp

<style>
    .dashboard-page {
        padding: 32px;
        background: #f8fafc;
        min-height: 100vh;
    }

    .dashboard-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
    }

    .dashboard-title-wrap {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .dashboard-back {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        border: 1px solid #e5e7eb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #0f172a;
        text-decoration: none;
        background: #fff;
    }

    .dashboard-title {
        font-size: 22px;
        font-weight: 700;
        margin: 0;
        color: #020617;
    }

    .dashboard-subtitle {
        margin: 4px 0 0;
        color: #475569;
        font-size: 15px;
    }

    .dashboard-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 12px;
        border-radius: 999px;
        background: #e2e8f0;
        color: #0f172a;
        font-size: 12px;
        font-weight: 700;
        margin-left: 10px;
    }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .kpi-card {
        min-height: 118px;
        padding: 22px 24px;
        border-radius: 16px;
        border: 1px solid #dbeafe;
        background: #fff;
    }

    .kpi-title {
        font-size: 13px;
        color: #334155;
        margin-bottom: 26px;
    }

    .kpi-value {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
    }

    .kpi-blue { background: #eef4ff; border-color: #bfdbfe; }
    .kpi-green { background: #ecfdf3; border-color: #bbf7d0; }
    .kpi-purple { background: #f7f0ff; border-color: #e9d5ff; }
    .kpi-orange { background: #fff7ed; border-color: #fed7aa; }
    .kpi-red { background: #fff1f2; border-color: #fecdd3; }

    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    .dashboard-card {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }

    .dashboard-card-header {
        padding: 22px 24px 0;
    }

    .dashboard-section-title {
        font-size: 18px;
        font-weight: 700;
        color: #020617;
        margin: 0;
    }

    .dashboard-card-body {
        padding: 24px;
    }

    .tasa-box {
        text-align: center;
        padding: 24px;
    }

    .tasa-value {
        font-size: 46px;
        font-weight: 800;
        color: #0f172a;
        margin: 8px 0;
    }

    .tasa-text {
        color: #64748b;
        margin: 0;
        font-size: 14px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e5e7eb;
        color: #475569;
        font-size: 14px;
    }

    .summary-row:last-child {
        border-bottom: none;
    }

    .summary-row strong {
        color: #0f172a;
    }

    .table-header-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 24px 24px 8px;
    }

    .export-actions {
        display: flex;
        gap: 8px;
    }

    .export-btn {
        border: 1px solid #e5e7eb;
        background: #fff;
        border-radius: 10px;
        padding: 8px 14px;
        font-weight: 700;
        text-decoration: none;
        color: #0f172a;
        font-size: 14px;
    }

    .export-btn:hover {
        background: #f8fafc;
        color: #0f172a;
    }

    .detail-table-wrap {
        padding: 16px 24px 24px;
        overflow-x: auto;
    }

    .detail-table {
        width: 100%;
        border-collapse: collapse;
    }

    .detail-table th {
        font-size: 15px;
        color: #020617;
        padding: 16px 12px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
    }

    .detail-table td {
        padding: 16px 12px;
        border-bottom: 1px solid #e5e7eb;
        color: #0f172a;
        font-size: 14px;
    }

    .status-badge {
        display: inline-flex;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
    }

    .status-enviado,
    .status-recibido,
    .status-leido {
        background: #020617;
        color: #fff;
    }

    .status-cancelado,
    .status-fallo,
    .status-fallido {
        background: #e11d48;
        color: #fff;
    }

    .status-pendiente {
        background: #f59e0b;
        color: #fff;
    }

    .table-footer {
        text-align: center;
        color: #64748b;
        font-size: 14px;
        padding: 18px 0 4px;
    }

    @media (max-width: 1200px) {
        .kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .dashboard-page {
            padding: 18px;
        }

        .kpi-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-header {
            align-items: flex-start;
            flex-direction: column;
            gap: 12px;
        }
    }
</style>

<div class="dashboard-page">

    <div class="dashboard-header">
        <div class="dashboard-title-wrap">
            <a href="{{ route('campanias.index') }}" class="dashboard-back">
                ←
            </a>

            <div>
                <h1 class="dashboard-title">
                    {{ $campania->nombre }}
                    <span class="dashboard-badge">{{ ucfirst($campania->estado) }}</span>
                </h1>

                <p class="dashboard-subtitle">
                    {{ $campania->descripcion ?? 'Sin descripción' }}
                </p>
            </div>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-title">Total Mensajes</div>
            <div class="kpi-value">{{ number_format($total, 0, ',', '.') }}</div>
        </div>

        <div class="kpi-card kpi-blue">
            <div class="kpi-title">Aceptado Meta</div>
            <div class="kpi-value">{{ number_format($aceptados, 0, ',', '.') }}</div>
        </div>

        <div class="kpi-card kpi-blue">
            <div class="kpi-title">Enviados</div>
            <div class="kpi-value">{{ number_format($enviados, 0, ',', '.') }}</div>
        </div>

        <div class="kpi-card kpi-green">
            <div class="kpi-title">Recibidos</div>
            <div class="kpi-value">{{ number_format($recibidos, 0, ',', '.') }}</div>
        </div>

        <div class="kpi-card kpi-purple">
            <div class="kpi-title">Leídos</div>
            <div class="kpi-value">{{ number_format($leidos, 0, ',', '.') }}</div>
        </div>

        <div class="kpi-card kpi-red">
            <div class="kpi-title">Fallos</div>
            <div class="kpi-value">{{ number_format($fallos, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="content-grid">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h2 class="dashboard-section-title">Distribución de mensajes por estado</h2>
            </div>

            <div class="dashboard-card-body">
                <div id="graficoEstados"></div>
            </div>
        </div>

        <div>
            <div class="dashboard-card" style="margin-bottom: 24px;">
                <div class="tasa-box">
                    <div class="kpi-title">Tasa de lectura</div>
                    <div class="tasa-value">{{ $tasaLectura }}%</div>
                    <p class="tasa-text">
                        {{ number_format($leidos, 0, ',', '.') }} leídos sobre
                        {{ number_format($recibidos, 0, ',', '.') }} recibidos
                    </p>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h2 class="dashboard-section-title">Resumen rápido</h2>
                </div>

                <div class="dashboard-card-body">
                    <div class="summary-row">
                        <span>Aceptados por Meta</span>
                        <strong>{{ number_format($aceptados, 0, ',', '.') }}</strong>
                    </div>

                    <div class="summary-row">
                        <span>Enviados</span>
                        <strong>{{ number_format($enviados, 0, ',', '.') }}</strong>
                    </div>

                    <div class="summary-row">
                        <span>Recibidos</span>
                        <strong>{{ number_format($recibidos, 0, ',', '.') }}</strong>
                    </div>

                    <div class="summary-row">
                        <span>Leídos</span>
                        <strong>{{ number_format($leidos, 0, ',', '.') }}</strong>
                    </div>

                    <div class="summary-row">
                        <span>Fallos</span>
                        <strong>{{ number_format($fallos, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        
        <div class="table-header-actions">
            <h2 class="dashboard-section-title">Detalle por paciente</h2>

            <div class="export-actions">
                <div class="export-actions">
 
                    <a href="{{ route('campanias.dashboard.excel', $campania->id) }}" class="export-btn">
                        Excel
                    </a>
                </div>
            </div>
        </div>

        <div class="detail-table-wrap">
            <div style="margin-bottom: 16px;">
    
</div>
<div class="d-flex gap-2 mb-3">

    <input
        type="text"
        id="buscadorPaciente"
        class="form-control"
        placeholder="Buscar paciente..."
    >

    <select id="filtroEstado" class="form-select" style="max-width:250px;">
        <option value="">Todos los estados</option>
       <option value="Enviado">Enviado</option>
        <option value="Recibido">Recibido</option>
        <option value="Leído">Leído</option>
        <option value="Fallo">Fallo</option>
        <option value="No aceptado Meta">No aceptado Meta</option>
        <option value="Revisar">Revisar</option>
        <option value="Eliminado">Eliminado</option>
    </select>

</div>
           <table class="table table-hover align-middle">
    <thead>
        <tr>
            <th>Paciente</th>
            <th>Estado</th>
            <th>Fecha envío</th>
            <th>Fecha lectura</th>
        </tr>
    </thead>

    <tbody>

    @foreach($detallePacientes as $paciente)

        <tr
            class="fila-paciente"
            data-estado="{{ $paciente['estado'] }}"
        >

            <td>
                <div class="fw-semibold">
                    {{ $paciente['nombre'] }}
                </div>

                @if(!empty($paciente['codigo_persona']))
                    <small class="text-muted">
                        Código: {{ $paciente['codigo_persona'] }}
                    </small>
                @endif
            </td>

            <td>

                @php

                    $badgeClass = match($paciente['estado']) {
                        'Aceptado Meta' => 'bg-primary',
                        'Enviado' => 'bg-info',
                        'Recibido' => 'bg-success',
                        'Leído' => 'bg-success',
                        'Revisar' => 'bg-warning',
                        'Fallo' => 'bg-danger',
                        'No aceptado Meta' => 'bg-danger',
                        default => 'bg-secondary'
                    };

                @endphp

                <span class="badge {{ $badgeClass }}">
                    {{ $paciente['estado'] }}
                </span>

            </td>

            <td>
                {{ $paciente['fecha_envio']
                    ? \Carbon\Carbon::parse($paciente['fecha_envio'])->format('d/m/Y H:i')
                    : '-' }}
            </td>

            <td>
                {{ $paciente['fecha_leido']
                    ? \Carbon\Carbon::parse($paciente['fecha_leido'])->format('d/m/Y H:i')
                    : '-' }}
            </td>

        </tr>

    @endforeach

    </tbody>
</table>

            <div class="table-footer">
                Mostrando {{ count($detallePacientes ?? []) }} de {{ number_format($total, 0, ',', '.') }} registros
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    const buscador = document.getElementById('buscadorPaciente');
const filtroEstado = document.getElementById('filtroEstado');

function aplicarFiltros() {

    const texto = buscador.value.toLowerCase();
    const estado = filtroEstado.value;

    document.querySelectorAll('.fila-paciente').forEach(row => {

        const contenido = row.innerText.toLowerCase();
        const estadoFila = row.dataset.estado;

        const coincideTexto =
            contenido.includes(texto);

        const coincideEstado =
            estado === '' || estadoFila === estado;

        row.style.display =
            coincideTexto && coincideEstado
                ? ''
                : 'none';
    });
}

buscador.addEventListener('keyup', aplicarFiltros);
filtroEstado.addEventListener('change', aplicarFiltros);

document.addEventListener('DOMContentLoaded', function () {
    const options = {
        chart: {
            type: 'donut',
            height: 360
        },
        series: [
            {{ (int) $aceptados }},
            {{ (int) $enviados }},
            {{ (int) $recibidos }},
            {{ (int) $leidos }},
            {{ (int) $fallos }}
        ],
        labels: [
            'Aceptados Meta',
            'Enviados',
            'Recibidos',
            'Leídos',
            'Fallos'
        ],
        legend: {
            position: 'bottom'
        },
        dataLabels: {
            enabled: true
        },
        noData: {
            text: 'Sin datos disponibles'
        }
    };

    new ApexCharts(
        document.querySelector("#graficoEstados"),
        options
    ).render();
});
</script>
@endpush
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="fw-bold mb-1">Detalle de la Campaña</h3>


@if($errors->has('general'))
    <div class="alert alert-danger">
        {{ $errors->first('general') }}
    </div>
@endif
    <p class="text-muted mb-4">Revisa los detalles de la campaña antes de programar</p>

    <div class="row g-4">
        <!-- IZQUIERDA -->
        <div class="col-lg-8">
           <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Información de la campaña</h6>

        <div class="row">
            <div class="col-md-6">
                <small class="text-muted">Nombre</small>
                <div>{{ $campania->titulo }}</div>
            </div>

            <div class="col-md-6">
                <small class="text-muted">Solicitante</small>
                <div>{{ $campania->responsable->name ?? '-' }}</div>
            </div>

            <div class="col-12 mt-3">
                <small class="text-muted">Descripción</small>
                <div>{{ $campania->descripcion }}</div>
            </div>
        </div>
    </div>
</div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Alcance y costo estimado</h6>

        <!-- Alcance -->
        <div class="p-3 rounded mb-3" style="background:#e7f0ff;">
            <div class="text-muted small">Destinatarios totales</div>
            <div class="fw-bold fs-5 text-primary">
                {{ $campania->cantidad_destinatarios ?? 0 }}
            </div>
        </div>
@php
    $costoPorMensaje = 0.02;
@endphp
        <!-- Costo -->
        <div class="p-3 rounded" style="background:#e9f7ef;">
            <div class="text-muted small">Costo estimado de la campaña</div>
            <div class="fw-bold text-success">
USD {{ number_format(($campania->cantidad_destinatarios ?? 0) * $costoPorMensaje, 2) }}            </div>
<small class="text-muted">(USD {{ number_format($costoPorMensaje, 3) }} por mensaje)</small>        </div>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Filtros aplicados</h6>

        @if($campania->segmentacion_tipo === 'sql')
            <div class="small text-muted mb-2">Consulta SQL avanzada</div>
            <pre class="small bg-light p-3 rounded border" style="white-space: pre-wrap;">{{ $campania->segmentacion_sql ?: '-' }}</pre>
        @else
            <div class="small">
                <div><strong>Rango de edad:</strong> {{ $campania->edad_min ?? '-' }} - {{ $campania->edad_max ?? '-' }} años</div>
                <div><strong>Sexo:</strong> {{ $campania->sexo ?: 'Todos' }}</div>
                <div><strong>Localidad:</strong> {{ $campania->localidad ?: 'Todas' }}</div>
                <div><strong>Diagnóstico:</strong> {{ $campania->diagnostico ?: 'Todos' }}</div>
                <div><strong>Última atención desde:</strong> {{ optional($campania->ultima_atencion_desde)->format('d/m/Y') ?: '-' }}</div>
                <div><strong>Última atención hasta:</strong> {{ optional($campania->ultima_atencion_hasta)->format('d/m/Y') ?: '-' }}</div>
            </div>
        @endif
    </div>
</div>
        </div>

        <!-- DERECHA -->
        <div class="col-lg-4">
           <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Vista previa del mensaje</h6>

        <div class="p-3 border rounded" style="background:#f5f5f5;">
            {{ $campania->mensaje }}
        </div>
    </div>
    @if(!empty($campania->adjunto_path) && str_starts_with($campania->adjunto_tipo_mime, 'image/'))
    <div class="mt-3">
        <label class="form-label fw-bold">Imagen adjunta</label>

        <div class="border rounded p-2 bg-light">
            <img 
                src="{{ asset('storage/' . $campania->adjunto_path) }}" 
                class="img-fluid rounded"
                style="max-height: 250px;"
            >
        </div>
    </div>
    
@endif
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Programación</h6>

        @if($campania->fecha_programada)
            <div class="p-3 rounded" style="background:#fff7e6;">
                <div class="text-muted small">Día y hora de ejecución</div>
                <div class="fw-bold">
                    {{ $campania->fecha_programada->format('d/m/Y H:i') }}
                </div>
            </div>
        @else
            <div class="text-muted small">Sin programar</div>
        @endif
    </div>
</div>
</div>
<div class="d-grid gap-2">
@if($campania->puedeEditarse())

    <a href="{{ route('campanias.edit', $campania->id) }}" class="btn btn-outline-secondary">
        Editar campaña
    </a>

    <button 
        type="button" 
        class="btn btn-dark w-100"
        data-bs-toggle="modal"
        data-bs-target="#modalProgramarCampania"
    >
        {{ $campania->estado === 'programada' ? 'Reprogramar envío' : 'Programar envío' }}
    </button>

    @else
        <div class="alert alert-danger">
            Esta campaña no puede modificarse.
        </div>
    @endif
  
 </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalProgramarCampania" tabindex="-1" aria-labelledby="modalProgramarCampaniaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('campanias.programar', $campania->id) }}">
                @csrf
                @method('PATCH')

                <div class="modal-header">
                    <h5 class="modal-title" id="modalProgramarCampaniaLabel">Programar campaña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

               <div class="modal-body">

            

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 small">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Fecha y hora de envío</label>
                            <input
                                type="datetime-local"
                                class="form-control @error('fecha_programada') is-invalid @enderror"
                                name="fecha_programada"
                                value="{{ old('fecha_programada', optional($campania->fecha_programada)->format('Y-m-d\TH:i')) }}"
                                required
                            >
                            @error('fecha_programada')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-dark">
                        Confirmar programación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let modal = new bootstrap.Modal(document.getElementById('modalProgramarCampania'));
        modal.show();
    });
</script>
@endif
@if(session('success') || $errors->has('general'))
<div class="modal fade" id="modalMensajeCampania" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header {{ session('success') ? 'bg-success text-white' : 'bg-danger text-white' }}">
                <h5 class="modal-title">
                    {{ session('success') ? 'Operación exitosa' : 'Error' }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="mb-0">
                    {{ session('success') ?: $errors->first('general') }}
                </p>
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
        const modal = new bootstrap.Modal(
            document.getElementById('modalMensajeCampania')
        );

        modal.show();
    });
</script>
@endif
@endsection
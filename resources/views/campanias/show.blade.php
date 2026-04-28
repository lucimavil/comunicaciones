@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="fw-bold mb-1">Detalle de la Campaña</h3>
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

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

        <!-- Costo -->
        <div class="p-3 rounded" style="background:#e9f7ef;">
            <div class="text-muted small">Costo estimado de la campaña</div>
            <div class="fw-bold text-success">
                USD {{ number_format(($campania->cantidad_destinatarios ?? 0) * 0.05, 2) }}
            </div>
            <small class="text-muted">(USD 0.050 por mensaje)</small>
        </div>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Filtros aplicados</h6>

        @if($campania->segmentacion_tipo === 'sql')
            <pre class="small bg-light p-2 rounded">
{{ $campania->segmentacion_sql }}
            </pre>
        @else
            <div class="small">
                <div>Edad: {{ $campania->edad_min }} - {{ $campania->edad_max }} años</div>
                <div>Sexo: {{ $campania->sexo ?: 'Todos' }}</div>
                <div>Localidad: {{ $campania->localidad ?: 'Todas' }}</div>
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
    <div class="col-md-6 mt-3">
              <h6 class="fw-bold mb-3">Fecha programada</h6>
            <div>
                @if($campania->fecha_programada)
                    {{ $campania->fecha_programada->format('d/m/Y H:i') }}
                @else
                    Sin programar
                @endif
            </div>
        </div>
@endif
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
@endsection
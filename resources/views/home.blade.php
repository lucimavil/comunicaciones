@extends('layouts.app')

@section('content')
<div class="container">

   <div class="hero-card mb-4">
                <span class="badge text-bg-light text-primary d-inline-flex align-items-center px-3 py-2 rounded-pill mb-3" style="width: fit-content;">
                    <i class="bi bi-whatsapp me-2"></i>
                </span>
                <h1>Bienvenido al Sistema de Comunicaciones por Whatsapp</h1>
                <p>Gestioná campañas, segmentá pacientes, programá envíos y seguí resultados desde un único tablero.</p>
            </div>


    <div class="row g-4">

        <!-- COMUNICACIONES -->
        <div class="col-md-4">
            <a href="{{ route('campanias.index') }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5 class="fw-bold text-primary">Campañas</h5>
                        <p class="text-muted">
                            Gestión de campañas y mensajería institucional.
                        </p>
                    </div>
                </div>
            </a>
        </div>

        <!-- OTROS MODULOS -->
        @foreach(['Comunicacion Interna', 'Encuestas', 'Mensajeria','Interconsultas'] as $modulo)
        <div class="col-md-4">
            <div class="card bg-light border-0 h-100">
                <div class="card-body">
                    <h5 class="text-muted">{{ $modulo }}</h5>
                    <span class="badge bg-secondary">En desarrollo</span>
                </div>
            </div>
        </div>
        @endforeach

    </div>

</div>
@endsection
@extends('layouts.app')

@section('content')
    <div class="bg-white border-bottom py-3">
        
        <div class="container d-flex justify-content-between align-items-center">

            <h4 class="mb-0 fw-semibold">
                Gestión de Campañas
            </h4>

            <a href="{{ route('campanias.create') }}" 
            class="btn btn-dark">
                + Nueva campaña
            </a>

        </div>

    </div>
    <div class="container">

           @foreach($campanias as $camp)

            <div class="card mt-3">
                <div class="card-body">

                    <h5>{{ $camp->nombre }}</h5>

                    <p>{{ $camp->descripcion }}</p>

                    <span class="badge bg-info">{{ $camp->tipo }}</span>

                    <p>
                    Responsable: {{ $camp->responsable->name }}
                    </p>

                    <a href="/campanias/{id}/dashboard" class="btn btn-sm btn-outline-primary">
                    Dashboard
                    </a>

                    <a href="#" class="btn btn-sm btn-outline-secondary">
                    Editar
                    </a>

                </div>
            </div>

        @endforeach
    </div>

    
@endsection


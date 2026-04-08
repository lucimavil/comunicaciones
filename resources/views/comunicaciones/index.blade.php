@extends('layouts.app')

@section('content')
    <div class="bg-white border-bottom py-3">
        
        <div class="container d-flex justify-content-between align-items-center">

            <h4 class="mb-0 fw-semibold">
                Comunicación Interna
            </h4>

            <a href="{{ route('comunicaciones.create') }}" 
            class="btn btn-dark">
                + Nueva comunicación
            </a>

        </div>

    </div>
    <div class="container">

           @foreach($comunicaciones as $com)

            <div class="card mt-3">
                <div class="card-body">

                    <h5>{{ $com->nombre }}</h5>

                    <p>{{ $com->descripcion }}</p>

                    <span class="badge bg-info">{{ $com->tipo }}</span>

                    <p>
                    Responsable: {{ $com->responsable->name }}
                    </p>

                    <a href="/comunicaciones/{id}/dashboard" class="btn btn-sm btn-outline-primary">
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


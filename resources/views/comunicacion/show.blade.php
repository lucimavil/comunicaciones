@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h3 class="fw-bold mb-1">Detalle de la comunicación</h3>
            <p class="text-muted mb-0">
                Revisá la información, segmentación y programación de la comunicación.
            </p>
        </div>

        <a href="{{ route('comunicacion.index') }}"
           class="btn btn-outline-secondary">
            Volver
        </a>
    </div>


    {{-- ERRORES --}}
    @if($errors->has('general'))
        <div class="alert alert-danger">
            {{ $errors->first('general') }}
        </div>
    @endif


    <div class="row g-4">

        {{-- COLUMNA IZQUIERDA --}}
        <div class="col-lg-8">

            {{-- INFORMACIÓN GENERAL --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">

                    <h5 class="fw-bold mb-4">
                        Información de la comunicación
                    </h5>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <small class="text-muted d-block">
                                Nombre del aviso
                            </small>

                            <div class="fw-semibold">
                                {{ $comunicacion->nombre ?: '-' }}
                            </div>
                        </div>


                        <div class="col-md-6">
                            <small class="text-muted d-block">
                                Tipo
                            </small>

                            <div class="fw-semibold">
                                {{ ucfirst(str_replace('_', ' ', $comunicacion->tipo ?? '-')) }}
                            </div>
                        </div>


                        <div class="col-md-6">
                            <small class="text-muted d-block">
                                Solicitante
                            </small>

                            <div class="fw-semibold">
                                {{ ucfirst(str_replace('_', ' ', $comunicacion->solicitante ?? '-')) }}
                            </div>
                        </div>


                        <div class="col-md-6">
                            <small class="text-muted d-block">
                                Creado por
                            </small>

                            <div class="fw-semibold">
                                {{ $comunicacion->responsable->name ?? '-' }}
                            </div>
                        </div>


                        <div class="col-12">
                            <small class="text-muted d-block">
                                Descripción
                            </small>

                            <div>
                                {{ $comunicacion->descripcion ?: '-' }}
                            </div>
                        </div>


                        <div class="col-md-6">
                            <small class="text-muted d-block">
                                Estado
                            </small>

                            @php
                                $estado = $comunicacion->estado ?? 'borrador';

                                $claseEstado = match($estado) {
                                    'programada' => 'bg-warning text-dark',
                                    'finalizada' => 'bg-success',
                                    'cancelada' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            @endphp

                            <span class="badge {{ $claseEstado }}">
                                {{ ucfirst($estado) }}
                            </span>
                        </div>


                        <div class="col-md-6">
                            <small class="text-muted d-block">
                                Fecha de creación
                            </small>

                            <div>
                                {{ optional($comunicacion->created_at)->format('d/m/Y H:i') ?? '-' }}
                            </div>
                        </div>

                    </div>

                </div>
            </div>


            {{-- SEGMENTACIÓN --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">

                    <h5 class="fw-bold mb-4">
                        Segmentación aplicada
                    </h5>


                    @if($comunicacion->tipo_segmentacion === 'segmentos')

                        <div class="mb-3">
                            <small class="text-muted d-block mb-2">
                                Segmentos seleccionados
                            </small>

                            @forelse($comunicacion->segmentos as $segmento)

                                <span class="badge bg-primary me-1 mb-1">
                                    {{ $segmento->nombre }}
                                </span>

                            @empty

                                <span class="text-muted">
                                    No hay segmentos seleccionados.
                                </span>

                            @endforelse
                        </div>


                    @elseif($comunicacion->tipo_segmentacion === 'todo_personal')

                        <div class="alert alert-primary mb-0">
                            <i class="fa-solid fa-hospital-user me-2"></i>

                            Esta comunicación está dirigida a
                            <strong>todo el personal activo</strong>.
                        </div>


                    @elseif($comunicacion->tipo_segmentacion === 'sql')

                        <div>
                            <small class="text-muted d-block mb-2">
                                Consulta SQL avanzada
                            </small>

                            <pre
                                class="bg-light border rounded p-3 mb-0"
                                style="
                                    white-space: pre-wrap;
                                    max-height: 350px;
                                    overflow-y: auto;
                                    font-size: 13px;
                                "
                            >{{ $comunicacion->segmentacion_sql ?: '-' }}</pre>
                        </div>


                    @else

                        <div class="text-muted">
                            No hay información de segmentación disponible.
                        </div>

                    @endif

                </div>
            </div>


            {{-- ALCANCE --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">

                    <h5 class="fw-bold mb-4">
                        Alcance y costo estimado
                    </h5>

                    @php
                        $costoPorMensaje = 0.02;
                        $cantidad = $comunicacion->cantidad_destinatarios ?? 0;
                        $costoTotal = $cantidad * $costoPorMensaje;
                    @endphp


                    <div class="row g-3">

                        <div class="col-md-6">

                            <div
                                class="border rounded-4 p-4 h-100"
                                style="background:#eef5ff;"
                            >

                                <div class="text-muted small mb-1">
                                    Destinatarios totales
                                </div>

                                <div class="fs-3 fw-bold text-primary">
                                    {{ number_format($cantidad, 0, ',', '.') }}
                                </div>

                                <div class="small text-muted">
                                    personas
                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div
                                class="border rounded-4 p-4 h-100"
                                style="background:#eefaf2;"
                            >

                                <div class="text-muted small mb-1">
                                    Costo estimado
                                </div>

                                <div class="fs-3 fw-bold text-success">
                                    USD {{ number_format($costoTotal, 2, ',', '.') }}
                                </div>

                                <div class="small text-muted">
                                    USD {{ number_format($costoPorMensaje, 2, ',', '.') }}
                                    por mensaje
                                </div>

                            </div>

                        </div>

                    </div>

                </div>
            </div>

        </div>


        {{-- COLUMNA DERECHA --}}
        <div class="col-lg-4">

            {{-- MENSAJE --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">

                    <h5 class="fw-bold mb-3">
                        Vista previa del mensaje
                    </h5>

                    <div
                        class="p-3 rounded"
                        style="background:#e5ddd5;"
                    >

                        <div
                            class="bg-white rounded p-3 shadow-sm"
                            style="white-space: pre-wrap;"
                        >{{ $comunicacion->mensaje ?: '-' }}</div>

                    </div>

                </div>
            </div>


            {{-- ADJUNTO --}}
            @if(!empty($comunicacion->adjunto_path))

                @php
                    $mime = $comunicacion->adjunto_tipo_mime ?? '';

                    $urlAdjunto = asset(
                        'storage/' . $comunicacion->adjunto_path
                    );
                @endphp


                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">

                        <h5 class="fw-bold mb-3">
                            Archivo adjunto
                        </h5>


                        {{-- IMAGEN --}}
                        @if(str_starts_with($mime, 'image/'))

                            <div class="text-center">

                                <img
                                    src="{{ $urlAdjunto }}"
                                    class="img-fluid rounded border"
                                    style="
                                        max-height:300px;
                                        object-fit:contain;
                                    "
                                    alt="Adjunto"
                                >

                            </div>


                        {{-- PDF --}}
                        @elseif($mime === 'application/pdf')

                            <iframe
                                src="{{ $urlAdjunto }}"
                                width="100%"
                                height="400"
                                class="border rounded">
                            </iframe>

                            <a
                                href="{{ $urlAdjunto }}"
                                target="_blank"
                                class="btn btn-sm btn-primary w-100 mt-2"
                            >
                                Abrir PDF
                            </a>


                        {{-- DOC / OTROS --}}
                        @else

                            <div
                                class="border rounded p-3 bg-light"
                            >

                                <div class="mb-3">

                                    <i class="fa-solid fa-file fs-3 me-2"></i>

                                    <span>
                                        {{ $comunicacion->adjunto_nombre ?? 'Archivo adjunto' }}
                                    </span>

                                </div>


                                <a
                                    href="{{ $urlAdjunto }}"
                                    target="_blank"
                                    class="btn btn-sm btn-primary w-100"
                                >
                                    Ver archivo
                                </a>

                            </div>

                        @endif

                    </div>
                </div>

            @endif


            {{-- PROGRAMACIÓN --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">

                    <h5 class="fw-bold mb-3">
                        Programación
                    </h5>

                    @if($comunicacion->fecha_programada)

                        <div
                            class="p-3 rounded"
                            style="background:#fff7e6;"
                        >

                            <div class="text-muted small">
                                Día y hora de ejecución
                            </div>

                            <div class="fw-bold">
                                {{ $comunicacion->fecha_programada->format('d/m/Y H:i') }}
                            </div>

                        </div>

                    @else

                        <div class="text-muted">
                            Sin programar
                        </div>

                    @endif

                </div>
            </div>


            {{-- ACCIONES --}}
            <div class="d-grid gap-2">

                @if($comunicacion->puedeEditarse())

                    <a
                        href="{{ route('comunicacion.edit', $comunicacion->id) }}"
                        class="btn btn-outline-secondary"
                    >
                        <i class="fa-solid fa-pen me-2"></i>
                        Editar comunicación
                    </a>


                    <button
                        type="button"
                        class="btn btn-dark"
                        data-bs-toggle="modal"
                        data-bs-target="#modalProgramarComunicacion"
                    >
                        <i class="fa-solid fa-calendar me-2"></i>

                        {{ $comunicacion->estado === 'programada'
                            ? 'Reprogramar envío'
                            : 'Programar envío'
                        }}
                    </button>

                @else

                    <div class="alert alert-warning mb-0">
                        Esta comunicación ya no puede modificarse.
                    </div>

                @endif

            </div>

        </div>

    </div>
</div>


{{-- MODAL PROGRAMACIÓN --}}
<div
    class="modal fade"
    id="modalProgramarComunicacion"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <form
                method="POST"
                action="{{ route('comunicacion.programar', $comunicacion->id) }}"
            >

                @csrf
                @method('PATCH')


                <div class="modal-header">

                    <h5 class="modal-title">
                        {{ $comunicacion->estado === 'programada'
                            ? 'Reprogramar comunicación'
                            : 'Programar comunicación'
                        }}
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

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

                        <label class="form-label fw-semibold">
                            Fecha y hora de envío
                        </label>

                        <input
                            type="datetime-local"
                            class="form-control
                                @error('fecha_programada')
                                    is-invalid
                                @enderror"
                            name="fecha_programada"
                            value="{{
                                old(
                                    'fecha_programada',
                                    optional($comunicacion->fecha_programada)
                                        ->format('Y-m-d\TH:i')
                                )
                            }}"
                            required
                        >

                        @error('fecha_programada')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-dark"
                    >
                        Confirmar programación
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>


{{-- ABRIR MODAL SI HUBO ERROR DE PROGRAMACIÓN --}}
@if($errors->any() && !$errors->has('general'))

<script>
document.addEventListener('DOMContentLoaded', function () {

    const elemento =
        document.getElementById('modalProgramarComunicacion');

    if (!elemento) {
        return;
    }

    const modal =
        bootstrap.Modal.getOrCreateInstance(elemento);

    modal.show();

});
</script>

@endif


{{-- MODAL MENSAJE GENERAL --}}
@if(session('success') || $errors->has('general'))

<div
    class="modal fade"
    id="modalMensajeComunicacion"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <div
                class="modal-header
                {{
                    session('success')
                        ? 'bg-success text-white'
                        : 'bg-danger text-white'
                }}"
            >

                <h5 class="modal-title">

                    {{
                        session('success')
                            ? 'Operación exitosa'
                            : 'Error'
                    }}

                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">
                </button>

            </div>


            <div class="modal-body">

                <p class="mb-0">
                    {{
                        session('success')
                            ?: $errors->first('general')
                    }}
                </p>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-dismiss="modal"
                >
                    Aceptar
                </button>

            </div>

        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const elemento =
        document.getElementById('modalMensajeComunicacion');

    if (!elemento) {
        return;
    }

    const modal =
        bootstrap.Modal.getOrCreateInstance(elemento);

    modal.show();

});
</script>

@endif

@endsection
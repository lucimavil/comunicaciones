@extends('layouts.app')

@section('content')
@push('styles')
<style>
[x-cloak] {
    display: none !important;
}

.segmento-card {
    min-height: 125px;
    padding: 18px;
    border: 1px solid #dee2e6;
    border-radius: 14px;
    background: #fff;
    transition: all .2s ease;
}

.segmento-card:hover {
    border-color: #86b7fe;
    box-shadow: 0 8px 20px rgba(13, 110, 253, .10);
    transform: translateY(-2px);
}

.segmento-card-active {
    border: 2px solid #0d6efd;
    background: #eef5ff;
    box-shadow: 0 8px 20px rgba(13, 110, 253, .14);
}

.segmento-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e9f2ff;
    color: #0d6efd;
    flex-shrink: 0;
}

.segmento-check {
    width: 25px;
    height: 25px;
    border-radius: 50%;
    background: #0d6efd;
    color: #fff;
    font-weight: bold;
    text-align: center;
    line-height: 25px;
}
</style>
@endpush
<div class="container mt-4">
@php
    $comunicacionData = isset($comunicacion) ? [
        'id' => $comunicacion->id,
        'nombre' => $comunicacion->nombre,
        'tipo' => $comunicacion->tipo,
        'descripcion' => $comunicacion->descripcion,
        'solicitante' => $comunicacion->solicitante,

        'tipo_segmentacion' => $comunicacion->tipo_segmentacion,

        'segmentos_seleccionados' => $comunicacion
            ->segmentos
            ->pluck('id')
            ->values()
            ->toArray(),

        'segmentacion_sql' => $comunicacion->segmentacion_sql ?? '',
        'sql_generada' => $comunicacion->segmentacion_sql ?? '',

        'alcance' => $comunicacion->cantidad_destinatarios,

        'mensaje' => $comunicacion->mensaje,

        'fecha_programada' => optional($comunicacion->fecha_programada)
            ?->format('Y-m-d\TH:i'),

        'adjunto_path' => $comunicacion->adjunto_path,
        'adjunto_nombre' => $comunicacion->adjunto_nombre,
        'adjunto_tipo_mime' => $comunicacion->adjunto_tipo_mime,
    ] : [];
@endphp
<div
    x-data='wizardComunicacion(
        @json($comunicacionData),
        @json($segmentos)
    )'
    class="bg-white shadow rounded p-4"
>

<h4 class="fw-bold mb-4">Nueva Comunicación interna</h4>


<!-- PROGRESS -->

<div class="progress mb-4" style="height:6px">
<div class="progress-bar bg-dark" :style="'width:'+progress+'%'"></div>
</div>

<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">

    <div class="d-flex align-items-center gap-2">
        <div class="step-circle" :class="step >= 1 ? 'active' : ''">1</div>
        <span :class="step >= 1 ? 'fw-bold text-dark' : 'text-muted'">Información</span>
    </div>

    <div class="d-flex align-items-center gap-2">
        <div class="step-circle" :class="step >= 2 ? 'active' : ''">2</div>
        <span :class="step >= 2 ? 'fw-bold text-dark' : 'text-muted'">Segmentación</span>
    </div>

    <div class="d-flex align-items-center gap-2">
        <div class="step-circle" :class="step >= 3 ? 'active' : ''">3</div>
        <span :class="step >= 3 ? 'fw-bold text-dark' : 'text-muted'">Mensaje</span>
    </div>

    <div class="d-flex align-items-center gap-2">
        <div class="step-circle" :class="step >= 4 ? 'active' : ''">4</div>
        <span :class="step >= 4 ? 'fw-bold text-dark' : 'text-muted'">Confirmar</span>
    </div>

</div>
<form
    method="POST"
    action="{{ route('comunicacion.store') }}"
    enctype="multipart/form-data"
>
@csrf


<!-- PASO 1 -->
<div x-show="step == 1">

    <h5 class="mb-4">Información de la comunicación interna</h5>

    <!-- NOMBRE -->
    <div class="mb-3">
        <label for="nombre" class="form-label">
            Nombre del aviso *
        </label>

        <input
            type="text"
            id="nombre"
            name="nombre"
            class="form-control"
            x-model="nombre"
            :class="{ 'is-invalid': errores.nombre }"
            placeholder="Ej.: Capacitación sobre nuevos protocolos">

        <template x-if="errores.nombre">
            <div
                class="invalid-feedback d-block"
                x-text="errores.nombre[0]">
            </div>
        </template>
    </div>

    <!-- TIPO -->
    <div class="mb-3">
        <label for="tipo" class="form-label">
            Tipo de comunicación *
        </label>

        <select
            id="tipo"
            name="tipo"
            class="form-select"
            x-model="tipo"
            :class="{ 'is-invalid': errores.tipo }">

            <option value="">Seleccione tipo...</option>
            <option value="capacitacion">Capacitación</option>
            <option value="cambio_guardia">Cambio de guardia</option>
            <option value="evento_institucional">Evento institucional</option>
            <option value="emergencia">Emergencia urgente</option>
            <option value="aviso_general">Aviso general</option>
        </select>

        <template x-if="errores.tipo">
            <div
                class="invalid-feedback d-block"
                x-text="errores.tipo[0]">
            </div>
        </template>
    </div>

    <!-- DESCRIPCIÓN -->
    <div class="mb-3">
        <label for="descripcion" class="form-label">
            Descripción breve *
        </label>

        <textarea
            id="descripcion"
            name="descripcion"
            class="form-control"
            rows="3"
            x-model="descripcion"
            :class="{ 'is-invalid': errores.descripcion }"
            placeholder="Describa brevemente de qué trata esta comunicación..."></textarea>

        <template x-if="errores.descripcion">
            <div
                class="invalid-feedback d-block"
                x-text="errores.descripcion[0]">
            </div>
        </template>
    </div>

    <!-- ÁREA RESPONSABLE -->
    <div class="mb-3">
        <label for="solicitante" class="form-label">
            Solicitante *
        </label>

        <select
            id="solicitante"
            name="solicitante"
            class="form-select"
            x-model="solicitante"
            :class="{ 'is-invalid': errores.solicitante }">

            <option value="">Seleccione solicitante...</option>
            <option value="prensa">Prensa</option>
            <option value="informatica_medica">Informática Médica</option>
            <option value="administracion">Administración</option>
            <option value="directorio">Directorio</option>
            <option value="recursos_humanos">Recursos Humanos</option>
        </select>

        <template x-if="errores.solicitante">
            <div
                class="invalid-feedback d-block"
                x-text="errores.solicitante[0]">
            </div>
        </template>
    </div>

    <!-- USUARIO QUE CREA LA COMUNICACIÓN -->
    <div class="mb-3">
        <label class="form-label">
            Creado por
        </label>

        <input
            type="text"
            class="form-control"
            value="{{ auth()->user()->name }}"
            disabled>

        <input
            type="hidden"
            name="usuario_id"
            value="{{ auth()->id() }}">
    </div>

</div>
<!-- PASO 2 -->
<div x-show="step === 2" x-cloak>

    <h4 class="mb-1">Segmentación del personal</h4>

    <p class="text-muted mb-4">
        Seleccioná uno o varios grupos, todo el personal o ingresá una consulta avanzada.
    </p>

    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <label
                class="segmentacion-option"
                :class="{ 'active': tipo_segmentacion === 'segmentos' }"
            >
                <input
                    type="radio"
                    class="d-none"
                    value="segmentos"
                    x-model="tipo_segmentacion"
                    @change="marcarSegmentacionModificada()"
                >

                <div class="card h-100">
                    <div class="card-body">
                        <i class="fa-solid fa-users me-2"></i>
                        <strong>Segmentos</strong>

                        <p class="text-muted small mb-0 mt-2">
                            Seleccionar uno o varios grupos de personal.
                        </p>
                    </div>
                </div>
            </label>
        </div>

        <div class="col-md-4">
            <label
                class="segmentacion-option"
                :class="{ 'active': tipo_segmentacion === 'todo_personal' }"
            >
                <input
                    type="radio"
                    class="d-none"
                    value="todo_personal"
                    x-model="tipo_segmentacion"
                    @change="marcarSegmentacionModificada()"
                >

                <div class="card h-100">
                    <div class="card-body">
                        <i class="fa-solid fa-hospital-user me-2"></i>
                        <strong>Todo el personal</strong>

                        <p class="text-muted small mb-0 mt-2">
                            Incluye a todo el personal activo disponible.
                        </p>
                    </div>
                </div>
            </label>
        </div>

        <div class="col-md-4">
            <label
                class="segmentacion-option"
                :class="{ 'active': tipo_segmentacion === 'sql' }"
            >
                <input
                    type="radio"
                    class="d-none"
                    value="sql"
                    x-model="tipo_segmentacion"
                    @change="marcarSegmentacionModificada()"
                >

                <div class="card h-100">
                    <div class="card-body">
                        <i class="fa-solid fa-database me-2"></i>
                        <strong>Consulta avanzada</strong>

                        <p class="text-muted small mb-0 mt-2">
                            Ingresar una consulta SQL personalizada.
                        </p>
                    </div>
                </div>
            </label>
        </div>

    </div>

    {{-- SEGMENTOS PREDEFINIDOS --}}
    <div x-show="tipo_segmentacion === 'segmentos'" x-cloak>

        <div class="d-flex justify-content-between align-items-center mb-3">

            <div>
                <strong>Grupos disponibles</strong>

                <div class="text-muted small">
                    Podés seleccionar más de uno.
                </div>
            </div>

            <div class="d-flex gap-2">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    @click="seleccionarTodosLosSegmentos()"
                >
                    Seleccionar todos
                </button>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    @click="limpiarSegmentos()"
                >
                    Limpiar
                </button>
            </div>

        </div>

        <div class="row g-3">

            <template
                x-for="segmento in segmentos"
                :key="segmento.id"
            >
                <div class="col-md-4">

                    <button
                        type="button"
                        class="card w-100 h-100 text-start segmento-card"
                        :class="{
                            'border-primary shadow-sm':
                                segmentoEstaSeleccionado(segmento.id)
                        }"
                        @click="toggleSegmento(segmento)"
                    >
                        <div class="card-body d-flex align-items-start gap-3">

                            <div class="fs-4 text-primary">
                                <i :class="iconoSegmento(segmento.codigo)"></i>
                            </div>

                            <div class="flex-grow-1">
                                <div class="fw-semibold" x-text="segmento.nombre"></div>

                                <div
                                    class="small text-muted"
                                    x-text="segmento.descripcion || ''"
                                ></div>
                            </div>

                            <div>
                                <i
                                    class="fa-solid"
                                    :class="
                                        segmentoEstaSeleccionado(segmento.id)
                                            ? 'fa-circle-check text-primary'
                                            : 'fa-circle text-muted'
                                    "
                                ></i>
                            </div>

                        </div>
                    </button>

                </div>
            </template>

        </div>

        <div
            class="alert alert-info mt-3"
            x-show="segmentos_seleccionados.length > 0"
        >
            <strong>Seleccionados:</strong>

            <span x-text="nombresSegmentosSeleccionados"></span>
        </div>

        <div
            class="text-danger mt-2"
            x-show="errores.segmentos_seleccionados"
        >
            <span
                x-text="errores.segmentos_seleccionados?.[0]"
            ></span>
        </div>

    </div>

    {{-- TODO EL PERSONAL --}}
    <div
        x-show="tipo_segmentacion === 'todo_personal'"
        x-cloak
        class="alert alert-primary"
    >
        <div class="d-flex align-items-start gap-3">

            <i class="fa-solid fa-hospital-user fs-3"></i>

            <div>
                <strong>Se incluirá a todo el personal activo</strong>

                <div class="small mt-1">
                    La cantidad definitiva se obtendrá al probar la segmentación.
                </div>
            </div>

        </div>
    </div>

    {{-- CONSULTA SQL --}}
    <div x-show="tipo_segmentacion === 'sql'" x-cloak>

        <label
            for="segmentacion_sql"
            class="form-label fw-semibold"
        >
            Consulta SQL
        </label>

        <textarea
            id="segmentacion_sql"
            class="form-control font-monospace"
            rows="12"
            x-model="segmentacion_sql"
            @input="marcarSegmentacionModificada()"
            placeholder="SELECT
    pf.cd_pessoa_fisica AS codigoPersona,
    pf.nm_pessoa_fisica AS nombrePersona,
    pf.nr_identidade AS dniPersona,
    pf.nr_telefone_celular AS telefono
FROM pessoa_fisica pf
WHERE ..."
        ></textarea>

        <div class="form-text">
              La consulta debe ser de tipo SELECT, contener FROM y WHERE,
    y devolver los campos:
    <strong>codigoPersona, nombrePersona, dniPersona y telefono</strong>.
        </div>

        <div
            class="text-danger mt-2"
            x-show="errores.segmentacion_sql"
        >
            <span
                x-text="errores.segmentacion_sql?.[0]"
            ></span>
        </div>

    </div>

    <div class="mt-4">

        <button
            type="button"
            class="btn btn-primary"
            :disabled="loadingSegmentacion"
            @click="probarSegmentacion()"
        >
            <span
                x-show="loadingSegmentacion"
                class="spinner-border spinner-border-sm me-2"
            ></span>

            <i
                x-show="!loadingSegmentacion"
                class="fa-solid fa-flask me-2"
            ></i>

            Probar segmentación
        </button>
  <div
    class="mt-4"
    x-show="sql_generada"
    x-cloak
>
    <div class="d-flex justify-content-between align-items-center mb-2">

        <label class="form-label fw-semibold mb-0">
            Consulta SQL generada
        </label>

        <button
            type="button"
            class="btn btn-sm btn-outline-secondary"
            @click="navigator.clipboard.writeText(sql_generada)"
        >
            Copiar SQL
        </button>

    </div>

    <pre
        class="bg-dark text-light border rounded p-3"
        style="
            white-space: pre-wrap;
            max-height: 350px;
            overflow-y: auto;
            font-size: 13px;
        "
        x-text="sql_generada"
    ></pre>
</div>

    </div>
  

    <div
        class="alert alert-success mt-3"
        x-show="segmentacionProbada && alcance > 0"
    >
        La segmentación alcanza a

        <strong x-text="formatearNumero(alcance)"></strong>

        personas.
    </div>

</div>

<!-- PASO 3 -->

<div x-show="step==3">

<h5 class="mb-3">Mensaje de la comunicación</h5>

<textarea
    name="mensaje"
    rows="4"
    class="form-control"
    x-model="mensaje"
    minlength="10"
    maxlength="500"
    :class="{'is-invalid':errores.mensaje}">
</textarea>

<div class="text-end small text-muted mt-1">
    <span x-text="mensaje.length"></span>/500 caracteres
</div>

<div class="mb-3">
    <label class="form-label">Adjuntar imagen o documento</label>
    <input
    type="file"
    name="adjunto"
    class="form-control"
    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
    @change="
        adjuntoFile = $event.target.files[0];

       if (adjuntoFile) {
        if (/\s/.test(adjuntoFile.name)) {
            $event.target.value = '';
            adjuntoFile = null;
            adjuntoPreview = '';
            mostrarModal(
                'Nombre de archivo inválido',
                'El nombre del archivo no puede tener espacios. Renombralo y volvé a adjuntarlo.',
                'warning'
            );
            return;
        }

        adjuntoPreview = URL.createObjectURL(adjuntoFile);
        adjunto_path = '';
        adjunto_nombre = adjuntoFile.name;
        adjunto_tipo_mime = adjuntoFile.type;
    }
    "
>
    <small class="text-muted">Permitidos: JPG, PNG, PDF, DOC, DOCX. Máx 10MB.</small>
</div>
<div x-show="!adjuntoFile && adjunto_path" class="mt-3">
    <label class="form-label fw-bold">Adjunto actual</label>

    <template x-if="adjunto_tipo_mime && adjunto_tipo_mime.startsWith('image/')">
        <div class="border rounded p-2 bg-light">
            <img :src="'/storage/' + adjunto_path" class="img-fluid rounded" style="max-height: 220px;">
        </div>
    </template>

    <template x-if="!adjunto_tipo_mime || !adjunto_tipo_mime.startsWith('image/')">
        <div class="border rounded p-3 bg-light d-flex justify-content-between align-items-center">
            <span x-text="adjunto_nombre"></span>
            <a :href="'/storage/' + adjunto_path" target="_blank" class="btn btn-sm btn-primary">
                Ver archivo
            </a>
        </div>
    </template>
</div>
<!-- PREVIEW -->

</div>


<div x-show="step === 4">
    <h4 class="fw-bold mb-4">Resumen de la comunicación</h4>

    <div class="row g-4">
        <div class="col-lg-8">

            {{-- Datos principales --}}
            <div class="panel-card mb-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small d-block">Nombre</label>
                        <div class="fw-semibold fs-5" x-text="nombre || '-'"></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="text-muted small d-block">Solicitante*</label>
<div class="fw-semibold fs-5" x-text="solicitante || '-'"></div>                    </div>

                    <div class="col-12">
                        <label class="text-muted small d-block">Descripción</label>
                        <div class="fw-semibold" x-text="descripcion || '-'"></div>
                    </div>
                </div>
            </div>

<div class="panel-card mb-4">
    <h5 class="fw-bold mb-3">Segmentación aplicada</h5>

    <template x-if="tipo_segmentacion === 'segmentos'">
        <div>
            <strong>Segmentos:</strong>
            <span x-text="nombresSegmentosSeleccionados || '-'"></span>
        </div>
    </template>

    <template x-if="tipo_segmentacion === 'todo_personal'">
        <div>
            <strong>Segmentación:</strong>
            Todo el personal
        </div>
    </template>

    <template x-if="tipo_segmentacion === 'sql'">
        <div>
            <strong>Segmentación:</strong>
            Consulta SQL avanzada

            <pre
                class="bg-light border rounded p-3 small mt-3"
                style="white-space: pre-wrap;"
                x-text="segmentacion_sql || '-'"
            ></pre>
        </div>
    </template>

    <div class="mt-3">
        <strong>Destinatarios estimados:</strong>
        <span x-text="formatearNumero(alcance)"></span>
        personas
    </div>
</div>

            {{-- Alcance y costo --}}
            <div class="panel-card">
                <h5 class="fw-bold mb-4">Alcance y costo estimado</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded-4 p-4 h-100">
                            <div class="text-muted small mb-1">Destinatarios totales</div>
                            <div class="fs-4 fw-bold" x-text="formatearNumero(alcance)"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded-4 p-4 h-100 bg-light">
                            <div class="text-muted small mb-1">Costo estimado de la comunicación</div>
                            <div class="fs-4 fw-bold text-success">
                                USD <span x-text="calcularCosto()"></span>
                            </div>
                            <div class="small text-muted">
                                (USD 0,02 Costo por mensaje entregado)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Acciones --}}
        <div class="col-lg-4">
            <div class="panel-card">
                <div class="d-grid gap-3">
                     {{-- Mensaje --}}
                <div class="panel-card mb-4">
                    <label class="text-muted small d-block mb-2">Vista previa del mensaje</label>

                    <div style="background:#e5ddd5;padding:20px;border-radius:10px;width:320px">

                        <div style="background:white;padding:10px;border-radius:10px">

                            <p x-text="mensaje"></p>

                            <small class="text-muted">12:45</small>

                        </div>

                    </div>
                
                </div>
              <div class="panel-card mb-4">
    <label class="text-muted small d-block mb-3">
        Archivo adjunto
    </label>

    <!-- IMAGEN NUEVA -->
    <template x-if="adjuntoFile && adjuntoFile.type.startsWith('image/')">
        <div class="text-center">
            <img
                :src="adjuntoPreview"
                class="img-fluid rounded shadow border"
                style="max-width: 400px; max-height: 400px; object-fit: contain;">
        </div>
    </template>

    <!-- PDF NUEVO -->
    <template x-if="adjuntoFile && adjuntoFile.type === 'application/pdf'">
    <iframe
        :src="adjuntoPreview"
        width="100%"
        height="420"
        class="border rounded">
    </iframe>
</template>

    <!-- DOC/DOCX NUEVO -->
    <template x-if="adjuntoFile &&
        (
            adjuntoFile.type === 'application/msword' ||
            adjuntoFile.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        )">
        <div class="border rounded p-4 bg-light text-center">
            <div class="fs-1 mb-2">📄</div>

            <div class="fw-semibold" x-text="adjuntoFile.name"></div>

            <div class="small text-muted mt-1">
                Documento Word adjunto
            </div>
        </div>
    </template>

    <!-- ARCHIVO YA GUARDADO -->
    <template x-if="!adjuntoFile && adjunto_path">

        <div class="text-center">

            <!-- Imagen -->
            <template x-if="adjunto_tipo_mime && adjunto_tipo_mime.startsWith('image/')">
               <div class="text-center w-100 overflow-hidden">
    <img
        :src="'/storage/' + adjunto_path"
        class="img-fluid rounded shadow border"
        style="width: 100%; max-width: 320px; max-height: 260px; object-fit: contain;">
</div>
            </template>

            <!-- PDF -->
            <template x-if="adjunto_tipo_mime === 'application/pdf'">
                <iframe
                    :src="'/storage/' + adjunto_path"
                    width="100%"
                    height="500"
                    class="border rounded">
                </iframe>
            </template>

        </div>
    </template>

    <template x-if="!adjuntoFile && !adjunto_path">
        <div class="text-muted">
            Sin archivo adjunto
        </div>
    </template>
   
</div>
 <div x-show="fecha_programada" class="border rounded p-3 bg-light">
        <div class="small text-muted">
            Fecha programada
        </div>

        <div class="fw-semibold">
           <span x-text="formatearFechaProgramada(fecha_programada)"></span>
        </div>
    </div>
                </div>
            </div>
           
        </div>
    </div>
</div>

<div class="d-flex justify-content-between mt-4 pt-3 border-top">

    <div>
        <button
            type="button"
            class="btn btn-outline-danger px-4"
            @click="cancelar()">
            Cancelar
        </button>
    </div>

    <div class="d-flex align-items-center">
        <button
            type="button"
            class="btn btn-outline-secondary me-2 px-4"
            @click="anterior()"
            x-show="step > 1">
            Atrás
        </button>

  <button
    type="button"
    class="btn btn-primary"
    @click="probarSegmentacion"
    :disabled="loadingSegmentacion"
      x-show="step === 2"
>
    <span
        x-show="loadingSegmentacion"
        class="spinner-border spinner-border-sm me-1"
    ></span>

<span
    x-text="
        loadingSegmentacion
            ? 'Calculando destinatarios...'
            : 'Probar segmentación'
    "
></span>
</button>
        <button
            type="button"
            class="btn btn-success me-2 px-4"
            x-show="step == 4"
             @click.prevent="guardarBorrador()"
    :disabled="loadingGuardar">
           <span x-show="!loadingGuardar">Guardar </span>
    <span x-show="loadingGuardar">
        <span class="spinner-border spinner-border-sm me-2"></span>
        Guardando...
        </button>

       <button
    type="button"
    class="btn btn-primary px-4"
    @click="siguiente()"
    x-show="step < 4"
    :disabled="
    step === 2 &&
    (
        !segmentacionProbada ||
        alcance <= 0 ||
        (
            tipo_segmentacion === 'segmentos' &&
            segmentos_seleccionados.length === 0
        ) ||
        (
            tipo_segmentacion === 'sql' &&
            !segmentacion_sql.trim()
        )
    )
"
>
    Siguiente
</button>
    </div>

</div>

</form>

<!-- MODAL RESULTADO SEGMENTACION -->
<div class="modal fade" id="resultadoSegmentacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Resultado de Segmentación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">

                <h2 class="text-primary mb-3" x-text="alcance"></h2>
                <p>personas alcanzados</p>

                <div class="alert alert-warning mt-3"
                     x-show="advertencia_segmentacion">
                    <span x-text="advertencia_segmentacion"></span>
                </div>

                <div class="alert alert-danger mt-3"
                     x-show="alcance == 0">
    No se encontraron destinatarios para esta segmentación.
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" data-bs-dismiss="modal">
                    Aceptar
                </button>
            </div>

        </div>
    </div>
</div>
<!---->

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
</div>

</div>


</div>

</div>


@push('scripts')
<script>
function wizardComunicacion(
    comunicacion = null,
    segmentosDisponibles = []
) {
    return {
        step: 1,

        id: comunicacion?.id ?? null,

        modalTitulo: '',
        modalMensaje: '',
        modalTipo: 'info',

        nombre: comunicacion?.nombre ?? '',
        tipo: comunicacion?.tipo ?? '',
        descripcion: comunicacion?.descripcion ?? '',
        solicitante: comunicacion?.solicitante ?? '',

        segmentos: segmentosDisponibles ?? [],

        tipo_segmentacion:
        comunicacion?.tipo_segmentacion ?? 'segmentos',

        segmentos_seleccionados:
        comunicacion?.segmentos_seleccionados ?? [],

        segmentacion_sql:
        comunicacion?.segmentacion_sql ?? '',

        sql_generada: comunicacion?.sql_generada ?? '',

        segmentacionProbada:
        Number(comunicacion?.alcance ?? 0) > 0,

        segmentacionModificada: false,

        alcance: Number(comunicacion?.alcance ?? 0),

        fecha_programada:
            comunicacion?.fecha_programada ?? '',

        mensaje:
            comunicacion?.mensaje ?? '',

        adjuntoFile: null,
        adjuntoPreview: '',

        adjunto_path:
            comunicacion?.adjunto_path ?? '',

        adjunto_nombre:
            comunicacion?.adjunto_nombre ?? '',

        adjunto_tipo_mime:
            comunicacion?.adjunto_tipo_mime ?? '',

        advertencia_segmentacion: '',

        errores: {},

        loadingSegmentacion: false,
        loadingGuardar: false,

       get segmentosSeleccionadosDetalle() {
            return this.segmentos.filter(segmento =>
                this.segmentos_seleccionados
                    .map(String)
                    .includes(String(segmento.id))
            );
        },

        get nombresSegmentosSeleccionados() {
    return this.segmentos
        .filter(segmento =>
            this.segmentos_seleccionados
                .map(String)
                .includes(String(segmento.id))
        )
        .map(segmento => segmento.nombre)
        .join(', ');
},
        get modalTipoClase() {
            if (this.modalTipo === 'error') {
                return 'bg-danger text-white';
            }

            if (this.modalTipo === 'success') {
                return 'bg-success text-white';
            }

            if (this.modalTipo === 'warning') {
                return 'bg-warning';
            }

            return 'bg-primary text-white';
        },

        get progress() {
            return (this.step - 1) * 33;
        },

        toggleSegmento(segmento) {
    const id = String(segmento.id);

    const seleccionados =
        this.segmentos_seleccionados.map(String);

    const indice = seleccionados.indexOf(id);

    if (indice >= 0) {
        this.segmentos_seleccionados.splice(indice, 1);
    } else {
        this.segmentos_seleccionados.push(segmento.id);
    }

    this.marcarSegmentacionModificada();

    delete this.errores.segmentos_seleccionados;
},

        segmentoEstaSeleccionado(segmentoId) {
            return this.segmentos_seleccionados
                .map(String)
                .includes(String(segmentoId));
        },

        seleccionarTodosLosSegmentos() {
            this.segmentos_seleccionados =
                this.segmentos.map(segmento => segmento.id);

            this.marcarSegmentacionModificada();
        },

        limpiarSegmentos() {
            this.segmentos_seleccionados = [];

            this.marcarSegmentacionModificada();
        },
    cambiarTipoSegmentacion(tipo) {
        this.tipo_segmentacion = tipo;
        this.marcarSegmentacionModificada();
    },
        iconoSegmento(codigo) {
            const iconos = {
                MEDICOS: 'fa-solid fa-user-doctor',
                ENFERMERIA: 'fa-solid fa-user-nurse',
                KINESIOLOGIA: 'fa-solid fa-person-walking',
                LABORATORIO: 'fa-solid fa-flask',
                FARMACIA: 'fa-solid fa-pills',
                NUTRICION: 'fa-solid fa-apple-whole',
                PSICOLOGIA: 'fa-solid fa-brain',
                TRABAJO_SOCIAL: 'fa-solid fa-people-group',
                TECNICOS: 'fa-solid fa-screwdriver-wrench',
                ADMINISTRATIVOS: 'fa-solid fa-briefcase',
                DIRECTIVOS: 'fa-solid fa-building'
            };

            return iconos[codigo] ?? 'fa-solid fa-users';
        },

        mostrarModal(titulo, mensaje, tipo = 'info') {
            this.modalTitulo = titulo;
            this.modalMensaje = mensaje;
            this.modalTipo = tipo;

            this.$nextTick(() => {
                const elementoModal = document.getElementById(
                    'modalMensajeCampania'
                );

                if (!elementoModal) {
                    console.error(
                        'No se encontró el modal modalMensajeCampania'
                    );
                    return;
                }

                const modal = bootstrap.Modal.getOrCreateInstance(
                    elementoModal
                );

                modal.show();
            });
        },

        cancelar() {
            const confirmar = window.confirm(
                '¿Querés cancelar? Se perderán los cambios no guardados.'
            );

            if (confirmar) {
                window.location.href = '/comunicacion';
            }
        },

       marcarSegmentacionModificada() {
            this.segmentacionModificada = true;
            this.segmentacionProbada = false;

            this.alcance = 0;
            this.sql_generada = '';
            this.advertencia_segmentacion = '';
        },

        anterior() {
            this.errores = {};

            if (this.step > 1) {
                this.step--;
            }
        },

        async siguiente() {
            this.errores = {};

            /*
             * PASO 1
             */
            if (this.step === 1) {
                if (!this.nombre.trim()) {
                    this.errores.nombre = [
                        'Debe ingresar el nombre del aviso.'
                    ];
                }

                if (!this.tipo) {
                    this.errores.tipo = [
                        'Debe seleccionar el tipo de comunicación.'
                    ];
                }

                if (!this.descripcion.trim()) {
                    this.errores.descripcion = [
                        'Debe ingresar una descripción.'
                    ];
                }

                if (!this.solicitante) {
                    this.errores.solicitante = [
                        'Debe ingresar el solicitante.'
                    ];
                }

                if (Object.keys(this.errores).length > 0) {
                    this.mostrarModal(
                        'Faltan datos',
                        'Completá los campos obligatorios para continuar.',
                        'warning'
                    );

                    return;
                }
            }

            /*
             * PASO 2
             */
         if (this.step === 2) {

            if (
                this.tipo_segmentacion === 'segmentos' &&
                this.segmentos_seleccionados.length === 0
            ) {
                this.errores.segmentos_seleccionados = [
                    'Debe seleccionar al menos un segmento.'
                ];

                this.mostrarModal(
                    'Segmentos requeridos',
                    'Seleccioná al menos un grupo de personal.',
                    'warning'
                );

                return;
            }

            if (
                this.tipo_segmentacion === 'sql' &&
                !this.segmentacion_sql.trim()
            ) {
                this.errores.segmentacion_sql = [
                    'Debe ingresar la consulta SQL.'
                ];

                this.mostrarModal(
                    'Consulta requerida',
                    'Ingresá una consulta SQL antes de continuar.',
                    'warning'
                );

                return;
            }

            if (!this.segmentacionProbada) {
                this.mostrarModal(
                    'Segmentación pendiente',
                    'Primero tenés que probar la segmentación.',
                    'warning'
                );

                return;
            }

            if (Number(this.alcance) <= 0) {
                this.mostrarModal(
                    'Sin destinatarios',
                    'La segmentación no tiene destinatarios válidos.',
                    'warning'
                );

                return;
            }
        }

            /*
             * PASO 3
             */
            if (this.step === 3) {
                const MIN_MENSAJE = 10;
                const MAX_MENSAJE = 500;

                if (!this.mensaje || !this.mensaje.trim()) {
                    this.errores.mensaje = [
                        'Debe escribir el mensaje de la comunicación.'
                    ];

                    this.mostrarModal(
                        'Mensaje requerido',
                        'Debe escribir el mensaje de la comunicación.',
                        'warning'
                    );

                    return;
                }

                if (this.mensaje.trim().length < MIN_MENSAJE) {
                    this.errores.mensaje = [
                        `El mensaje debe tener al menos ${MIN_MENSAJE} caracteres.`
                    ];

                    this.mostrarModal(
                        'Mensaje demasiado corto',
                        `El mensaje debe tener al menos ${MIN_MENSAJE} caracteres.`,
                        'warning'
                    );

                    return;
                }

                if (this.mensaje.length > MAX_MENSAJE) {
                    this.errores.mensaje = [
                        `El mensaje no puede superar los ${MAX_MENSAJE} caracteres.`
                    ];

                    this.mostrarModal(
                        'Mensaje demasiado largo',
                        `El mensaje no puede superar los ${MAX_MENSAJE} caracteres.`,
                        'warning'
                    );

                    return;
                }

                if (this.adjuntoFile) {
                    const tiposPermitidos = [
                        'image/jpeg',
                        'image/png',
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ];

                    const maxSize = 10 * 1024 * 1024;

                    if (
                        !tiposPermitidos.includes(
                            this.adjuntoFile.type
                        )
                    ) {
                        this.adjuntoFile = null;
                        this.adjuntoPreview = '';

                        this.mostrarModal(
                            'Archivo inválido',
                            'Solo se permiten archivos JPG, PNG, PDF, DOC y DOCX.',
                            'warning'
                        );

                        return;
                    }

                    if (this.adjuntoFile.size > maxSize) {
                        this.adjuntoFile = null;
                        this.adjuntoPreview = '';

                        this.mostrarModal(
                            'Archivo demasiado grande',
                            'El archivo no puede superar los 10 MB.',
                            'warning'
                        );

                        return;
                    }
                }
            }

            if (this.step < 4) {
                this.step++;
            }
        },

async probarSegmentacion() {
    if (this.loadingSegmentacion) {
        return false;
    }
    this.errores = {};
    this.advertencia_segmentacion = '';
    this.alcance = 0;
    this.segmentacionProbada = false;

    if (
        this.tipo_segmentacion === 'segmentos' &&
        this.segmentos_seleccionados.length === 0
    ) {
        this.mostrarModal(
            'Segmentos requeridos',
            'Seleccioná al menos un segmento.',
            'warning'
        );

        return false;
    }

    if (
        this.tipo_segmentacion === 'sql' &&
        !this.segmentacion_sql.trim()
    ) {
        this.mostrarModal(
            'Consulta requerida',
            'Debe ingresar una consulta SQL.',
            'warning'
        );

        return false;
    }

    this.loadingSegmentacion = true;

    try {
        const response = await fetch(
            "{{ route('comunicacion.probar-segmentacion') }}",
            {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') ?? ''
                },

                body: JSON.stringify({
                    tipo_segmentacion: this.tipo_segmentacion,
                    segmentos: this.segmentos_seleccionados,
                    segmentacion_sql: this.segmentacion_sql
                })
            }
        );

        const data = await response.json();

        if (!response.ok) {

    console.error('Error segmentación:', data);

    this.mostrarModal(
        'Error en segmentación',
        data.error_real ??
        data.error_api ??
        data.message ??
        'No se pudo probar la segmentación.',
        'error'
    );

    return false;
}

        this.alcance = Number(data.cantidad ?? 0);
        this.sql_generada = data.sql_generada ?? '';
        /*
         * Muy útil:
         * guardamos la consulta que Laravel terminó generando.
         */
        if (data.sql_generada) {
            this.segmentacion_sql = data.sql_generada;
        }

        this.advertencia_segmentacion =
            data.advertencia ?? '';

        if (this.alcance <= 0) {
            this.mostrarModal(
                'Sin destinatarios',
                'La consulta no encontró destinatarios.',
                'warning'
            );

            return false;
        }

        this.segmentacionProbada = true;
        this.segmentacionModificada = false;

        this.mostrarModal(
            'Segmentación probada',
            `La consulta alcanza a ${this.formatearNumero(this.alcance)} personas.`,
            'success'
        );

        return true;

    } catch (error) {
        console.error(error);

        this.mostrarModal(
            'Error de conexión',
            'No se pudo conectar con el servidor.',
            'error'
        );

        return false;

    } finally {
        this.loadingSegmentacion = false;
    }
},

        async abrirResultado() {
            if (this.loadingSegmentacion) {
                return;
            }

            const ok = await this.probarSegmentacion();

            if (!ok) {
                return;
            }

            const elementoModal =
                document.getElementById('resultadoSegmentacion');

            if (!elementoModal) {
                return;
            }

            const modal = bootstrap.Modal.getOrCreateInstance(
                elementoModal
            );

            modal.show();
        },

        formatearNumero(valor) {
            return new Intl.NumberFormat('es-AR').format(
                Number(valor) || 0
            );
        },
        calcularCosto() {
    const COSTO_POR_MENSAJE = 0.02;

    const total =
        (Number(this.alcance) || 0) *
        COSTO_POR_MENSAJE;

    return total.toFixed(2);
},

        formatearFechaProgramada(valor) {
            if (!valor) {
                return '-';
            }

            const [fecha, horaCompleta] = valor.split('T');

            if (!fecha || !horaCompleta) {
                return valor;
            }

            const [anio, mes, dia] = fecha.split('-');
            const hora = horaCompleta.substring(0, 5);

            return `${dia}/${mes}/${anio} - ${hora}`;
        },

        async guardarBorrador() {
            if (this.loadingGuardar) {
                return;
            }

            this.errores = {};

            if (!this.segmentacionProbada || this.alcance <= 0) {
                this.mostrarModal(
                    'Segmentación pendiente',
                    'La segmentación debe estar probada antes de guardar.',
                    'warning'
                );

                return;
            }

            this.loadingGuardar = true;

            const formData = new FormData();

            formData.append(
                'id',
                this.id ?? ''
            );

            formData.append(
                'nombre',
                this.nombre ?? ''
            );

            formData.append(
                'tipo',
                this.tipo ?? ''
            );

            formData.append(
                'descripcion',
                this.descripcion ?? ''
            );

            formData.append(
                'solicitante',
                this.solicitante ?? ''
            );

            formData.append(
    'tipo_segmentacion',
    this.tipo_segmentacion
);

(this.segmentos_seleccionados ?? []).forEach(id => {
    formData.append('segmentos[]', id);
});

formData.append(
    'segmentacion_sql',
    this.tipo_segmentacion === 'sql'
        ? this.segmentacion_sql
        : ''
);
            formData.append(
                'cantidad_destinatarios',
                this.alcance ?? 0
            );

            formData.append(
                'segmentacion_modificada',
                this.segmentacionModificada ? '1' : '0'
            );

            formData.append(
                'mensaje',
                this.mensaje ?? ''
            );

            formData.append(
                'fecha_programada',
                this.fecha_programada ?? ''
            );

            if (this.adjuntoFile) {
                formData.append(
                    'adjunto',
                    this.adjuntoFile
                );
            }

            try {
                const response = await fetch(
                    "{{ route('comunicacion.guardar-borrador') }}",
                    {
                        method: 'POST',

                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN':
                                document
                                    .querySelector(
                                        'meta[name="csrf-token"]'
                                    )
                                    ?.getAttribute('content') ?? ''
                        },

                        body: formData
                    }
                );

                const raw = await response.text();

                let data;

                try {
                    data = JSON.parse(raw);
                } catch (error) {
                    console.error(
                        'Respuesta no JSON:',
                        raw
                    );

                    this.mostrarModal(
                        'Error inesperado',
                        'El servidor no devolvió JSON. Revisá la consola para ver el error real.',
                        'error'
                    );

                    return;
                }

                if (!response.ok) {
                    if (data.errors) {
    this.errores = data.errors;

    console.log('Errores de validación:', data.errors);

    const mensajes = Object.entries(data.errors)
        .flatMap(([campo, errores]) =>
            errores.map(error => `• ${campo}: ${error}`)
        )
        .join('\n');

    this.mostrarModal(
        'Datos inválidos',
        mensajes,
        'warning'
    );
} else {
                        this.mostrarModal(
                            'No se pudo guardar',
                            data.error_real ??
                                data.message ??
                                'Error del servidor.',
                            'error'
                        );
                    }

                    return;
                }

                this.id = data.id ?? this.id;

                this.alcance = Number(
                    data.alcance ??
                    data.cantidad ??
                    this.alcance
                );
                this.sql_generada = data.sql_generada ?? '';
                this.advertencia_segmentacion =
                    data.advertencia ?? '';

                this.segmentacionModificada = false;

                if (data.warning) {
                    this.mostrarModal(
                        'Comunicación guardada con advertencia',
                        `${data.message ?? 'La comunicación fue guardada.'} Detalle: ${data.warning}`,
                        'warning'
                    );
                } else {
                    this.mostrarModal(
                        'Comunicación guardada',
                        data.message ??
                            'La comunicación fue guardada correctamente.',
                        'success'
                    );
                }

            } catch (error) {
                console.error(error);

                this.mostrarModal(
                    'Error de conexión',
                    'No se pudo conectar con el servidor.',
                    'error'
                );

            } finally {
                this.loadingGuardar = false;
            }
        },

        async ejecutarAhora() {
            if (
                !this.segmentacionProbada ||
                Number(this.alcance) <= 0
            ) {
                this.mostrarModal(
                    'Segmentación pendiente',
                    'Primero tenés que probar la segmentación.',
                    'warning'
                );

                return;
            }

            try {
                const response = await fetch(
                    '/comunicacion',
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN':
                                document
                                    .querySelector(
                                        'meta[name="csrf-token"]'
                                    )
                                    ?.getAttribute('content') ?? ''
                        },

                        body: JSON.stringify({
                            id: this.id,
                            nombre: this.nombre,
                            tipo: this.tipo,
                            descripcion: this.descripcion,
                            solicitante: this.solicitante,
                            segmentos_seleccionados: this.segmentos_seleccionados,
                            mensaje: this.mensaje,
                            alcance: this.alcance,
                            estado: 'finalizada'
                        })
                    }
                );

                const raw = await response.text();

                let data = {};

                try {
                    data = JSON.parse(raw);
                } catch (error) {
                    console.error(
                        'Respuesta no JSON:',
                        raw
                    );
                }

                if (!response.ok) {
                    this.mostrarModal(
                        'No se pudo ejecutar',
                        data.message ??
                            'Ocurrió un error al ejecutar la comunicación.',
                        'error'
                    );

                    return;
                }

                window.location.href = '/comunicacion';

            } catch (error) {
                console.error(error);

                this.mostrarModal(
                    'Error de conexión',
                    'No se pudo conectar con el servidor.',
                    'error'
                );
            }
        }
    };
}
</script>
@endpush

@endsection
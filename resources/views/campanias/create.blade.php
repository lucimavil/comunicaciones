@extends('layouts.app')

@section('content')

<div class="container mt-4">

@php
    $campaniaData = isset($campania) ? [
        'id' => $campania->id,
        'nombre' => $campania->titulo,
        'descripcion' => $campania->descripcion,
        'solicitante' => $campania->solicitante,
        'segmentacion_tipo' => $campania->segmentacion_tipo,
        'edad_min' => $campania->edad_min,
        'edad_max' => $campania->edad_max,
        'sexo' => $campania->sexo,
        'localidad' => $campania->localidad,
        'diagnostico' => $campania->diagnostico,
        'ultima_atencion_desde' => optional($campania->ultima_atencion_desde)->format('Y-m-d'),
        'ultima_atencion_hasta' => optional($campania->ultima_atencion_hasta)->format('Y-m-d'),
        'segmentacion_sql' => $campania->segmentacion_sql,
        'alcance' => $campania->cantidad_destinatarios,
        'mensaje' => $campania->mensaje,
        'fecha_programada' => optional($campania->fecha_programada)?->format('Y-m-d\TH:i'),
        'adjunto_path' => $campania->adjunto_path,
        'adjunto_nombre' => $campania->adjunto_nombre,
        'adjunto_tipo_mime' => $campania->adjunto_tipo_mime,
  ] : [
    'segmentacionProbada' => false,
];
@endphp
<div x-data='wizardCampania(@json($campaniaData))' class="bg-white shadow rounded p-4">

<h4 class="fw-bold mb-4">Nueva Campaña</h4>


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

<form method="POST" action="{{ route('campanias.store') }}">
@csrf


<!-- PASO 1 -->

<div x-show="step==1">

<h5 class="mb-3">Información de la campaña</h5>

<label class="form-label">Nombre *</label>

<input
type="text"
name="nombre"
class="form-control"
x-model="nombre"
:class="{'is-invalid':errores.nombre}">

<template x-if="errores.nombre">
    <div class="invalid-feedback d-block" x-text="errores.nombre[0]"></div>
</template>

<label class="form-label mt-3">Descripción *</label>

<textarea
name="descripcion"
class="form-control"
x-model="descripcion"
:class="{'is-invalid':errores.descripcion}">
</textarea>
<template x-if="errores.descripcion">
    <div class="invalid-feedback d-block" x-text="errores.descripcion[0]"></div>
</template>


<div class="mb-3">
    <label class="form-label">Solicitante*</label>
    <select
        class="form-control"
        x-model="solicitante"
        name="solicitante"
        :class="{'is-invalid': errores.solicitante}">
        
        <option value="">Seleccione...</option>
        <option value="Cardiología">Cardiología</option>
        <option value="Infectología">Infectología</option>
        <option value="Dirección Médica">Dirección</option>
        <option value="Imágenes">Imágenes</option>
    </select>

    
    <template x-if="errores.solicitante">
        <div class="invalid-feedback d-block" x-text="errores.solicitante[0]"></div>
    </template>
</div>

<div class="invalid-feedback">
    Debe seleccionar un solicitante.
</div>
</div>
<div class="mb-3">
    <label class="form-label">Responsable</label>
    <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
</div>
<!-- PASO 2 -->

<div x-show="step==2">

    <h5 class="mb-4">Segmentación de pacientes</h5>

    <div class="mb-4">
        <label class="form-label fw-semibold">Método de segmentación *</label>

        <div class="d-flex gap-4 mt-2">
            <div class="form-check">
                <input
                    class="form-check-input"
                    type="radio"
                    name="segmentacion_tipo"
                    id="segmentacion_filtros"
                    value="filtros"
                    x-model="segmentacion_tipo">

                <label class="form-check-label" for="segmentacion_filtros">
                    Filtros predefinidos
                </label>
            </div>

            <div class="form-check">
                <input
                    class="form-check-input"
                    type="radio"
                    name="segmentacion_tipo"
                    id="segmentacion_sql"
                    value="sql"
                    x-model="segmentacion_tipo">

                <label class="form-check-label" for="segmentacion_sql">
                    Consulta SQL avanzada
                </label>
            </div>
        </div>
    </div>

    <div class="alert alert-info" x-show="segmentacion_tipo == 'filtros'">
        Usá filtros demográficos básicos para definir el público objetivo.
    </div>

    <div class="alert alert-warning" x-show="segmentacion_tipo == 'sql'">
        La consulta SQL debe ser solo de lectura, tipo <b>SELECT</b>, y devolver al menos
        datos identificatorios y de contacto del paciente.
    </div>

    <!-- MODO FILTROS -->
    <div x-show="segmentacion_tipo == 'filtros'">

        <div class="card mb-4">
            <div class="card-body">

                <h6>Rango de edad</h6>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Edad mínima</label>
                        <input
    type="range"
    class="form-range"
    min="14"
    max="100"
    x-model="edad_min"
    @input="marcarSegmentacionModificada()">
                        <p class="small text-muted">
                            <span x-text="edad_min"></span> años
                        </p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Edad máxima</label>
                       <input
    type="range"
    class="form-range"
    min="14"
    max="100"
    x-model="edad_max"
    @input="marcarSegmentacionModificada()">
                        <p class="small text-muted">
                            <span x-text="edad_max"></span> años
                        </p>
                    </div>
                </div>

                <div class="alert alert-light mb-0">
                    Rango seleccionado:
                    <b>
                        <span x-text="edad_min"></span> -
                        <span x-text="edad_max"></span> años
                    </b>
                </div>

            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Sexo</label>
                <select
                    class="form-control mb-3"
                    x-model="sexo"
                    name="sexo"
                    @change="marcarSegmentacionModificada()">
                    <option value="">Todos</option>
                    <option value="F">Femenino</option>
                    <option value="M">Masculino</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Localidad</label>
                <input
                    type="text"
                    class="form-control mb-3"
                    x-model="localidad"
                    name="localidad"
                    placeholder="Ej: San Luis"
                     @change="marcarSegmentacionModificada()">
            </div>

            <div class="col-md-4">
                <label class="form-label">Diagnóstico</label>
                <input
                    type="text"
                    class="form-control mb-3"
                    x-model="diagnostico"
                    name="diagnostico"
                    placeholder="Ej: Diabetes"
                   @change="marcarSegmentacionModificada()">
            </div>

        </div>

        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Última atención desde*</label>
                <input
                    type="date"
                    class="form-control mb-3"
                    x-model="ultima_atencion_desde"
                    name="ultima_atencion_desde" @change="marcarSegmentacionModificada()">
            </div>

            <div class="col-md-6">
                <label class="form-label">Última atención hasta*</label>
                <input
                    type="date"
                    class="form-control mb-3"
                    x-model="ultima_atencion_hasta"
                    name="ultima_atencion_hasta"  @change="marcarSegmentacionModificada()">
            </div>
        </div>

        <input type="hidden"  @change="segmentacionModificada = true; segmentacionProbada = false"  name="edad_min" :value="edad_min">
        <input type="hidden"   @change="segmentacionModificada = true; segmentacionProbada = false" name="edad_max" :value="edad_max">
    </div>

    <!-- MODO SQL -->
    <div x-show="segmentacion_tipo == 'sql'">
        <label class="form-label">Consulta SQL *</label>

        <textarea
            name="segmentacion_sql"
           @change="marcarSegmentacionModificada()"
            class="form-control"
            rows="8"
            x-model="segmentacion_sql"
            placeholder="Ejemplo: select codigoPersona, nombrePaciente, dniPaciente, telefono from dual
La consulta debe traer campos con esos alias">
        </textarea>

     <template x-if="segmentacion_tipo === 'sql' && errores.segmentacion_sql">
        <div class="text-danger mt-1" x-text="errores.segmentacion_sql[0]"></div>
    </template>

        <div class="form-text mt-2">
            Recomendado: permitir solo consultas sobre vistas autorizadas.
        </div>
    </div>

    <!-- RESUMEN -->
    <div class="border rounded-4 p-4 mt-4 d-flex align-items-center"
         style="background:#eef4fb;border-color:#c8dbf6;">

        <div class="me-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                <path d="M16 3.128a4 4 0 0 1 0 7.744"></path>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                <circle cx="9" cy="7" r="4"></circle>
            </svg>
        </div>

        <div>
            <div class="text-muted small">Destinatarios estimados</div>
            <div class="fw-semibold text-primary">
                <span x-text="alcance"></span>
            </div>
            <div class="small text-warning mt-1" x-show="advertencia_segmentacion">
                <span x-text="advertencia_segmentacion"></span>
            </div>
        </div>
    </div>
</div>

<!-- PASO 3 -->

<div x-show="step==3">

<h5 class="mb-3">Mensaje de la campaña</h5>

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
            adjuntoPreview = URL.createObjectURL(adjuntoFile);
        }
    "
>
    <small class="text-muted">Permitidos: JPG, PNG, PDF, DOC, DOCX. Máx 10MB.</small>
</div>
<div x-show="adjunto_path" class="mt-3">
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
    <h4 class="fw-bold mb-4">Resumen de campaña</h4>

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
                        <div class="fw-semibold fs-5" x-text="solicitante || '-'"></div>
                    </div>

                    <div class="col-12">
                        <label class="text-muted small d-block">Descripción</label>
                        <div class="fw-semibold" x-text="descripcion || '-'"></div>
                    </div>
                </div>
            </div>

           <div class="panel-card mb-4">
    <h5 class="fw-bold mb-3">Segmentación aplicada</h5>

    <template x-if="segmentacion_tipo === 'filtros'">
        <div>
            <div><strong>Tipo:</strong> Filtros predefinidos</div>
            <div><strong>Rango de edad:</strong> <span x-text="edad_min"></span> a <span x-text="edad_max"></span> años</div>
            <div><strong>Sexo:</strong> <span x-text="sexo || 'Todos'"></span></div>
            <div><strong>Localidad:</strong> <span x-text="localidad || 'Todas'"></span></div>
            <div><strong>Diagnóstico:</strong> <span x-text="diagnostico || 'Todos'"></span></div>
            <div><strong>Última atención desde:</strong> <span x-text="ultima_atencion_desde || '-'"></span></div>
            <div><strong>Última atención hasta:</strong> <span x-text="ultima_atencion_hasta || '-'"></span></div>
        </div>
    </template>

    <template x-if="segmentacion_tipo === 'sql'">
        <div>
            <div><strong>Tipo:</strong> Consulta SQL avanzada</div>

            <label class="form-label mt-3">Consulta SQL aplicada</label>
            <pre class="bg-light border rounded p-3 small"
                 style="white-space: pre-wrap;"
                 x-text="segmentacion_sql || '-'"></pre>
        </div>
    </template>
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
                            <div class="text-muted small mb-1">Costo estimado de la campaña</div>
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
            <span x-text="fecha_programada"></span>
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
      x-show="step > 1"
>
    <span
        x-show="loadingSegmentacion"
        class="spinner-border spinner-border-sm me-1"
    ></span>

    <span x-text="loadingSegmentacion ? 'Calculando pacientes...' : 'Probar segmentación'"></span>
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
            x-show="step < 4">
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
                <p>pacientes alcanzados</p>

                <div class="alert alert-warning mt-3"
                     x-show="advertencia_segmentacion">
                    <span x-text="advertencia_segmentacion"></span>
                </div>

                <div class="alert alert-danger mt-3"
                     x-show="alcance == 0">
                    No se encontraron pacientes para esta segmentación.
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
function wizardCampania(campania = null) {
     
    return {
        
        step: 1,
        id: campania?.id ?? null,
        modalTitulo: '',
        modalMensaje: '',
        modalTipo: 'info',

        nombre: campania?.nombre ?? '',
        descripcion: campania?.descripcion ?? '',
        solicitante: campania?.solicitante ?? '',
        segmentacionProbada: campania?.alcance > 0,
        segmentacionModificada: false,

        segmentacion_tipo: 'filtros',
        segmentacion_tipo: campania?.segmentacion_tipo ?? 'filtros',
        edad_min: campania?.edad_min ?? '',
        edad_max: campania?.edad_max ?? '',
         sexo: campania?.sexo ?? '',
        localidad: campania?.localidad ?? '',
        diagnostico: campania?.diagnostico ?? '',
        ultima_atencion_desde: campania?.ultima_atencion_desde ?? '',
        ultima_atencion_hasta: campania?.ultima_atencion_hasta ?? '',
        segmentacion_sql: campania?.segmentacion_sql ?? '',

        alcance: campania?.alcance ?? 0,
        fecha_programada: campania?.fecha_programada ?? '',
        mensaje: campania?.mensaje ?? '',
        adjuntoFile: null,
        adjunto_path: campania?.adjunto_path ?? '',
        adjunto_nombre: campania?.adjunto_nombre ?? '',
        adjunto_tipo_mime: campania?.adjunto_tipo_mime ?? '',

        advertencia_segmentacion: '',
        errores: {},
        loadingSegmentacion: false,
        loadingGuardar: false,

        get modalTipoClase() {
            if (this.modalTipo === 'error') return 'bg-danger text-white';
            if (this.modalTipo === 'success') return 'bg-success text-white';
            if (this.modalTipo === 'warning') return 'bg-warning';
            return 'bg-primary text-white';
        },
        mostrarModal(titulo, mensaje, tipo = 'info') {
            this.modalTitulo = titulo;
            this.modalMensaje = mensaje;
            this.modalTipo = tipo;

            this.$nextTick(() => {
                const modal = new bootstrap.Modal(
                    document.getElementById('modalMensajeCampania')
                );
                modal.show();
            });
        },
        get progress() {
            return (this.step - 1) * 33;
        },
        cancelar() {
            if (confirm('¿Querés cancelar? Se perderán los cambios no guardados.')) {
                window.location.href = '/campanias';
            }
        },
        marcarSegmentacionModificada() {
            this.segmentacionModificada = true;
            this.segmentacionProbada = false;
            this.alcance = 0;
            this.advertencia_segmentacion = '';
        },
        async siguiente() {
            this.errores = {};

           if (this.step == 1) {
                if (!this.nombre) this.errores.nombre = ['Debe ingresar el nombre de la campaña'];
                if (!this.descripcion) this.errores.descripcion = ['Debe ingresar una descripción'];
                if (!this.solicitante) this.errores.solicitante = ['Debe seleccionar un solicitante'];

                if (Object.keys(this.errores).length > 0) {
                    this.mostrarModal(
                        'Faltan datos',
                        'Completá los campos obligatorios para continuar.',
                        'warning'
                    );
                    return;
                }
            }
            if (this.step == 2) {
                if (this.segmentacion_tipo === 'filtros') {
                    if (this.alcance <= 0) {
                        this.mostrarModal('Segmentación inválida', 'No se puede continuar sin pacientes alcanzados.', 'warning');
                        return;
                    }

                    if (this.advertencia_segmentacion) {
                        this.mostrarModal('Segmentación inválida', this.advertencia_segmentacion, 'warning');
                        return;
                    }
                    if (parseInt(this.edad_min) > parseInt(this.edad_max)) {
                        this.mostrarModal(
                            'Validación',
                            'La edad mínima no puede ser mayor a la edad máxima',
                            'warning'
                        );
                        return;
                    }
                    if (this.alcance <= 0) {
                        this.mostrarModal(
                            'Segmentación inválida',
                            'No puede continuar con una segmentación sin pacientes.',
                            'warning'
                        );
                        return;
                    }

                    if (this.advertencia_segmentacion) {
                        this.mostrarModal(
                            'Segmentación inválida',
                            this.advertencia_segmentacion,
                            'warning'
                        );
                        return;
                    }

                   if (!this.segmentacionProbada) {
                        this.mostrarModal(
                            'Segmentación pendiente',
                            'Primero tenés que probar la segmentación antes de continuar.',
                            'warning'
                        );
                        return;
                    }
                }

                if (this.segmentacion_tipo === 'sql') {
                    if (!this.segmentacion_sql.trim()) {
                        this.errores.segmentacion_sql = ['Debe ingresar una consulta SQL'];
                        return;
                    }

                    const ok = await this.probarSegmentacion();
                    if (!ok) return;
                }
            }

           if (this.step == 3) {

                if (!this.mensaje) {
                    this.errores.mensaje = true;

                    this.mostrarModal(
                        'Mensaje requerido',
                        'Debe escribir el mensaje de la campaña.',
                        'warning'
                    );

                    return;
                }
                   const MIN_MENSAJE = 10;
    const MAX_MENSAJE = 500;

    // Validar vacío
    if (!this.mensaje || !this.mensaje.trim()) {

        this.errores.mensaje = true;

        this.mostrarModal(
            'Mensaje requerido',
            'Debe escribir el mensaje de la campaña.',
            'warning'
        );

        return;
    }

    // Validar mínimo
    if (this.mensaje.trim().length < MIN_MENSAJE) {

        this.mostrarModal(
            'Mensaje demasiado corto',
            `El mensaje debe tener al menos ${MIN_MENSAJE} caracteres.`,
            'warning'
        );

        return;
    }

    // Validar máximo
    if (this.mensaje.length > MAX_MENSAJE) {

        this.mostrarModal(
            'Mensaje demasiado largo',
            `El mensaje no puede superar los ${MAX_MENSAJE} caracteres.`,
            'warning'
        );

        return;
    }
                // VALIDAR ADJUNTO
                if (this.adjuntoFile) {

                    const tiposPermitidos = [
                        'image/jpeg',
                        'image/png',
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ];

                    const maxSize = 10 * 1024 * 1024; // 10MB

                    // Validar tipo
                    if (!tiposPermitidos.includes(this.adjuntoFile.type)) {

                        this.adjuntoFile = null;

                        this.mostrarModal(
                            'Archivo inválido',
                            'Solo se permiten archivos JPG, PNG, PDF, DOC y DOCX.',
                            'warning'
                        );

                        return;
                    }

                    // Validar tamaño
                    if (this.adjuntoFile.size > maxSize) {

                        this.adjuntoFile = null;

                        this.mostrarModal(
                            'Archivo demasiado grande',
                            'El archivo no puede superar los 10MB.',
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
        anterior() {
            this.errores = {};

            if (this.step > 1) {
                this.step--;
            }
        },
        async probarSegmentacion() {
            if (this.loadingSegmentacion) return;
            this.errores = {};
            this.advertencia_segmentacion = '';
            this.alcance = 0;

            const payload = {
                segmentacion_tipo: this.segmentacion_tipo,
                edad_min: this.edad_min,
                edad_max: this.edad_max,
                sexo: this.sexo,
                localidad: this.localidad,
                diagnostico: this.diagnostico,
                ultima_atencion_desde: this.ultima_atencion_desde,
                ultima_atencion_hasta: this.ultima_atencion_hasta,
                segmentacion_sql: this.segmentacion_sql
            };
            if (this.segmentacion_tipo === 'filtros') {
                if (parseInt(this.edad_min) > parseInt(this.edad_max)) {
                    this.mostrarModal('Validación', 'La edad mínima no puede ser mayor a la edad máxima', 'warning');
                    return false;
                }

                if (!this.ultima_atencion_desde || !this.ultima_atencion_hasta) {
                    this.mostrarModal('Validación', 'Debe ingresar el rango de fechas de atención para probar la segmentación.', 'warning');
                    return false;
                }

                if (this.ultima_atencion_desde > this.ultima_atencion_hasta) {
                    this.mostrarModal('Validación', 'La fecha desde no puede ser mayor a la fecha hasta.', 'warning');
                    return false;
                }
            }
            
            this.loadingSegmentacion = true;
            try {
                const response = await fetch("{{ route('campanias.probar-segmentacion') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify(payload),
                });
                const raw = await response.text();
                  let data = {};
                    try {
                        data = JSON.parse(raw);
                        dd(data);
                        this.alcance = data.cantidad ?? 0;
                        this.advertencia_segmentacion = data.advertencia ?? '';

                        if (this.alcance <= 0) {
                            this.segmentacionProbada = false;
                            this.mostrarModal(
                                'Segmentación inválida',
                                'La segmentación no arrojó pacientes.',
                                'warning'
                            );

                            return false;
                           
                        }

                        if (data.supera_maximo) {
                            this.segmentacionProbada = false;

                            this.mostrarModal(
                                'Segmentación inválida',
                                data.advertencia,
                                'warning'
                            );

                            return false;
                        }

                        this.segmentacionProbada = true;
                        this.segmentacionModificada = false;

                        return true;
                    } catch (jsonError) {
                        console.error('La respuesta no es JSON válido:', raw);
                        this.mostrarModal(
                            'Error inesperado',
                            'El servidor respondió, pero no devolvió un JSON válido. Revisá la consola.',
                            'error'
                        );
                        return false;
                    }

                

                if (!response.ok) {

                    if (data.errors) {
                        this.errores = data.errors;
                    } else {
                        this.mostrarModal(
                            'Error en segmentación',
                            data.message || 'Ocurrió un error al validar la segmentación',
                            'error'
                        );
                    }

                    return false;
                }

                this.alcance = data.cantidad ?? 0;
                this.advertencia_segmentacion = data.advertencia ?? '';
                this.segmentacionProbada = true;
                this.segmentacionModificada = false;
                return true;

            } catch (e) {
                console.error('ERROR REAL:', e);
                this.mostrarModal(
                    'Error de conexión',
                    'No se pudo conectar con el servidor',
                    'error'
                );
                return false;
            } finally {
                this.loadingSegmentacion = false; // 👈 termina carga SIEMPRE
            }
        },

        async abrirResultado() {
            if (this.loadingSegmentacion) return;
            const ok = await this.probarSegmentacion();

            if (!ok) return;

            let modal = new bootstrap.Modal(
                document.getElementById('resultadoSegmentacion')
            );
              modal.show();
        },
        formatearNumero(valor) {
            return new Intl.NumberFormat('es-AR').format(valor || 0);
        },

        calcularCosto() {
            let total = (this.alcance || 0) * 0.02;
            return total.toFixed(2);
        },
    async guardarBorrador() {
    if (this.loadingGuardar) return;

    this.errores = {};
    this.loadingGuardar = true;

    const formData = new FormData();

    formData.append('id', this.id ?? '');
    formData.append('nombre', this.nombre ?? '');
    formData.append('descripcion', this.descripcion ?? '');
    formData.append('solicitante', this.solicitante ?? '');
    formData.append('segmentacion_modificada', this.segmentacionModificada ? '1' : '0');

    formData.append('segmentacion_tipo', this.segmentacion_tipo ?? 'filtros');
    formData.append('edad_min', this.edad_min ?? '');
    formData.append('edad_max', this.edad_max ?? '');
    formData.append('sexo', this.sexo ?? '');
    formData.append('localidad', this.localidad ?? '');
    formData.append('diagnostico', this.diagnostico ?? '');
    formData.append('ultima_atencion_desde', this.ultima_atencion_desde ?? '');
    formData.append('ultima_atencion_hasta', this.ultima_atencion_hasta ?? '');
    formData.append('segmentacion_sql', this.segmentacion_sql ?? '');

    formData.append('mensaje', this.mensaje ?? '');

    if (this.adjuntoFile) {
        formData.append('adjunto', this.adjuntoFile);
    }

    try {
        const response = await fetch("{{ route('campanias.guardar-borrador') }}", {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData,
        });

        const raw = await response.text();
        console.log('RESPUESTA GUARDAR:', raw);

        let data = {};

        try {
            data = JSON.parse(raw);
        } catch (e) {
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
            } else {
                this.mostrarModal(
                    'No se pudo guardar',
                    data.error_real || data.message || 'Error del servidor',
                    'error'
                );
            }

            return;
        }

        this.id = data.id;
        this.alcance = data.cantidad ?? 0;
        this.advertencia_segmentacion = data.advertencia ?? '';
        this.segmentacion_sql = data.sql_generada ?? this.segmentacion_sql;

        if (data.warning) {
            this.mostrarModal(
                'Campaña guardada con advertencia',
                data.message + ' Detalle: ' + data.warning,
                'warning'
            );
        } else {
            this.mostrarModal(
                'Campaña guardada',
                data.message || 'Campaña guardada',
                'success'
            );
        }

    } catch (e) {
        console.error(e);

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
                const response = await fetch('/campanias', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        nombre: this.nombre,
                        descripcion: this.descripcion,
                        solicitante: this.solicitante,
                        mensaje: this.mensaje,
                        alcance: this.alcance,
                        estado: 'finalizada'
                    })
                });

                if (response.ok) {
                    window.location.href = '/campanias';
                }
            },      
        }
}

function validarSqlFrontend(sql) {
    const errores = [];
    const sqlLower = sql.toLowerCase().trim();

    if (!sqlLower) {
        errores.push('Debe ingresar una consulta SQL.');
        return errores;
    }

    if (!sqlLower.startsWith('select')) {
        errores.push('La consulta debe comenzar con SELECT.');
    }

    if (!sqlLower.includes(' from ')) {
        errores.push('La consulta debe incluir FROM.');
    }

    const prohibidas = ['insert', 'update', 'delete', 'drop', 'truncate', 'alter'];

    prohibidas.forEach(palabra => {
        const regex = new RegExp(`\\b${palabra}\\b`, 'i');
        if (regex.test(sql)) {
            errores.push(`No se permite usar ${palabra.toUpperCase()}.`);
        }
    });

    const aliases = ['codigopersona', 'nombrepaciente', 'dnipaciente', 'telefono'];

    const selectMatch = sql.match(/select([\s\S]*?)from/i);
    const selectPart = (selectMatch ? selectMatch[1] : '').toLowerCase();

    aliases.forEach(alias => {
        if (!selectPart.includes(alias)) {
            errores.push(`Falta el alias obligatorio: ${alias}`);
        }
    });

    return errores;
}
function validarYAvanzar() {
    const tipo = document.getElementById('tipo_segmentacion').value;
    const sql = document.getElementById('segmentacion_sql').value;
    const contenedorErrores = document.getElementById('errores');

    contenedorErrores.innerHTML = '';

    if (tipo === 'segmentacion_sql') {
        const errores = validarSqlFrontend(sql);

        if (errores.length > 0) {
            contenedorErrores.innerHTML = errores.map(e => `<div>${e}</div>`).join('');
            return;
        }
    }

    // Acá igual deberías llamar al backend para validar de verdad
    guardarOAvanzar();
}

</script>
@endpush

@endsection
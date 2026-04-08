@extends('layouts.app')

@section('content')

<div class="container mt-4">

<div x-data="wizardCampania()" class="bg-white shadow rounded p-4">

<h4 class="fw-bold mb-4">Nueva Campaña</h4>


<!-- PROGRESS -->

<div class="progress mb-4" style="height:6px">
<div class="progress-bar bg-dark" :style="'width:'+progress+'%'"></div>
</div>

<div class="d-flex justify-content-between small text-muted mb-4">
<span :class="{'fw-bold text-dark':step>=1}">1 Información</span>
<span :class="{'fw-bold text-dark':step>=2}">2 Segmentación</span>
<span :class="{'fw-bold text-dark':step>=3}">3 Mensaje</span>
<span :class="{'fw-bold text-dark':step>=4}">4 Confirmar</span>
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

<div class="invalid-feedback">
Ingrese el nombre
</div>


<label class="form-label mt-3">Descripción *</label>

<textarea
name="descripcion"
class="form-control"
x-model="descripcion"
:class="{'is-invalid':errores.descripcion}">
</textarea>

<div class="invalid-feedback">
Ingrese una descripción
</div>


<label class="form-label mt-3">Responsable *</label>

<select
name="responsable_id"
class="form-control"
x-model="responsable"
:class="{'is-invalid':errores.responsable}">

<option value="">Seleccione</option>

@foreach($usuarios as $u)

<option value="{{ $u->id }}">
{{ $u->name }}
</option>

@endforeach

</select>

<div class="invalid-feedback">
Seleccione responsable
</div>

</div>



<!-- PASO 2 -->

<div x-show="step==2">

<h5 class="mb-4">Filtros de segmentación</h5>


<div class="card mb-4">
<div class="card-body">

<h6>Rango de edad</h6>

<div class="row">

<div class="col-md-6">

<label class="form-label">Edad mínima</label>

<input
type="range"
class="form-range"
min="0"
max="100"
x-model="edad_min">

<p class="small text-muted">
<span x-text="edad_min"></span> años
</p>

</div>


<div class="col-md-6">

<label class="form-label">Edad máxima</label>

<input
type="range"
class="form-range"
min="0"
max="100"
x-model="edad_max">

<p class="small text-muted">
<span x-text="edad_max"></span> años
</p>

</div>

</div>

<div class="alert alert-light">

Rango seleccionado:
<b>
<span x-text="edad_min"></span>
-
<span x-text="edad_max"></span>
años
</b>

</div>

</div>
</div>


<label class="form-label">Sexo</label>

<select class="form-control mb-3" x-model="sexo">
<option value="">Todos</option>
<option value="F">Femenino</option>
<option value="M">Masculino</option>
</select>


<label class="form-label">Patología</label>

<input
type="text"
class="form-control mb-3"
x-model="patologia">


<label class="form-label">Localidad</label>

<input
type="text"
class="form-control"
x-model="localidad">


<input type="hidden" name="edad_min" :value="edad_min">
<input type="hidden" name="edad_max" :value="edad_max">
<div class="border rounded-4 p-4 mt-4 d-flex align-items-center"
style="background:#eef4fb;border-color:#c8dbf6;">

<div class="me-3">

<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users size-8 text-blue-600" aria-hidden="true" data-fg-sgm31=":12.5810:/components/Previsualizacion.tsx:78:15:2626:42:e:Users::::::DV8M"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><path d="M16 3.128a4 4 0 0 1 0 7.744"></path><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><circle cx="9" cy="7" r="4"></circle></svg>

</div>


<div>

<div class="text-muted small">
Destinatarios estimados
</div>

<div class="fw-semibold text-primary">

<span x-text="alcance"></span>

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
:class="{'is-invalid':errores.mensaje}">
</textarea>

<div class="invalid-feedback">
Debe escribir el mensaje
</div>


<!-- PREVIEW -->

<div class="mt-4">

<p class="small text-muted">Vista previa</p>

<div style="background:#e5ddd5;padding:20px;border-radius:10px;width:320px">

<div style="background:white;padding:10px;border-radius:10px">

<p x-text="mensaje"></p>

<small class="text-muted">12:45</small>

</div>

</div>

</div>

</div>



<!-- PASO 4 -->

<div x-show="step==4">

<h5 class="mb-4">Confirmación</h5>

<div class="border rounded p-3 mb-3">

<p><b>Nombre:</b> <span x-text="nombre"></span></p>
<p><b>Descripción:</b> <span x-text="descripcion"></span></p>

</div>

<div class="border rounded p-3 mb-3">

<h6>Mensaje</h6>
<p x-text="mensaje"></p>

</div>

<div class="border rounded p-3">

<h6>Alcance estimado</h6>

<h3 class="text-success">
<span x-text="alcance"></span> pacientes
</h3>

</div>

</div>



<!-- BOTONES -->
<div class="d-flex justify-content-between mt-4">

    <div>

        <button
            type="button"
            class="btn btn-light"
            @click="cancelar">

            Cancelar

        </button>

    </div>


    <div>

        <button
        type="button"
        class="btn btn-outline-secondary me-2"
        x-show="step>1"
        @click="atras">

        Atrás

        </button>


        <button
        type="button"
        class="btn btn-secondary me-2"
        x-show="step==2"
        @click="abrirResultado()">

        Probar segmentación

        </button>


        <button
        type="button"
        class="btn btn-dark"
        x-show="step<4"
        @click="siguiente">

        Siguiente

        </button>


        <button
        type="submit"
        class="btn btn-success"
        x-show="step==4">

        Confirmar campaña

        </button>

    </div>

</div>

</form>

</div>

</div>



<!-- MODAL RESULTADO SEGMENTACION -->

<div class="modal fade" id="resultadoSegmentacion" tabindex="-1">

<div class="modal-dialog modal-dialog-centered">

<div class="modal-content rounded-4 border-0 shadow">

<div class="modal-header border-0">

<h5 class="modal-title">
Resultado de la Segmentación
</h5>

<button type="button" class="btn-close" data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<p class="text-muted small mb-4">
Estos son los resultados estimados según los filtros aplicados
</p>


<div class="p-4 rounded-4"
style="background:#eaf7ef;border:1px solid #b8e6c7;">

<div class="d-flex align-items-center">

<div class="me-3"
style="
width:36px;
height:36px;
background:#22c55e;
border-radius:50%;
display:flex;
align-items:center;
justify-content:center;
color:white;">

✓

</div>

<div>

<div class="text-muted small">
Total de destinatarios
</div>

<div class="fw-bold text-success fs-4">
<span x-text="alcance"></span>
</div>

</div>

</div>

</div>


<button
class="btn btn-dark w-100 mt-4"
data-bs-dismiss="modal">

Entendido

</button>

</div>

</div>

</div>

</div>



<script>

function wizardCampania(){

return{

    step:1,

    nombre:'',
    descripcion:'',
    responsable:'',

    edad_min:15,
    edad_max:30,

    sexo:'',
    patologia:'',
    localidad:'',

    mensaje:'',

    alcance:0,

    errores:{},

    get progress(){

        return (this.step-1)*33

    },

    siguiente(){

        this.errores={}

        if(this.step==1){

            if(!this.nombre){this.errores.nombre=true}
            if(!this.descripcion){this.errores.descripcion=true}
            if(!this.responsable){this.errores.responsable=true}

            if(Object.keys(this.errores).length>0){
                return
            }

        }

        if(this.step==2){

            if(this.edad_min > this.edad_max){

                alert("La edad mínima no puede ser mayor")

                return

            }

        }

        if(this.step==3){

            if(!this.mensaje){
                this.errores.mensaje=true
                return
            }

        }

        this.step++

    },
    atras(){

        if(this.step > 1){
        this.step--
        }

    },
    cancelar(){

        window.location.href="{{ route('campanias.index') }}"

    },
    
probarSegmentacion(){

this.alcance = Math.floor(Math.random()*10000)+1000

},
    abrirResultado(){

        this.alcance=Math.floor(Math.random()*10000)+1000

        let modal=new bootstrap.Modal(
        document.getElementById('resultadoSegmentacion')
    )

    modal.show()

    }

    }

}

</script>


@endsection
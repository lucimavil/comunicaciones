@extends('layouts.app')

@section('content')
  <div class="bg-white border-bottom py-3">

    <div class="container d-flex align-items-center">

        <a href="{{ route('comunicaciones.index') }}" 
           class="btn btn-light me-3">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
</svg>
        </a>

        <h4 class="mb-0 fw-semibold">
            Nueva comunicación
        </h4>

    </div>

</div>
<div class="max-w-5xl mx-auto">

<form method="POST" action="{{ route('comunicaciones.store') }}">
@csrf

<div x-data="{step:1}" class="bg-white shadow rounded-xl p-6">

<div class="flex mb-8 space-x-4">

<button type="button" @click="step=1"
class="px-4 py-2 rounded-circle bg-blue-600 text-white">
1 
</button>Datos

<button type="button" @click="step=2"
class="px-4 py-2 rounded-circle bg-gray-200">
2 
</button>Segmentación

<button type="button" @click="step=3"
class="px-4 py-2 rounded-circle bg-gray-200">
3 
</button>Mensaje

<button type="button" @click="step=4"
class="px-4 py-2 rounded-circle bg-gray-200">
4 
</button>Revisión

</div>


<div x-show="step==1">

<label class="block mb-2 font-semibold">
Nombre del aviso
</label>

<input name="nombre"
class="w-full border rounded p-2 mb-4">

<label class="block mb-2 font-semibold">
Tipo
</label>

<select name="tipo"
class="w-full border rounded p-2">

<option>Capacitación</option>
<option>Emergencia</option>
<option>Aviso administrativo</option>

</select>

</div>


<div x-show="step==2">

<h3 class="font-semibold mb-3">
Segmentos
</h3>

@foreach($segmentos as $seg)

<label class="block">

<input type="checkbox"
name="segmentos[]"
value="{{ $seg->id }}">

{{ $seg->nombre }}

</label>

@endforeach

</div>


<div x-show="step==3">

<label class="block mb-2 font-semibold">
Mensaje
</label>

<textarea name="descripcion"
class="w-full border rounded p-3 h-40"></textarea>

</div>


<div x-show="step==4">

<h3 class="font-bold mb-3">
Confirmar envío
</h3>

<p>
Revise la información antes de enviar
</p>

</div>


<div class="mt-6 flex justify-end">

<button type="submit"
class="bg-blue-600 text-white px-6 py-2 rounded">

Guardar comunicación

</button>

</div>

</div>

</form>

</div>

@endsection
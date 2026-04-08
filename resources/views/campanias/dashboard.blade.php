@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto">

<h1 class="text-2xl font-bold mb-6">
Dashboard de Comunicación
</h1>

<div class="grid grid-cols-6 gap-4 mb-6">

<div class="bg-white shadow rounded p-4">
Total mensajes
<div class="text-xl font-bold">
{{ $total }}
</div>
</div>

<div class="bg-white shadow rounded p-4">
Leídos
<div class="text-xl text-green-600 font-bold">
{{ $leidos }}
</div>
</div>

<div class="bg-white shadow rounded p-4">
Confirmados
<div class="text-xl text-blue-600 font-bold">
{{ $confirmados }}
</div>
</div>

<div class="bg-white shadow rounded p-4">
Cancelados
<div class="text-xl text-red-600 font-bold">
{{ $cancelados }}
</div>
</div>

</div>

<div class="grid grid-cols-2 gap-6">

<div class="bg-white shadow rounded p-6">

<h3 class="font-bold mb-3">
Estado de lectura
</h3>

<canvas id="lectura"></canvas>

</div>

<div class="bg-white shadow rounded p-6">

<h3 class="font-bold mb-3">
Respuestas por sector
</h3>

<canvas id="sectores"></canvas>

</div>

</div>

<div class="bg-white shadow rounded p-6 mt-6">

<h3 class="font-bold mb-4">
Detalle de destinatarios
</h3>

<table class="w-full">

<thead>

<tr class="border-b">

<th class="text-left p-2">Nombre</th>
<th class="text-left p-2">Sector</th>
<th class="text-left p-2">Estado</th>
<th class="text-left p-2">Respuesta</th>
<th class="text-left p-2">Hora lectura</th>

</tr>

</thead>

<tbody>

@foreach($com->destinatarios as $d)

<tr class="border-b">

<td class="p-2">
{{ $d->user->name }}
</td>

<td class="p-2">
{{ $d->user->sector ?? '-' }}
</td>

<td class="p-2">
{{ $d->estado }}
</td>

<td class="p-2">
{{ $d->respuesta }}
</td>

<td class="p-2">
{{ $d->leido_at }}
</td>

</tr>

@endforeach

</tbody>

</table>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

new Chart(document.getElementById('lectura'),{
type:'pie',
data:{
labels:['Leído','No leído'],
datasets:[{
data:[
{{ $leidos }},
{{ $total - $leidos }}
]
}]
}
})

</script>

@endsection
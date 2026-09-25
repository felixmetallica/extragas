@extends('informes.layout')

@section('informe')
@php $sum = fn ($k) => collect($stock)->sum($k); @endphp
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">@include('partials.kpi', ['icono' => 'droplet-fill', 'color' => 'i-green', 'label' => 'Llenas', 'valor' => $sum('LLENA')])</div>
    <div class="col-6 col-xl-3">@include('partials.kpi', ['icono' => 'droplet', 'color' => 'i-blue', 'label' => 'Vacías aptas', 'valor' => $sum('VACIA')])</div>
    <div class="col-6 col-xl-3">@include('partials.kpi', ['icono' => 'x-octagon', 'color' => 'i-red', 'label' => 'No aptas', 'valor' => $sum('NO_APTA')])</div>
    <div class="col-6 col-xl-3">@include('partials.kpi', ['icono' => 'house', 'color' => 'i-violet', 'label' => 'En clientes', 'valor' => $sum('EN_CLIENTE')])</div>
</div>
<div class="card mb-3"><div class="table-responsive"><table class="table align-middle">
    <thead><tr><th>Capacidad</th><th class="num">Llenas</th><th class="num">Vacías aptas</th><th class="num">No aptas</th><th class="num">En depósito</th><th class="num">En clientes</th><th class="num">Parque</th></tr></thead>
    <tbody>@foreach ($stock as $cap => $e)<tr><td class="fw-semibold">{{ $cap }} kg</td><td class="num">{{ $e['LLENA'] }}</td><td class="num">{{ $e['VACIA'] }}</td><td class="num">{{ $e['NO_APTA'] }}</td>
        <td class="num">{{ $e['LLENA'] + $e['VACIA'] + $e['NO_APTA'] }}</td><td class="num">{{ $e['EN_CLIENTE'] }}</td><td class="num fw-semibold">{{ array_sum($e) }}</td></tr>@endforeach</tbody>
</table></div></div>
<div class="card"><div class="card-header"><h2>Garrafas en poder de clientes ({{ $enClientes->count() }})</h2></div>
    <div class="table-responsive" style="max-height:520px"><table class="table align-middle"><thead><tr><th>Cliente</th><th>Código</th><th>Capacidad</th><th>Desde</th><th class="num">Días</th></tr></thead>
        <tbody>@foreach ($enClientes as $g)<tr><td><a href="{{ route('clientes.show', $g->cliente_id) }}">{{ $g->cliente }}</a></td><td><a href="{{ route('garrafas.show', $g->garrafa_id) }}">{{ $g->codigo }}</a></td>
            <td>{{ $g->capacidad_kg }} kg</td><td>{{ fecha($g->fecha_ultimo_movimiento) }}</td><td class="num">{{ $g->dias_en_cliente }}</td></tr>@endforeach</tbody></table></div></div>
@endsection

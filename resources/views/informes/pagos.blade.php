@extends('informes.layout')

@section('informe')
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">@include('partials.kpi', ['icono' => 'cash-stack', 'color' => 'i-green', 'label' => 'Cobrado', 'valor' => pesos($cobrado), 'sub' => $porForma->sum('cantidad').' pagos'])</div>
    <div class="col-6 col-xl-3">@include('partials.kpi', ['icono' => 'percent', 'color' => 'i-blue', 'label' => 'Cobrabilidad', 'valor' => $vendido->total ? round(($vendido->total - $vendido->saldo) / $vendido->total * 100).'%' : '—', 'sub' => 'de '.pesos($vendido->total).' vendidos'])</div>
    <div class="col-6 col-xl-3">@include('partials.kpi', ['icono' => 'exclamation-diamond', 'color' => 'i-red', 'label' => 'Deuda de clientes', 'valor' => pesos($deudores->sum('saldo_total')), 'sub' => $deudores->count().' clientes'])</div>
    <div class="col-6 col-xl-3">@include('partials.kpi', ['icono' => 'truck', 'color' => 'i-violet', 'label' => 'Pagado a proveedores', 'valor' => pesos($pagadoProv), 'sub' => 'Adeudado: '.pesos($proveedores->sum('saldo_total'))])</div>
</div>
<div class="card mb-3"><div class="card-header"><h2>Ingresos y egresos por día</h2></div><div class="card-body"><div class="chart-box"><canvas data-chart='@json($graficoFlujo)'></canvas></div></div></div>
<div class="row g-3 mb-3">
    <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Cobros por forma de pago</h2></div><div class="card-body"><div class="chart-box sm"><canvas data-chart='@json($graficoFormas)'></canvas></div></div></div></div>
    <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Forma de pago habitual de los clientes</h2></div><div class="card-body"><div class="chart-box sm"><canvas data-chart='@json($graficoHabitual)'></canvas></div></div></div></div>
</div>
<div class="row g-3">
    <div class="col-md-7"><div class="card h-100"><div class="card-header"><h2>Clientes con saldo pendiente</h2></div>
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Cliente</th><th class="num">Pedidos</th><th class="num">Saldo</th></tr></thead>
            <tbody>@forelse ($deudores as $d)<tr><td><a href="{{ route('clientes.show', $d->cliente_id) }}">{{ $d->cliente }}</a><div class="text-muted-sm">{{ $d->telefono_principal }}</div></td><td class="num">{{ $d->pedidos_pendientes }}</td><td class="num text-danger fw-semibold">{{ pesos($d->saldo_total) }}</td></tr>
            @empty<tr><td colspan="3" class="empty">Sin deudas</td></tr>@endforelse</tbody></table></div></div></div>
    <div class="col-md-5"><div class="card h-100"><div class="card-header"><h2>Deuda con proveedores</h2></div>
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Proveedor</th><th class="num">Saldo</th></tr></thead>
            <tbody>@forelse ($proveedores as $p)<tr><td><a href="{{ route('proveedores.show', $p->proveedor_id) }}">{{ $p->razon_social }}</a></td><td class="num fw-semibold">{{ pesos($p->saldo_total) }}</td></tr>
            @empty<tr><td colspan="2" class="empty">Sin deudas</td></tr>@endforelse</tbody></table></div></div></div>
</div>
@endsection

@extends('layouts.app')

@section('contenido')
@php $r = $recepcion; @endphp
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    {{ badge_pago($r->estadoPago()) }}<span class="text-muted-sm">Recibió {{ $r->empleado->nombreCompleto() }} · {{ fecha($r->fecha, true) }}</span>
    <div class="ms-auto">@if ($r->saldo > 0)<a class="btn btn-sm btn-primary" href="{{ route('pagos-proveedores.create', ['proveedor' => $r->proveedor_id, 'recepcion' => $r->id]) }}"><i class="bi bi-wallet2"></i> Registrar pago</a>@endif</div>
</div>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3"><div class="card-header"><h2>Productos recibidos</h2></div>
            <div class="table-responsive"><table class="table align-middle">
                <thead><tr><th>Producto</th><th class="num">Cantidad</th><th class="num">Costo unit.</th><th class="num">Subtotal</th></tr></thead>
                <tbody>@foreach ($r->items as $it)<tr><td>{{ $it->producto->nombre }}</td><td class="num">{{ num($it->cantidad) }}</td><td class="num">{{ pesos($it->precio_unitario) }}</td><td class="num fw-semibold">{{ pesos($it->subtotal) }}</td></tr>@endforeach</tbody>
                <tfoot>
                    @if ($r->descuento > 0)<tr><td colspan="3" class="text-end">Descuento</td><td class="num text-danger">− {{ pesos($r->descuento) }}</td></tr>@endif
                    <tr><th colspan="3" class="text-end">Total</th><th class="num fs-5">{{ pesos($r->total) }}</th></tr>
                    <tr><td colspan="3" class="text-end">Pagado</td><td class="num text-success">{{ pesos($r->monto_pagado) }}</td></tr>
                </tfoot>
            </table></div></div>
        <div class="row g-3">
            <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Garrafas llenas ingresadas ({{ $r->garrafas->count() }})</h2></div>
                <div class="card-body small" style="max-height:260px;overflow:auto">@forelse ($r->garrafas as $g)<a href="{{ route('garrafas.show', $g) }}" class="me-2 d-inline-block">{{ $g->codigo }}</a>@empty<span class="text-body-secondary">Ninguna</span>@endforelse</div></div></div>
            <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Vacías entregadas ({{ $entregadas->count() }})</h2></div>
                <div class="card-body small" style="max-height:260px;overflow:auto">@forelse ($entregadas as $m)<a href="{{ route('garrafas.show', $m->garrafa) }}" class="me-2 d-inline-block">{{ $m->garrafa->codigo }}</a>@empty<span class="text-body-secondary">Ninguna</span>@endforelse</div></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header"><h2>Proveedor</h2><div class="ms-auto"><a class="btn btn-sm btn-light" href="{{ route('proveedores.show', $r->proveedor) }}">Ver ficha</a></div></div>
            <div class="card-body"><dl class="info-list"><dt>Razón social</dt><dd class="fw-semibold">{{ $r->proveedor->razon_social }}</dd><dt>CUIT</dt><dd>{{ $r->proveedor->cuit }}</dd>
                <dt>Factura</dt><dd>{{ $r->numero_factura_proveedor ?: '—' }}</dd><dt>Obs.</dt><dd>{{ $r->observaciones ?: '—' }}</dd></dl></div></div>
        <div class="card"><div class="card-header"><h2>Pagos</h2></div>
            <ul class="list-group list-group-flush">@forelse ($r->pagos as $pg)
                <li class="list-group-item d-flex justify-content-between"><span>{{ $pg->numero }}<div class="text-muted-sm">{{ fecha($pg->fecha) }} · {{ $pg->formaPago->nombre }}</div></span><b>{{ pesos($pg->monto) }}</b></li>
            @empty<li class="list-group-item text-body-secondary small">Sin pagos</li>@endforelse</ul></div>
    </div>
</div>
@endsection

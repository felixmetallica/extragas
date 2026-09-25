@extends('pdf.layout')

@section('titulo')PEDIDO {{ $pedido->numero }}@endsection
@section('subtitulo')Fecha: {{ fecha($pedido->fecha, true) }}@endsection

@section('cuerpo')
@php $c = $pedido->cliente; @endphp
<table class="bloques"><tr>
    <td><div class="et">Cliente</div><b>{{ $c->nombreCompleto() }}</b><br>Tel.: {{ $c->telefono_principal }}<br>{{ $c->domicilioCompleto() }}@if ($c->referencias)<br><span class="muted">Ref.: {{ $c->referencias }}</span>@endif</td>
    <td><div class="et">Pedido</div>Medio: {{ $pedido->medioContacto?->nombre ?? '—' }}<br>Entrega: {{ $pedido->canal->nombre }}@if ($pedido->direccion_entrega)<br>Dirección: {{ $pedido->direccion_entrega }}@endif
        <br>Estado: {{ $pedido->estado->nombre }}<br>Atendió: {{ $pedido->empleado->nombreCompleto() }}</td>
</tr></table>
<table>
    <thead><tr><th>Producto</th><th class="num">Cantidad</th><th class="num">Precio unit.</th><th class="num">Subtotal</th></tr></thead>
    <tbody>@foreach ($pedido->items->where('tipo_linea', 'VENTA') as $it)
        <tr><td>{{ $it->producto->nombre }}</td><td class="num">{{ num($it->cantidad) }}</td><td class="num">{{ pesos($it->precio_unitario) }}</td><td class="num">{{ pesos($it->subtotal) }}</td></tr>
    @endforeach</tbody>
</table>
@if ($pedido->descuento > 0)<p class="num" style="margin:2mm 0 0">Subtotal {{ pesos($pedido->subtotal) }} · Descuento − {{ pesos($pedido->descuento) }}</p>@endif
<div class="total"><span>TOTAL</span><b>{{ pesos($pedido->total) }}</b></div>
<div style="clear:both;padding-top:3mm">Pagado: {{ pesos($pedido->monto_pagado) }} · Saldo: <b>{{ pesos($pedido->esCancelado() ? 0 : $pedido->saldo) }}</b>
    @if ($pedido->cliente->formaPagoHabitual) · Forma de pago habitual: {{ $pedido->cliente->formaPagoHabitual->nombre }}@endif</div>

@php $envases = $pedido->items->where('tipo_linea', '!=', 'VENTA'); @endphp
@if ($envases->isNotEmpty())
    <h3>Envases</h3>
    <table>
        <thead><tr><th>Movimiento</th><th>Producto</th><th class="num">Cantidad</th><th>Códigos</th></tr></thead>
        <tbody>@foreach ($envases as $it)
            @php $tipoMov = $it->tipo_linea === 'ENTREGA' ? 'ENTREGA_CLIENTE' : 'DEVOLUCION_CLIENTE'; @endphp
            <tr><td>{{ $it->tipo_linea === 'ENTREGA' ? 'Llenas entregadas' : 'Vacías recibidas' }}</td><td>{{ $it->producto->nombre }}</td><td class="num">{{ num($it->cantidad) }}</td>
                <td>{{ $pedido->movimientosGarrafa->filter(fn ($m) => $m->tipo->codigo === $tipoMov && $m->garrafa->capacidad_kg == $it->producto->capacidad())->pluck('garrafa.codigo')->implode(', ') }}</td></tr>
        @endforeach</tbody>
    </table>
@endif
@if ($pedido->observaciones)<p class="muted" style="margin-top:4mm">Observaciones: {{ $pedido->observaciones }}</p>@endif
<div class="firma">Firma y aclaración del cliente</div>
@endsection

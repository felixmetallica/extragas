@extends('layouts.app')

@section('contenido')
@php
    $c = $pedido->cliente;
    $flujo = \App\Models\Catalogos\EstadoPedido::FLUJO;
    $idx = array_search($pedido->estado->codigo, $flujo, true);
    $siguiente = $pedido->siguienteEstado();
    $nombres = \App\Models\Catalogos\EstadoPedido::todos();
@endphp
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    {{ badge_estado_pedido($pedido->estado) }} {{ badge_pago($pedido->estadoPago()) }}
    <span class="text-body-secondary small">Tomado por {{ $pedido->empleado->nombreCompleto() }} · @include('partials.medio', ['medio' => $pedido->medioContacto])</span>
    <div class="ms-auto d-flex flex-wrap gap-2">
        @if ($pedido->esEditable())
            <a class="btn btn-sm btn-light" href="{{ route('pedidos.edit', $pedido) }}"><i class="bi bi-pencil"></i> Editar</a>
            <form method="POST" action="{{ route('pedidos.cancelar', $pedido) }}" data-confirm="¿Cancelar el pedido {{ $pedido->numero }}?">@csrf<button class="btn btn-sm btn-light text-danger"><i class="bi bi-x-circle"></i> Cancelar</button></form>
        @endif
        <a class="btn btn-sm btn-light" href="{{ wa_link($c->telefono_principal) }}" target="_blank" rel="noopener"><i class="bi bi-whatsapp text-success"></i> WhatsApp</a>
        <a class="btn btn-sm btn-light" href="{{ route('pedidos.pdf', $pedido) }}"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        @if ($pedido->saldo > 0 && ! $pedido->esCancelado())
            <a class="btn btn-sm btn-outline-primary" href="{{ route('cobros.create', ['cliente' => $c->id, 'pedido' => $pedido->id]) }}"><i class="bi bi-cash-coin"></i> Registrar pago</a>
        @endif
        @if ($siguiente === 'ENTREGADO')
            <a class="btn btn-sm btn-primary" href="{{ route('pedidos.entrega', $pedido) }}"><i class="bi bi-check2-circle"></i> Registrar entrega</a>
        @elseif ($siguiente)
            <form method="POST" action="{{ route('pedidos.avanzar', $pedido) }}">@csrf<button class="btn btn-sm btn-primary"><i class="bi bi-arrow-right-circle"></i> Pasar a {{ $nombres[$siguiente]->nombre }}</button></form>
        @endif
    </div>
</div>

@if ($pedido->esCancelado())
    <div class="alert alert-secondary"><i class="bi bi-x-circle me-1"></i>Este pedido fue cancelado.</div>
@else
    <div class="card mb-3"><div class="card-body"><div class="stepper">
        @foreach ($flujo as $i => $codigo)
            <div @class(['step', 'done' => $i <= $idx])><div class="dot"><i class="bi bi-{{ ['clock', 'box-seam', 'truck', 'check-lg'][$i] }}"></i></div>{{ $nombres[$codigo]->nombre }}</div>
        @endforeach
    </div></div></div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><h2>Productos</h2></div>
            <div class="table-responsive"><table class="table align-middle">
                <thead><tr><th>Producto</th><th>Línea</th><th class="num">Cantidad</th><th class="num">Precio</th><th class="num">Subtotal</th></tr></thead>
                <tbody>
                @foreach ($pedido->items->sortBy(fn ($i) => ['VENTA' => 0, 'ENTREGA' => 1, 'DEVOLUCION' => 2][$i->tipo_linea]) as $it)
                    <tr @class(['text-body-secondary' => $it->tipo_linea !== 'VENTA'])>
                        <td><i class="bi bi-{{ $it->producto->icono() }} me-1"></i>{{ $it->producto->nombre }}</td>
                        <td>{{ badge(['VENTA' => 'Venta', 'ENTREGA' => 'Envase entregado', 'DEVOLUCION' => 'Envase recibido'][$it->tipo_linea], $it->tipo_linea === 'VENTA' ? 'b-info' : 'b-gray') }}</td>
                        <td class="num">{{ num($it->cantidad) }}</td><td class="num">{{ $it->tipo_linea === 'VENTA' ? pesos($it->precio_unitario) : '—' }}</td>
                        <td class="num fw-semibold">{{ $it->tipo_linea === 'VENTA' ? pesos($it->subtotal) : '' }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    @if ($pedido->descuento > 0)
                        <tr><td colspan="4" class="text-end">Subtotal</td><td class="num">{{ pesos($pedido->subtotal) }}</td></tr>
                        <tr><td colspan="4" class="text-end">Descuento</td><td class="num text-danger">− {{ pesos($pedido->descuento) }}</td></tr>
                    @endif
                    <tr><th colspan="4" class="text-end">Total</th><th class="num fs-5">{{ pesos($pedido->total) }}</th></tr>
                </tfoot>
            </table></div>
        </div>

        @if ($pedido->movimientosGarrafa->isNotEmpty())
            <div class="card mb-3">
                <div class="card-header"><h2>Garrafas intercambiadas</h2></div>
                <div class="table-responsive"><table class="table align-middle">
                    <thead><tr><th>Código</th><th>Capacidad</th><th>Movimiento</th><th>Fecha</th></tr></thead>
                    <tbody>@foreach ($pedido->movimientosGarrafa as $m)
                        <tr><td><a href="{{ route('garrafas.show', $m->garrafa) }}" class="fw-semibold">{{ $m->garrafa->codigo }}</a></td><td>{{ $m->garrafa->capacidad_kg }} kg</td>
                            <td>{{ badge($m->tipo->nombre, $m->tipo->codigo === 'ENTREGA_CLIENTE' ? 'b-info' : 'b-pagado') }}</td><td>{{ fecha($m->fecha, true) }}</td></tr>
                    @endforeach</tbody>
                </table></div>
            </div>
        @endif

        <div class="card">
            <div class="card-header"><h2>Pagos del pedido</h2></div>
            <div class="table-responsive"><table class="table align-middle">
                <thead><tr><th>Recibo</th><th>Fecha</th><th>Forma</th><th>Referencia</th><th class="num">Monto</th><th></th></tr></thead>
                <tbody>
                @forelse ($pedido->pagos as $pg)
                    <tr><td class="fw-semibold">{{ $pg->numero_recibo }}</td><td>{{ fecha($pg->fecha, true) }}</td><td>{{ $pg->formaPago->nombre }}</td><td>{{ $pg->referencia ?: '—' }}</td><td class="num">{{ pesos($pg->monto) }}</td>
                        <td class="actions"><a class="btn btn-sm btn-light" href="{{ route('cobros.recibo', ['ids' => $pg->id]) }}" title="Recibo PDF"><i class="bi bi-file-earmark-pdf"></i></a></td></tr>
                @empty
                    <tr><td colspan="6" class="empty">Sin pagos registrados</td></tr>
                @endforelse
                </tbody>
            </table></div>
            <div class="card-body border-top d-flex justify-content-end gap-4">
                <span>Pagado: <b class="text-success">{{ pesos($pedido->monto_pagado) }}</b></span>
                <span>Saldo: <b @class(['text-danger' => $pedido->saldo > 0])>{{ pesos($pedido->esCancelado() ? 0 : $pedido->saldo) }}</b></span>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h2>Cliente</h2><div class="ms-auto"><a href="{{ route('clientes.show', $c) }}" class="btn btn-sm btn-light">Ver ficha</a></div></div>
            <div class="card-body"><dl class="info-list">
                <dt>Nombre</dt><dd class="fw-semibold">{{ $c->nombreCompleto() }}</dd>
                <dt>Teléfono</dt><dd>{{ $c->telefono_principal }}</dd>
                <dt>Domicilio</dt><dd>{{ $c->domicilioCompleto() ?: '—' }}</dd>
                @if ($c->referencias)<dt>Referencia</dt><dd>{{ $c->referencias }}</dd>@endif
                <dt>Pago habitual</dt><dd>{{ $c->formaPagoHabitual?->nombre ?? '—' }}</dd>
            </dl></div>
        </div>
        <div class="card">
            <div class="card-header"><h2>Entrega</h2></div>
            <div class="card-body"><dl class="info-list">
                <dt>Pedido</dt><dd>{{ fecha($pedido->fecha, true) }}</dd>
                <dt>Tipo</dt><dd>{{ $pedido->canal->nombre }}</dd>
                @if ($pedido->direccion_entrega)<dt>Dirección</dt><dd>{{ $pedido->direccion_entrega }}</dd>@endif
                <dt>Entregado</dt><dd>{{ $pedido->entregado ? fecha($pedido->fecha_entrega, true) : 'No' }}</dd>
                <dt>Observaciones</dt><dd>{{ $pedido->observaciones ?: '—' }}</dd>
            </dl></div>
        </div>
    </div>
</div>
@endsection

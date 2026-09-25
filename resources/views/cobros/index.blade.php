@extends('layouts.app')

@section('contenido')
@php $ef = $porForma['Efectivo'] ?? 0; @endphp
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'calendar-check', 'label' => 'Cobrado hoy', 'valor' => pesos($cobradoHoy)])</div>
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'cash-stack', 'color' => 'i-green', 'label' => 'Cobrado en el período', 'valor' => pesos($totalPeriodo), 'sub' => fecha($desde).' – '.fecha($hasta)])</div>
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'pie-chart', 'color' => 'i-blue', 'label' => 'Efectivo / otros', 'valor' => $totalPeriodo ? round($ef / $totalPeriodo * 100).'% / '.(100 - round($ef / $totalPeriodo * 100)).'%' : '—', 'sub' => $porForma->map(fn ($t, $f) => "$f ".pesos($t))->implode(' · ')])</div>
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'exclamation-diamond', 'color' => 'i-red', 'label' => 'Pendiente de cobro', 'valor' => pesos($pendientes->sum('saldo')), 'sub' => $pendientes->count().' pedidos'])</div>
</div>

<div class="card">
    <div class="card-header p-0 px-2 pt-2 border-0"><ul class="nav nav-tabs w-100">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tPagos" type="button"><i class="bi bi-receipt me-1"></i>Pagos recibidos</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tPend" type="button"><i class="bi bi-hourglass-split me-1"></i>Pendientes de cobro ({{ $pendientes->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tSaldos" type="button"><i class="bi bi-people me-1"></i>Saldos por cliente</button></li>
    </ul></div>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="tPagos">
            <form class="toolbar p-3 pb-0" method="GET" data-auto-submit>
                <div class="search"><i class="bi bi-search"></i><input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Cliente, recibo o referencia"></div>
                @include('partials.rango')
                <select class="form-select form-select-sm" name="forma_pago"><option value="">Todas las formas</option>@foreach ($formasPago as $f)<option value="{{ $f->id }}" @selected(request('forma_pago') == $f->id)>{{ $f->nombre }}</option>@endforeach</select>
                <div class="ms-auto d-flex gap-2">
                    <a class="btn btn-sm btn-light" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
                    <a class="btn btn-sm btn-primary" href="{{ route('cobros.create') }}"><i class="bi bi-plus-lg"></i> Registrar pago</a>
                </div>
            </form>
            <div class="table-responsive"><table class="table table-hover align-middle">
                <thead><tr><th>Recibo</th><th>Fecha</th><th>Cliente</th><th>Pedido</th><th>Forma</th><th>Referencia</th><th>Recibió</th><th class="num">Importe</th><th></th></tr></thead>
                <tbody>
                @forelse ($pagos as $p)
                    <tr><td class="fw-semibold text-nowrap">{{ $p->numero_recibo }}</td><td class="text-nowrap">{{ fecha($p->fecha, true) }}</td>
                        <td><a href="{{ route('clientes.show', $p->cliente) }}">{{ $p->cliente->nombreCompleto() }}</a></td>
                        <td>@if ($p->pedido)<a href="{{ route('pedidos.show', $p->pedido) }}">{{ $p->pedido->numero }}</a>@else A cuenta @endif</td>
                        <td>{{ $p->formaPago->nombre }}</td><td>{{ $p->referencia ?: '—' }}</td><td class="small">{{ $p->creador?->nombreVisible() ?? '—' }}</td>
                        <td class="num fw-semibold">{{ pesos($p->monto) }}</td>
                        <td class="actions"><a class="btn btn-sm btn-light" href="{{ route('cobros.recibo', ['ids' => $p->id]) }}" title="Recibo PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                            @can('administrar')<form method="POST" action="{{ route('cobros.destroy', $p) }}" class="d-inline" data-confirm="¿Anular el pago {{ $p->numero_recibo }}? El saldo del pedido se recalcula.">@csrf @method('DELETE')<button class="btn btn-sm btn-light" title="Anular"><i class="bi bi-x-circle text-danger"></i></button></form>@endcan</td></tr>
                @empty
                    <tr><td colspan="9" class="empty"><i class="bi bi-inbox"></i>No hay pagos en el período</td></tr>
                @endforelse
                </tbody>
            </table></div>
            @include('partials.paginacion', ['items' => $pagos])
        </div>
        <div class="tab-pane fade" id="tPend">
            <div class="table-responsive"><table class="table table-hover align-middle">
                <thead><tr><th>Pedido</th><th>Fecha</th><th>Cliente</th><th>Estado</th><th class="num">Total</th><th class="num">Pagado</th><th class="num">Saldo</th><th></th></tr></thead>
                <tbody>
                @forelse ($pendientes as $p)
                    <tr><td><a href="{{ route('pedidos.show', $p) }}" class="fw-semibold">{{ $p->numero }}</a></td>
                        <td class="text-nowrap">{{ fecha($p->fecha) }}<div class="text-muted-sm">hace {{ (int) $p->fecha->diffInDays(now()) }} días</div></td>
                        <td><a href="{{ route('clientes.show', $p->cliente) }}">{{ $p->cliente->nombreCompleto() }}</a><div class="text-muted-sm">{{ $p->cliente->telefono_principal }}</div></td>
                        <td>{{ badge_estado_pedido($p->estado) }}</td><td class="num">{{ pesos($p->total) }}</td><td class="num">{{ pesos($p->monto_pagado) }}</td>
                        <td class="num fw-semibold text-danger">{{ pesos($p->saldo) }}</td>
                        <td class="actions"><a class="btn btn-sm btn-light" href="{{ wa_link($p->cliente->telefono_principal) }}" target="_blank" rel="noopener" title="Recordar por WhatsApp"><i class="bi bi-whatsapp text-success"></i></a>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('cobros.create', ['cliente' => $p->cliente_id, 'pedido' => $p->id]) }}"><i class="bi bi-cash-coin"></i> Cobrar</a></td></tr>
                @empty
                    <tr><td colspan="8" class="empty">No hay pedidos pendientes de cobro</td></tr>
                @endforelse
                </tbody>
                @if ($pendientes->isNotEmpty())<tfoot><tr><th colspan="6" class="text-end">Total pendiente</th><th class="num text-danger">{{ pesos($pendientes->sum('saldo')) }}</th><th></th></tr></tfoot>@endif
            </table></div>
        </div>
        <div class="tab-pane fade" id="tSaldos">
            <div class="table-responsive"><table class="table table-hover align-middle">
                <thead><tr><th>Cliente</th><th>Teléfono</th><th class="num">Pedidos adeudados</th><th class="num">Saldo</th><th></th></tr></thead>
                <tbody>
                @forelse ($saldos as $s)
                    <tr><td><a href="{{ route('clientes.show', $s->cliente_id) }}" class="fw-semibold">{{ $s->cliente }}</a></td><td>{{ $s->telefono_principal }}</td>
                        <td class="num">{{ $s->pedidos_pendientes }}</td><td class="num fw-semibold text-danger">{{ pesos($s->saldo_total) }}</td>
                        <td class="actions"><a class="btn btn-sm btn-outline-primary" href="{{ route('cobros.create', ['cliente' => $s->cliente_id]) }}"><i class="bi bi-cash-coin"></i> Cobrar</a></td></tr>
                @empty
                    <tr><td colspan="5" class="empty">Ningún cliente tiene saldo pendiente</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
</div>
@endsection

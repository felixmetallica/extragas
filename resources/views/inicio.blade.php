@extends('layouts.app')

@section('contenido')
@php
    $ef = $cobrosHoy->where('formaPago.codigo', 'EFECTIVO')->sum('monto');
    $totalHoy = $cobrosHoy->sum('monto');
@endphp
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">@include('partials.kpi', ['icono' => 'cart-check', 'label' => 'Pedidos de hoy', 'valor' => $pedidosHoy, 'sub' => $enCurso->count().' en curso sin entregar'])</div>
    <div class="col-sm-6 col-xl-3">@include('partials.kpi', ['icono' => 'cash-stack', 'color' => 'i-green', 'label' => 'Cobrado hoy', 'valor' => pesos($totalHoy), 'sub' => 'Efectivo '.pesos($ef).' · Otros '.pesos($totalHoy - $ef)])</div>
    <div class="col-sm-6 col-xl-3">@include('partials.kpi', ['icono' => 'exclamation-diamond', 'color' => 'i-red', 'label' => 'Pendiente de cobro', 'valor' => pesos($porCobrar->total), 'sub' => $porCobrar->clientes.' clientes con saldo'])</div>
    <div class="col-sm-6 col-xl-3">@include('partials.kpi', ['icono' => 'graph-up-arrow', 'color' => 'i-blue', 'label' => 'Ventas del mes', 'valor' => pesos($ventasMes), 'sub' => ucfirst(today()->translatedFormat('F Y'))])</div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card mb-3">
            <div class="card-header"><h2><i class="bi bi-list-task me-1 text-brand"></i> Pedidos en curso</h2>
                <div class="ms-auto"><a href="{{ route('pedidos.index') }}" class="btn btn-sm btn-light">Ver todos</a><a href="{{ route('pedidos.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Nuevo</a></div></div>
            <div class="table-responsive"><table class="table table-hover align-middle">
                <thead><tr><th>N°</th><th>Cliente</th><th style="min-width:140px">Productos</th><th class="num">Total</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                @forelse ($enCurso as $p)
                    <tr data-href="{{ route('pedidos.show', $p) }}" class="row-link">
                        <td><a href="{{ route('pedidos.show', $p) }}" class="fw-semibold">{{ $p->numero }}</a><div class="text-muted-sm">{{ $p->fecha->format('d/m H:i') }}</div></td>
                        <td style="min-width:160px">{{ $p->cliente->nombreCompleto() }}<div class="text-muted-sm">@include('partials.medio', ['medio' => $p->medioContacto]) · {{ $p->canal->nombre }}</div></td>
                        <td class="small">{{ $p->resumenItems() }}</td>
                        <td class="num">{{ pesos($p->total) }}</td>
                        <td>{{ badge_estado_pedido($p->estado) }}</td>
                        <td class="actions">
                            @if ($p->siguienteEstado() === 'ENTREGADO')
                                <a href="{{ route('pedidos.entrega', $p) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-check2-circle"></i> Entregar</a>
                            @else
                                <form method="POST" action="{{ route('pedidos.avanzar', $p) }}">@csrf<button class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-right-circle"></i> Avanzar</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty"><i class="bi bi-check2-all"></i>No hay pedidos pendientes</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
        <div class="card">
            <div class="card-header"><h2><i class="bi bi-bar-chart me-1 text-brand"></i> Ventas de los últimos 14 días</h2></div>
            <div class="card-body"><div class="chart-box sm"><canvas data-chart='@json($grafico)'></canvas></div></div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-3">
            <div class="card-header"><h2><i class="bi bi-fuel-pump me-1 text-brand"></i> Garrafas</h2><div class="ms-auto"><a href="{{ route('garrafas.index') }}" class="btn btn-sm btn-light">Detalle</a></div></div>
            <div class="card-body">
                @foreach ($stock as $cap => $e)
                    @php $total = max(1, array_sum($e)); $w = fn ($n) => round($n / $total * 100, 1).'%'; $min = $garrafasProducto[$cap]->stock_minimo ?? 0; @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between"><b>Garrafa {{ $cap }} kg</b>@if ($e['LLENA'] <= $min) {{ badge('Stock bajo', 'b-impago') }} @endif</div>
                        <div class="stackbar"><div style="width:{{ $w($e['LLENA']) }};background:#40c057"></div><div style="width:{{ $w($e['VACIA']) }};background:#4dabf7"></div><div style="width:{{ $w($e['NO_APTA']) }};background:#fa5252"></div><div style="width:{{ $w($e['EN_CLIENTE']) }};background:#9775fa"></div></div>
                        <div class="d-flex justify-content-between small mt-1 text-body-secondary flex-wrap gap-1">
                            <span><span class="legend-dot" style="background:#40c057"></span>{{ $e['LLENA'] }} llenas</span>
                            <span><span class="legend-dot" style="background:#4dabf7"></span>{{ $e['VACIA'] }} vacías</span>
                            <span><span class="legend-dot" style="background:#fa5252"></span>{{ $e['NO_APTA'] }} no aptas</span>
                            <span><span class="legend-dot" style="background:#9775fa"></span>{{ $e['EN_CLIENTE'] }} clientes</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><h2><i class="bi bi-alarm me-1 text-brand"></i> Clientes que suelen pedir</h2></div>
            <ul class="list-group list-group-flush">
                @forelse ($porPedir as $x)
                    <li class="list-group-item d-flex align-items-center gap-2">
                        <div class="flex-fill"><a href="{{ route('clientes.show', $x['cliente']) }}" class="fw-semibold">{{ $x['cliente']->nombreCompleto() }}</a>
                            <div class="text-muted-sm">Pide cada {{ $x['r']['promedio'] }} días · último {{ fecha($x['r']['ultimo']) }}</div></div>
                        {{ badge_regularidad($x['r']['estado']) }}
                        <a class="btn btn-sm btn-light" href="{{ wa_link($x['cliente']->telefono_principal) }}" target="_blank" rel="noopener" title="WhatsApp"><i class="bi bi-whatsapp text-success"></i></a>
                    </li>
                @empty
                    <li class="list-group-item empty">Sin avisos</li>
                @endforelse
            </ul>
        </div>
        <div class="card">
            <div class="card-header"><h2><i class="bi bi-exclamation-triangle me-1 text-brand"></i> Stock bajo</h2></div>
            <ul class="list-group list-group-flush">
                @forelse ($stockBajo as $p)
                    <li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-{{ $p->icono() }} me-2"></i>{{ $p->nombre }}</span>
                        <span class="fw-semibold text-danger">{{ $p->esGarrafa() ? $stock[$p->capacidad()]['LLENA'] : num($p->stock_actual) }} <small class="text-body-secondary fw-normal">/ mín. {{ num($p->stock_minimo) }}</small></span></li>
                @empty
                    <li class="list-group-item empty">Todo el stock está por encima del mínimo</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection

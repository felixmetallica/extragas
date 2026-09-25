@extends('layouts.app')

@section('contenido')
@php $tot = fn ($k) => collect($stock)->sum($k); @endphp
<div class="toolbar">
    <div class="text-body-secondary small"><i class="bi bi-info-circle me-1"></i>Parque: <b>{{ collect($stock)->map(fn ($e) => array_sum($e))->sum() }}</b> envases ·
        en depósito <b>{{ $tot('LLENA') + $tot('VACIA') + $tot('NO_APTA') }}</b> · en clientes <b>{{ $tot('EN_CLIENTE') }}</b></div>
    <div class="ms-auto d-flex gap-2 flex-wrap">
        <a class="btn btn-sm btn-light" href="{{ route('garrafas.informe') }}"><i class="bi bi-file-earmark-pdf"></i> Informe de stock</a>
        <a class="btn btn-sm btn-light" href="{{ route('recepciones.create') }}"><i class="bi bi-box-arrow-in-down"></i> Recepción de proveedor</a>
        <a class="btn btn-sm btn-primary" href="{{ route('garrafas.create') }}"><i class="bi bi-plus-lg"></i> Alta de garrafas</a>
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach ($stock as $cap => $e)
        @php $p = $productos[$cap] ?? null; $bajo = $p && $e['LLENA'] <= $p->stock_minimo; @endphp
        <div class="col-lg-4"><div class="card cyl-card h-100"><div class="card-body">
            <div class="cyl-head"><div class="cyl-icon"><i class="bi bi-fuel-pump-fill"></i></div>
                <div class="flex-fill"><div class="fw-bold fs-5">Garrafa {{ $cap }} kg</div><div class="text-muted-sm">Mínimo de llenas: {{ num($p?->stock_minimo) }} · {{ pesos($p?->precio_actual) }}</div></div>
                {!! $bajo ? '<span class="badge-soft b-impago"><i class="bi bi-exclamation-triangle"></i> Reponer</span>' : '<span class="badge-soft b-pagado">OK</span>' !!}</div>
            <div class="cyl-stats">
                <a class="cyl-stat full text-decoration-none" href="{{ route('garrafas.index', ['capacidad' => $cap, 'estado' => 'LLENA']) }}"><b>{{ $e['LLENA'] }}</b><span>Llenas</span></a>
                <a class="cyl-stat empty-ok text-decoration-none" href="{{ route('garrafas.index', ['capacidad' => $cap, 'estado' => 'VACIA']) }}"><b>{{ $e['VACIA'] }}</b><span>Vacías aptas</span></a>
                <a class="cyl-stat bad text-decoration-none" href="{{ route('garrafas.index', ['capacidad' => $cap, 'estado' => 'NO_APTA']) }}"><b>{{ $e['NO_APTA'] }}</b><span>No aptas</span></a>
                <a class="cyl-stat client text-decoration-none" href="{{ route('garrafas.index', ['capacidad' => $cap, 'estado' => 'EN_CLIENTE']) }}"><b>{{ $e['EN_CLIENTE'] }}</b><span>En clientes</span></a>
            </div>
            <div class="d-flex justify-content-between mt-3 small text-body-secondary"><span>En depósito: <b class="text-body">{{ $e['LLENA'] + $e['VACIA'] + $e['NO_APTA'] }}</b></span><span>Parque: <b class="text-body">{{ array_sum($e) }}</b></span></div>
        </div></div></div>
    @endforeach
</div>

<div class="card mb-3"><div class="card-header"><h2>Movimientos de los últimos 30 días</h2></div>
    <div class="card-body"><div class="chart-box sm"><canvas data-chart='@json($grafico)'></canvas></div></div></div>

<div class="card">
    <div class="card-header p-0 px-2 pt-2 border-0"><ul class="nav nav-tabs w-100">
        <li class="nav-item"><button @class(['nav-link', 'active' => ! request()->has('pagina_mov') && ! request()->has('tipo')]) data-bs-toggle="tab" data-bs-target="#tLista" type="button">Garrafas ({{ $garrafas->total() }})</button></li>
        <li class="nav-item"><button @class(['nav-link', 'active' => request()->has('pagina_mov') || request()->has('tipo')]) data-bs-toggle="tab" data-bs-target="#tMov" type="button">Movimientos</button></li>
    </ul></div>
    <div class="tab-content">
        <div @class(['tab-pane fade', 'show active' => ! request()->has('pagina_mov') && ! request()->has('tipo')]) id="tLista">
            <form class="toolbar p-3 pb-0" method="GET" data-auto-submit>
                <div class="search"><i class="bi bi-search"></i><input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Código o cliente"></div>
                <select class="form-select form-select-sm" name="capacidad"><option value="">Todas las capacidades</option>@foreach (\App\Models\Garrafa::CAPACIDADES as $c)<option value="{{ $c }}" @selected(request('capacidad') == $c)>{{ $c }} kg</option>@endforeach</select>
                <select class="form-select form-select-sm" name="estado"><option value="">Todos los estados</option>@foreach ($estados as $e)<option value="{{ $e->codigo }}" @selected(request('estado') === $e->codigo)>{{ $e->nombre }}</option>@endforeach</select>
            </form>
            <div class="table-responsive"><table class="table table-hover align-middle">
                <thead><tr><th>Código</th><th>Capacidad</th><th>Estado</th><th>Cliente</th><th>Último movimiento</th><th>Alta</th></tr></thead>
                <tbody>
                @forelse ($garrafas as $g)
                    <tr class="row-link" data-href="{{ route('garrafas.show', $g) }}"><td><a href="{{ route('garrafas.show', $g) }}" class="fw-semibold">{{ $g->codigo }}</a></td><td>{{ $g->capacidad_kg }} kg</td>
                        <td>{{ badge_estado_garrafa($g->estado) }}</td><td>@if ($g->cliente)<a href="{{ route('clientes.show', $g->cliente) }}">{{ $g->cliente->nombreCompleto() }}</a>@else — @endif</td>
                        <td>{{ fecha($g->fecha_ultimo_movimiento, true) }}</td><td>{{ fecha($g->fecha_compra) }}</td></tr>
                @empty
                    <tr><td colspan="6" class="empty"><i class="bi bi-inbox"></i>No hay garrafas para los filtros seleccionados</td></tr>
                @endforelse
                </tbody>
            </table></div>
            @include('partials.paginacion', ['items' => $garrafas])
        </div>
        <div @class(['tab-pane fade', 'show active' => request()->has('pagina_mov') || request()->has('tipo')]) id="tMov">
            <form class="toolbar p-3 pb-0" method="GET" data-auto-submit>
                <select class="form-select form-select-sm" name="tipo"><option value="">Todos los movimientos</option>@foreach ($tipos as $t)<option value="{{ $t->id }}" @selected(request('tipo') == $t->id)>{{ $t->nombre }}</option>@endforeach</select>
            </form>
            <div class="table-responsive"><table class="table align-middle">
                <thead><tr><th>Fecha</th><th>Garrafa</th><th>Movimiento</th><th>Estado</th><th>Detalle</th></tr></thead>
                <tbody>
                @forelse ($movimientos as $m)
                    <tr><td class="text-nowrap">{{ fecha($m->fecha, true) }}</td>
                        <td><a href="{{ route('garrafas.show', $m->garrafa) }}" class="fw-semibold">{{ $m->garrafa->codigo }}</a></td>
                        <td>{{ badge($m->tipo->nombre, ['ENTREGA_CLIENTE' => 'b-info', 'DEVOLUCION_CLIENTE' => 'b-reparto', 'ALTA' => 'b-pagado', 'ENTREGA_PROVEEDOR' => 'b-gray'][$m->tipo->codigo] ?? 'b-parcial') }}</td>
                        <td class="small text-nowrap">{{ $m->estadoOrigen?->nombre ?? '—' }} → <b>{{ $m->estadoDestino->nombre }}</b></td>
                        <td class="small">
                            @if ($m->pedido)<a href="{{ route('pedidos.show', $m->pedido) }}">{{ $m->pedido->numero }}</a> · @endif
                            @if ($m->recepcion)<a href="{{ route('recepciones.show', $m->recepcion) }}">{{ $m->recepcion->numero }}</a> · @endif
                            {{ $m->cliente?->nombreCompleto() }} {{ $m->observaciones }}</td></tr>
                @empty
                    <tr><td colspan="5" class="empty">Sin movimientos</td></tr>
                @endforelse
                </tbody>
            </table></div>
            @include('partials.paginacion', ['items' => $movimientos])
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('contenido')
<form class="toolbar" method="GET" data-auto-submit>
    <div class="search"><i class="bi bi-search"></i><input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Buscar por N°, cliente, domicilio o teléfono"></div>
    @include('partials.rango')
    <select class="form-select form-select-sm" name="estado"><option value="">Todos los estados</option>@foreach ($estados as $e)<option value="{{ $e->id }}" @selected(request('estado') == $e->id)>{{ $e->nombre }}</option>@endforeach</select>
    <select class="form-select form-select-sm" name="medio"><option value="">Todos los medios</option>@foreach ($medios as $m)<option value="{{ $m->id }}" @selected(request('medio') == $m->id)>{{ $m->nombre }}</option>@endforeach</select>
    <select class="form-select form-select-sm" name="pago"><option value="">Estado de pago</option>@foreach (['Pagado', 'Parcial', 'Pendiente'] as $e)<option @selected(request('pago') === $e)>{{ $e }}</option>@endforeach</select>
    <div class="ms-auto d-flex gap-2">
        <a class="btn btn-sm btn-light" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
        <a href="{{ route('pedidos.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Nuevo pedido</a>
    </div>
</form>

@php $wa = $porMedio[$medios['WHATSAPP']->id] ?? 0; @endphp
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'receipt', 'label' => 'Pedidos', 'valor' => num($resumen->n), 'sub' => 'sin contar cancelados'])</div>
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'currency-dollar', 'color' => 'i-green', 'label' => 'Total vendido', 'valor' => pesos($resumen->total), 'sub' => 'Ticket promedio '.pesos($resumen->n ? round($resumen->total / $resumen->n) : 0)])</div>
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'hourglass-split', 'color' => 'i-red', 'label' => 'Saldo pendiente', 'valor' => pesos($resumen->saldo)])</div>
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'whatsapp', 'color' => 'i-green', 'label' => 'Por WhatsApp', 'valor' => $resumen->n ? round($wa / $resumen->n * 100).'%' : '—', 'sub' => ($porMedio[$medios['TELEFONO']->id] ?? 0).' por teléfono · '.($porMedio[$medios['PRESENCIAL']->id] ?? 0).' en el local'])</div>
</div>

<div class="card">
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>N°</th><th>Fecha</th><th>Cliente</th><th>Medio</th><th>Entrega</th><th>Productos</th><th class="num">Total</th><th>Pago</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse ($pedidos as $p)
            <tr class="row-link" data-href="{{ route('pedidos.show', $p) }}">
                <td><a href="{{ route('pedidos.show', $p) }}" class="fw-semibold">{{ $p->numero }}</a></td>
                <td class="text-nowrap">{{ fecha($p->fecha) }}<div class="text-muted-sm">{{ $p->fecha->format('H:i') }}</div></td>
                <td>{{ $p->cliente->nombreCompleto() }}<div class="text-muted-sm">{{ $p->cliente->telefono_principal }}</div></td>
                <td>@include('partials.medio', ['medio' => $p->medioContacto])</td>
                <td class="small">{{ $p->canal->nombre }}</td>
                <td class="small">{{ $p->resumenItems() }}</td>
                <td class="num">{{ pesos($p->total) }}</td>
                <td>{{ badge_pago($p->estadoPago()) }}</td>
                <td>{{ badge_estado_pedido($p->estado) }}</td>
                <td class="actions">
                    <a class="btn btn-sm btn-light" href="{{ route('pedidos.pdf', $p) }}" title="Descargar PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                    @if ($p->siguienteEstado())
                        <form method="POST" action="{{ route('pedidos.avanzar', $p) }}" class="d-inline">@csrf<button class="btn btn-sm btn-light" title="Avanzar estado"><i class="bi bi-arrow-right-circle text-brand"></i></button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="10" class="empty"><i class="bi bi-inbox"></i>No hay pedidos para los filtros seleccionados</td></tr>
        @endforelse
        </tbody>
    </table></div>
    @include('partials.paginacion', ['items' => $pedidos])
</div>
@endsection

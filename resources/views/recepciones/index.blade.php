@extends('layouts.app')

@section('contenido')
<form class="toolbar" method="GET" data-auto-submit>
    @include('partials.rango')
    <select class="form-select form-select-sm" name="proveedor"><option value="">Todos los proveedores</option>@foreach ($proveedores as $id => $n)<option value="{{ $id }}" @selected(request('proveedor') == $id)>{{ $n }}</option>@endforeach</select>
    <select class="form-select form-select-sm" name="pago"><option value="">Estado de pago</option>@foreach (['Pagado', 'Parcial', 'Pendiente'] as $e)<option @selected(request('pago') === $e)>{{ $e }}</option>@endforeach</select>
    <div class="ms-auto d-flex gap-2">
        <a class="btn btn-sm btn-light" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
        <a class="btn btn-sm btn-primary" href="{{ route('recepciones.create') }}"><i class="bi bi-plus-lg"></i> Nueva recepción</a>
    </div>
</form>
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'box-arrow-in-down', 'color' => 'i-blue', 'label' => 'Recepciones', 'valor' => $resumen->n])</div>
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'fuel-pump', 'label' => 'Garrafas recibidas', 'valor' => $garrafasRecibidas])</div>
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'bag', 'color' => 'i-violet', 'label' => 'Total comprado', 'valor' => pesos($resumen->total)])</div>
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'exclamation-diamond', 'color' => 'i-red', 'label' => 'Deuda con proveedores', 'valor' => pesos($deuda)])</div>
</div>
<div class="card">
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>N°</th><th>Fecha</th><th>Proveedor</th><th>Factura</th><th>Productos recibidos</th><th class="num">Vacías entregadas</th><th class="num">Total</th><th>Pago</th><th></th></tr></thead>
        <tbody>
        @forelse ($recepciones as $r)
            <tr class="row-link" data-href="{{ route('recepciones.show', $r) }}">
                <td><a href="{{ route('recepciones.show', $r) }}" class="fw-semibold">{{ $r->numero }}</a></td><td>{{ fecha($r->fecha) }}</td>
                <td>{{ $r->proveedor->razon_social }}</td><td class="small">{{ $r->numero_factura_proveedor ?: '—' }}</td>
                <td class="small">{{ $r->resumenItems() }}</td><td class="num">{{ $r->vacias_entregadas ?: '—' }}</td>
                <td class="num">{{ pesos($r->total) }}</td><td>{{ badge_pago($r->estadoPago()) }}</td>
                <td class="actions">@if ($r->saldo > 0)<a class="btn btn-sm btn-outline-primary" href="{{ route('pagos-proveedores.create', ['proveedor' => $r->proveedor_id, 'recepcion' => $r->id]) }}"><i class="bi bi-wallet2"></i> Pagar</a>@endif</td>
            </tr>
        @empty
            <tr><td colspan="9" class="empty"><i class="bi bi-inbox"></i>Sin recepciones en el período</td></tr>
        @endforelse
        </tbody>
    </table></div>
    @include('partials.paginacion', ['items' => $recepciones])
</div>
@endsection

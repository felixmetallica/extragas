@extends('layouts.app')

@section('contenido')
<div class="row g-3 mb-3">
    @foreach ($proveedores as $p)
        @php $s = $saldos[$p->id]->saldo_total ?? 0; @endphp
        <div class="col-sm-6 col-xl-3"><div class="card kpi">
            <div class="kpi-icon {{ $s > 0 ? 'i-red' : 'i-green' }}"><i class="bi bi-truck"></i></div>
            <div class="flex-fill" style="min-width:0"><div class="kpi-label text-truncate">{{ $p->razon_social }}</div><div class="kpi-value">{{ pesos($s) }}</div>
                <div class="kpi-sub">@if ($s > 0)<a href="{{ route('pagos-proveedores.create', ['proveedor' => $p->id]) }}">Registrar pago</a>@else Sin deuda @endif</div></div>
        </div></div>
    @endforeach
</div>
<form class="toolbar" method="GET" data-auto-submit>
    @include('partials.rango')
    <select class="form-select form-select-sm" name="proveedor"><option value="">Todos los proveedores</option>@foreach ($proveedores as $p)<option value="{{ $p->id }}" @selected(request('proveedor') == $p->id)>{{ $p->razon_social }}</option>@endforeach</select>
    <div class="ms-auto d-flex gap-2">
        <a class="btn btn-sm btn-light" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
        <a class="btn btn-sm btn-primary" href="{{ route('pagos-proveedores.create') }}"><i class="bi bi-plus-lg"></i> Registrar pago</a>
    </div>
</form>
<div class="card">
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>N°</th><th>Fecha</th><th>Proveedor</th><th>Recepción</th><th>Forma</th><th>Referencia</th><th class="num">Importe</th></tr></thead>
        <tbody>
        @forelse ($pagos as $p)
            <tr><td class="fw-semibold text-nowrap">{{ $p->numero }}</td><td>{{ fecha($p->fecha) }}</td><td><a href="{{ route('proveedores.show', $p->proveedor) }}">{{ $p->proveedor->razon_social }}</a></td>
                <td>@if ($p->recepcion)<a href="{{ route('recepciones.show', $p->recepcion) }}">{{ $p->recepcion->numero }}</a>@else A cuenta @endif</td>
                <td>{{ $p->formaPago->nombre }}</td><td>{{ $p->referencia ?: '—' }}</td><td class="num fw-semibold">{{ pesos($p->monto) }}</td></tr>
        @empty
            <tr><td colspan="7" class="empty"><i class="bi bi-inbox"></i>Sin pagos en el período</td></tr>
        @endforelse
        </tbody>
        @if ($pagos->total())<tfoot><tr><th colspan="6" class="text-end">Total del período</th><th class="num">{{ pesos($totalPeriodo) }}</th></tr></tfoot>@endif
    </table></div>
    @include('partials.paginacion', ['items' => $pagos])
</div>
@endsection

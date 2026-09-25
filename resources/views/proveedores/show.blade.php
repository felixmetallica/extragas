@extends('layouts.app')

@section('contenido')
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    @unless ($proveedor->activo) {{ badge('Inactivo') }} @endunless
    <span class="text-muted-sm">CUIT {{ $proveedor->cuit }}</span>
    <div class="ms-auto d-flex flex-wrap gap-2">
        <a class="btn btn-sm btn-light" href="{{ route('proveedores.edit', $proveedor) }}"><i class="bi bi-pencil"></i> Editar</a>
        @if ($saldo > 0)<a class="btn btn-sm btn-outline-primary" href="{{ route('pagos-proveedores.create', ['proveedor' => $proveedor->id]) }}"><i class="bi bi-wallet2"></i> Registrar pago</a>@endif
        <a class="btn btn-sm btn-primary" href="{{ route('recepciones.create', ['proveedor' => $proveedor->id]) }}"><i class="bi bi-box-arrow-in-down"></i> Nueva recepción</a>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'box-arrow-in-down', 'color' => 'i-blue', 'label' => 'Recepciones', 'valor' => $recepciones->total()])</div>
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'bag', 'label' => 'Comprado 90 días', 'valor' => pesos($comprado90)])</div>
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'wallet2', 'color' => 'i-green', 'label' => 'Pagado total', 'valor' => pesos($pagadoTotal)])</div>
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'exclamation-diamond', 'color' => $saldo > 0 ? 'i-red' : 'i-green', 'label' => 'Saldo a pagar', 'valor' => pesos($saldo)])</div>
</div>
<div class="row g-3">
    <div class="col-lg-4"><div class="card"><div class="card-header"><h2>Datos del proveedor</h2></div><div class="card-body"><dl class="info-list">
        @if ($proveedor->nombre_fantasia)<dt>Fantasía</dt><dd>{{ $proveedor->nombre_fantasia }}</dd>@endif
        <dt>Teléfono</dt><dd>{{ $proveedor->telefono_principal ?: '—' }}{{ $proveedor->telefono_secundario ? ' / '.$proveedor->telefono_secundario : '' }}</dd>
        <dt>Email</dt><dd>{{ $proveedor->email ?: '—' }}</dd>
        <dt>Contacto</dt><dd>{{ $proveedor->contacto_nombre ?: '—' }}@if ($proveedor->contacto_telefono)<br>{{ $proveedor->contacto_telefono }} <a href="{{ wa_link($proveedor->contacto_telefono) }}" target="_blank" rel="noopener"><i class="bi bi-whatsapp text-success"></i></a>@endif</dd>
        <dt>Domicilio</dt><dd>{{ $proveedor->domicilioCompleto() ?: '—' }}</dd>
        <dt>Obs.</dt><dd>{{ $proveedor->observaciones ?: '—' }}</dd>
    </dl></div></div></div>
    <div class="col-lg-8"><div class="card">
        <div class="card-header p-0 px-2 pt-2 border-0"><ul class="nav nav-tabs w-100">
            <li class="nav-item"><button @class(['nav-link', 'active' => ! request()->has('pagina_pag')]) data-bs-toggle="tab" data-bs-target="#tRec" type="button">Recepciones</button></li>
            <li class="nav-item"><button @class(['nav-link', 'active' => request()->has('pagina_pag')]) data-bs-toggle="tab" data-bs-target="#tPag" type="button">Pagos</button></li>
        </ul></div>
        <div class="tab-content">
            <div @class(['tab-pane fade', 'show active' => ! request()->has('pagina_pag')]) id="tRec">
                <div class="table-responsive"><table class="table table-hover align-middle">
                    <thead><tr><th>N°</th><th>Fecha</th><th>Factura</th><th>Productos</th><th class="num">Total</th><th>Pago</th></tr></thead>
                    <tbody>@forelse ($recepciones as $r)
                        <tr class="row-link" data-href="{{ route('recepciones.show', $r) }}"><td><a href="{{ route('recepciones.show', $r) }}" class="fw-semibold">{{ $r->numero }}</a></td><td>{{ fecha($r->fecha) }}</td><td>{{ $r->numero_factura_proveedor ?: '—' }}</td>
                            <td class="small">{{ $r->resumenItems() }}</td><td class="num">{{ pesos($r->total) }}</td><td>{{ badge_pago($r->estadoPago()) }}</td></tr>
                    @empty<tr><td colspan="6" class="empty">Sin recepciones</td></tr>@endforelse</tbody>
                </table></div>
                @include('partials.paginacion', ['items' => $recepciones])
            </div>
            <div @class(['tab-pane fade', 'show active' => request()->has('pagina_pag')]) id="tPag">
                <div class="table-responsive"><table class="table align-middle">
                    <thead><tr><th>N°</th><th>Fecha</th><th>Recepción</th><th>Forma</th><th>Referencia</th><th class="num">Monto</th></tr></thead>
                    <tbody>@forelse ($pagos as $pg)
                        <tr><td class="fw-semibold">{{ $pg->numero }}</td><td>{{ fecha($pg->fecha) }}</td><td>{{ $pg->recepcion?->numero ?? 'A cuenta' }}</td><td>{{ $pg->formaPago->nombre }}</td><td>{{ $pg->referencia ?: '—' }}</td><td class="num">{{ pesos($pg->monto) }}</td></tr>
                    @empty<tr><td colspan="6" class="empty">Sin pagos</td></tr>@endforelse</tbody>
                </table></div>
                @include('partials.paginacion', ['items' => $pagos])
            </div>
        </div>
    </div></div>
</div>
@endsection

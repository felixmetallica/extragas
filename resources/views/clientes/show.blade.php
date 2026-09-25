@extends('layouts.app')

@section('contenido')
@php $r = $regularidad; $totalPagos = $formasUsadas->sum(); @endphp
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    @unless ($cliente->activo) {{ badge('Inactivo') }} @endunless
    @if ($r['promedio']) {{ badge_regularidad($r['estado']) }} @endif
    <span class="text-muted-sm">{{ $cliente->codigo ? $cliente->codigo.' · ' : '' }}Cliente desde {{ fecha($cliente->fecha_alta) }}</span>
    <div class="ms-auto d-flex flex-wrap gap-2">
        <a class="btn btn-sm btn-light" href="{{ wa_link($cliente->telefono_principal) }}" target="_blank" rel="noopener"><i class="bi bi-whatsapp text-success"></i> WhatsApp</a>
        <a class="btn btn-sm btn-light" href="{{ route('clientes.edit', $cliente) }}"><i class="bi bi-pencil"></i> Editar</a>
        <a class="btn btn-sm btn-light" href="{{ route('clientes.estado-cuenta', $cliente) }}"><i class="bi bi-file-earmark-pdf"></i> Estado de cuenta</a>
        @if ($saldo > 0)<a class="btn btn-sm btn-outline-primary" href="{{ route('cobros.create', ['cliente' => $cliente->id]) }}"><i class="bi bi-cash-coin"></i> Registrar pago</a>@endif
        <a class="btn btn-sm btn-primary" href="{{ route('pedidos.create', ['cliente' => $cliente->id]) }}"><i class="bi bi-cart-plus"></i> Nuevo pedido</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'receipt', 'label' => 'Pedidos', 'valor' => $cantidadPedidos, 'sub' => 'Total '.pesos($totalComprado)])</div>
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'calendar2-week', 'color' => 'i-blue', 'label' => 'Pide cada', 'valor' => $r['promedio'] ? $r['promedio'].' días' : '—', 'sub' => $r['proximo'] ? 'Próximo estimado: '.fecha($r['proximo']) : 'Sin historial suficiente'])</div>
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'fuel-pump', 'color' => 'i-violet', 'label' => 'Garrafas en su poder', 'valor' => $cliente->garrafas->count(), 'sub' => $cliente->garrafas->countBy('capacidad_kg')->map(fn ($n, $c) => "{$n}× {$c} kg")->implode(' · ') ?: 'Ninguna'])</div>
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icono' => 'wallet2', 'color' => $saldo > 0 ? 'i-red' : 'i-green', 'label' => 'Saldo', 'valor' => pesos($saldo), 'sub' => $saldo > 0 ? 'Adeudado' : 'Al día'])</div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header"><h2>Datos de contacto</h2></div><div class="card-body"><dl class="info-list">
            <dt>Teléfono</dt><dd class="fw-semibold">{{ $cliente->telefono_principal }}</dd>
            @if ($cliente->telefono_secundario)<dt>Alternativo</dt><dd>{{ $cliente->telefono_secundario }}</dd>@endif
            @if ($cliente->email)<dt>Email</dt><dd>{{ $cliente->email }}</dd>@endif
            <dt>Domicilio</dt><dd>{{ $cliente->domicilio() ?: '—' }}</dd>
            <dt>Ciudad</dt><dd>{{ collect([$cliente->ciudad, $cliente->codigo_postal ? "CP {$cliente->codigo_postal}" : null, $cliente->provincia?->nombre])->filter()->implode(' · ') ?: '—' }}</dd>
            <dt>Referencias</dt><dd>{{ $cliente->referencias ?: '—' }}</dd>
            <dt>DNI / CUIT</dt><dd>{{ collect([$cliente->dni, $cliente->cuit_cuil])->filter()->implode(' · ') ?: '—' }}</dd>
            <dt>Obs.</dt><dd>{{ $cliente->observaciones ?: '—' }}</dd>
        </dl></div></div>

        <div class="card mb-3"><div class="card-header"><h2>Otros contactos</h2></div>
            <ul class="list-group list-group-flush">
                @forelse ($cliente->contactos as $ct)
                    <li class="list-group-item d-flex align-items-center gap-2">
                        <span class="flex-fill"><small class="text-body-secondary">{{ $ct->tipo->nombre }}</small><br>{{ $ct->valor }} @if ($ct->es_principal) {{ badge('Principal', 'b-info') }} @endif
                            @if ($ct->observaciones)<div class="text-muted-sm">{{ $ct->observaciones }}</div>@endif</span>
                        <form method="POST" action="{{ route('clientes.contactos.destroy', [$cliente, $ct]) }}" data-confirm="¿Eliminar este contacto?">@csrf @method('DELETE')<button class="btn btn-sm btn-light" title="Eliminar"><i class="bi bi-trash text-danger"></i></button></form>
                    </li>
                @empty
                    <li class="list-group-item text-body-secondary small">Sin contactos adicionales</li>
                @endforelse
            </ul>
            <form method="POST" action="{{ route('clientes.contactos.store', $cliente) }}" class="card-body border-top row g-2">
                @csrf
                <div class="col-5"><select class="form-select form-select-sm" name="tipo_contacto_id" aria-label="Tipo">@foreach ($tiposContacto as $t)<option value="{{ $t->id }}">{{ $t->nombre }}</option>@endforeach</select></div>
                <div class="col-7"><input class="form-control form-control-sm" name="valor" placeholder="Número o email" required aria-label="Valor"></div>
                <div class="col-9"><input class="form-control form-control-sm" name="observaciones" placeholder="Ej.: hijo, trabajo" aria-label="Observaciones"></div>
                <div class="col-3 d-grid"><button class="btn btn-sm btn-outline-primary">Agregar</button></div>
            </form>
        </div>

        <div class="card mb-3"><div class="card-header"><h2>Hábitos de compra</h2></div><div class="card-body"><dl class="info-list">
            <dt>Pago habitual</dt><dd>{{ $cliente->formaPagoHabitual?->nombre ?? '—' }}</dd>
            <dt>Pagó con</dt><dd>{!! $formasUsadas->map(fn ($n, $f) => e($f).' <span class="text-body-secondary">('.round($n / max(1, $totalPagos) * 100).'%)</span>')->implode('<br>') ?: '—' !!}</dd>
            <dt>Último pedido</dt><dd>{{ $r['ultimo'] ? fecha($r['ultimo']).' (hace '.(int) $r['ultimo']->diffInDays(today()).' días)' : '—' }}</dd>
            <dt>Compra más</dt><dd>{!! $favoritos->map(fn ($f) => e($f->nombre).' <span class="text-body-secondary">('.num($f->cantidad).')</span>')->implode('<br>') ?: '—' !!}</dd>
        </dl></div></div>

        <div class="card"><div class="card-header"><h2>Garrafas en su poder</h2></div>
            <ul class="list-group list-group-flush">
                @forelse ($cliente->garrafas->sortBy('capacidad_kg') as $g)
                    <li class="list-group-item d-flex justify-content-between"><a href="{{ route('garrafas.show', $g) }}"><i class="bi bi-fuel-pump me-2 text-brand"></i>{{ $g->codigo }}</a>
                        <span class="text-body-secondary small">{{ $g->capacidad_kg }} kg · desde {{ fecha($g->fecha_ultimo_movimiento) }}</span></li>
                @empty
                    <li class="list-group-item text-body-secondary small">No tiene garrafas</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header p-0 px-2 pt-2 border-0">
                <ul class="nav nav-tabs w-100" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tPedidos" type="button">Pedidos ({{ $pedidos->total() }})</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tCta" type="button">Cuenta corriente</button></li>
                </ul>
            </div>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tPedidos">
                    <div class="table-responsive"><table class="table table-hover align-middle">
                        <thead><tr><th>N°</th><th>Fecha</th><th>Medio</th><th>Productos</th><th class="num">Total</th><th>Pago</th><th>Estado</th></tr></thead>
                        <tbody>
                        @forelse ($pedidos as $p)
                            <tr class="row-link" data-href="{{ route('pedidos.show', $p) }}"><td><a href="{{ route('pedidos.show', $p) }}" class="fw-semibold">{{ $p->numero }}</a></td><td>{{ fecha($p->fecha) }}</td>
                                <td>@include('partials.medio', ['medio' => $p->medioContacto])</td><td class="small">{{ $p->resumenItems() }}</td><td class="num">{{ pesos($p->total) }}</td>
                                <td>{{ badge_pago($p->estadoPago()) }}</td><td>{{ badge_estado_pedido($p->estado) }}</td></tr>
                        @empty
                            <tr><td colspan="7" class="empty">El cliente no tiene pedidos</td></tr>
                        @endforelse
                        </tbody>
                    </table></div>
                    @include('partials.paginacion', ['items' => $pedidos])
                </div>
                <div class="tab-pane fade" id="tCta">
                    <div class="table-responsive" style="max-height:620px"><table class="table align-middle">
                        <thead><tr><th>Fecha</th><th>Comprobante</th><th class="num">Debe</th><th class="num">Haber</th><th class="num">Saldo</th></tr></thead>
                        <tbody>
                        @forelse ($movimientos->reverse() as $m)
                            <tr><td>{{ fecha($m->fecha) }}</td>
                                <td>@if ($m->tipo_movimiento === 'PEDIDO')<a href="{{ route('pedidos.show', $m->pedido_id) }}">Pedido {{ $m->comprobante }}</a>@else Recibo {{ $m->comprobante }}@endif</td>
                                <td class="num">{{ $m->debe > 0 ? pesos($m->debe) : '' }}</td><td class="num text-success">{{ $m->haber > 0 ? pesos($m->haber) : '' }}</td>
                                <td class="num fw-semibold">{{ pesos($m->saldo) }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="empty">Sin movimientos</td></tr>
                        @endforelse
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
        @if ($saldo <= 0 && ! $cliente->garrafas->count())
            <form method="POST" action="{{ route('clientes.destroy', $cliente) }}" class="mt-3 text-end" data-confirm="¿Eliminar el cliente {{ $cliente->nombreCompleto() }}?">@csrf @method('DELETE')
                <button class="btn btn-sm btn-link text-danger"><i class="bi bi-trash"></i> Eliminar cliente</button></form>
        @endif
    </div>
</div>
@endsection

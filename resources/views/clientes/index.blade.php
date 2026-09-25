@extends('layouts.app')

@section('contenido')
<form class="toolbar" method="GET" data-auto-submit>
    <div class="search"><i class="bi bi-search"></i><input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre, domicilio, DNI o teléfono"></div>
    <select class="form-select form-select-sm" name="forma_pago"><option value="">Forma de pago habitual</option>@foreach ($formasPago as $f)<option value="{{ $f->id }}" @selected(request('forma_pago') == $f->id)>{{ $f->nombre }}</option>@endforeach</select>
    <select class="form-select form-select-sm" name="filtro">
        @foreach (['' => 'Todos los activos', 'deuda' => 'Con saldo adeudado', 'atrasado' => 'Atrasados según su regularidad', 'envases' => 'Con garrafas en su poder', 'inactivo' => 'Inactivos'] as $v => $t)
            <option value="{{ $v }}" @selected(request('filtro') === $v)>{{ $t }}</option>
        @endforeach
    </select>
    <div class="ms-auto d-flex gap-2">
        <a class="btn btn-sm btn-light" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
        <a class="btn btn-sm btn-primary" href="{{ route('clientes.create') }}"><i class="bi bi-person-plus"></i> Nuevo cliente</a>
    </div>
</form>
<div class="card">
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>Cliente</th><th>Domicilio</th><th>Teléfono</th><th>Pago habitual</th><th class="num">Garrafas</th><th>Último pedido</th><th>Regularidad</th><th class="num">Saldo</th><th></th></tr></thead>
        <tbody>
        @forelse ($clientes as $c)
            @php $r = $regularidad[$c->id]; $s = $saldos[$c->id] ?? 0; @endphp
            <tr class="row-link" data-href="{{ route('clientes.show', $c) }}">
                <td><a href="{{ route('clientes.show', $c) }}" class="fw-semibold">{{ $c->nombreCompleto() }}</a> @unless ($c->activo) {{ badge('Inactivo') }} @endunless
                    <div class="text-muted-sm">{{ $c->codigo }}{{ $c->dni ? ' · DNI '.$c->dni : '' }}</div></td>
                <td>{{ $c->domicilio() ?: '—' }}<div class="text-muted-sm">{{ $c->ciudad }}</div></td>
                <td class="text-nowrap">{{ $c->telefono_principal }} <a href="{{ wa_link($c->telefono_principal) }}" target="_blank" rel="noopener" title="WhatsApp"><i class="bi bi-whatsapp text-success"></i></a></td>
                <td>{{ $c->formaPagoHabitual?->nombre ?? '—' }}</td>
                <td class="num">{{ $c->garrafas_count ?: '0' }}</td>
                <td class="text-nowrap">{{ fecha($r['ultimo']) }}@if ($r['ultimo'])<div class="text-muted-sm">hace {{ (int) $r['ultimo']->diffInDays(today()) }} días</div>@endif</td>
                <td>@if ($r['promedio'])<span class="text-nowrap">Cada {{ $r['promedio'] }} días</span><div>{{ badge_regularidad($r['estado']) }}</div>@else<span class="text-body-tertiary">Sin datos</span>@endif</td>
                <td class="num">@if ($s > 0)<span class="text-danger fw-semibold">{{ pesos($s) }}</span>@else<span class="text-success">$ 0</span>@endif</td>
                <td class="actions">
                    <a class="btn btn-sm btn-light" href="{{ route('pedidos.create', ['cliente' => $c->id]) }}" title="Nuevo pedido"><i class="bi bi-cart-plus text-brand"></i></a>
                    <a class="btn btn-sm btn-light" href="{{ route('clientes.edit', $c) }}" title="Editar"><i class="bi bi-pencil"></i></a>
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="empty"><i class="bi bi-inbox"></i>No se encontraron clientes</td></tr>
        @endforelse
        </tbody>
    </table></div>
    @include('partials.paginacion', ['items' => $clientes])
</div>
@endsection

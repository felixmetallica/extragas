@extends('layouts.app')

@section('contenido')
<form class="toolbar" method="GET">
    <div class="search"><i class="bi bi-search"></i><input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Buscar proveedor, CUIT o contacto"></div>
    <div class="ms-auto"><a class="btn btn-sm btn-primary" href="{{ route('proveedores.create') }}"><i class="bi bi-plus-lg"></i> Nuevo proveedor</a></div>
</form>
<div class="card">
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>Proveedor</th><th>Contacto</th><th>Teléfono</th><th>Localidad</th><th>Última recepción</th><th class="num">Saldo a pagar</th><th></th></tr></thead>
        <tbody>
        @forelse ($proveedores as $p)
            <tr class="row-link" data-href="{{ route('proveedores.show', $p) }}">
                <td><a href="{{ route('proveedores.show', $p) }}" class="fw-semibold">{{ $p->razon_social }}</a> @unless ($p->activo) {{ badge('Inactivo') }} @endunless<div class="text-muted-sm">CUIT {{ $p->cuit }}{{ $p->nombre_fantasia ? ' · '.$p->nombre_fantasia : '' }}</div></td>
                <td>{{ $p->contacto_nombre ?: '—' }}@if ($p->contacto_telefono)<div class="text-muted-sm">{{ $p->contacto_telefono }} <a href="{{ wa_link($p->contacto_telefono) }}" target="_blank" rel="noopener"><i class="bi bi-whatsapp text-success"></i></a></div>@endif</td>
                <td>{{ $p->telefono_principal ?: '—' }}</td>
                <td>{{ collect([$p->ciudad, $p->provincia?->nombre])->filter()->implode(', ') ?: '—' }}</td>
                <td>{{ fecha($p->recepciones_max_fecha) }}</td>
                <td class="num">@if (($saldos[$p->id] ?? 0) > 0)<span class="text-danger fw-semibold">{{ pesos($saldos[$p->id]) }}</span>@else<span class="text-success">$ 0</span>@endif</td>
                <td class="actions"><a class="btn btn-sm btn-light" href="{{ route('proveedores.edit', $p) }}" title="Editar"><i class="bi bi-pencil"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="empty"><i class="bi bi-inbox"></i>No hay proveedores</td></tr>
        @endforelse
        </tbody>
    </table></div>
    @include('partials.paginacion', ['items' => $proveedores])
</div>
@endsection

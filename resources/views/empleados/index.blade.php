@extends('layouts.app')

@section('contenido')
<div class="toolbar"><div class="text-body-secondary small">Personal que atiende pedidos, reparte y recibe mercadería.</div>
    <div class="ms-auto"><a class="btn btn-sm btn-primary" href="{{ route('empleados.create') }}"><i class="bi bi-plus-lg"></i> Nuevo empleado</a></div></div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle">
    <thead><tr><th>Empleado</th><th>DNI / CUIL</th><th>Teléfono</th><th>Ingreso</th><th>Usuario</th><th class="num">Pedidos</th><th>Estado</th><th></th></tr></thead>
    <tbody>@foreach ($empleados as $e)
        <tr><td class="fw-semibold">{{ $e->nombreCompleto() }}</td><td>{{ collect([$e->dni, $e->cuil])->filter()->implode(' · ') ?: '—' }}</td><td>{{ $e->telefono ?: '—' }}</td>
            <td>{{ fecha($e->fecha_ingreso) }}</td><td>{{ $e->usuario?->username ?? '—' }}</td><td class="num">{{ $e->pedidos_count }}</td>
            <td>{!! $e->activo ? badge('Activo', 'b-pagado') : badge('Inactivo') !!}</td>
            <td class="actions"><a class="btn btn-sm btn-light" href="{{ route('empleados.edit', $e) }}"><i class="bi bi-pencil"></i></a></td></tr>
    @endforeach</tbody>
</table></div></div>
@endsection

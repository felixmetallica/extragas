@extends('layouts.app')

@section('contenido')
<div class="toolbar"><div class="text-body-secondary small">Personas que ingresan al sistema. Cada usuario se vincula con un empleado para registrar quién tomó cada pedido.</div>
    <div class="ms-auto"><a class="btn btn-sm btn-primary" href="{{ route('usuarios.create') }}"><i class="bi bi-person-plus"></i> Nuevo usuario</a></div></div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle">
    <thead><tr><th>Usuario</th><th>Empleado</th><th>Rol</th><th>Email</th><th>Último ingreso</th><th>Estado</th><th></th></tr></thead>
    <tbody>@foreach ($usuarios as $u)
        <tr><td><div class="d-flex align-items-center gap-2"><span class="avatar">{{ $u->iniciales() }}</span><span class="fw-semibold">{{ $u->username }}</span></div></td>
            <td>{{ $u->empleado?->nombreCompleto() ?? '—' }}</td><td>{{ badge($u->rol->nombre, $u->rol->codigo === 'ADMIN' ? 'b-parcial' : 'b-info') }}</td>
            <td>{{ $u->email ?: '—' }}</td><td>{{ fecha($u->ultimo_login, true) }}</td><td>{!! $u->activo ? badge('Activo', 'b-pagado') : badge('Inactivo') !!}</td>
            <td class="actions"><a class="btn btn-sm btn-light" href="{{ route('usuarios.edit', $u) }}"><i class="bi bi-pencil"></i></a></td></tr>
    @endforeach</tbody>
</table></div></div>
@endsection

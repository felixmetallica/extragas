@extends('layouts.app')

@section('contenido')
<form method="POST" action="{{ $usuario->exists ? route('usuarios.update', $usuario) : route('usuarios.store') }}" class="card" style="max-width:720px">
    @csrf
    @if ($usuario->exists) @method('PUT') @endif
    <div class="card-body row g-3">
        @include('partials.campo', ['name' => 'username', 'label' => 'Usuario', 'value' => $usuario->username, 'req' => true, 'attrs' => 'autocomplete="off"'])
        @include('partials.campo', ['name' => 'rol_id', 'label' => 'Rol', 'type' => 'select', 'opciones' => $roles->pluck('nombre', 'id'), 'value' => $usuario->rol_id])
        @include('partials.campo', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $usuario->email])
        @include('partials.campo', ['name' => 'empleado_id', 'label' => 'Empleado vinculado', 'type' => 'select', 'opciones' => $empleados->mapWithKeys(fn ($e) => [$e->id => $e->nombreCompleto()]), 'value' => $usuario->empleado?->id, 'vacio' => '— Sin vincular —'])
        @include('partials.campo', ['name' => 'password', 'label' => 'Contraseña', 'type' => 'password', 'req' => ! $usuario->exists, 'attrs' => 'autocomplete="new-password"', 'placeholder' => $usuario->exists ? 'Dejar vacío para no cambiarla' : 'Mínimo 6 caracteres'])
        @include('partials.campo', ['name' => 'password_confirmation', 'label' => 'Repetir contraseña', 'type' => 'password', 'attrs' => 'autocomplete="new-password"'])
        @if ($usuario->exists)
            <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="activo" value="1" id="activo" @checked(old('activo', $usuario->activo))><label class="form-check-label" for="activo">Usuario activo</label></div></div>
        @endif
        <div class="col-12"><div class="alert alert-light border small mb-0"><b>Administrador:</b> acceso total, precios, usuarios y configuración. <b>Empleado:</b> pedidos, clientes, cobros, garrafas, proveedores e informes.</div></div>
    </div>
    <div class="card-body border-top d-flex gap-2 justify-content-end"><a class="btn btn-light" href="{{ route('usuarios.index') }}">Cancelar</a><button class="btn btn-primary"><i class="bi bi-check2"></i> Guardar</button></div>
</form>
@endsection

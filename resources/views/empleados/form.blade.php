@extends('layouts.app')

@section('contenido')
<form method="POST" action="{{ $empleado->exists ? route('empleados.update', $empleado) : route('empleados.store') }}" class="card" style="max-width:900px">
    @csrf
    @if ($empleado->exists) @method('PUT') @endif
    <div class="card-body row g-3">
        @include('partials.campo', ['name' => 'nombre', 'label' => 'Nombre', 'value' => $empleado->nombre, 'req' => true, 'col' => 'col-md-4'])
        @include('partials.campo', ['name' => 'apellido', 'label' => 'Apellido', 'value' => $empleado->apellido, 'req' => true, 'col' => 'col-md-4'])
        @include('partials.campo', ['name' => 'fecha_ingreso', 'label' => 'Fecha de ingreso', 'type' => 'date', 'value' => $empleado->fecha_ingreso?->toDateString(), 'col' => 'col-md-4'])
        @include('partials.campo', ['name' => 'dni', 'label' => 'DNI', 'value' => $empleado->dni, 'col' => 'col-md-4'])
        @include('partials.campo', ['name' => 'cuil', 'label' => 'CUIL', 'value' => $empleado->cuil, 'col' => 'col-md-4'])
        @include('partials.campo', ['name' => 'telefono', 'label' => 'Teléfono', 'value' => $empleado->telefono, 'col' => 'col-md-4'])
        @include('partials.campo', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $empleado->email, 'col' => 'col-md-6'])
        @include('partials.campo', ['name' => 'calle', 'label' => 'Calle', 'value' => $empleado->calle, 'col' => 'col-md-4'])
        @include('partials.campo', ['name' => 'numero', 'label' => 'Número', 'value' => $empleado->numero, 'col' => 'col-md-2'])
        @include('partials.campo', ['name' => 'piso', 'label' => 'Piso', 'value' => $empleado->piso, 'col' => 'col-4 col-md-2'])
        @include('partials.campo', ['name' => 'depto', 'label' => 'Depto.', 'value' => $empleado->depto, 'col' => 'col-4 col-md-2'])
        @include('partials.campo', ['name' => 'codigo_postal', 'label' => 'CP', 'value' => $empleado->codigo_postal, 'col' => 'col-4 col-md-2'])
        @include('partials.campo', ['name' => 'ciudad', 'label' => 'Ciudad', 'value' => $empleado->ciudad, 'col' => 'col-md-6'])
        @include('partials.campo', ['name' => 'provincia_id', 'label' => 'Provincia', 'type' => 'select', 'value' => $empleado->provincia_id, 'opciones' => $provincias->pluck('nombre', 'id'), 'vacio' => '—', 'col' => 'col-md-6'])
        @include('partials.campo', ['name' => 'observaciones', 'label' => 'Observaciones', 'type' => 'textarea', 'value' => $empleado->observaciones, 'col' => 'col-12'])
        @if ($empleado->exists)
            <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="activo" value="1" id="activo" @checked(old('activo', $empleado->activo))><label class="form-check-label" for="activo">Empleado activo</label></div></div>
        @endif
    </div>
    <div class="card-body border-top d-flex gap-2 justify-content-end"><a class="btn btn-light" href="{{ route('empleados.index') }}">Cancelar</a><button class="btn btn-primary"><i class="bi bi-check2"></i> Guardar</button></div>
</form>
@endsection

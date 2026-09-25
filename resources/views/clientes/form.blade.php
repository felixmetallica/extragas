@extends('layouts.app')

@section('contenido')
<form method="POST" action="{{ $cliente->exists ? route('clientes.update', $cliente) : route('clientes.store') }}" class="card" style="max-width:980px">
    @csrf
    @if ($cliente->exists) @method('PUT') @endif
    <input type="hidden" name="volver" value="{{ request('volver') }}">
    <div class="card-body">
        <div class="section-title mt-0">Datos personales</div>
        <div class="row g-3">
            @include('partials.campo', ['name' => 'nombre', 'label' => 'Nombre', 'value' => $cliente->nombre, 'req' => true, 'col' => 'col-md-4'])
            @include('partials.campo', ['name' => 'apellido', 'label' => 'Apellido', 'value' => $cliente->apellido, 'req' => true, 'col' => 'col-md-4', 'placeholder' => 'Para comercios: nombre de fantasía'])
            @include('partials.campo', ['name' => 'codigo', 'label' => 'Código', 'value' => $cliente->codigo, 'col' => 'col-md-4', 'placeholder' => 'Opcional'])
            @include('partials.campo', ['name' => 'dni', 'label' => 'DNI', 'value' => $cliente->dni, 'col' => 'col-md-4'])
            @include('partials.campo', ['name' => 'cuit_cuil', 'label' => 'CUIT / CUIL', 'value' => $cliente->cuit_cuil, 'col' => 'col-md-4'])
            @include('partials.campo', ['name' => 'forma_pago_habitual_id', 'label' => 'Forma de pago habitual', 'type' => 'select', 'value' => $cliente->forma_pago_habitual_id, 'opciones' => $formasPago->pluck('nombre', 'id'), 'vacio' => '—', 'col' => 'col-md-4'])
        </div>
        <div class="section-title">Contacto</div>
        <div class="row g-3">
            @include('partials.campo', ['name' => 'telefono_principal', 'label' => 'Celular / teléfono principal', 'value' => $cliente->telefono_principal, 'req' => true, 'col' => 'col-md-4', 'placeholder' => '381 555-1234'])
            @include('partials.campo', ['name' => 'telefono_secundario', 'label' => 'Teléfono alternativo', 'value' => $cliente->telefono_secundario, 'col' => 'col-md-4'])
            @include('partials.campo', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $cliente->email, 'col' => 'col-md-4'])
        </div>
        <div class="section-title">Domicilio</div>
        <div class="row g-3">
            @include('partials.campo', ['name' => 'calle', 'label' => 'Calle', 'value' => $cliente->calle, 'col' => 'col-md-5'])
            @include('partials.campo', ['name' => 'numero', 'label' => 'Número', 'value' => $cliente->numero, 'col' => 'col-4 col-md-2'])
            @include('partials.campo', ['name' => 'piso', 'label' => 'Piso', 'value' => $cliente->piso, 'col' => 'col-4 col-md-2'])
            @include('partials.campo', ['name' => 'depto', 'label' => 'Depto.', 'value' => $cliente->depto, 'col' => 'col-4 col-md-3'])
            @include('partials.campo', ['name' => 'ciudad', 'label' => 'Ciudad / barrio', 'value' => $cliente->ciudad, 'col' => 'col-md-5'])
            @include('partials.campo', ['name' => 'codigo_postal', 'label' => 'Código postal', 'value' => $cliente->codigo_postal, 'col' => 'col-md-2'])
            @include('partials.campo', ['name' => 'provincia_id', 'label' => 'Provincia', 'type' => 'select', 'value' => $cliente->provincia_id, 'opciones' => $provincias->pluck('nombre', 'id'), 'vacio' => '—', 'col' => 'col-md-5'])
            @include('partials.campo', ['name' => 'referencias', 'label' => 'Referencias para la entrega', 'type' => 'textarea', 'value' => $cliente->referencias, 'col' => 'col-12', 'placeholder' => 'Ej.: portón verde, casa esquina'])
            @include('partials.campo', ['name' => 'observaciones', 'label' => 'Observaciones', 'type' => 'textarea', 'value' => $cliente->observaciones, 'col' => 'col-12'])
        </div>
        @if ($cliente->exists)
            <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="activo" value="1" id="activo" @checked(old('activo', $cliente->activo))><label class="form-check-label" for="activo">Cliente activo</label></div>
        @endif
    </div>
    <div class="card-body border-top d-flex gap-2 justify-content-end">
        <a class="btn btn-light" href="{{ $cliente->exists ? route('clientes.show', $cliente) : route('clientes.index') }}">Cancelar</a>
        <button class="btn btn-primary"><i class="bi bi-check2"></i> Guardar</button>
    </div>
</form>
@endsection

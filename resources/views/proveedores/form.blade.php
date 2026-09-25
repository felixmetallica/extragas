@extends('layouts.app')

@section('contenido')
<form method="POST" action="{{ $proveedor->exists ? route('proveedores.update', $proveedor) : route('proveedores.store') }}" class="card" style="max-width:980px">
    @csrf
    @if ($proveedor->exists) @method('PUT') @endif
    <div class="card-body">
        <div class="section-title mt-0">Datos fiscales</div>
        <div class="row g-3">
            @include('partials.campo', ['name' => 'razon_social', 'label' => 'Razón social', 'value' => $proveedor->razon_social, 'req' => true, 'col' => 'col-md-6'])
            @include('partials.campo', ['name' => 'nombre_fantasia', 'label' => 'Nombre de fantasía', 'value' => $proveedor->nombre_fantasia, 'col' => 'col-md-6'])
            @include('partials.campo', ['name' => 'cuit', 'label' => 'CUIT', 'value' => $proveedor->cuit, 'req' => true, 'col' => 'col-md-4', 'placeholder' => '30-00000000-0'])
            @include('partials.campo', ['name' => 'codigo', 'label' => 'Código', 'value' => $proveedor->codigo, 'col' => 'col-md-4'])
        </div>
        <div class="section-title">Contacto</div>
        <div class="row g-3">
            @include('partials.campo', ['name' => 'telefono_principal', 'label' => 'Teléfono principal', 'value' => $proveedor->telefono_principal, 'col' => 'col-md-4'])
            @include('partials.campo', ['name' => 'telefono_secundario', 'label' => 'Teléfono secundario', 'value' => $proveedor->telefono_secundario, 'col' => 'col-md-4'])
            @include('partials.campo', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $proveedor->email, 'col' => 'col-md-4'])
            @include('partials.campo', ['name' => 'contacto_nombre', 'label' => 'Persona de contacto', 'value' => $proveedor->contacto_nombre, 'col' => 'col-md-4'])
            @include('partials.campo', ['name' => 'contacto_telefono', 'label' => 'Teléfono del contacto', 'value' => $proveedor->contacto_telefono, 'col' => 'col-md-4'])
            @include('partials.campo', ['name' => 'contacto_email', 'label' => 'Email del contacto', 'type' => 'email', 'value' => $proveedor->contacto_email, 'col' => 'col-md-4'])
        </div>
        <div class="section-title">Domicilio</div>
        <div class="row g-3">
            @include('partials.campo', ['name' => 'calle', 'label' => 'Calle', 'value' => $proveedor->calle, 'col' => 'col-md-5'])
            @include('partials.campo', ['name' => 'numero', 'label' => 'Número', 'value' => $proveedor->numero, 'col' => 'col-4 col-md-2'])
            @include('partials.campo', ['name' => 'piso', 'label' => 'Piso', 'value' => $proveedor->piso, 'col' => 'col-4 col-md-2'])
            @include('partials.campo', ['name' => 'depto', 'label' => 'Depto.', 'value' => $proveedor->depto, 'col' => 'col-4 col-md-3'])
            @include('partials.campo', ['name' => 'ciudad', 'label' => 'Ciudad', 'value' => $proveedor->ciudad, 'col' => 'col-md-5'])
            @include('partials.campo', ['name' => 'codigo_postal', 'label' => 'Código postal', 'value' => $proveedor->codigo_postal, 'col' => 'col-md-2'])
            @include('partials.campo', ['name' => 'provincia_id', 'label' => 'Provincia', 'type' => 'select', 'value' => $proveedor->provincia_id, 'opciones' => $provincias->pluck('nombre', 'id'), 'vacio' => '—', 'col' => 'col-md-5'])
            @include('partials.campo', ['name' => 'referencias', 'label' => 'Referencias', 'type' => 'textarea', 'value' => $proveedor->referencias, 'col' => 'col-12'])
            @include('partials.campo', ['name' => 'observaciones', 'label' => 'Observaciones (condición de pago, CBU / alias, días de entrega)', 'type' => 'textarea', 'value' => $proveedor->observaciones, 'col' => 'col-12'])
        </div>
        @if ($proveedor->exists)
            <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="activo" value="1" id="activo" @checked(old('activo', $proveedor->activo))><label class="form-check-label" for="activo">Proveedor activo</label></div>
        @endif
    </div>
    <div class="card-body border-top d-flex gap-2 justify-content-end">
        <a class="btn btn-light" href="{{ $proveedor->exists ? route('proveedores.show', $proveedor) : route('proveedores.index') }}">Cancelar</a>
        <button class="btn btn-primary"><i class="bi bi-check2"></i> Guardar</button>
    </div>
</form>
@endsection

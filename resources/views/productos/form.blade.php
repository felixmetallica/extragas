@extends('layouts.app')

@section('contenido')
<form method="POST" action="{{ $producto->exists ? route('productos.update', $producto) : route('productos.store') }}" class="card" style="max-width:760px">
    @csrf
    @if ($producto->exists) @method('PUT') @endif
    <div class="card-body row g-3">
        @include('partials.campo', ['name' => 'tipo_producto_id', 'label' => 'Tipo', 'type' => 'select', 'opciones' => $tipos->pluck('nombre', 'id'), 'value' => $producto->tipo_producto_id, 'req' => true, 'col' => 'col-md-4'])
        @include('partials.campo', ['name' => 'codigo', 'label' => 'Código', 'value' => $producto->codigo, 'req' => true, 'col' => 'col-md-4'])
        @include('partials.campo', ['name' => 'capacidad_kg', 'label' => 'Presentación (kg)', 'type' => 'number', 'value' => $producto->capacidad_kg, 'req' => true, 'attrs' => 'min="0.1" step="0.1"', 'col' => 'col-md-4'])
        @include('partials.campo', ['name' => 'nombre', 'label' => 'Nombre', 'value' => $producto->nombre, 'req' => true, 'col' => 'col-md-8'])
        @include('partials.campo', ['name' => 'unidad_venta', 'label' => 'Unidad de venta', 'type' => 'select', 'opciones' => ['GARRAFA' => 'Garrafa', 'BOLSA' => 'Bolsa', 'UNIDAD' => 'Unidad'], 'value' => $producto->unidad_venta, 'col' => 'col-md-4'])
        @include('partials.campo', ['name' => 'descripcion', 'label' => 'Descripción', 'value' => $producto->descripcion, 'col' => 'col-12'])
        @include('partials.campo', ['name' => 'precio_actual', 'label' => 'Precio de venta', 'type' => 'number', 'value' => $producto->precio_actual, 'req' => true, 'attrs' => 'min="0" step="1"', 'col' => 'col-md-4'])
        @include('partials.campo', ['name' => 'costo_actual', 'label' => 'Costo de compra', 'type' => 'number', 'value' => $producto->costo_actual, 'attrs' => 'min="0" step="1"', 'col' => 'col-md-4'])
        @include('partials.campo', ['name' => 'stock_minimo', 'label' => 'Stock mínimo', 'type' => 'number', 'value' => $producto->stock_minimo, 'attrs' => 'min="0" step="1"', 'col' => 'col-md-4'])
        @unless ($producto->exists)
            @include('partials.campo', ['name' => 'stock_actual', 'label' => 'Stock inicial (carbón / leña)', 'type' => 'number', 'value' => 0, 'attrs' => 'min="0" step="1"', 'col' => 'col-md-4'])
            <div class="col-12 form-text">Los productos de tipo "Gas envasado" se controlan por garrafa individual (módulo Garrafas).</div>
        @else
            <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="activo" value="1" id="activo" @checked(old('activo', $producto->activo))><label class="form-check-label" for="activo">Disponible para la venta</label></div></div>
        @endunless
    </div>
    <div class="card-body border-top d-flex gap-2 justify-content-end">
        <a class="btn btn-light" href="{{ route('productos.index') }}">Cancelar</a><button class="btn btn-primary"><i class="bi bi-check2"></i> Guardar</button>
    </div>
</form>
@endsection

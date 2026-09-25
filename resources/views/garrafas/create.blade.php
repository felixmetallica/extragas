@extends('layouts.app')

@section('contenido')
<form method="POST" action="{{ route('garrafas.store') }}" class="card" style="max-width:760px">
    @csrf
    <div class="card-body row g-3">
        <div class="col-12"><div class="alert alert-light border small mb-0"><i class="bi bi-info-circle me-1"></i>Usá esta pantalla para cargar el parque inicial o envases comprados fuera de una recepción. Las garrafas que trae un proveedor se registran desde <a href="{{ route('recepciones.create') }}">Recepciones</a>.</div></div>
        @include('partials.campo', ['name' => 'capacidad_kg', 'label' => 'Capacidad', 'type' => 'select', 'opciones' => collect(\App\Models\Garrafa::CAPACIDADES)->mapWithKeys(fn ($c) => [$c => "$c kg"]), 'req' => true, 'col' => 'col-md-4'])
        @include('partials.campo', ['name' => 'cantidad', 'label' => 'Cantidad', 'type' => 'number', 'value' => 1, 'req' => true, 'attrs' => 'min="1" max="200"', 'col' => 'col-md-4'])
        @include('partials.campo', ['name' => 'estado', 'label' => 'Estado inicial', 'type' => 'select', 'opciones' => ['LLENA' => 'Llena', 'VACIA' => 'Vacía apta'], 'col' => 'col-md-4'])
        @include('partials.campo', ['name' => 'proveedor_id', 'label' => 'Proveedor', 'type' => 'select', 'opciones' => $proveedores, 'vacio' => '—', 'col' => 'col-md-6'])
        @include('partials.campo', ['name' => 'observaciones', 'label' => 'Observaciones', 'col' => 'col-md-6'])
        @include('partials.campo', ['name' => 'codigos', 'label' => 'Códigos (opcional)', 'type' => 'textarea', 'rows' => 3, 'col' => 'col-12', 'placeholder' => 'Uno por línea o separados por coma. Si se deja vacío se generan automáticamente (G10-00001…)'])
    </div>
    <div class="card-body border-top d-flex gap-2 justify-content-end">
        <a class="btn btn-light" href="{{ route('garrafas.index') }}">Cancelar</a>
        <button class="btn btn-primary"><i class="bi bi-check2"></i> Dar de alta</button>
    </div>
</form>
@endsection

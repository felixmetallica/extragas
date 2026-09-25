@extends('layouts.app')

@section('contenido')
<form method="POST" action="{{ route('configuracion.update') }}">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-lg-7"><div class="card">
            <div class="card-header"><h2>Datos de la empresa</h2><small class="text-body-secondary">Aparecen en los PDF de pedidos, recibos e informes</small></div>
            <div class="card-body row g-3">
                @include('partials.campo', ['name' => 'nombre', 'label' => 'Nombre comercial', 'value' => $config->nombre, 'req' => true])
                @include('partials.campo', ['name' => 'cuit', 'label' => 'CUIT', 'value' => $config->cuit])
                @include('partials.campo', ['name' => 'razon_social', 'label' => 'Razón social / descripción', 'value' => $config->razon_social, 'col' => 'col-12'])
                @include('partials.campo', ['name' => 'direccion', 'label' => 'Dirección', 'value' => $config->direccion])
                @include('partials.campo', ['name' => 'localidad', 'label' => 'Localidad', 'value' => $config->localidad])
                @include('partials.campo', ['name' => 'telefono', 'label' => 'Teléfono', 'value' => $config->telefono, 'col' => 'col-md-4'])
                @include('partials.campo', ['name' => 'whatsapp', 'label' => 'WhatsApp', 'value' => $config->whatsapp, 'col' => 'col-md-4'])
                @include('partials.campo', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $config->email, 'col' => 'col-md-4'])
                @include('partials.campo', ['name' => 'horario', 'label' => 'Horario de atención', 'value' => $config->horario, 'col' => 'col-12'])
            </div></div></div>
        <div class="col-lg-5">
            <div class="card mb-3"><div class="card-header"><h2>Parámetros</h2></div><div class="card-body">
                @include('partials.campo', ['name' => 'dias_tolerancia_regularidad', 'label' => 'Tolerancia de regularidad (días)', 'type' => 'number', 'value' => $config->dias_tolerancia_regularidad, 'col' => '', 'attrs' => 'min="0" max="60"'])
                <div class="form-text">Margen antes de marcar a un cliente como "Atrasado" respecto de su frecuencia habitual.</div>
            </div></div>
            <div class="card"><div class="card-header"><h2>Formas de pago habilitadas</h2></div><div class="card-body">
                @foreach ($formasPago as $f)
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="formas_activas[]" value="{{ $f->id }}" id="fp{{ $f->id }}" @checked($f->activo)><label class="form-check-label" for="fp{{ $f->id }}">{{ $f->nombre }} @if ($f->requiere_referencia)<small class="text-body-secondary">(pide referencia)</small>@endif</label></div>
                @endforeach
            </div></div>
        </div>
    </div>
    <div class="d-flex justify-content-end mt-3"><button class="btn btn-primary"><i class="bi bi-check2"></i> Guardar configuración</button></div>
</form>
@endsection

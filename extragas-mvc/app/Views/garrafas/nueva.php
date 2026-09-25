<?php use App\Core\Vista; ?>
<form method="POST" action="<?= url('garrafas') ?>" class="card" style="max-width:760px">
    <?= csrf_campo() ?>
    <div class="card-body row g-3">
        <div class="col-12"><div class="alert alert-light border small mb-0"><i class="bi bi-info-circle me-1"></i>Usá esta pantalla para cargar el parque inicial o envases comprados fuera de una recepción. Las garrafas que trae un proveedor se registran desde <a href="<?= url('recepciones/nueva') ?>">Recepciones</a>.</div></div>
        <?php Vista::parcial('campo', ['name' => 'capacidad_kg', 'label' => 'Capacidad', 'type' => 'select', 'opciones' => [10 => '10 kg', 15 => '15 kg', 45 => '45 kg'], 'req' => true, 'col' => 'col-md-4']) ?>
        <?php Vista::parcial('campo', ['name' => 'cantidad', 'label' => 'Cantidad', 'type' => 'number', 'value' => 1, 'req' => true, 'attrs' => 'min="1" max="200"', 'col' => 'col-md-4']) ?>
        <?php Vista::parcial('campo', ['name' => 'estado', 'label' => 'Estado inicial', 'type' => 'select', 'opciones' => ['LLENA' => 'Llena', 'VACIA' => 'Vacía apta'], 'col' => 'col-md-4']) ?>
        <?php Vista::parcial('campo', ['name' => 'proveedor_id', 'label' => 'Proveedor', 'type' => 'select', 'opciones' => $proveedores, 'vacio' => '—']) ?>
        <?php Vista::parcial('campo', ['name' => 'observaciones', 'label' => 'Observaciones']) ?>
        <?php Vista::parcial('campo', ['name' => 'codigos', 'label' => 'Códigos (opcional)', 'type' => 'textarea', 'rows' => 3, 'col' => 'col-12', 'placeholder' => 'Uno por línea o separados por coma. Si se deja vacío se generan automáticamente (G10-00001…)']) ?>
    </div>
    <div class="card-body border-top d-flex gap-2 justify-content-end">
        <a class="btn btn-light" href="<?= url('garrafas') ?>">Cancelar</a>
        <button class="btn btn-primary"><i class="bi bi-check2"></i> Dar de alta</button>
    </div>
</form>

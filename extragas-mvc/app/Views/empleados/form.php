<?php use App\Core\Vista; $e = $empleado; $v = fn ($k) => $e[$k] ?? null; $campo = fn (array $d) => Vista::parcial('campo', $d); ?>
<form method="POST" action="<?= $e['id'] ? url('empleados/'.$e['id'].'/editar') : url('empleados') ?>" class="card" style="max-width:900px">
    <?= csrf_campo() ?>
    <div class="card-body row g-3">
        <?php $campo(['name' => 'nombre', 'label' => 'Nombre', 'value' => $v('nombre'), 'req' => true, 'col' => 'col-md-4']) ?>
        <?php $campo(['name' => 'apellido', 'label' => 'Apellido', 'value' => $v('apellido'), 'req' => true, 'col' => 'col-md-4']) ?>
        <?php $campo(['name' => 'fecha_ingreso', 'label' => 'Fecha de ingreso', 'type' => 'date', 'value' => $v('fecha_ingreso'), 'col' => 'col-md-4']) ?>
        <?php $campo(['name' => 'dni', 'label' => 'DNI', 'value' => $v('dni'), 'col' => 'col-md-4']) ?>
        <?php $campo(['name' => 'cuil', 'label' => 'CUIL', 'value' => $v('cuil'), 'col' => 'col-md-4']) ?>
        <?php $campo(['name' => 'telefono', 'label' => 'Teléfono', 'value' => $v('telefono'), 'col' => 'col-md-4']) ?>
        <?php $campo(['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $v('email')]) ?>
        <?php $campo(['name' => 'calle', 'label' => 'Calle', 'value' => $v('calle'), 'col' => 'col-md-4']) ?>
        <?php $campo(['name' => 'numero', 'label' => 'Número', 'value' => $v('numero'), 'col' => 'col-md-2']) ?>
        <?php $campo(['name' => 'piso', 'label' => 'Piso', 'value' => $v('piso'), 'col' => 'col-4 col-md-2']) ?>
        <?php $campo(['name' => 'depto', 'label' => 'Depto.', 'value' => $v('depto'), 'col' => 'col-4 col-md-2']) ?>
        <?php $campo(['name' => 'codigo_postal', 'label' => 'CP', 'value' => $v('codigo_postal'), 'col' => 'col-4 col-md-2']) ?>
        <?php $campo(['name' => 'ciudad', 'label' => 'Ciudad', 'value' => $v('ciudad')]) ?>
        <?php $campo(['name' => 'provincia_id', 'label' => 'Provincia', 'type' => 'select', 'value' => $v('provincia_id'), 'opciones' => $provincias, 'vacio' => '—']) ?>
        <?php $campo(['name' => 'observaciones', 'label' => 'Observaciones', 'type' => 'textarea', 'value' => $v('observaciones'), 'col' => 'col-12']) ?>
        <?php if ($e['id']) Vista::parcial('activo', ['valor' => $e['activo'], 'texto' => 'Empleado activo']) ?>
    </div>
    <div class="card-body border-top d-flex gap-2 justify-content-end"><a class="btn btn-light" href="<?= url('empleados') ?>">Cancelar</a><button class="btn btn-primary"><i class="bi bi-check2"></i> Guardar</button></div>
</form>

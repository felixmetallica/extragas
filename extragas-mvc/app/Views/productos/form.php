<?php use App\Core\Vista; $p = $producto; $v = fn ($k) => $p[$k] ?? null; $campo = fn (array $d) => Vista::parcial('campo', $d); ?>
<form method="POST" action="<?= $p['id'] ? url('productos/'.$p['id'].'/editar') : url('productos') ?>" class="card" style="max-width:760px">
    <?= csrf_campo() ?>
    <div class="card-body row g-3">
        <?php $campo(['name' => 'tipo_producto_id', 'label' => 'Tipo', 'type' => 'select', 'opciones' => $tipos, 'value' => $v('tipo_producto_id'), 'req' => true, 'col' => 'col-md-4']) ?>
        <?php $campo(['name' => 'codigo', 'label' => 'Código', 'value' => $v('codigo'), 'req' => true, 'col' => 'col-md-4']) ?>
        <?php $campo(['name' => 'capacidad_kg', 'label' => 'Presentación (kg)', 'type' => 'number', 'value' => $v('capacidad_kg') !== null ? (float) $v('capacidad_kg') : '', 'req' => true, 'attrs' => 'min="0.1" step="0.1"', 'col' => 'col-md-4']) ?>
        <?php $campo(['name' => 'nombre', 'label' => 'Nombre', 'value' => $v('nombre'), 'req' => true, 'col' => 'col-md-8']) ?>
        <?php $campo(['name' => 'unidad_venta', 'label' => 'Unidad de venta', 'type' => 'select', 'opciones' => ['GARRAFA' => 'Garrafa', 'BOLSA' => 'Bolsa', 'UNIDAD' => 'Unidad'], 'value' => $v('unidad_venta'), 'col' => 'col-md-4']) ?>
        <?php $campo(['name' => 'descripcion', 'label' => 'Descripción', 'value' => $v('descripcion'), 'col' => 'col-12']) ?>
        <?php $campo(['name' => 'precio_actual', 'label' => 'Precio de venta', 'type' => 'number', 'value' => $v('precio_actual') !== null ? (float) $v('precio_actual') : '', 'req' => true, 'attrs' => 'min="0" step="1"', 'col' => 'col-md-4']) ?>
        <?php $campo(['name' => 'costo_actual', 'label' => 'Costo de compra', 'type' => 'number', 'value' => $v('costo_actual') !== null ? (float) $v('costo_actual') : '', 'attrs' => 'min="0" step="1"', 'col' => 'col-md-4']) ?>
        <?php $campo(['name' => 'stock_minimo', 'label' => 'Stock mínimo', 'type' => 'number', 'value' => $v('stock_minimo') !== null ? (float) $v('stock_minimo') : '', 'attrs' => 'min="0" step="1"', 'col' => 'col-md-4']) ?>
        <?php if (! $p['id']): ?>
            <?php $campo(['name' => 'stock_actual', 'label' => 'Stock inicial (carbón / leña)', 'type' => 'number', 'value' => 0, 'attrs' => 'min="0" step="1"', 'col' => 'col-md-4']) ?>
            <div class="col-12 form-text">Los productos de tipo "Gas envasado" se controlan por garrafa individual (módulo Garrafas).</div>
        <?php else: Vista::parcial('activo', ['valor' => $p['activo'], 'texto' => 'Disponible para la venta']); endif ?>
    </div>
    <div class="card-body border-top d-flex gap-2 justify-content-end">
        <a class="btn btn-light" href="<?= url('productos') ?>">Cancelar</a><button class="btn btn-primary"><i class="bi bi-check2"></i> Guardar</button>
    </div>
</form>

<?php use App\Core\Vista; $c = $config; $campo = fn (array $d) => Vista::parcial('campo', $d); ?>
<form method="POST" action="<?= url('configuracion') ?>">
    <?= csrf_campo() ?>
    <div class="row g-3">
        <div class="col-lg-7"><div class="card">
            <div class="card-header"><h2>Datos de la empresa</h2><small class="text-body-secondary">Aparecen en los PDF de pedidos, recibos e informes</small></div>
            <div class="card-body row g-3">
                <?php $campo(['name' => 'nombre', 'label' => 'Nombre comercial', 'value' => $c['nombre'], 'req' => true]) ?>
                <?php $campo(['name' => 'cuit', 'label' => 'CUIT', 'value' => $c['cuit']]) ?>
                <?php $campo(['name' => 'razon_social', 'label' => 'Razón social / descripción', 'value' => $c['razon_social'], 'col' => 'col-12']) ?>
                <?php $campo(['name' => 'direccion', 'label' => 'Dirección', 'value' => $c['direccion']]) ?>
                <?php $campo(['name' => 'localidad', 'label' => 'Localidad', 'value' => $c['localidad']]) ?>
                <?php $campo(['name' => 'telefono', 'label' => 'Teléfono', 'value' => $c['telefono'], 'col' => 'col-md-4']) ?>
                <?php $campo(['name' => 'whatsapp', 'label' => 'WhatsApp', 'value' => $c['whatsapp'], 'col' => 'col-md-4']) ?>
                <?php $campo(['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $c['email'], 'col' => 'col-md-4']) ?>
                <?php $campo(['name' => 'horario', 'label' => 'Horario de atención', 'value' => $c['horario'], 'col' => 'col-12']) ?>
            </div></div></div>
        <div class="col-lg-5">
            <div class="card mb-3"><div class="card-header"><h2>Parámetros</h2></div><div class="card-body">
                <?php $campo(['name' => 'dias_tolerancia_regularidad', 'label' => 'Tolerancia de regularidad (días)', 'type' => 'number', 'value' => $c['dias_tolerancia_regularidad'], 'col' => '', 'attrs' => 'min="0" max="60"']) ?>
                <div class="form-text">Margen antes de marcar a un cliente como "Atrasado" respecto de su frecuencia habitual.</div>
            </div></div>
            <div class="card"><div class="card-header"><h2>Formas de pago habilitadas</h2></div><div class="card-body">
                <?php foreach ($formasPago as $f): ?>
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="formas_activas[]" value="<?= $f['id'] ?>" id="fp<?= $f['id'] ?>" <?= chk($f['activo']) ?>><label class="form-check-label" for="fp<?= $f['id'] ?>"><?= e($f['nombre']) ?><?= $f['requiere_referencia'] ? ' <small class="text-body-secondary">(pide referencia)</small>' : '' ?></label></div>
                <?php endforeach ?>
            </div></div>
        </div>
    </div>
    <div class="d-flex justify-content-end mt-3"><button class="btn btn-primary"><i class="bi bi-check2"></i> Guardar configuración</button></div>
</form>

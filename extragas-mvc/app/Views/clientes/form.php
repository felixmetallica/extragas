<?php use App\Core\Vista;
$c = $cliente; $v = fn ($k) => $c[$k] ?? null;
$campo = fn (array $d) => Vista::parcial('campo', $d); ?>
<form method="POST" action="<?= $c['id'] ? url('clientes/'.$c['id'].'/editar') : url('clientes') ?>" class="card" style="max-width:980px">
    <?= csrf_campo() ?>
    <input type="hidden" name="volver" value="<?= e($_GET['volver'] ?? '') ?>">
    <div class="card-body">
        <div class="section-title mt-0">Datos personales</div>
        <div class="row g-3">
            <?php $campo(['name' => 'nombre', 'label' => 'Nombre', 'value' => $v('nombre'), 'req' => true, 'col' => 'col-md-4']) ?>
            <?php $campo(['name' => 'apellido', 'label' => 'Apellido', 'value' => $v('apellido'), 'req' => true, 'col' => 'col-md-4', 'placeholder' => 'Para comercios: nombre de fantasía']) ?>
            <?php $campo(['name' => 'codigo', 'label' => 'Código', 'value' => $v('codigo'), 'col' => 'col-md-4', 'placeholder' => 'Opcional']) ?>
            <?php $campo(['name' => 'dni', 'label' => 'DNI', 'value' => $v('dni'), 'col' => 'col-md-4']) ?>
            <?php $campo(['name' => 'cuit_cuil', 'label' => 'CUIT / CUIL', 'value' => $v('cuit_cuil'), 'col' => 'col-md-4']) ?>
            <?php $campo(['name' => 'forma_pago_habitual_id', 'label' => 'Forma de pago habitual', 'type' => 'select', 'value' => $v('forma_pago_habitual_id'), 'opciones' => $formasPago, 'vacio' => '—', 'col' => 'col-md-4']) ?>
        </div>
        <div class="section-title">Contacto</div>
        <div class="row g-3">
            <?php $campo(['name' => 'telefono_principal', 'label' => 'Celular / teléfono principal', 'value' => $v('telefono_principal'), 'req' => true, 'col' => 'col-md-4', 'placeholder' => '381 555-1234']) ?>
            <?php $campo(['name' => 'telefono_secundario', 'label' => 'Teléfono alternativo', 'value' => $v('telefono_secundario'), 'col' => 'col-md-4']) ?>
            <?php $campo(['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $v('email'), 'col' => 'col-md-4']) ?>
        </div>
        <div class="section-title">Domicilio</div>
        <div class="row g-3">
            <?php $campo(['name' => 'calle', 'label' => 'Calle', 'value' => $v('calle'), 'col' => 'col-md-5']) ?>
            <?php $campo(['name' => 'numero', 'label' => 'Número', 'value' => $v('numero'), 'col' => 'col-4 col-md-2']) ?>
            <?php $campo(['name' => 'piso', 'label' => 'Piso', 'value' => $v('piso'), 'col' => 'col-4 col-md-2']) ?>
            <?php $campo(['name' => 'depto', 'label' => 'Depto.', 'value' => $v('depto'), 'col' => 'col-4 col-md-3']) ?>
            <?php $campo(['name' => 'ciudad', 'label' => 'Ciudad / barrio', 'value' => $v('ciudad'), 'col' => 'col-md-5']) ?>
            <?php $campo(['name' => 'codigo_postal', 'label' => 'Código postal', 'value' => $v('codigo_postal'), 'col' => 'col-md-2']) ?>
            <?php $campo(['name' => 'provincia_id', 'label' => 'Provincia', 'type' => 'select', 'value' => $v('provincia_id'), 'opciones' => $provincias, 'vacio' => '—', 'col' => 'col-md-5']) ?>
            <?php $campo(['name' => 'referencias', 'label' => 'Referencias para la entrega', 'type' => 'textarea', 'value' => $v('referencias'), 'col' => 'col-12', 'placeholder' => 'Ej.: portón verde, casa esquina']) ?>
            <?php $campo(['name' => 'observaciones', 'label' => 'Observaciones', 'type' => 'textarea', 'value' => $v('observaciones'), 'col' => 'col-12']) ?>
            <?php if ($c['id']) Vista::parcial('activo', ['valor' => $c['activo'], 'texto' => 'Cliente activo']) ?>
        </div>
    </div>
    <div class="card-body border-top d-flex gap-2 justify-content-end">
        <a class="btn btn-light" href="<?= $c['id'] ? url('clientes/'.$c['id']) : url('clientes') ?>">Cancelar</a>
        <button class="btn btn-primary"><i class="bi bi-check2"></i> Guardar</button>
    </div>
</form>

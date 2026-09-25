<?php use App\Core\Vista; $p = $proveedor; $v = fn ($k) => $p[$k] ?? null; $campo = fn (array $d) => Vista::parcial('campo', $d); ?>
<form method="POST" action="<?= $p['id'] ? url('proveedores/'.$p['id'].'/editar') : url('proveedores') ?>" class="card" style="max-width:980px">
    <?= csrf_campo() ?>
    <div class="card-body">
        <div class="section-title mt-0">Datos fiscales</div>
        <div class="row g-3">
            <?php $campo(['name' => 'razon_social', 'label' => 'Razón social', 'value' => $v('razon_social'), 'req' => true]) ?>
            <?php $campo(['name' => 'nombre_fantasia', 'label' => 'Nombre de fantasía', 'value' => $v('nombre_fantasia')]) ?>
            <?php $campo(['name' => 'cuit', 'label' => 'CUIT', 'value' => $v('cuit'), 'req' => true, 'col' => 'col-md-4', 'placeholder' => '30-00000000-0']) ?>
            <?php $campo(['name' => 'codigo', 'label' => 'Código', 'value' => $v('codigo'), 'col' => 'col-md-4']) ?>
        </div>
        <div class="section-title">Contacto</div>
        <div class="row g-3">
            <?php $campo(['name' => 'telefono_principal', 'label' => 'Teléfono principal', 'value' => $v('telefono_principal'), 'col' => 'col-md-4']) ?>
            <?php $campo(['name' => 'telefono_secundario', 'label' => 'Teléfono secundario', 'value' => $v('telefono_secundario'), 'col' => 'col-md-4']) ?>
            <?php $campo(['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $v('email'), 'col' => 'col-md-4']) ?>
            <?php $campo(['name' => 'contacto_nombre', 'label' => 'Persona de contacto', 'value' => $v('contacto_nombre'), 'col' => 'col-md-4']) ?>
            <?php $campo(['name' => 'contacto_telefono', 'label' => 'Teléfono del contacto', 'value' => $v('contacto_telefono'), 'col' => 'col-md-4']) ?>
            <?php $campo(['name' => 'contacto_email', 'label' => 'Email del contacto', 'type' => 'email', 'value' => $v('contacto_email'), 'col' => 'col-md-4']) ?>
        </div>
        <div class="section-title">Domicilio</div>
        <div class="row g-3">
            <?php $campo(['name' => 'calle', 'label' => 'Calle', 'value' => $v('calle'), 'col' => 'col-md-5']) ?>
            <?php $campo(['name' => 'numero', 'label' => 'Número', 'value' => $v('numero'), 'col' => 'col-4 col-md-2']) ?>
            <?php $campo(['name' => 'piso', 'label' => 'Piso', 'value' => $v('piso'), 'col' => 'col-4 col-md-2']) ?>
            <?php $campo(['name' => 'depto', 'label' => 'Depto.', 'value' => $v('depto'), 'col' => 'col-4 col-md-3']) ?>
            <?php $campo(['name' => 'ciudad', 'label' => 'Ciudad', 'value' => $v('ciudad'), 'col' => 'col-md-5']) ?>
            <?php $campo(['name' => 'codigo_postal', 'label' => 'Código postal', 'value' => $v('codigo_postal'), 'col' => 'col-md-2']) ?>
            <?php $campo(['name' => 'provincia_id', 'label' => 'Provincia', 'type' => 'select', 'value' => $v('provincia_id'), 'opciones' => $provincias, 'vacio' => '—', 'col' => 'col-md-5']) ?>
            <?php $campo(['name' => 'referencias', 'label' => 'Referencias', 'type' => 'textarea', 'value' => $v('referencias'), 'col' => 'col-12']) ?>
            <?php $campo(['name' => 'observaciones', 'label' => 'Observaciones (condición de pago, CBU / alias, días de entrega)', 'type' => 'textarea', 'value' => $v('observaciones'), 'col' => 'col-12']) ?>
            <?php if ($p['id']) Vista::parcial('activo', ['valor' => $p['activo'], 'texto' => 'Proveedor activo']) ?>
        </div>
    </div>
    <div class="card-body border-top d-flex gap-2 justify-content-end">
        <a class="btn btn-light" href="<?= $p['id'] ? url('proveedores/'.$p['id']) : url('proveedores') ?>">Cancelar</a>
        <button class="btn btn-primary"><i class="bi bi-check2"></i> Guardar</button>
    </div>
</form>

<?php use App\Core\Vista; use App\Models\Proveedor; $p = $proveedor; $tabPag = isset($_GET['pagina_pag']); ?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <?= $p['activo'] ? '' : badge('Inactivo') ?> <span class="text-muted-sm">CUIT <?= e($p['cuit']) ?></span>
    <div class="ms-auto d-flex flex-wrap gap-2">
        <a class="btn btn-sm btn-light" href="<?= url('proveedores/'.$p['id'].'/editar') ?>"><i class="bi bi-pencil"></i> Editar</a>
        <?php if ($saldo > 0): ?><a class="btn btn-sm btn-outline-primary" href="<?= url('pagos-proveedores/nuevo', ['proveedor' => $p['id']]) ?>"><i class="bi bi-wallet2"></i> Registrar pago</a><?php endif ?>
        <a class="btn btn-sm btn-primary" href="<?= url('recepciones/nueva', ['proveedor' => $p['id']]) ?>"><i class="bi bi-box-arrow-in-down"></i> Nueva recepción</a>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'box-arrow-in-down', 'color' => 'i-blue', 'label' => 'Recepciones', 'valor' => $pagRec->total]) ?></div>
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'bag', 'label' => 'Comprado 90 días', 'valor' => pesos($comprado90)]) ?></div>
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'wallet2', 'color' => 'i-green', 'label' => 'Pagado total', 'valor' => pesos($pagadoTotal)]) ?></div>
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'exclamation-diamond', 'color' => $saldo > 0 ? 'i-red' : 'i-green', 'label' => 'Saldo a pagar', 'valor' => pesos($saldo)]) ?></div>
</div>
<div class="row g-3">
    <div class="col-lg-4"><div class="card"><div class="card-header"><h2>Datos del proveedor</h2></div><div class="card-body"><dl class="info-list">
        <?php if ($p['nombre_fantasia']): ?><dt>Fantasía</dt><dd><?= e($p['nombre_fantasia']) ?></dd><?php endif ?>
        <dt>Teléfono</dt><dd><?= e(implode(' / ', array_filter([$p['telefono_principal'], $p['telefono_secundario']])) ?: '—') ?></dd>
        <dt>Email</dt><dd><?= e($p['email'] ?: '—') ?></dd>
        <dt>Contacto</dt><dd><?= e($p['contacto_nombre'] ?: '—') ?><?php if ($p['contacto_telefono']): ?><br><?= e($p['contacto_telefono']) ?> <a href="<?= e(wa_link($p['contacto_telefono'])) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp text-success"></i></a><?php endif ?></dd>
        <dt>Domicilio</dt><dd><?= e(Proveedor::domicilio($p) ?: '—') ?></dd>
        <dt>Obs.</dt><dd><?= e($p['observaciones'] ?: '—') ?></dd>
    </dl></div></div></div>
    <div class="col-lg-8"><div class="card">
        <div class="card-header p-0 px-2 pt-2 border-0"><ul class="nav nav-tabs w-100">
            <li class="nav-item"><button class="nav-link <?= $tabPag ? '' : 'active' ?>" data-bs-toggle="tab" data-bs-target="#tRec" type="button">Recepciones</button></li>
            <li class="nav-item"><button class="nav-link <?= $tabPag ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tPag" type="button">Pagos</button></li>
        </ul></div>
        <div class="tab-content">
            <div class="tab-pane fade <?= $tabPag ? '' : 'show active' ?>" id="tRec">
                <div class="table-responsive"><table class="table table-hover align-middle">
                    <thead><tr><th>N°</th><th>Fecha</th><th>Factura</th><th>Productos</th><th class="num">Total</th><th>Pago</th></tr></thead>
                    <tbody><?php foreach ($recepciones as $r): ?>
                        <tr class="row-link" data-href="<?= url('recepciones/'.$r['id']) ?>"><td><a href="<?= url('recepciones/'.$r['id']) ?>" class="fw-semibold"><?= e($r['numero']) ?></a></td><td><?= fecha($r['fecha']) ?></td>
                            <td><?= e($r['numero_factura_proveedor'] ?: '—') ?></td><td class="small"><?= e($r['productos']) ?></td><td class="num"><?= pesos($r['total']) ?></td><td><?= badge_pago(estado_pago($r)) ?></td></tr>
                    <?php endforeach ?>
                    <?php if (! $recepciones): ?><tr><td colspan="6" class="empty">Sin recepciones</td></tr><?php endif ?></tbody>
                </table></div>
                <?= $pagRec->enlaces() ?>
            </div>
            <div class="tab-pane fade <?= $tabPag ? 'show active' : '' ?>" id="tPag">
                <div class="table-responsive"><table class="table align-middle">
                    <thead><tr><th>N°</th><th>Fecha</th><th>Recepción</th><th>Forma</th><th>Referencia</th><th class="num">Monto</th></tr></thead>
                    <tbody><?php foreach ($pagos as $pg): ?>
                        <tr><td class="fw-semibold"><?= e($pg['numero']) ?></td><td><?= fecha($pg['fecha']) ?></td><td><?= e($pg['recepcion_numero'] ?? 'A cuenta') ?></td><td><?= e($pg['forma_nombre']) ?></td><td><?= e($pg['referencia'] ?: '—') ?></td><td class="num"><?= pesos($pg['monto']) ?></td></tr>
                    <?php endforeach ?>
                    <?php if (! $pagos): ?><tr><td colspan="6" class="empty">Sin pagos</td></tr><?php endif ?></tbody>
                </table></div>
                <?= $pagPag->enlaces() ?>
            </div>
        </div>
    </div></div>
</div>

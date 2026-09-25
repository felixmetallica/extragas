<?php $r = $recepcion; ?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <?= badge_pago(estado_pago($r)) ?><span class="text-muted-sm">Recibió <?= e(nombre($r, 'empleado_')) ?> · <?= fecha($r['fecha'], true) ?></span>
    <div class="ms-auto"><?php if ($r['saldo'] > 0): ?><a class="btn btn-sm btn-primary" href="<?= url('pagos-proveedores/nuevo', ['proveedor' => $r['proveedor_id'], 'recepcion' => $r['id']]) ?>"><i class="bi bi-wallet2"></i> Registrar pago</a><?php endif ?></div>
</div>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3"><div class="card-header"><h2>Productos recibidos</h2></div>
            <div class="table-responsive"><table class="table align-middle">
                <thead><tr><th>Producto</th><th class="num">Cantidad</th><th class="num">Costo unit.</th><th class="num">Subtotal</th></tr></thead>
                <tbody><?php foreach ($items as $it): ?><tr><td><?= e($it['producto_nombre']) ?></td><td class="num"><?= num($it['cantidad']) ?></td><td class="num"><?= pesos($it['precio_unitario']) ?></td><td class="num fw-semibold"><?= pesos($it['subtotal']) ?></td></tr><?php endforeach ?></tbody>
                <tfoot>
                    <?php if ($r['descuento'] > 0): ?><tr><td colspan="3" class="text-end">Descuento</td><td class="num text-danger">− <?= pesos($r['descuento']) ?></td></tr><?php endif ?>
                    <tr><th colspan="3" class="text-end">Total</th><th class="num fs-5"><?= pesos($r['total']) ?></th></tr>
                    <tr><td colspan="3" class="text-end">Pagado</td><td class="num text-success"><?= pesos($r['monto_pagado']) ?></td></tr>
                </tfoot>
            </table></div></div>
        <div class="row g-3">
            <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Garrafas llenas ingresadas (<?= count($garrafas) ?>)</h2></div>
                <div class="card-body small" style="max-height:260px;overflow:auto"><?php foreach ($garrafas as $g): ?><a href="<?= url('garrafas/'.$g['id']) ?>" class="me-2 d-inline-block"><?= e($g['codigo']) ?></a><?php endforeach ?><?= $garrafas ? '' : '<span class="text-body-secondary">Ninguna</span>' ?></div></div></div>
            <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Vacías entregadas (<?= count($entregadas) ?>)</h2></div>
                <div class="card-body small" style="max-height:260px;overflow:auto"><?php foreach ($entregadas as $g): ?><a href="<?= url('garrafas/'.$g['id']) ?>" class="me-2 d-inline-block"><?= e($g['codigo']) ?></a><?php endforeach ?><?= $entregadas ? '' : '<span class="text-body-secondary">Ninguna</span>' ?></div></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header"><h2>Proveedor</h2><div class="ms-auto"><a class="btn btn-sm btn-light" href="<?= url('proveedores/'.$r['proveedor_id']) ?>">Ver ficha</a></div></div>
            <div class="card-body"><dl class="info-list"><dt>Razón social</dt><dd class="fw-semibold"><?= e($r['proveedor_nombre']) ?></dd><dt>CUIT</dt><dd><?= e($r['proveedor_cuit']) ?></dd>
                <dt>Factura</dt><dd><?= e($r['numero_factura_proveedor'] ?: '—') ?></dd><dt>Obs.</dt><dd><?= e($r['observaciones'] ?: '—') ?></dd></dl></div></div>
        <div class="card"><div class="card-header"><h2>Pagos</h2></div>
            <ul class="list-group list-group-flush"><?php foreach ($pagos as $pg): ?>
                <li class="list-group-item d-flex justify-content-between"><span><?= e($pg['numero']) ?><div class="text-muted-sm"><?= fecha($pg['fecha']) ?> · <?= e($pg['forma_nombre']) ?></div></span><b><?= pesos($pg['monto']) ?></b></li>
            <?php endforeach ?>
            <?php if (! $pagos): ?><li class="list-group-item text-body-secondary small">Sin pagos</li><?php endif ?></ul></div>
    </div>
</div>

<?php use App\Core\Vista; ?>
<form class="toolbar" method="GET" data-auto-submit>
    <?php Vista::parcial('rango', compact('desde', 'hasta')) ?>
    <select class="form-select form-select-sm" name="proveedor"><option value="">Todos los proveedores</option><?php foreach ($proveedores as $id => $n): ?><option value="<?= $id ?>" <?= sel($_GET['proveedor'] ?? '', $id) ?>><?= e($n) ?></option><?php endforeach ?></select>
    <select class="form-select form-select-sm" name="pago"><option value="">Estado de pago</option><?php foreach (['Pagado', 'Parcial', 'Pendiente'] as $e): ?><option <?= sel($_GET['pago'] ?? '', $e) ?>><?= $e ?></option><?php endforeach ?></select>
    <div class="ms-auto d-flex gap-2">
        <a class="btn btn-sm btn-light" href="<?= e(url_actual(['pdf' => 1])) ?>"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
        <a class="btn btn-sm btn-primary" href="<?= url('recepciones/nueva') ?>"><i class="bi bi-plus-lg"></i> Nueva recepción</a>
    </div>
</form>
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'box-arrow-in-down', 'color' => 'i-blue', 'label' => 'Recepciones', 'valor' => $resumen['n']]) ?></div>
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'fuel-pump', 'label' => 'Garrafas recibidas', 'valor' => $resumen['garrafas']]) ?></div>
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'bag', 'color' => 'i-violet', 'label' => 'Total comprado', 'valor' => pesos($resumen['total'])]) ?></div>
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'exclamation-diamond', 'color' => 'i-red', 'label' => 'Deuda con proveedores', 'valor' => pesos($deuda)]) ?></div>
</div>
<div class="card">
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>N°</th><th>Fecha</th><th>Proveedor</th><th>Factura</th><th>Productos recibidos</th><th class="num">Vacías entregadas</th><th class="num">Total</th><th>Pago</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($recepciones as $r): ?>
            <tr class="row-link" data-href="<?= url('recepciones/'.$r['id']) ?>">
                <td><a href="<?= url('recepciones/'.$r['id']) ?>" class="fw-semibold"><?= e($r['numero']) ?></a></td><td><?= fecha($r['fecha']) ?></td>
                <td><?= e($r['proveedor_nombre']) ?></td><td class="small"><?= e($r['numero_factura_proveedor'] ?: '—') ?></td>
                <td class="small"><?= e($r['productos']) ?></td><td class="num"><?= $r['vacias_entregadas'] ?: '—' ?></td>
                <td class="num"><?= pesos($r['total']) ?></td><td><?= badge_pago(estado_pago($r)) ?></td>
                <td class="actions"><?php if ($r['saldo'] > 0): ?><a class="btn btn-sm btn-outline-primary" href="<?= url('pagos-proveedores/nuevo', ['proveedor' => $r['proveedor_id'], 'recepcion' => $r['id']]) ?>"><i class="bi bi-wallet2"></i> Pagar</a><?php endif ?></td>
            </tr>
        <?php endforeach ?>
        <?php if (! $recepciones): ?><tr><td colspan="9" class="empty"><i class="bi bi-inbox"></i>Sin recepciones en el período</td></tr><?php endif ?>
        </tbody>
    </table></div>
    <?= $pag->enlaces() ?>
</div>

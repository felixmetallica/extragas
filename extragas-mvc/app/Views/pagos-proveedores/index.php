<?php use App\Core\Vista; ?>
<div class="row g-3 mb-3">
    <?php foreach ($proveedores as $id => $nombre): $s = (float) ($saldos[$id]['saldo_total'] ?? 0); ?>
        <div class="col-sm-6 col-xl-3"><div class="card kpi">
            <div class="kpi-icon <?= $s > 0 ? 'i-red' : 'i-green' ?>"><i class="bi bi-truck"></i></div>
            <div class="flex-fill" style="min-width:0"><div class="kpi-label text-truncate"><?= e($nombre) ?></div><div class="kpi-value"><?= pesos($s) ?></div>
                <div class="kpi-sub"><?= $s > 0 ? '<a href="'.url('pagos-proveedores/nuevo', ['proveedor' => $id]).'">Registrar pago</a>' : 'Sin deuda' ?></div></div>
        </div></div>
    <?php endforeach ?>
</div>
<form class="toolbar" method="GET" data-auto-submit>
    <?php Vista::parcial('rango', compact('desde', 'hasta')) ?>
    <select class="form-select form-select-sm" name="proveedor"><option value="">Todos los proveedores</option><?php foreach ($proveedores as $id => $n): ?><option value="<?= $id ?>" <?= sel($_GET['proveedor'] ?? '', $id) ?>><?= e($n) ?></option><?php endforeach ?></select>
    <div class="ms-auto d-flex gap-2">
        <a class="btn btn-sm btn-light" href="<?= e(url_actual(['pdf' => 1])) ?>"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
        <a class="btn btn-sm btn-primary" href="<?= url('pagos-proveedores/nuevo') ?>"><i class="bi bi-plus-lg"></i> Registrar pago</a>
    </div>
</form>
<div class="card">
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>N°</th><th>Fecha</th><th>Proveedor</th><th>Recepción</th><th>Forma</th><th>Referencia</th><th class="num">Importe</th></tr></thead>
        <tbody>
        <?php foreach ($pagos as $p): ?>
            <tr><td class="fw-semibold text-nowrap"><?= e($p['numero']) ?></td><td><?= fecha($p['fecha']) ?></td><td><a href="<?= url('proveedores/'.$p['proveedor_id']) ?>"><?= e($p['proveedor_nombre']) ?></a></td>
                <td><?= $p['recepcion_id'] ? '<a href="'.url('recepciones/'.$p['recepcion_id']).'">'.e($p['recepcion_numero']).'</a>' : 'A cuenta' ?></td>
                <td><?= e($p['forma_nombre']) ?></td><td><?= e($p['referencia'] ?: '—') ?></td><td class="num fw-semibold"><?= pesos($p['monto']) ?></td></tr>
        <?php endforeach ?>
        <?php if (! $pagos): ?><tr><td colspan="7" class="empty"><i class="bi bi-inbox"></i>Sin pagos en el período</td></tr><?php endif ?>
        </tbody>
        <?php if ($pagos): ?><tfoot><tr><th colspan="6" class="text-end">Total del período</th><th class="num"><?= pesos($totalPeriodo) ?></th></tr></tfoot><?php endif ?>
    </table></div>
    <?= $pag->enlaces() ?>
</div>

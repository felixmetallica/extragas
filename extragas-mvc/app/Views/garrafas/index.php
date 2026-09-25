<?php use App\Core\Vista; use App\Models\Garrafa;
$tot = fn ($k) => array_sum(array_column($stock, $k));
$tabMov = isset($_GET['pagina_mov']) || ! empty($_GET['tipo']);
$colores = ['ENTREGA_CLIENTE' => 'b-info', 'DEVOLUCION_CLIENTE' => 'b-reparto', 'ALTA' => 'b-pagado', 'ENTREGA_PROVEEDOR' => 'b-gray']; ?>
<div class="toolbar">
    <div class="text-body-secondary small"><i class="bi bi-info-circle me-1"></i>Parque: <b><?= array_sum(array_map('array_sum', $stock)) ?></b> envases ·
        en depósito <b><?= $tot('LLENA') + $tot('VACIA') + $tot('NO_APTA') ?></b> · en clientes <b><?= $tot('EN_CLIENTE') ?></b></div>
    <div class="ms-auto d-flex gap-2 flex-wrap">
        <a class="btn btn-sm btn-light" href="<?= url('garrafas/informe') ?>"><i class="bi bi-file-earmark-pdf"></i> Informe de stock</a>
        <a class="btn btn-sm btn-light" href="<?= url('recepciones/nueva') ?>"><i class="bi bi-box-arrow-in-down"></i> Recepción de proveedor</a>
        <a class="btn btn-sm btn-primary" href="<?= url('garrafas/nueva') ?>"><i class="bi bi-plus-lg"></i> Alta de garrafas</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <?php foreach ($stock as $cap => $e): $p = $productos[$cap] ?? null; $bajo = $p && $e['LLENA'] <= (float) $p['stock_minimo']; ?>
        <div class="col-lg-4"><div class="card cyl-card h-100"><div class="card-body">
            <div class="cyl-head"><div class="cyl-icon"><i class="bi bi-fuel-pump-fill"></i></div>
                <div class="flex-fill"><div class="fw-bold fs-5">Garrafa <?= $cap ?> kg</div><div class="text-muted-sm">Mínimo de llenas: <?= num($p['stock_minimo'] ?? 0) ?> · <?= pesos($p['precio_actual'] ?? 0) ?></div></div>
                <?= $bajo ? '<span class="badge-soft b-impago"><i class="bi bi-exclamation-triangle"></i> Reponer</span>' : '<span class="badge-soft b-pagado">OK</span>' ?></div>
            <div class="cyl-stats">
                <?php foreach (['LLENA' => ['full', 'Llenas'], 'VACIA' => ['empty-ok', 'Vacías aptas'], 'NO_APTA' => ['bad', 'No aptas'], 'EN_CLIENTE' => ['client', 'En clientes']] as $est => [$cls, $txt]): ?>
                    <a class="cyl-stat <?= $cls ?> text-decoration-none" href="<?= url('garrafas', ['capacidad' => $cap, 'estado' => $est]) ?>"><b><?= $e[$est] ?></b><span><?= $txt ?></span></a>
                <?php endforeach ?>
            </div>
            <div class="d-flex justify-content-between mt-3 small text-body-secondary"><span>En depósito: <b class="text-body"><?= $e['LLENA'] + $e['VACIA'] + $e['NO_APTA'] ?></b></span><span>Parque: <b class="text-body"><?= array_sum($e) ?></b></span></div>
        </div></div></div>
    <?php endforeach ?>
</div>

<div class="card mb-3"><div class="card-header"><h2>Movimientos de los últimos 30 días</h2></div>
    <div class="card-body"><div class="chart-box sm"><canvas data-chart='<?= json($grafico) ?>'></canvas></div></div></div>

<div class="card">
    <div class="card-header p-0 px-2 pt-2 border-0"><ul class="nav nav-tabs w-100">
        <li class="nav-item"><button class="nav-link <?= $tabMov ? '' : 'active' ?>" data-bs-toggle="tab" data-bs-target="#tLista" type="button">Garrafas (<?= $pag->total ?>)</button></li>
        <li class="nav-item"><button class="nav-link <?= $tabMov ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tMov" type="button">Movimientos</button></li>
    </ul></div>
    <div class="tab-content">
        <div class="tab-pane fade <?= $tabMov ? '' : 'show active' ?>" id="tLista">
            <form class="toolbar p-3 pb-0" method="GET" data-auto-submit>
                <div class="search"><i class="bi bi-search"></i><input class="form-control form-control-sm" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Código o cliente"></div>
                <select class="form-select form-select-sm" name="capacidad"><option value="">Todas las capacidades</option><?php foreach (Garrafa::CAPACIDADES as $c): ?><option value="<?= $c ?>" <?= sel($_GET['capacidad'] ?? '', $c) ?>><?= $c ?> kg</option><?php endforeach ?></select>
                <select class="form-select form-select-sm" name="estado"><option value="">Todos los estados</option><?php foreach ($estados as $e): ?><option value="<?= $e['codigo'] ?>" <?= sel($_GET['estado'] ?? '', $e['codigo']) ?>><?= e($e['nombre']) ?></option><?php endforeach ?></select>
            </form>
            <div class="table-responsive"><table class="table table-hover align-middle">
                <thead><tr><th>Código</th><th>Capacidad</th><th>Estado</th><th>Cliente</th><th>Último movimiento</th><th>Alta</th></tr></thead>
                <tbody>
                <?php foreach ($garrafas as $g): ?>
                    <tr class="row-link" data-href="<?= url('garrafas/'.$g['id']) ?>"><td><a href="<?= url('garrafas/'.$g['id']) ?>" class="fw-semibold"><?= e($g['codigo']) ?></a></td><td><?= $g['capacidad_kg'] ?> kg</td>
                        <td><?= badge_color($g['estado_nombre'], $g['estado_color']) ?></td><td><?= $g['cliente_id'] ? '<a href="'.url('clientes/'.$g['cliente_id']).'">'.e(nombre($g, 'cliente_')).'</a>' : '—' ?></td>
                        <td><?= fecha($g['fecha_ultimo_movimiento'], true) ?></td><td><?= fecha($g['fecha_compra']) ?></td></tr>
                <?php endforeach ?>
                <?php if (! $garrafas): ?><tr><td colspan="6" class="empty"><i class="bi bi-inbox"></i>No hay garrafas para los filtros seleccionados</td></tr><?php endif ?>
                </tbody>
            </table></div>
            <?= $pag->enlaces() ?>
        </div>
        <div class="tab-pane fade <?= $tabMov ? 'show active' : '' ?>" id="tMov">
            <form class="toolbar p-3 pb-0" method="GET" data-auto-submit>
                <select class="form-select form-select-sm" name="tipo"><option value="">Todos los movimientos</option><?php foreach ($tipos as $id => $n): ?><option value="<?= $id ?>" <?= sel($_GET['tipo'] ?? '', $id) ?>><?= e($n) ?></option><?php endforeach ?></select>
            </form>
            <?php Vista::parcial('movimientos', ['movimientos' => $movimientos, 'conGarrafa' => true, 'colores' => $colores]) ?>
            <?= $pagMov->enlaces() ?>
        </div>
    </div>
</div>

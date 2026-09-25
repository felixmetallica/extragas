<?php use App\Core\Vista; use App\Models\Pedido;
$wa = (int) ($resumen['por_medio'][$medios['WHATSAPP']['id']] ?? 0); ?>
<form class="toolbar" method="GET" data-auto-submit>
    <div class="search"><i class="bi bi-search"></i><input class="form-control form-control-sm" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Buscar por N°, cliente, domicilio o teléfono"></div>
    <?php Vista::parcial('rango', compact('desde', 'hasta')) ?>
    <select class="form-select form-select-sm" name="estado"><option value="">Todos los estados</option><?php foreach ($estados as $e): ?><option value="<?= $e['id'] ?>" <?= sel($_GET['estado'] ?? '', $e['id']) ?>><?= e($e['nombre']) ?></option><?php endforeach ?></select>
    <select class="form-select form-select-sm" name="medio"><option value="">Todos los medios</option><?php foreach ($medios as $m): ?><option value="<?= $m['id'] ?>" <?= sel($_GET['medio'] ?? '', $m['id']) ?>><?= e($m['nombre']) ?></option><?php endforeach ?></select>
    <select class="form-select form-select-sm" name="pago"><option value="">Estado de pago</option><?php foreach (['Pagado', 'Parcial', 'Pendiente'] as $e): ?><option <?= sel($_GET['pago'] ?? '', $e) ?>><?= $e ?></option><?php endforeach ?></select>
    <div class="ms-auto d-flex gap-2">
        <a class="btn btn-sm btn-light" href="<?= e(url_actual(['pdf' => 1])) ?>"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
        <a href="<?= url('pedidos/nuevo') ?>" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Nuevo pedido</a>
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'receipt', 'label' => 'Pedidos', 'valor' => num($resumen['n']), 'sub' => 'sin contar cancelados']) ?></div>
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'currency-dollar', 'color' => 'i-green', 'label' => 'Total vendido', 'valor' => pesos($resumen['total']), 'sub' => 'Ticket promedio '.pesos($resumen['n'] ? round($resumen['total'] / $resumen['n']) : 0)]) ?></div>
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'hourglass-split', 'color' => 'i-red', 'label' => 'Saldo pendiente', 'valor' => pesos($resumen['saldo'])]) ?></div>
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'whatsapp', 'color' => 'i-green', 'label' => 'Por WhatsApp', 'valor' => $resumen['n'] ? round($wa / $resumen['n'] * 100).'%' : '—',
        'sub' => ($resumen['por_medio'][$medios['TELEFONO']['id']] ?? 0).' por teléfono · '.($resumen['por_medio'][$medios['PRESENCIAL']['id']] ?? 0).' en el local']) ?></div>
</div>

<div class="card">
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>N°</th><th>Fecha</th><th>Cliente</th><th>Medio</th><th>Entrega</th><th>Productos</th><th class="num">Total</th><th>Pago</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($pedidos as $p): ?>
            <tr class="row-link" data-href="<?= url('pedidos/'.$p['id']) ?>">
                <td><a href="<?= url('pedidos/'.$p['id']) ?>" class="fw-semibold"><?= e($p['numero']) ?></a></td>
                <td class="text-nowrap"><?= fecha($p['fecha']) ?><div class="text-muted-sm"><?= date('H:i', strtotime($p['fecha'])) ?></div></td>
                <td><?= e(nombre($p, 'cliente_')) ?><div class="text-muted-sm"><?= e($p['cliente_telefono']) ?></div></td>
                <td><?= medio($p['medio_codigo'], $p['medio_nombre']) ?></td>
                <td class="small"><?= e($p['canal_nombre']) ?></td>
                <td class="small"><?= e($p['productos']) ?></td>
                <td class="num"><?= pesos($p['total']) ?></td>
                <td><?= badge_pago(estado_pago($p)) ?></td>
                <td><?= badge_estado_pedido($p['estado_codigo'], $p['estado_nombre']) ?></td>
                <td class="actions">
                    <a class="btn btn-sm btn-light" href="<?= url('pedidos/'.$p['id'].'/pdf') ?>" title="Descargar PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                    <?php if (Pedido::siguienteEstado($p['estado_codigo'])): ?>
                        <form method="POST" action="<?= url('pedidos/'.$p['id'].'/avanzar') ?>" class="d-inline"><?= csrf_campo() ?><button class="btn btn-sm btn-light" title="Avanzar estado"><i class="bi bi-arrow-right-circle text-brand"></i></button></form>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
        <?php if (! $pedidos): ?><tr><td colspan="10" class="empty"><i class="bi bi-inbox"></i>No hay pedidos para los filtros seleccionados</td></tr><?php endif ?>
        </tbody>
    </table></div>
    <?= $pag->enlaces() ?>
</div>

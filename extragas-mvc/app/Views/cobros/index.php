<?php use App\Core\Vista;
$ef = (float) ($porForma['Efectivo'] ?? 0); ?>
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'calendar-check', 'label' => 'Cobrado hoy', 'valor' => pesos($cobradoHoy)]) ?></div>
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'cash-stack', 'color' => 'i-green', 'label' => 'Cobrado en el período', 'valor' => pesos($totalPeriodo), 'sub' => fecha($desde).' – '.fecha($hasta)]) ?></div>
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'pie-chart', 'color' => 'i-blue', 'label' => 'Efectivo / otros', 'valor' => $totalPeriodo ? round($ef / $totalPeriodo * 100).'% / '.(100 - round($ef / $totalPeriodo * 100)).'%' : '—',
        'sub' => implode(' · ', array_map(fn ($f, $t) => "{$f} ".pesos($t), array_keys($porForma), $porForma))]) ?></div>
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'exclamation-diamond', 'color' => 'i-red', 'label' => 'Pendiente de cobro', 'valor' => pesos(array_sum(array_column($pendientes, 'saldo'))), 'sub' => count($pendientes).' pedidos']) ?></div>
</div>

<div class="card">
    <div class="card-header p-0 px-2 pt-2 border-0"><ul class="nav nav-tabs w-100">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tPagos" type="button"><i class="bi bi-receipt me-1"></i>Pagos recibidos</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tPend" type="button"><i class="bi bi-hourglass-split me-1"></i>Pendientes de cobro (<?= count($pendientes) ?>)</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tSaldos" type="button"><i class="bi bi-people me-1"></i>Saldos por cliente</button></li>
    </ul></div>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="tPagos">
            <form class="toolbar p-3 pb-0" method="GET" data-auto-submit>
                <div class="search"><i class="bi bi-search"></i><input class="form-control form-control-sm" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Cliente, recibo o referencia"></div>
                <?php Vista::parcial('rango', compact('desde', 'hasta')) ?>
                <select class="form-select form-select-sm" name="forma_pago"><option value="">Todas las formas</option><?php foreach ($formasPago as $id => $n): ?><option value="<?= $id ?>" <?= sel($_GET['forma_pago'] ?? '', $id) ?>><?= e($n) ?></option><?php endforeach ?></select>
                <div class="ms-auto d-flex gap-2">
                    <a class="btn btn-sm btn-light" href="<?= e(url_actual(['pdf' => 1])) ?>"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
                    <a class="btn btn-sm btn-primary" href="<?= url('cobros/nuevo') ?>"><i class="bi bi-plus-lg"></i> Registrar pago</a>
                </div>
            </form>
            <div class="table-responsive"><table class="table table-hover align-middle">
                <thead><tr><th>Recibo</th><th>Fecha</th><th>Cliente</th><th>Pedido</th><th>Forma</th><th>Referencia</th><th>Recibió</th><th class="num">Importe</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($pagos as $p): ?>
                    <tr><td class="fw-semibold text-nowrap"><?= e($p['numero_recibo']) ?></td><td class="text-nowrap"><?= fecha($p['fecha'], true) ?></td>
                        <td><a href="<?= url('clientes/'.$p['cliente_id']) ?>"><?= e(nombre($p, 'cliente_')) ?></a></td>
                        <td><?= $p['pedido_id'] ? '<a href="'.url('pedidos/'.$p['pedido_id']).'">'.e($p['pedido_numero']).'</a>' : 'A cuenta' ?></td>
                        <td><?= e($p['forma_nombre']) ?></td><td><?= e($p['referencia'] ?: '—') ?></td><td class="small"><?= e(trim($p['empleado_nombre'].' '.$p['empleado_apellido']) ?: ($p['username'] ?? '—')) ?></td>
                        <td class="num fw-semibold"><?= pesos($p['monto']) ?></td>
                        <td class="actions"><a class="btn btn-sm btn-light" href="<?= url('cobros/recibo', ['ids' => $p['id']]) ?>" title="Recibo PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                            <?php if (\App\Core\Auth::esAdmin()): ?><form method="POST" action="<?= url('cobros/'.$p['id'].'/anular') ?>" class="d-inline" data-confirm="¿Anular el pago <?= e($p['numero_recibo']) ?>? El saldo del pedido se recalcula."><?= csrf_campo() ?><button class="btn btn-sm btn-light" title="Anular"><i class="bi bi-x-circle text-danger"></i></button></form><?php endif ?></td></tr>
                <?php endforeach ?>
                <?php if (! $pagos): ?><tr><td colspan="9" class="empty"><i class="bi bi-inbox"></i>No hay pagos en el período</td></tr><?php endif ?>
                </tbody>
            </table></div>
            <?= $pag->enlaces() ?>
        </div>
        <div class="tab-pane fade" id="tPend">
            <div class="table-responsive"><table class="table table-hover align-middle">
                <thead><tr><th>Pedido</th><th>Fecha</th><th>Cliente</th><th>Estado</th><th class="num">Total</th><th class="num">Pagado</th><th class="num">Saldo</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($pendientes as $p): ?>
                    <tr><td><a href="<?= url('pedidos/'.$p['id']) ?>" class="fw-semibold"><?= e($p['numero']) ?></a></td>
                        <td class="text-nowrap"><?= fecha($p['fecha']) ?><div class="text-muted-sm">hace <?= dias_entre($p['fecha'], hoy()) ?> días</div></td>
                        <td><a href="<?= url('clientes/'.$p['cliente_id']) ?>"><?= e(nombre($p, 'cliente_')) ?></a><div class="text-muted-sm"><?= e($p['cliente_telefono']) ?></div></td>
                        <td><?= badge_estado_pedido($p['estado_codigo'], $p['estado_nombre']) ?></td><td class="num"><?= pesos($p['total']) ?></td><td class="num"><?= pesos($p['monto_pagado']) ?></td>
                        <td class="num fw-semibold text-danger"><?= pesos($p['saldo']) ?></td>
                        <td class="actions"><a class="btn btn-sm btn-light" href="<?= e(wa_link($p['cliente_telefono'])) ?>" target="_blank" rel="noopener" title="Recordar por WhatsApp"><i class="bi bi-whatsapp text-success"></i></a>
                            <a class="btn btn-sm btn-outline-primary" href="<?= url('cobros/nuevo', ['cliente' => $p['cliente_id'], 'pedido' => $p['id']]) ?>"><i class="bi bi-cash-coin"></i> Cobrar</a></td></tr>
                <?php endforeach ?>
                <?php if (! $pendientes): ?><tr><td colspan="8" class="empty">No hay pedidos pendientes de cobro</td></tr><?php endif ?>
                </tbody>
                <?php if ($pendientes): ?><tfoot><tr><th colspan="6" class="text-end">Total pendiente</th><th class="num text-danger"><?= pesos(array_sum(array_column($pendientes, 'saldo'))) ?></th><th></th></tr></tfoot><?php endif ?>
            </table></div>
        </div>
        <div class="tab-pane fade" id="tSaldos">
            <div class="table-responsive"><table class="table table-hover align-middle">
                <thead><tr><th>Cliente</th><th>Teléfono</th><th class="num">Pedidos adeudados</th><th class="num">Saldo</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($saldos as $s): ?>
                    <tr><td><a href="<?= url('clientes/'.$s['cliente_id']) ?>" class="fw-semibold"><?= e($s['cliente']) ?></a></td><td><?= e($s['telefono_principal']) ?></td>
                        <td class="num"><?= $s['pedidos_pendientes'] ?></td><td class="num fw-semibold text-danger"><?= pesos($s['saldo_total']) ?></td>
                        <td class="actions"><a class="btn btn-sm btn-outline-primary" href="<?= url('cobros/nuevo', ['cliente' => $s['cliente_id']]) ?>"><i class="bi bi-cash-coin"></i> Cobrar</a></td></tr>
                <?php endforeach ?>
                <?php if (! $saldos): ?><tr><td colspan="5" class="empty">Ningún cliente tiene saldo pendiente</td></tr><?php endif ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>

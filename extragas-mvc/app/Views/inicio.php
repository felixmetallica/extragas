<?php use App\Core\Vista; use App\Models\Pedido;
$ef = (float) ($cobrosHoy['EFECTIVO'] ?? 0); ?>
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => 'cart-check', 'label' => 'Pedidos de hoy', 'valor' => $pedidosHoy, 'sub' => count($enCurso).' en curso sin entregar']) ?></div>
    <div class="col-sm-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => 'cash-stack', 'color' => 'i-green', 'label' => 'Cobrado hoy', 'valor' => pesos($cobradoHoy), 'sub' => 'Efectivo '.pesos($ef).' · Otros '.pesos($cobradoHoy - $ef)]) ?></div>
    <div class="col-sm-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => 'exclamation-diamond', 'color' => 'i-red', 'label' => 'Pendiente de cobro', 'valor' => pesos($porCobrar), 'sub' => $deudores.' clientes con saldo']) ?></div>
    <div class="col-sm-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => 'graph-up-arrow', 'color' => 'i-blue', 'label' => 'Ventas del mes', 'valor' => pesos($ventasMes), 'sub' => mes_anio()]) ?></div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card mb-3">
            <div class="card-header"><h2><i class="bi bi-list-task me-1 text-brand"></i> Pedidos en curso</h2>
                <div class="ms-auto"><a href="<?= url('pedidos') ?>" class="btn btn-sm btn-light">Ver todos</a><a href="<?= url('pedidos/nuevo') ?>" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Nuevo</a></div></div>
            <div class="table-responsive"><table class="table table-hover align-middle">
                <thead><tr><th>N°</th><th>Cliente</th><th style="min-width:140px">Productos</th><th class="num">Total</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($enCurso as $p): ?>
                    <tr class="row-link" data-href="<?= url('pedidos/'.$p['id']) ?>">
                        <td><a href="<?= url('pedidos/'.$p['id']) ?>" class="fw-semibold"><?= e($p['numero']) ?></a><div class="text-muted-sm"><?= date('d/m H:i', strtotime($p['fecha'])) ?></div></td>
                        <td style="min-width:160px"><?= e(nombre($p, 'cliente_')) ?><div class="text-muted-sm"><?= medio($p['medio_codigo'], $p['medio_nombre']) ?> · <?= e($p['canal_nombre']) ?></div></td>
                        <td class="small"><?= e($p['productos']) ?></td>
                        <td class="num"><?= pesos($p['total']) ?></td>
                        <td><?= badge_estado_pedido($p['estado_codigo'], $p['estado_nombre']) ?></td>
                        <td class="actions">
                            <?php if (Pedido::siguienteEstado($p['estado_codigo']) === 'ENTREGADO'): ?>
                                <a href="<?= url('pedidos/'.$p['id'].'/entrega') ?>" class="btn btn-sm btn-outline-primary" title="Registrar entrega"><i class="bi bi-check2-circle"></i></a>
                            <?php else: ?>
                                <form method="POST" action="<?= url('pedidos/'.$p['id'].'/avanzar') ?>"><?= csrf_campo() ?><button class="btn btn-sm btn-outline-primary" title="Avanzar al siguiente estado"><i class="bi bi-arrow-right-circle"></i></button></form>
                            <?php endif ?>
                        </td>
                    </tr>
                <?php endforeach ?>
                <?php if (! $enCurso): ?><tr><td colspan="6" class="empty"><i class="bi bi-check2-all"></i>No hay pedidos pendientes</td></tr><?php endif ?>
                </tbody>
            </table></div>
        </div>
        <div class="card">
            <div class="card-header"><h2><i class="bi bi-bar-chart me-1 text-brand"></i> Ventas de los últimos 14 días</h2></div>
            <div class="card-body"><div class="chart-box sm"><canvas data-chart='<?= json($grafico) ?>'></canvas></div></div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-3">
            <div class="card-header"><h2><i class="bi bi-fuel-pump me-1 text-brand"></i> Garrafas</h2><div class="ms-auto"><a href="<?= url('garrafas') ?>" class="btn btn-sm btn-light">Detalle</a></div></div>
            <div class="card-body">
                <?php foreach ($stock as $cap => $e): $total = max(1, array_sum($e)); $w = fn ($n) => round($n / $total * 100, 1).'%'; $min = (float) ($garrafaProducto[$cap]['stock_minimo'] ?? 0); ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between"><b>Garrafa <?= $cap ?> kg</b><?= $e['LLENA'] <= $min ? badge('Stock bajo', 'b-impago') : '' ?></div>
                        <div class="stackbar"><div style="width:<?= $w($e['LLENA']) ?>;background:#40c057"></div><div style="width:<?= $w($e['VACIA']) ?>;background:#4dabf7"></div><div style="width:<?= $w($e['NO_APTA']) ?>;background:#fa5252"></div><div style="width:<?= $w($e['EN_CLIENTE']) ?>;background:#9775fa"></div></div>
                        <div class="d-flex justify-content-between small mt-1 text-body-secondary flex-wrap gap-1">
                            <span><span class="legend-dot" style="background:#40c057"></span><?= $e['LLENA'] ?> llenas</span>
                            <span><span class="legend-dot" style="background:#4dabf7"></span><?= $e['VACIA'] ?> vacías</span>
                            <span><span class="legend-dot" style="background:#fa5252"></span><?= $e['NO_APTA'] ?> no aptas</span>
                            <span><span class="legend-dot" style="background:#9775fa"></span><?= $e['EN_CLIENTE'] ?> clientes</span>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><h2><i class="bi bi-alarm me-1 text-brand"></i> Clientes que suelen pedir</h2></div>
            <ul class="list-group list-group-flush">
                <?php foreach ($porPedir as $c): $r = $c['regularidad']; ?>
                    <li class="list-group-item d-flex align-items-center gap-2">
                        <div class="flex-fill"><a href="<?= url('clientes/'.$c['id']) ?>" class="fw-semibold"><?= e(nombre($c)) ?></a>
                            <div class="text-muted-sm">Pide cada <?= $r['promedio'] ?> días · último <?= fecha($r['ultimo']) ?></div></div>
                        <?= badge_regularidad($r['estado']) ?>
                        <a class="btn btn-sm btn-light" href="<?= e(wa_link($c['telefono_principal'])) ?>" target="_blank" rel="noopener" title="WhatsApp"><i class="bi bi-whatsapp text-success"></i></a>
                    </li>
                <?php endforeach ?>
                <?php if (! $porPedir): ?><li class="list-group-item empty">Sin avisos</li><?php endif ?>
            </ul>
        </div>
        <div class="card">
            <div class="card-header"><h2><i class="bi bi-exclamation-triangle me-1 text-brand"></i> Stock bajo</h2></div>
            <ul class="list-group list-group-flush">
                <?php foreach ($stockBajo as $p): ?>
                    <li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-<?= icono_producto($p['tipo_codigo']) ?> me-2"></i><?= e($p['nombre']) ?></span>
                        <span class="fw-semibold text-danger"><?= num(\App\Models\Producto::stockDisponible($p, $stock)) ?> <small class="text-body-secondary fw-normal">/ mín. <?= num($p['stock_minimo']) ?></small></span></li>
                <?php endforeach ?>
                <?php if (! $stockBajo): ?><li class="list-group-item empty">Todo el stock está por encima del mínimo</li><?php endif ?>
            </ul>
        </div>
    </div>
</div>

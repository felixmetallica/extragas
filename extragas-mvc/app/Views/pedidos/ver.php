<?php use App\Models\Pedido;
$p = $pedido; $c = $cliente;
$flujo = Pedido::FLUJO;
$idx = array_search($p['estado_codigo'], $flujo, true);
$siguiente = Pedido::siguienteEstado($p['estado_codigo']);
$cancelado = $p['estado_codigo'] === 'CANCELADO';
$lineas = ['VENTA' => ['Venta', 'b-info'], 'ENTREGA' => ['Envase entregado', 'b-gray'], 'DEVOLUCION' => ['Envase recibido', 'b-gray']]; ?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <?= badge_estado_pedido($p['estado_codigo'], $p['estado_nombre']) ?> <?= badge_pago(estado_pago($p)) ?>
    <span class="text-body-secondary small">Tomado por <?= e(nombre($p, 'empleado_')) ?> · <?= medio($p['medio_codigo'], $p['medio_nombre']) ?></span>
    <div class="ms-auto d-flex flex-wrap gap-2">
        <?php if (! $p['es_final']): ?>
            <a class="btn btn-sm btn-light" href="<?= url('pedidos/'.$p['id'].'/editar') ?>"><i class="bi bi-pencil"></i> Editar</a>
            <form method="POST" action="<?= url('pedidos/'.$p['id'].'/cancelar') ?>" data-confirm="¿Cancelar el pedido <?= e($p['numero']) ?>?"><?= csrf_campo() ?><button class="btn btn-sm btn-light text-danger"><i class="bi bi-x-circle"></i> Cancelar</button></form>
        <?php endif ?>
        <a class="btn btn-sm btn-light" href="<?= e(wa_link($c['telefono_principal'] ?? '')) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp text-success"></i> WhatsApp</a>
        <a class="btn btn-sm btn-light" href="<?= url('pedidos/'.$p['id'].'/pdf') ?>"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        <?php if ($p['saldo'] > 0 && ! $cancelado): ?>
            <a class="btn btn-sm btn-outline-primary" href="<?= url('cobros/nuevo', ['cliente' => $p['cliente_id'], 'pedido' => $p['id']]) ?>"><i class="bi bi-cash-coin"></i> Registrar pago</a>
        <?php endif ?>
        <?php if ($siguiente === 'ENTREGADO'): ?>
            <a class="btn btn-sm btn-primary" href="<?= url('pedidos/'.$p['id'].'/entrega') ?>"><i class="bi bi-check2-circle"></i> Registrar entrega</a>
        <?php elseif ($siguiente): ?>
            <form method="POST" action="<?= url('pedidos/'.$p['id'].'/avanzar') ?>"><?= csrf_campo() ?><button class="btn btn-sm btn-primary"><i class="bi bi-arrow-right-circle"></i> Pasar a <?= e($estados[$siguiente]['nombre']) ?></button></form>
        <?php endif ?>
    </div>
</div>

<?php if ($cancelado): ?>
    <div class="alert alert-secondary"><i class="bi bi-x-circle me-1"></i>Este pedido fue cancelado.</div>
<?php else: ?>
    <div class="card mb-3"><div class="card-body"><div class="stepper">
        <?php foreach ($flujo as $i => $codigo): ?>
            <div class="step <?= $i <= $idx ? 'done' : '' ?>"><div class="dot"><i class="bi bi-<?= ['clock', 'box-seam', 'truck', 'check-lg'][$i] ?>"></i></div><?= e($estados[$codigo]['nombre']) ?></div>
        <?php endforeach ?>
    </div></div></div>
<?php endif ?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><h2>Productos</h2></div>
            <div class="table-responsive"><table class="table align-middle">
                <thead><tr><th>Producto</th><th>Línea</th><th class="num">Cantidad</th><th class="num">Precio</th><th class="num">Subtotal</th></tr></thead>
                <tbody>
                <?php foreach ($items as $it): $venta = $it['tipo_linea'] === 'VENTA'; ?>
                    <tr class="<?= $venta ? '' : 'text-body-secondary' ?>">
                        <td><i class="bi bi-<?= icono_producto($it['tipo_codigo']) ?> me-1"></i><?= e($it['producto_nombre']) ?></td>
                        <td><?= badge(...$lineas[$it['tipo_linea']]) ?></td>
                        <td class="num"><?= num($it['cantidad']) ?></td><td class="num"><?= $venta ? pesos($it['precio_unitario']) : '—' ?></td>
                        <td class="num fw-semibold"><?= $venta ? pesos($it['subtotal']) : '' ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
                <tfoot>
                    <?php if ($p['descuento'] > 0): ?>
                        <tr><td colspan="4" class="text-end">Subtotal</td><td class="num"><?= pesos($p['subtotal']) ?></td></tr>
                        <tr><td colspan="4" class="text-end">Descuento</td><td class="num text-danger">− <?= pesos($p['descuento']) ?></td></tr>
                    <?php endif ?>
                    <tr><th colspan="4" class="text-end">Total</th><th class="num fs-5"><?= pesos($p['total']) ?></th></tr>
                </tfoot>
            </table></div>
        </div>

        <?php if ($movimientos): ?>
            <div class="card mb-3">
                <div class="card-header"><h2>Garrafas intercambiadas</h2></div>
                <div class="table-responsive"><table class="table align-middle">
                    <thead><tr><th>Código</th><th>Capacidad</th><th>Movimiento</th><th>Fecha</th></tr></thead>
                    <tbody><?php foreach ($movimientos as $m): ?>
                        <tr><td><a href="<?= url('garrafas/'.$m['garrafa_id']) ?>" class="fw-semibold"><?= e($m['codigo']) ?></a></td><td><?= $m['capacidad_kg'] ?> kg</td>
                            <td><?= badge($m['tipo_nombre'], $m['tipo_codigo'] === 'ENTREGA_CLIENTE' ? 'b-info' : 'b-pagado') ?></td><td><?= fecha($m['fecha'], true) ?></td></tr>
                    <?php endforeach ?></tbody>
                </table></div>
            </div>
        <?php endif ?>

        <div class="card">
            <div class="card-header"><h2>Pagos del pedido</h2></div>
            <div class="table-responsive"><table class="table align-middle">
                <thead><tr><th>Recibo</th><th>Fecha</th><th>Forma</th><th>Referencia</th><th class="num">Monto</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($pagos as $pg): ?>
                    <tr><td class="fw-semibold"><?= e($pg['numero_recibo']) ?></td><td><?= fecha($pg['fecha'], true) ?></td><td><?= e($pg['forma_nombre']) ?></td><td><?= e($pg['referencia'] ?: '—') ?></td><td class="num"><?= pesos($pg['monto']) ?></td>
                        <td class="actions"><a class="btn btn-sm btn-light" href="<?= url('cobros/recibo', ['ids' => $pg['id']]) ?>" title="Recibo PDF"><i class="bi bi-file-earmark-pdf"></i></a></td></tr>
                <?php endforeach ?>
                <?php if (! $pagos): ?><tr><td colspan="6" class="empty">Sin pagos registrados</td></tr><?php endif ?>
                </tbody>
            </table></div>
            <div class="card-body border-top d-flex justify-content-end gap-4">
                <span>Pagado: <b class="text-success"><?= pesos($p['monto_pagado']) ?></b></span>
                <span>Saldo: <b class="<?= $p['saldo'] > 0 && ! $cancelado ? 'text-danger' : '' ?>"><?= pesos($cancelado ? 0 : $p['saldo']) ?></b></span>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h2>Cliente</h2><div class="ms-auto"><a href="<?= url('clientes/'.$p['cliente_id']) ?>" class="btn btn-sm btn-light">Ver ficha</a></div></div>
            <div class="card-body"><dl class="info-list">
                <dt>Nombre</dt><dd class="fw-semibold"><?= e(nombre($p, 'cliente_')) ?></dd>
                <dt>Teléfono</dt><dd><?= e($p['cliente_telefono']) ?></dd>
                <dt>Domicilio</dt><dd><?= e($c ? domicilio($c) : '—') ?></dd>
                <?php if (! empty($c['referencias'])): ?><dt>Referencia</dt><dd><?= e($c['referencias']) ?></dd><?php endif ?>
                <dt>Pago habitual</dt><dd><?= e($c['forma_pago_nombre'] ?? '—') ?></dd>
            </dl></div>
        </div>
        <div class="card">
            <div class="card-header"><h2>Entrega</h2></div>
            <div class="card-body"><dl class="info-list">
                <dt>Pedido</dt><dd><?= fecha($p['fecha'], true) ?></dd>
                <dt>Tipo</dt><dd><?= e($p['canal_nombre']) ?></dd>
                <?php if ($p['direccion_entrega']): ?><dt>Dirección</dt><dd><?= e($p['direccion_entrega']) ?></dd><?php endif ?>
                <dt>Entregado</dt><dd><?= $p['entregado'] ? fecha($p['fecha_entrega'], true) : 'No' ?></dd>
                <dt>Observaciones</dt><dd><?= e($p['observaciones'] ?: '—') ?></dd>
            </dl></div>
        </div>
    </div>
</div>

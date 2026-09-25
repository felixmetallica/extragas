<?php $viejasE = viejo('entregadas'); $viejasD = viejo('devueltas'); ?>
<form method="POST" action="<?= url('pedidos/'.$pedido['id'].'/entrega') ?>">
    <?= csrf_campo() ?>
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="alert alert-light border"><i class="bi bi-info-circle me-1"></i>
                Indicá qué garrafas <b>llenas</b> salen del depósito y qué envases <b>vacíos</b> entrega <?= e(nombre($pedido, 'cliente_')) ?>.
                Se sugieren las llenas más antiguas y los envases que el cliente tiene registrados.</div>

            <?php foreach ($necesidades as $n): $cap = $n['capacidad']; ?>
                <div class="card mb-3">
                    <div class="card-header"><h2><i class="bi bi-fuel-pump-fill text-brand me-1"></i> Garrafa <?= $cap ?> kg · <?= $n['cantidad'] ?> unidad(es)</h2></div>
                    <div class="card-body row g-3">
                        <div class="col-md-6">
                            <div class="section-title mt-0">Llenas que se entregan (elegir <?= $n['cantidad'] ?>)</div>
                            <?php if (count($n['llenas']) < $n['cantidad']): ?>
                                <div class="alert alert-danger py-2 small">Sólo hay <?= count($n['llenas']) ?> garrafas llenas de <?= $cap ?> kg en depósito.</div>
                            <?php endif ?>
                            <div class="border rounded p-2" style="max-height:240px;overflow:auto">
                                <?php foreach ($n['llenas'] as $i => $g): $marcada = $viejasE !== null ? in_array((string) $g['id'], (array) $viejasE, true) : $i < $n['cantidad']; ?>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="entregadas[]" value="<?= $g['id'] ?>" id="e<?= $g['id'] ?>" <?= chk($marcada) ?>>
                                        <label class="form-check-label" for="e<?= $g['id'] ?>"><?= e($g['codigo']) ?> <small class="text-body-secondary">· desde <?= fecha($g['fecha_ultimo_movimiento']) ?></small></label></div>
                                <?php endforeach ?>
                                <?php if (! $n['llenas']): ?><div class="text-body-secondary small">No hay garrafas llenas.</div><?php endif ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="section-title mt-0">Envases vacíos que devuelve</div>
                            <div class="border rounded p-2 mb-2" style="max-height:170px;overflow:auto">
                                <?php foreach ($n['del_cliente'] as $i => $g): $marcada = $viejasD !== null ? in_array((string) $g['id'], (array) $viejasD, true) : $i < $n['cantidad']; ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check flex-fill"><input class="form-check-input" type="checkbox" name="devueltas[]" value="<?= $g['id'] ?>" id="d<?= $g['id'] ?>" <?= chk($marcada) ?>>
                                            <label class="form-check-label" for="d<?= $g['id'] ?>"><?= e($g['codigo']) ?> <small class="text-body-secondary">· en su poder desde <?= fecha($g['fecha_ultimo_movimiento']) ?></small></label></div>
                                        <div class="form-check form-check-inline m-0" title="Vuelve dañada o vencida"><input class="form-check-input" type="checkbox" name="no_aptas[]" value="<?= $g['id'] ?>" id="na<?= $g['id'] ?>"><label class="form-check-label small text-danger text-nowrap" for="na<?= $g['id'] ?>">No apta</label></div>
                                    </div>
                                <?php endforeach ?>
                                <?php if (! $n['del_cliente']): ?><div class="text-body-secondary small">El cliente no tiene garrafas de <?= $cap ?> kg registradas.</div><?php endif ?>
                            </div>
                            <label class="form-label small" for="sr<?= $cap ?>">Envases vacíos sin registrar que entrega</label>
                            <input type="number" min="0" max="50" class="form-control form-control-sm" name="sin_registrar[<?= $cap ?>]" id="sr<?= $cap ?>"
                                value="<?= e(viejo('sin_registrar')[$cap] ?? max(0, $n['cantidad'] - count($n['del_cliente']))) ?>">
                            <div class="form-text">Se dan de alta como vacías aptas con un código nuevo.</div>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
            <?php if (! $necesidades): ?><div class="card card-body mb-3 text-body-secondary"><i class="bi bi-info-circle"></i> Este pedido no incluye garrafas.</div><?php endif ?>

            <?php if ($otros): ?>
                <div class="card"><div class="card-header"><h2>Otros productos</h2></div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($otros as $it): ?>
                            <li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-<?= icono_producto($it['tipo_codigo']) ?> me-2"></i><?= num($it['cantidad']) ?>× <?= e($it['producto_nombre']) ?></span>
                                <span class="<?= $it['stock_actual'] < $it['cantidad'] ? 'text-danger fw-semibold' : 'text-body-secondary' ?>">stock <?= num($it['stock_actual']) ?></span></li>
                        <?php endforeach ?>
                    </ul></div>
            <?php endif ?>
        </div>
        <div class="col-xl-4">
            <div class="card summary-card"><div class="card-header"><h2>Confirmar entrega</h2></div><div class="card-body">
                <dl class="info-list mb-3"><dt>Pedido</dt><dd><?= e($pedido['numero']) ?></dd><dt>Cliente</dt><dd><?= e(nombre($pedido, 'cliente_')) ?></dd>
                    <dt>Total</dt><dd class="fw-bold"><?= pesos($pedido['total']) ?></dd><dt>Saldo</dt><dd><?= pesos($pedido['saldo']) ?></dd></dl>
                <?php if ($pedido['saldo'] > 0): ?>
                    <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="cobrar" value="1" id="cobrar" checked><label class="form-check-label" for="cobrar">Registrar el cobro a continuación</label></div>
                <?php endif ?>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Confirmar entrega</button>
                    <a href="<?= url('pedidos/'.$pedido['id']) ?>" class="btn btn-light">Volver</a>
                </div>
            </div></div>
        </div>
    </div>
</form>

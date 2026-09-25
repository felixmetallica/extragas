<?php use App\Core\Vista;
$c = $cliente; $r = $regularidad; $totalPagos = array_sum($formasUsadas);
$porCap = array_count_values(array_map(fn ($g) => (int) $g['capacidad_kg'], $garrafas)); ?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <?= $c['activo'] ? '' : badge('Inactivo') ?> <?= $r['promedio'] ? badge_regularidad($r['estado']) : '' ?>
    <span class="text-muted-sm"><?= $c['codigo'] ? e($c['codigo']).' · ' : '' ?>Cliente desde <?= fecha($c['fecha_alta']) ?></span>
    <div class="ms-auto d-flex flex-wrap gap-2">
        <a class="btn btn-sm btn-light" href="<?= e(wa_link($c['telefono_principal'])) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp text-success"></i> WhatsApp</a>
        <a class="btn btn-sm btn-light" href="<?= url('clientes/'.$c['id'].'/editar') ?>"><i class="bi bi-pencil"></i> Editar</a>
        <a class="btn btn-sm btn-light" href="<?= url('clientes/'.$c['id'].'/estado-cuenta') ?>"><i class="bi bi-file-earmark-pdf"></i> Estado de cuenta</a>
        <?php if ($saldo > 0): ?><a class="btn btn-sm btn-outline-primary" href="<?= url('cobros/nuevo', ['cliente' => $c['id']]) ?>"><i class="bi bi-cash-coin"></i> Registrar pago</a><?php endif ?>
        <a class="btn btn-sm btn-primary" href="<?= url('pedidos/nuevo', ['cliente' => $c['id']]) ?>"><i class="bi bi-cart-plus"></i> Nuevo pedido</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'receipt', 'label' => 'Pedidos', 'valor' => $totales['n'], 'sub' => 'Total '.pesos($totales['total'])]) ?></div>
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'calendar2-week', 'color' => 'i-blue', 'label' => 'Pide cada', 'valor' => $r['promedio'] ? $r['promedio'].' días' : '—', 'sub' => $r['proximo'] ? 'Próximo estimado: '.fecha($r['proximo']) : 'Sin historial suficiente']) ?></div>
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'fuel-pump', 'color' => 'i-violet', 'label' => 'Garrafas en su poder', 'valor' => count($garrafas), 'sub' => implode(' · ', array_map(fn ($n, $cap) => "{$n}× {$cap} kg", $porCap, array_keys($porCap))) ?: 'Ninguna']) ?></div>
    <div class="col-6 col-lg-3"><?php Vista::parcial('kpi', ['icono' => 'wallet2', 'color' => $saldo > 0 ? 'i-red' : 'i-green', 'label' => 'Saldo', 'valor' => pesos($saldo), 'sub' => $saldo > 0 ? 'Adeudado' : 'Al día']) ?></div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header"><h2>Datos de contacto</h2></div><div class="card-body"><dl class="info-list">
            <dt>Teléfono</dt><dd class="fw-semibold"><?= e($c['telefono_principal']) ?></dd>
            <?php if ($c['telefono_secundario']): ?><dt>Alternativo</dt><dd><?= e($c['telefono_secundario']) ?></dd><?php endif ?>
            <?php if ($c['email']): ?><dt>Email</dt><dd><?= e($c['email']) ?></dd><?php endif ?>
            <dt>Domicilio</dt><dd><?= e(domicilio(['calle' => $c['calle'], 'numero' => $c['numero'], 'piso' => $c['piso'], 'depto' => $c['depto']]) ?: '—') ?></dd>
            <dt>Ciudad</dt><dd><?= e(implode(' · ', array_filter([$c['ciudad'], $c['codigo_postal'] ? 'CP '.$c['codigo_postal'] : null, $c['provincia_nombre']])) ?: '—') ?></dd>
            <dt>Referencias</dt><dd><?= e($c['referencias'] ?: '—') ?></dd>
            <dt>DNI / CUIT</dt><dd><?= e(implode(' · ', array_filter([$c['dni'], $c['cuit_cuil']])) ?: '—') ?></dd>
            <dt>Obs.</dt><dd><?= e($c['observaciones'] ?: '—') ?></dd>
        </dl></div></div>

        <div class="card mb-3"><div class="card-header"><h2>Otros contactos</h2></div>
            <ul class="list-group list-group-flush">
                <?php foreach ($contactos as $ct): ?>
                    <li class="list-group-item d-flex align-items-center gap-2">
                        <span class="flex-fill"><small class="text-body-secondary"><?= e($ct['tipo_nombre']) ?></small><br><?= e($ct['valor']) ?>
                            <?php if ($ct['observaciones']): ?><div class="text-muted-sm"><?= e($ct['observaciones']) ?></div><?php endif ?></span>
                        <form method="POST" action="<?= url('clientes/'.$c['id'].'/contactos/'.$ct['id'].'/eliminar') ?>" data-confirm="¿Eliminar este contacto?"><?= csrf_campo() ?><button class="btn btn-sm btn-light" title="Eliminar"><i class="bi bi-trash text-danger"></i></button></form>
                    </li>
                <?php endforeach ?>
                <?php if (! $contactos): ?><li class="list-group-item text-body-secondary small">Sin contactos adicionales</li><?php endif ?>
            </ul>
            <form method="POST" action="<?= url('clientes/'.$c['id'].'/contactos') ?>" class="card-body border-top row g-2">
                <?= csrf_campo() ?>
                <div class="col-5"><select class="form-select form-select-sm" name="tipo_contacto_id" aria-label="Tipo"><?php foreach ($tiposContacto as $id => $n): ?><option value="<?= $id ?>"><?= e($n) ?></option><?php endforeach ?></select></div>
                <div class="col-7"><input class="form-control form-control-sm" name="valor" placeholder="Número o email" required aria-label="Valor"></div>
                <div class="col-9"><input class="form-control form-control-sm" name="observaciones" placeholder="Ej.: hijo, trabajo" aria-label="Observaciones"></div>
                <div class="col-3 d-grid"><button class="btn btn-sm btn-outline-primary">Agregar</button></div>
            </form>
        </div>

        <div class="card mb-3"><div class="card-header"><h2>Hábitos de compra</h2></div><div class="card-body"><dl class="info-list">
            <dt>Pago habitual</dt><dd><?= e($c['forma_pago_nombre'] ?? '—') ?></dd>
            <dt>Pagó con</dt><dd><?= implode('<br>', array_map(fn ($f, $n) => e($f).' <span class="text-body-secondary">('.round($n / max(1, $totalPagos) * 100).'%)</span>', array_keys($formasUsadas), $formasUsadas)) ?: '—' ?></dd>
            <dt>Último pedido</dt><dd><?= $r['ultimo'] ? fecha($r['ultimo']).' (hace '.dias_entre($r['ultimo'], hoy()).' días)' : '—' ?></dd>
            <dt>Compra más</dt><dd><?= implode('<br>', array_map(fn ($p, $n) => e($p).' <span class="text-body-secondary">('.num($n).')</span>', array_keys($favoritos), $favoritos)) ?: '—' ?></dd>
        </dl></div></div>

        <div class="card"><div class="card-header"><h2>Garrafas en su poder</h2></div>
            <ul class="list-group list-group-flush">
                <?php foreach ($garrafas as $g): ?>
                    <li class="list-group-item d-flex justify-content-between"><a href="<?= url('garrafas/'.$g['id']) ?>"><i class="bi bi-fuel-pump me-2 text-brand"></i><?= e($g['codigo']) ?></a>
                        <span class="text-body-secondary small"><?= $g['capacidad_kg'] ?> kg · desde <?= fecha($g['fecha_ultimo_movimiento']) ?></span></li>
                <?php endforeach ?>
                <?php if (! $garrafas): ?><li class="list-group-item text-body-secondary small">No tiene garrafas</li><?php endif ?>
            </ul>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header p-0 px-2 pt-2 border-0"><ul class="nav nav-tabs w-100">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tPedidos" type="button">Pedidos (<?= $pag->total ?>)</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tCta" type="button">Cuenta corriente</button></li>
            </ul></div>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tPedidos">
                    <div class="table-responsive"><table class="table table-hover align-middle">
                        <thead><tr><th>N°</th><th>Fecha</th><th>Medio</th><th>Productos</th><th class="num">Total</th><th>Pago</th><th>Estado</th></tr></thead>
                        <tbody>
                        <?php foreach ($pedidos as $p): ?>
                            <tr class="row-link" data-href="<?= url('pedidos/'.$p['id']) ?>"><td><a href="<?= url('pedidos/'.$p['id']) ?>" class="fw-semibold"><?= e($p['numero']) ?></a></td><td><?= fecha($p['fecha']) ?></td>
                                <td><?= medio($p['medio_codigo'], $p['medio_nombre']) ?></td><td class="small"><?= e($p['productos']) ?></td><td class="num"><?= pesos($p['total']) ?></td>
                                <td><?= badge_pago(estado_pago($p)) ?></td><td><?= badge_estado_pedido($p['estado_codigo'], $p['estado_nombre']) ?></td></tr>
                        <?php endforeach ?>
                        <?php if (! $pedidos): ?><tr><td colspan="7" class="empty">El cliente no tiene pedidos</td></tr><?php endif ?>
                        </tbody>
                    </table></div>
                    <?= $pag->enlaces() ?>
                </div>
                <div class="tab-pane fade" id="tCta">
                    <div class="table-responsive" style="max-height:620px"><table class="table align-middle">
                        <thead><tr><th>Fecha</th><th>Comprobante</th><th class="num">Debe</th><th class="num">Haber</th><th class="num">Saldo</th></tr></thead>
                        <tbody>
                        <?php foreach (array_reverse($movimientos) as $m): ?>
                            <tr><td><?= fecha($m['fecha']) ?></td>
                                <td><?= $m['tipo_movimiento'] === 'PEDIDO' ? '<a href="'.url('pedidos/'.$m['pedido_id']).'">Pedido '.e($m['comprobante']).'</a>' : 'Recibo '.e($m['comprobante']) ?></td>
                                <td class="num"><?= $m['debe'] > 0 ? pesos($m['debe']) : '' ?></td><td class="num text-success"><?= $m['haber'] > 0 ? pesos($m['haber']) : '' ?></td>
                                <td class="num fw-semibold"><?= pesos($m['saldo']) ?></td></tr>
                        <?php endforeach ?>
                        <?php if (! $movimientos): ?><tr><td colspan="5" class="empty">Sin movimientos</td></tr><?php endif ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
        <?php if ($saldo <= 0 && ! $garrafas): ?>
            <form method="POST" action="<?= url('clientes/'.$c['id'].'/eliminar') ?>" class="mt-3 text-end" data-confirm="¿Eliminar el cliente <?= e(nombre($c)) ?>?"><?= csrf_campo() ?>
                <button class="btn btn-sm btn-link text-danger"><i class="bi bi-trash"></i> Eliminar cliente</button></form>
        <?php endif ?>
    </div>
</div>

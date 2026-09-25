<?php $colores ??= []; ?>
<div class="table-responsive"><table class="table align-middle">
    <thead><tr><th>Fecha</th><?= ! empty($conGarrafa) ? '<th>Garrafa</th>' : '' ?><th>Movimiento</th><th>Estado</th><th>Detalle</th><th>Empleado</th></tr></thead>
    <tbody>
    <?php foreach ($movimientos as $m): ?>
        <tr><td class="text-nowrap"><?= fecha($m['fecha'], true) ?></td>
            <?php if (! empty($conGarrafa)): ?><td><a href="<?= url('garrafas/'.$m['garrafa_id']) ?>" class="fw-semibold"><?= e($m['garrafa_codigo']) ?></a></td><?php endif ?>
            <td><?= badge($m['tipo_nombre'], $colores[$m['tipo_codigo']] ?? 'b-parcial') ?></td>
            <td class="small text-nowrap"><?= e($m['origen_nombre'] ?? '—') ?> → <b><?= e($m['destino_nombre']) ?></b></td>
            <td class="small">
                <?php if ($m['pedido_id']): ?><a href="<?= url('pedidos/'.$m['pedido_id']) ?>"><?= e($m['pedido_numero']) ?></a> · <?php endif ?>
                <?php if ($m['recepcion_id']): ?><a href="<?= url('recepciones/'.$m['recepcion_id']) ?>"><?= e($m['recepcion_numero']) ?></a> · <?php endif ?>
                <?= e(trim(nombre($m, 'cliente_').' '.$m['observaciones'])) ?></td>
            <td class="small"><?= e(nombre($m, 'empleado_') ?: '—') ?></td></tr>
    <?php endforeach ?>
    <?php if (! $movimientos): ?><tr><td colspan="6" class="empty">Sin movimientos</td></tr><?php endif ?>
    </tbody>
</table></div>

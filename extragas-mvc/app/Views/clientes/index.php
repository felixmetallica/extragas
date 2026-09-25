<?php use App\Core\Vista; ?>
<form class="toolbar" method="GET" data-auto-submit>
    <div class="search"><i class="bi bi-search"></i><input class="form-control form-control-sm" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Buscar por nombre, domicilio, DNI o teléfono"></div>
    <select class="form-select form-select-sm" name="forma_pago"><option value="">Forma de pago habitual</option><?php foreach ($formasPago as $id => $n): ?><option value="<?= $id ?>" <?= sel($_GET['forma_pago'] ?? '', $id) ?>><?= e($n) ?></option><?php endforeach ?></select>
    <select class="form-select form-select-sm" name="filtro">
        <?php foreach (['' => 'Todos los activos', 'deuda' => 'Con saldo adeudado', 'atrasado' => 'Atrasados según su regularidad', 'envases' => 'Con garrafas en su poder', 'inactivo' => 'Inactivos'] as $v => $t): ?>
            <option value="<?= $v ?>" <?= sel($_GET['filtro'] ?? '', $v) ?>><?= $t ?></option>
        <?php endforeach ?>
    </select>
    <div class="ms-auto d-flex gap-2">
        <a class="btn btn-sm btn-light" href="<?= e(url_actual(['pdf' => 1])) ?>"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
        <a class="btn btn-sm btn-primary" href="<?= url('clientes/nuevo') ?>"><i class="bi bi-person-plus"></i> Nuevo cliente</a>
    </div>
</form>
<div class="card">
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>Cliente</th><th>Domicilio</th><th>Teléfono</th><th>Pago habitual</th><th class="num">Garrafas</th><th>Último pedido</th><th>Regularidad</th><th class="num">Saldo</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($clientes as $c): $r = $c['regularidad']; ?>
            <tr class="row-link" data-href="<?= url('clientes/'.$c['id']) ?>">
                <td><a href="<?= url('clientes/'.$c['id']) ?>" class="fw-semibold"><?= e(nombre($c)) ?></a> <?= $c['activo'] ? '' : badge('Inactivo') ?>
                    <div class="text-muted-sm"><?= e($c['codigo']) ?><?= $c['dni'] ? ' · DNI '.e($c['dni']) : '' ?></div></td>
                <td><?= e(trim($c['calle'].' '.$c['numero']) ?: '—') ?><div class="text-muted-sm"><?= e($c['ciudad']) ?></div></td>
                <td class="text-nowrap"><?= e($c['telefono_principal']) ?> <a href="<?= e(wa_link($c['telefono_principal'])) ?>" target="_blank" rel="noopener" title="WhatsApp"><i class="bi bi-whatsapp text-success"></i></a></td>
                <td><?= e($c['forma_pago_nombre'] ?? '—') ?></td>
                <td class="num"><?= $c['garrafas'] ?></td>
                <td class="text-nowrap"><?= fecha($r['ultimo']) ?><?php if ($r['ultimo']): ?><div class="text-muted-sm">hace <?= dias_entre($r['ultimo'], hoy()) ?> días</div><?php endif ?></td>
                <td><?php if ($r['promedio']): ?><span class="text-nowrap">Cada <?= $r['promedio'] ?> días</span><div><?= badge_regularidad($r['estado']) ?></div><?php else: ?><span class="text-body-tertiary">Sin datos</span><?php endif ?></td>
                <td class="num"><?= $c['saldo'] > 0 ? '<span class="text-danger fw-semibold">'.pesos($c['saldo']).'</span>' : '<span class="text-success">$ 0</span>' ?></td>
                <td class="actions">
                    <a class="btn btn-sm btn-light" href="<?= url('pedidos/nuevo', ['cliente' => $c['id']]) ?>" title="Nuevo pedido"><i class="bi bi-cart-plus text-brand"></i></a>
                    <a class="btn btn-sm btn-light" href="<?= url('clientes/'.$c['id'].'/editar') ?>" title="Editar"><i class="bi bi-pencil"></i></a>
                </td>
            </tr>
        <?php endforeach ?>
        <?php if (! $clientes): ?><tr><td colspan="9" class="empty"><i class="bi bi-inbox"></i>No se encontraron clientes</td></tr><?php endif ?>
        </tbody>
    </table></div>
    <?= $pag->enlaces() ?>
</div>

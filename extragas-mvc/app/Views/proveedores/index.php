<form class="toolbar" method="GET">
    <div class="search"><i class="bi bi-search"></i><input class="form-control form-control-sm" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Buscar proveedor, CUIT o contacto"></div>
    <div class="ms-auto"><a class="btn btn-sm btn-primary" href="<?= url('proveedores/nuevo') ?>"><i class="bi bi-plus-lg"></i> Nuevo proveedor</a></div>
</form>
<div class="card">
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>Proveedor</th><th>Contacto</th><th>Teléfono</th><th>Localidad</th><th>Última recepción</th><th class="num">Saldo a pagar</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($proveedores as $p): ?>
            <tr class="row-link" data-href="<?= url('proveedores/'.$p['id']) ?>">
                <td><a href="<?= url('proveedores/'.$p['id']) ?>" class="fw-semibold"><?= e($p['razon_social']) ?></a> <?= $p['activo'] ? '' : badge('Inactivo') ?>
                    <div class="text-muted-sm">CUIT <?= e($p['cuit']) ?><?= $p['nombre_fantasia'] ? ' · '.e($p['nombre_fantasia']) : '' ?></div></td>
                <td><?= e($p['contacto_nombre'] ?: '—') ?><?php if ($p['contacto_telefono']): ?><div class="text-muted-sm"><?= e($p['contacto_telefono']) ?> <a href="<?= e(wa_link($p['contacto_telefono'])) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp text-success"></i></a></div><?php endif ?></td>
                <td><?= e($p['telefono_principal'] ?: '—') ?></td>
                <td><?= e(implode(', ', array_filter([$p['ciudad'], $p['provincia_nombre']])) ?: '—') ?></td>
                <td><?= fecha($p['ultima_recepcion']) ?></td>
                <td class="num"><?= $p['saldo'] > 0 ? '<span class="text-danger fw-semibold">'.pesos($p['saldo']).'</span>' : '<span class="text-success">$ 0</span>' ?></td>
                <td class="actions"><a class="btn btn-sm btn-light" href="<?= url('proveedores/'.$p['id'].'/editar') ?>" title="Editar"><i class="bi bi-pencil"></i></a></td>
            </tr>
        <?php endforeach ?>
        <?php if (! $proveedores): ?><tr><td colspan="7" class="empty"><i class="bi bi-inbox"></i>No hay proveedores</td></tr><?php endif ?>
        </tbody>
    </table></div>
    <?= $pag->enlaces() ?>
</div>

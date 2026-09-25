<?php use App\Core\Auth; use App\Models\Producto;
$iconos = ['GAS' => 'fuel-pump-fill', 'CARBON' => 'fire', 'LENA' => 'tree-fill']; ?>
<div class="toolbar">
    <div class="text-body-secondary small">La columna "Vendidos 30 d" muestra las unidades vendidas en los últimos 30 días.</div>
    <?php if (Auth::esAdmin()): ?>
        <div class="ms-auto d-flex gap-2">
            <a class="btn btn-sm btn-light" href="<?= url('productos/precios') ?>"><i class="bi bi-percent"></i> Actualizar precios</a>
            <a class="btn btn-sm btn-primary" href="<?= url('productos/nuevo') ?>"><i class="bi bi-plus-lg"></i> Nuevo producto</a>
        </div>
    <?php endif ?>
</div>
<?php foreach ($tipos as $codigo => $tipo): $lista = $productos[$codigo] ?? []; ?>
    <div class="card mb-3">
        <div class="card-header"><h2><i class="bi bi-<?= $iconos[$codigo] ?? 'box' ?> me-1 text-brand"></i> <?= e($tipo['nombre']) ?></h2>
            <?php if ($codigo === 'GAS'): ?><div class="ms-auto"><a href="<?= url('garrafas') ?>" class="btn btn-sm btn-light">Control de envases</a></div><?php endif ?></div>
        <div class="table-responsive"><table class="table table-hover align-middle">
            <thead><tr><th>Producto</th><th class="num">Precio venta</th><th class="num">Costo</th><th class="num">Margen</th><th class="num"><?= $codigo === 'GAS' ? 'Llenas' : 'Stock' ?></th><th class="num">Mínimo</th><th class="num">Vendidos 30 d</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($lista as $p): $s = Producto::stockDisponible($p, $stock); $bajo = $s <= (float) $p['stock_minimo']; ?>
                <tr class="<?= $p['activo'] ? '' : 'opacity-50' ?>">
                    <td class="fw-semibold"><?= e($p['nombre']) ?><div class="text-muted-sm"><?= e($p['codigo']) ?> · <?= num($p['capacidad_kg']) ?> kg · <?= e(strtolower($p['unidad_venta'])) ?></div></td>
                    <td class="num fw-semibold"><?= pesos($p['precio_actual']) ?></td><td class="num"><?= pesos($p['costo_actual']) ?></td>
                    <td class="num"><?= $p['precio_actual'] > 0 ? round(($p['precio_actual'] - $p['costo_actual']) / $p['precio_actual'] * 100).'%' : '—' ?></td>
                    <td class="num <?= $bajo ? 'text-danger fw-bold' : '' ?>"><?= num($s) ?></td><td class="num"><?= num($p['stock_minimo']) ?></td><td class="num"><?= num($vendidos[$p['id']] ?? 0) ?></td>
                    <td><?= ! $p['activo'] ? badge('Inactivo') : ($bajo ? badge('Stock bajo', 'b-impago') : badge('Disponible', 'b-pagado')) ?></td>
                    <td class="actions">
                        <?php if (! $p['maneja_garrafa_individual']): ?><button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#stock<?= $p['id'] ?>" title="Ajustar stock"><i class="bi bi-box-seam"></i></button><?php endif ?>
                        <?php if (Auth::esAdmin()): ?><a class="btn btn-sm btn-light" href="<?= url('productos/'.$p['id'].'/editar') ?>" title="Editar"><i class="bi bi-pencil"></i></a><?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table></div>
    </div>
    <?php foreach ($lista as $p): if ($p['maneja_garrafa_individual']) continue; ?>
        <div class="modal fade" id="stock<?= $p['id'] ?>" tabindex="-1"><div class="modal-dialog modal-sm"><form method="POST" action="<?= url('productos/'.$p['id'].'/stock') ?>" class="modal-content"><?= csrf_campo() ?>
            <div class="modal-header"><h5 class="modal-title">Ajustar stock</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body"><p class="text-muted-sm"><?= e($p['nombre']) ?> · stock actual <b><?= num($p['stock_actual']) ?></b></p>
                <label class="form-label">Tipo de ajuste</label><select class="form-select mb-2" name="tipo"><option value="fijar">Fijar cantidad contada</option><option value="baja">Baja por rotura / pérdida</option></select>
                <label class="form-label">Cantidad</label><input type="number" min="0" step="1" class="form-control mb-2" name="cantidad" value="<?= num($p['stock_actual']) ?>" required>
                <label class="form-label">Motivo</label><input class="form-control" name="motivo"></div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary">Guardar</button></div>
        </form></div></div>
    <?php endforeach ?>
<?php endforeach ?>

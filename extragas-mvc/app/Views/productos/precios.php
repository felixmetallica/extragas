<?php use App\Core\Vista; ?>
<form method="POST" action="<?= url('productos/precios') ?>" class="card" style="max-width:820px">
    <?= csrf_campo() ?>
    <div class="card-body">
        <div class="row g-2 align-items-end mb-3">
            <div class="col-sm-5"><label class="form-label" for="apTipo">Aplicar aumento a</label><select class="form-select" id="apTipo"><option value="">Todos los productos</option><?php foreach ($tipos as $id => $n): ?><option value="<?= $id ?>"><?= e($n) ?></option><?php endforeach ?></select></div>
            <div class="col-sm-4"><label class="form-label" for="apPct">Porcentaje</label><div class="input-group"><input type="number" step="0.5" class="form-control" id="apPct" value="10"><span class="input-group-text">%</span></div></div>
            <div class="col-sm-3"><button type="button" class="btn btn-outline-primary w-100" id="apBtn">Calcular</button></div>
        </div>
        <div class="table-responsive"><table class="table align-middle">
            <thead><tr><th>Producto</th><th class="num">Precio actual</th><th style="width:180px">Nuevo precio</th></tr></thead>
            <tbody><?php foreach ($productos as $p): ?>
                <tr data-tipo="<?= $p['tipo_producto_id'] ?>"><td><?= e($p['nombre']) ?></td><td class="num"><?= pesos($p['precio_actual']) ?></td>
                    <td><input type="number" min="0" step="1" class="form-control form-control-sm text-end" name="precios[<?= $p['id'] ?>]" value="<?= (float) $p['precio_actual'] ?>" data-base="<?= (float) $p['precio_actual'] ?>" aria-label="Nuevo precio"></td></tr>
            <?php endforeach ?></tbody>
        </table></div>
        <div class="form-text">Los precios nuevos se aplican a los pedidos que se carguen desde ahora; los pedidos registrados conservan su precio.</div>
    </div>
    <div class="card-body border-top d-flex gap-2 justify-content-end"><a class="btn btn-light" href="<?= url('productos') ?>">Cancelar</a><button class="btn btn-primary">Guardar precios</button></div>
</form>
<?php Vista::inicio('scripts') ?>
<script>
document.getElementById('apBtn').addEventListener('click', () => {
    const tipo = document.getElementById('apTipo').value, pct = +document.getElementById('apPct').value || 0;
    document.querySelectorAll('tr[data-tipo]').forEach(tr => {
        if (tipo && tr.dataset.tipo !== tipo) return;
        const inp = tr.querySelector('input');
        inp.value = Math.round(+inp.dataset.base * (1 + pct / 100) / 100) * 100;
    });
});
</script>
<?php Vista::fin() ?>

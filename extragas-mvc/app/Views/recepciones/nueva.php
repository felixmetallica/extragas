<?php use App\Core\Vista; use App\Models\Garrafa; ?>
<form method="POST" action="<?= url('recepciones') ?>" id="formRecepcion">
    <?= csrf_campo() ?>
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-3"><div class="card-header"><h2>Datos de la recepción</h2></div><div class="card-body row g-3">
                <?php Vista::parcial('campo', ['name' => 'proveedor_id', 'label' => 'Proveedor', 'type' => 'select', 'opciones' => $proveedores, 'value' => $proveedorId, 'vacio' => 'Seleccioná…', 'req' => true]) ?>
                <?php Vista::parcial('campo', ['name' => 'fecha', 'label' => 'Fecha', 'type' => 'datetime-local', 'value' => date('Y-m-d\TH:i'), 'req' => true, 'col' => 'col-md-3']) ?>
                <?php Vista::parcial('campo', ['name' => 'numero_factura_proveedor', 'label' => 'N° factura / remito', 'col' => 'col-md-3']) ?>
            </div></div>
            <div class="card mb-3"><div class="card-header"><h2>Productos recibidos</h2>
                <div class="ms-auto"><select class="form-select form-select-sm" id="addProd" aria-label="Agregar producto"><option value="">+ Agregar producto</option><?php foreach ($productos as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['nombre']) ?></option><?php endforeach ?></select></div></div>
                <div class="table-responsive"><table class="table align-middle mb-0">
                    <thead><tr><th>Producto</th><th style="width:110px">Cantidad</th><th style="width:140px">Costo unit.</th><th class="num">Subtotal</th><th></th></tr></thead>
                    <tbody id="items"></tbody>
                </table></div>
            </div>
            <div class="card"><div class="card-header"><h2>Envases vacíos que se lleva el proveedor</h2></div><div class="card-body row g-3">
                <?php foreach (Garrafa::CAPACIDADES as $cap): ?>
                    <div class="col-md-4"><label class="form-label" for="v<?= $cap ?>">Garrafas de <?= $cap ?> kg</label>
                        <input type="number" min="0" max="<?= $vacias[$cap] ?? 0 ?>" class="form-control" name="vacias[<?= $cap ?>]" id="v<?= $cap ?>" value="<?= e(viejo('vacias')[$cap] ?? 0) ?>">
                        <div class="form-text">Hay <?= $vacias[$cap] ?? 0 ?> vacías aptas en depósito</div></div>
                <?php endforeach ?>
                <div class="col-12 form-text">Se toman las vacías aptas más antiguas; salen del parque como "Entregada al proveedor".</div>
            </div></div>
        </div>
        <div class="col-xl-4"><div class="card summary-card"><div class="card-header"><h2>Resumen</h2></div><div class="card-body">
            <div class="d-flex justify-content-between mb-2"><span class="text-body-secondary">Subtotal</span><span id="subtotal">$ 0</span></div>
            <div class="d-flex justify-content-between align-items-center mb-2"><label class="text-body-secondary" for="descuento">Descuento</label>
                <div class="input-group input-group-sm" style="width:140px"><span class="input-group-text">$</span><input type="number" min="0" class="form-control text-end" name="descuento" id="descuento" value="<?= e(viejo('descuento', 0)) ?>"></div></div>
            <div class="d-flex justify-content-between align-items-end border-top pt-3 mb-3"><span class="text-body-secondary">Total</span><span class="summary-total" id="total">$ 0</span></div>
            <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="pagada" value="1" id="pagada" <?= chk(viejo('pagada')) ?>><label class="form-check-label" for="pagada">Pagada en el momento</label></div>
            <select class="form-select mb-2 d-none" name="forma_pago_id" id="formaPago" aria-label="Forma de pago"><?php foreach ($formasPago as $f): if (! $f['activo']) continue; ?><option value="<?= $f['id'] ?>" <?= sel($f['codigo'], 'TRANSFERENCIA') ?>><?= e($f['nombre']) ?></option><?php endforeach ?></select>
            <label class="form-label" for="obs">Observaciones</label><textarea class="form-control mb-3" name="observaciones" id="obs" rows="2"><?= e(viejo('observaciones')) ?></textarea>
            <div class="d-grid gap-2"><button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Registrar recepción</button><a class="btn btn-light" href="<?= url('recepciones') ?>">Cancelar</a></div>
        </div></div></div>
    </div>
</form>

<?php Vista::inicio('scripts') ?>
<script>
(() => {
    const productos = <?= json($productos) ?>;
    let items = Object.values(<?= json((array) viejo('items', [])) ?>).map(i => ({ ...i, producto_id: +i.producto_id, cantidad: +i.cantidad, precio_unitario: +i.precio_unitario }));
    const $ = id => document.getElementById(id), prod = id => productos.find(p => p.id === +id);
    const pesos = n => '$ ' + Number(n || 0).toLocaleString('es-AR');
    const esc = s => String(s ?? '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
    function dibujar() {
        $('items').innerHTML = items.length ? items.map((it, i) => {
            const p = prod(it.producto_id);
            return `<tr><td class="fw-semibold">${esc(p.nombre)}<input type="hidden" name="items[${i}][producto_id]" value="${p.id}">
                    ${p.garrafa ? `<textarea class="form-control form-control-sm mt-1" rows="1" name="items[${i}][codigos]" placeholder="Códigos (opcional; si no, se generan automáticamente)">${esc(it.codigos || '')}</textarea>` : `<div class="text-muted-sm">Stock actual: ${p.stock}</div>`}</td>
                <td><input type="number" min="1" class="form-control form-control-sm" name="items[${i}][cantidad]" data-i="${i}" data-f="cantidad" value="${it.cantidad}" aria-label="Cantidad"></td>
                <td><input type="number" min="0" class="form-control form-control-sm text-end" name="items[${i}][precio_unitario]" data-i="${i}" data-f="precio_unitario" value="${it.precio_unitario}" aria-label="Costo"></td>
                <td class="num sub">${pesos(it.cantidad * it.precio_unitario)}</td>
                <td class="actions"><button type="button" class="btn btn-sm btn-light" data-del="${i}"><i class="bi bi-trash text-danger"></i></button></td></tr>`;
        }).join('') : '<tr><td colspan="5" class="empty"><i class="bi bi-box"></i>Agregá los productos del remito</td></tr>';
        totales();
    }
    function totales() {
        const sub = items.reduce((s, it) => s + it.cantidad * it.precio_unitario, 0);
        $('subtotal').textContent = pesos(sub);
        $('total').textContent = pesos(Math.max(0, sub - (+$('descuento').value || 0)));
    }
    $('addProd').addEventListener('change', e => {
        const p = prod(e.target.value); e.target.value = '';
        if (!p || items.some(it => it.producto_id === p.id)) return;
        items.push({ producto_id: p.id, cantidad: 1, precio_unitario: +p.costo });
        if (p.garrafa) { const v = $('v' + p.capacidad); if (v && +v.value === 0) v.value = Math.min(1, +v.max); }
        dibujar();
    });
    $('items').addEventListener('input', e => {
        const t = e.target;
        if (t.name?.endsWith('[codigos]')) { items[+t.name.match(/\d+/)[0]].codigos = t.value; return; }
        if (!t.dataset.f) return;
        const it = items[+t.dataset.i];
        it[t.dataset.f] = Math.max(0, +t.value || 0);
        const p = prod(it.producto_id);
        if (t.dataset.f === 'cantidad' && p.garrafa) { const v = $('v' + p.capacidad); if (v) v.value = Math.min(it.cantidad, +v.max); }
        t.closest('tr').querySelector('.sub').textContent = pesos(it.cantidad * it.precio_unitario);
        totales();
    });
    $('items').addEventListener('click', e => { const b = e.target.closest('[data-del]'); if (b) { items.splice(+b.dataset.del, 1); dibujar(); } });
    $('descuento').addEventListener('input', totales);
    $('pagada').addEventListener('change', e => $('formaPago').classList.toggle('d-none', !e.target.checked));
    if ($('pagada').checked) $('formaPago').classList.remove('d-none');
    $('formRecepcion').addEventListener('submit', e => { if (!items.length) { e.preventDefault(); alert('Agregá los productos del remito.'); } });
    dibujar();
})();
</script>
<?php Vista::fin() ?>

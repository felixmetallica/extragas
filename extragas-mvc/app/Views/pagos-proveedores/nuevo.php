<?php use App\Core\Vista; $provSel = viejo('proveedor_id', $proveedorId); ?>
<form method="POST" action="<?= url('pagos-proveedores') ?>" class="card" style="max-width:720px">
    <?= csrf_campo() ?>
    <div class="card-body row g-3">
        <div class="col-12"><label class="form-label req" for="provSel">Proveedor</label>
            <select class="form-select" name="proveedor_id" id="provSel" required><option value="">Seleccioná…</option>
                <?php foreach ($proveedores as $id => $n): ?><option value="<?= $id ?>" data-saldo="<?= (float) ($saldos[$id] ?? 0) ?>" data-obs="<?= e($observaciones[$id] ?? '') ?>" <?= sel($provSel, $id) ?>><?= e($n) ?> — debe <?= pesos($saldos[$id] ?? 0) ?></option><?php endforeach ?>
            </select></div>
        <div class="col-12"><label class="form-label" for="recSel">Recepción</label><select class="form-select" name="recepcion_id" id="recSel"></select>
            <div class="form-text">"A cuenta" aplica el importe a las recepciones impagas más antiguas.</div></div>
        <div class="col-12"><div class="alert alert-light border small py-2 mb-0" id="info"></div></div>
        <?php Vista::parcial('campo', ['name' => 'monto', 'label' => 'Importe', 'type' => 'number', 'req' => true, 'attrs' => 'min="1" step="0.01"']) ?>
        <?php Vista::parcial('campo', ['name' => 'fecha', 'label' => 'Fecha', 'type' => 'date', 'value' => hoy(), 'req' => true]) ?>
        <?php Vista::parcial('campo', ['name' => 'forma_pago_id', 'label' => 'Forma de pago', 'type' => 'select', 'opciones' => array_column(array_filter($formasPago, fn ($f) => $f['activo']), 'nombre', 'id'), 'value' => $formasPago['TRANSFERENCIA']['id'] ?? null]) ?>
        <?php Vista::parcial('campo', ['name' => 'referencia', 'label' => 'Comprobante / referencia']) ?>
        <?php Vista::parcial('campo', ['name' => 'observaciones', 'label' => 'Observaciones', 'col' => 'col-12']) ?>
    </div>
    <div class="card-body border-top d-flex gap-2 justify-content-end"><a class="btn btn-light" href="<?= url('pagos-proveedores') ?>">Cancelar</a><button class="btn btn-primary"><i class="bi bi-check2"></i> Registrar pago</button></div>
</form>
<?php Vista::inicio('scripts') ?>
<script>
(() => {
    const pendientes = <?= json($pendientes) ?>, pre = <?= (int) viejo('recepcion_id', $recepcionId) ?>;
    const prov = document.getElementById('provSel'), rec = document.getElementById('recSel'), monto = document.getElementById('f_monto'), info = document.getElementById('info');
    const pesos = n => '$ ' + Number(n).toLocaleString('es-AR');
    const esc = s => String(s ?? '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
    function cargar() {
        rec.innerHTML = '<option value="">A cuenta (recepciones más antiguas primero)</option>' + pendientes.filter(r => r.proveedor_id === +prov.value)
            .map(r => `<option value="${r.id}" data-saldo="${r.saldo}" ${r.id === pre ? 'selected' : ''}>${r.texto}</option>`).join('');
        actualizar();
    }
    function actualizar() {
        const op = rec.selectedOptions[0], po = prov.selectedOptions[0];
        const saldo = op && op.value ? +op.dataset.saldo : +(po?.dataset.saldo || 0);
        if (!monto.dataset.tocado) monto.value = saldo || '';
        info.innerHTML = prov.value ? `Saldo: <b>${pesos(saldo)}</b>${po.dataset.obs ? `<br><span class="text-body-secondary">${esc(po.dataset.obs)}</span>` : ''}` : 'Seleccioná un proveedor.';
    }
    prov.addEventListener('change', cargar); rec.addEventListener('change', actualizar);
    monto.addEventListener('input', () => { monto.dataset.tocado = 1; });
    if (monto.value) monto.dataset.tocado = 1;
    cargar();
})();
</script>
<?php Vista::fin() ?>

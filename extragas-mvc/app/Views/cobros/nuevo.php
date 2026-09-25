<?php use App\Core\Vista;
$clienteSel = viejo('cliente_id', $cliente['id'] ?? null);
$formaSel = viejo('forma_pago_id', $cliente['forma_pago_habitual_id'] ?? null); ?>
<form method="POST" action="<?= url('cobros') ?>" class="card" style="max-width:720px">
    <?= csrf_campo() ?>
    <div class="card-body row g-3">
        <div class="col-12"><label class="form-label req" for="clienteSel">Cliente</label>
            <select class="form-select" name="cliente_id" id="clienteSel" required>
                <option value="">Seleccioná…</option>
                <?php foreach ($saldos as $s): ?><option value="<?= $s['cliente_id'] ?>" data-saldo="<?= $s['saldo_total'] ?>" <?= sel($clienteSel, $s['cliente_id']) ?>><?= e($s['cliente']) ?> — saldo <?= pesos($s['saldo_total']) ?></option><?php endforeach ?>
                <?php if ($cliente && ! isset($saldos[$cliente['id']])): ?><option value="<?= $cliente['id'] ?>" data-saldo="0" selected><?= e(nombre($cliente)) ?> — sin deuda</option><?php endif ?>
            </select></div>
        <div class="col-12"><label class="form-label" for="pedidoSel">Pedido</label>
            <select class="form-select" name="pedido_id" id="pedidoSel"></select>
            <div class="form-text">"A cuenta" aplica el importe a los pedidos impagos más antiguos (se emite un recibo por pedido).</div></div>
        <div class="col-12"><div class="alert alert-light border py-2 mb-0 small" id="saldoInfo"></div></div>
        <?php Vista::parcial('campo', ['name' => 'monto', 'label' => 'Importe', 'type' => 'number', 'req' => true, 'attrs' => 'min="1" step="0.01"']) ?>
        <?php Vista::parcial('campo', ['name' => 'fecha', 'label' => 'Fecha', 'type' => 'date', 'value' => hoy(), 'req' => true]) ?>
        <div class="col-md-6"><label class="form-label" for="f_forma_pago_id">Forma de pago</label>
            <select class="form-select" name="forma_pago_id" id="f_forma_pago_id">
                <?php foreach ($formasPago as $f): if (! $f['activo']) continue; ?><option value="<?= $f['id'] ?>" <?= sel($formaSel, $f['id']) ?>><?= e($f['nombre']) ?><?= $f['requiere_referencia'] ? ' (pide referencia)' : '' ?></option><?php endforeach ?>
            </select></div>
        <?php Vista::parcial('campo', ['name' => 'referencia', 'label' => 'Referencia', 'placeholder' => 'N° de operación / comprobante']) ?>
        <?php Vista::parcial('campo', ['name' => 'observaciones', 'label' => 'Observaciones', 'col' => 'col-12']) ?>
    </div>
    <div class="card-body border-top d-flex gap-2 justify-content-end">
        <a href="<?= url('cobros') ?>" class="btn btn-light">Cancelar</a>
        <button class="btn btn-primary"><i class="bi bi-check2"></i> Registrar pago</button>
    </div>
</form>

<?php Vista::inicio('scripts') ?>
<script>
(() => {
    const pendientes = <?= json($pendientes) ?>;
    const prePedido = <?= (int) viejo('pedido_id', $pedidoId) ?>;
    const cli = document.getElementById('clienteSel'), ped = document.getElementById('pedidoSel'), monto = document.getElementById('f_monto'), info = document.getElementById('saldoInfo');
    const pesos = n => '$ ' + Number(n).toLocaleString('es-AR');
    function cargarPedidos() {
        const lista = pendientes.filter(p => p.cliente_id === +cli.value);
        ped.innerHTML = '<option value="">A cuenta (pedidos más antiguos primero)</option>' + lista.map(p => `<option value="${p.id}" data-saldo="${p.saldo}" ${p.id === prePedido ? 'selected' : ''}>${p.texto}</option>`).join('');
        actualizar();
    }
    function actualizar() {
        const op = ped.selectedOptions[0];
        const saldo = op && op.value ? +op.dataset.saldo : +(cli.selectedOptions[0]?.dataset.saldo || 0);
        if (!monto.dataset.tocado) monto.value = saldo || '';
        info.innerHTML = cli.value ? `Saldo ${op && op.value ? 'del pedido' : 'total del cliente'}: <b>${pesos(saldo)}</b>` : 'Seleccioná un cliente.';
    }
    cli.addEventListener('change', cargarPedidos);
    ped.addEventListener('change', actualizar);
    monto.addEventListener('input', () => { monto.dataset.tocado = 1; });
    if (monto.value) monto.dataset.tocado = 1;
    cargarPedidos();
})();
</script>
<?php Vista::fin() ?>

<?php use App\Core\Vista;
$editando = (bool) $pedido['id'];
$clienteId = viejo('cliente_id', $pedido['cliente_id']);
$medioSel = viejo('medio_contacto_id', $pedido['medio_contacto_id']); ?>
<form method="POST" action="<?= $editando ? url('pedidos/'.$pedido['id'].'/editar') : url('pedidos') ?>" id="formPedido" novalidate>
    <?= csrf_campo() ?>
    <input type="hidden" name="cliente_id" id="clienteId" value="<?= e($clienteId) ?>">
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-3">
                <div class="card-header"><h2>1. Cliente</h2>
                    <?php if (! $editando): ?><div class="ms-auto"><a href="<?= url('clientes/nuevo', ['volver' => 'pedido']) ?>" class="btn btn-sm btn-light"><i class="bi bi-person-plus"></i> Nuevo cliente</a></div><?php endif ?>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label req" for="cliente">Buscar cliente</label>
                            <input class="form-control" id="cliente" list="dlClientes" placeholder="Nombre, apellido o teléfono…" autocomplete="off" <?= $editando ? 'disabled' : '' ?>>
                            <datalist id="dlClientes"><?php foreach ($clientes as $c): ?><option value="<?= e($c['label']) ?>"></option><?php endforeach ?></datalist>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">¿Cómo hizo el pedido?</label>
                            <div class="btn-group w-100 canal-options" role="group">
                                <?php foreach ($medios as $m): if ($m['codigo'] === 'OTRO') continue; ?>
                                    <input type="radio" class="btn-check" name="medio_contacto_id" id="medio<?= $m['id'] ?>" value="<?= $m['id'] ?>" data-codigo="<?= e($m['codigo']) ?>" <?= chk((string) $medioSel === (string) $m['id']) ?>>
                                    <label class="btn btn-outline-secondary btn-sm" for="medio<?= $m['id'] ?>"><?= medio($m['codigo'], $m['nombre']) ?></label>
                                <?php endforeach ?>
                            </div>
                        </div>
                    </div>
                    <div id="clienteInfo" class="mt-3"></div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h2>2. Productos</h2><small class="text-body-secondary ms-2">Tocá un producto para agregarlo</small></div>
                <div class="card-body">
                    <?php $grupos = []; foreach ($productos as $p) { $grupos[$p['tipo']][] = $p; } ?>
                    <?php foreach ($grupos as $tipo => $lista): ?>
                        <div class="section-title mt-0"><?= e($tipo) ?></div>
                        <div class="prod-grid mb-3">
                            <?php foreach ($lista as $p): ?>
                                <button type="button" class="prod-btn <?= $p['stock'] <= 0 ? 'out' : '' ?>" data-add="<?= $p['id'] ?>">
                                    <i class="bi bi-<?= $p['icono'] ?> pi text-brand"></i><b><?= e(str_replace(' para hogar', '', $p['nombre'])) ?></b><small><?= pesos($p['precio']) ?> · stock <?= num($p['stock']) ?></small>
                                </button>
                            <?php endforeach ?>
                        </div>
                    <?php endforeach ?>
                    <div class="table-responsive border rounded"><table class="table align-middle mb-0">
                        <thead><tr><th>Producto</th><th style="width:120px">Cantidad</th><th class="num" style="width:150px">Precio unit.</th><th class="num">Subtotal</th><th></th></tr></thead>
                        <tbody id="items"></tbody>
                    </table></div>
                    <div class="form-text"><i class="bi bi-info-circle"></i> Los envases (garrafas llenas que salen y vacías que se reciben) se registran al confirmar la entrega.</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h2>3. Entrega</h2></div>
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="canal">Tipo de entrega</label>
                        <select class="form-select" name="canal_venta_id" id="canal">
                            <?php foreach ($canales as $c): ?><option value="<?= $c['id'] ?>" <?= sel(viejo('canal_venta_id', $pedido['canal_venta_id']), $c['id']) ?>><?= e($c['nombre']) ?></option><?php endforeach ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label" for="fecha">Fecha y hora del pedido</label>
                        <input type="datetime-local" class="form-control" name="fecha" id="fecha" value="<?= e(viejo('fecha', date('Y-m-d\TH:i', strtotime($pedido['fecha'])))) ?>"></div>
                    <div class="col-12" id="dirWrap"><label class="form-label" for="direccion">Dirección de entrega</label>
                        <input class="form-control" name="direccion_entrega" id="direccion" value="<?= e(viejo('direccion_entrega', $pedido['direccion_entrega'])) ?>" placeholder="Se completa con el domicilio del cliente"></div>
                    <div class="col-12"><label class="form-label" for="obs">Observaciones</label>
                        <textarea class="form-control" name="observaciones" id="obs" rows="2" placeholder="Ej.: tocar timbre, cambio de $20.000…"><?= e(viejo('observaciones', $pedido['observaciones'])) ?></textarea></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card summary-card">
                <div class="card-header"><h2>Resumen</h2></div>
                <div class="card-body">
                    <div id="resumenItems" class="small mb-3"></div>
                    <div class="d-flex justify-content-between align-items-center mb-2"><span class="text-body-secondary">Subtotal</span><span id="subtotal">$ 0</span></div>
                    <div class="d-flex justify-content-between align-items-center mb-2"><label class="text-body-secondary" for="descuento">Descuento</label>
                        <div class="input-group input-group-sm" style="width:140px"><span class="input-group-text">$</span><input type="number" min="0" step="1" class="form-control text-end" name="descuento" id="descuento" value="<?= e(viejo('descuento', (float) $pedido['descuento'])) ?>"></div></div>
                    <div class="d-flex justify-content-between align-items-end border-top pt-3"><span class="text-body-secondary">Total</span><span class="summary-total" id="total">$ 0</span></div>

                    <?php if (! $editando): ?>
                        <div class="section-title">Pago</div>
                        <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="registrar_pago" value="1" id="registrarPago" <?= chk(viejo('registrar_pago')) ?>><label class="form-check-label" for="registrarPago">Registrar pago ahora</label></div>
                        <div id="pagoWrap" class="d-none">
                            <select class="form-select mb-2" name="forma_pago_id" id="formaPago" aria-label="Forma de pago"><?php foreach ($formasPago as $id => $n): ?><option value="<?= $id ?>" <?= sel(viejo('forma_pago_id'), $id) ?>><?= e($n) ?></option><?php endforeach ?></select>
                            <div class="input-group mb-2"><span class="input-group-text">$</span><input type="number" min="0" step="1" class="form-control" name="pago_monto" id="pagoMonto" value="<?= e(viejo('pago_monto')) ?>" aria-label="Monto"></div>
                            <input class="form-control mb-2" name="pago_referencia" value="<?= e(viejo('pago_referencia')) ?>" placeholder="N° de operación (transferencia)">
                        </div>
                        <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="entregar_ahora" value="1" id="entregarAhora" <?= chk(viejo('entregar_ahora')) ?>><label class="form-check-label" for="entregarAhora">Entregar en el momento</label></div>
                    <?php endif ?>
                    <div id="alertas"></div>
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> <?= $editando ? 'Guardar cambios' : 'Registrar pedido' ?></button>
                        <?php if (! $editando): ?><button class="btn btn-outline-primary" name="con_pdf" value="1"><i class="bi bi-file-earmark-pdf"></i> Registrar y generar PDF</button><?php endif ?>
                        <a class="btn btn-light" href="<?= $editando ? url('pedidos/'.$pedido['id']) : url('pedidos') ?>">Cancelar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php Vista::inicio('scripts') ?>
<script>
    window.PEDIDO = {
        clientes: <?= json($clientes) ?>,
        productos: <?= json($productos) ?>,
        items: <?= json(array_values($items)) ?>,
        canalDomicilio: <?= (int) $canales['DOMICILIO']['id'] ?>,
        canalLocal: <?= (int) $canales['MOSTRADOR']['id'] ?>,
        editando: <?= $editando ? 'true' : 'false' ?>,
    };
</script>
<script src="<?= asset('js/pedido-form.js') ?>"></script>
<?php Vista::fin() ?>

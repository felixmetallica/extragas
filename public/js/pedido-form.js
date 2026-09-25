/* Formulario de pedido: selección de cliente, carga de productos y totales */
(() => {
    const P = window.PEDIDO;
    const $ = id => document.getElementById(id);
    const pesos = n => '$ ' + Number(n || 0).toLocaleString('es-AR', { maximumFractionDigits: 2 });
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const prod = id => P.productos.find(p => p.id === +id);
    let items = P.items.map(i => ({ producto_id: +i.producto_id, cantidad: +i.cantidad, precio_unitario: +i.precio_unitario }));
    let pagoTocado = false;

    function mostrarCliente(c) {
        $('clienteId').value = c ? c.id : '';
        if (!c) {
            $('clienteInfo').innerHTML = '<div class="text-muted-sm"><i class="bi bi-info-circle"></i> Seleccioná un cliente para ver su domicilio, garrafas en su poder y saldo.</div>';
            return;
        }
        $('cliente').value = c.label;
        if (!P.editando || !$('direccion').value) $('direccion').value = c.domicilio;
        if (c.forma_pago_id && $('formaPago')) $('formaPago').value = c.forma_pago_id;
        $('clienteInfo').innerHTML = `<div class="border rounded p-3 bg-body-tertiary"><div class="row g-2">
            <div class="col-md-6"><dl class="info-list">
                <dt>Cliente</dt><dd><a href="${c.url}" target="_blank" class="fw-semibold">${esc(c.nombre)}</a></dd>
                <dt>Teléfono</dt><dd>${esc(c.telefono)} <a href="${c.wa}" target="_blank" rel="noopener"><i class="bi bi-whatsapp text-success"></i></a></dd>
                <dt>Domicilio</dt><dd>${esc(c.domicilio) || '—'}${c.referencias ? `<div class="text-muted-sm">${esc(c.referencias)}</div>` : ''}</dd></dl></div>
            <div class="col-md-6"><dl class="info-list">
                <dt>Paga con</dt><dd>${esc(c.forma_pago) || '—'}</dd>
                <dt>Garrafas</dt><dd>${esc(c.garrafas) || 'Ninguna'}</dd>
                <dt>Saldo</dt><dd class="${c.saldo ? 'text-danger fw-semibold' : 'text-success'}">${c.saldo ? pesos(c.saldo) + ' adeudado' : 'Sin deuda'}</dd></dl></div>
        </div></div>`;
    }

    function dibujar() {
        $('items').innerHTML = items.length ? items.map((it, i) => {
            const p = prod(it.producto_id);
            return `<tr><td><i class="bi bi-${p.icono} me-1 text-body-secondary"></i>${esc(p.nombre)}
                    <input type="hidden" name="items[${i}][producto_id]" value="${p.id}"></td>
                <td><input type="number" min="1" step="1" class="form-control form-control-sm" name="items[${i}][cantidad]" data-i="${i}" data-f="cantidad" value="${it.cantidad}" aria-label="Cantidad"></td>
                <td><input type="number" min="0" step="1" class="form-control form-control-sm text-end" name="items[${i}][precio_unitario]" data-i="${i}" data-f="precio_unitario" value="${it.precio_unitario}" aria-label="Precio"></td>
                <td class="num fw-semibold sub">${pesos(it.cantidad * it.precio_unitario)}</td>
                <td class="actions"><button type="button" class="btn btn-sm btn-light" data-del="${i}" title="Quitar"><i class="bi bi-trash text-danger"></i></button></td></tr>`;
        }).join('') : '<tr><td colspan="5" class="empty"><i class="bi bi-basket"></i>Todavía no agregaste productos</td></tr>';
        resumen();
    }

    function resumen() {
        const sub = items.reduce((s, it) => s + it.cantidad * it.precio_unitario, 0);
        const total = Math.max(0, sub - (+$('descuento').value || 0));
        $('subtotal').textContent = pesos(sub);
        $('total').textContent = pesos(total);
        $('resumenItems').innerHTML = items.map(it => `<div class="d-flex justify-content-between"><span>${it.cantidad}× ${esc(prod(it.producto_id).nombre)}</span><span>${pesos(it.cantidad * it.precio_unitario)}</span></div>`).join('') || '<span class="text-body-secondary">Sin productos</span>';
        if ($('pagoMonto') && !pagoTocado) $('pagoMonto').value = total || '';
        $('alertas').innerHTML = items.filter(it => it.cantidad > prod(it.producto_id).stock)
            .map(it => `<div class="alert alert-warning py-2 px-3 small mb-2"><i class="bi bi-exclamation-triangle me-1"></i>Stock insuficiente de ${esc(prod(it.producto_id).nombre)} (${prod(it.producto_id).stock} disponibles).</div>`).join('');
    }

    function toggleEntrega() {
        $('dirWrap').classList.toggle('d-none', +$('canal').value !== P.canalDomicilio);
    }

    document.querySelectorAll('[data-add]').forEach(b => b.addEventListener('click', () => {
        const p = prod(b.dataset.add);
        const ex = items.find(it => it.producto_id === p.id);
        if (ex) ex.cantidad++; else items.push({ producto_id: p.id, cantidad: 1, precio_unitario: +p.precio });
        dibujar();
    }));
    $('items').addEventListener('click', e => {
        const b = e.target.closest('[data-del]');
        if (b) { items.splice(+b.dataset.del, 1); dibujar(); }
    });
    $('items').addEventListener('input', e => {
        const t = e.target; if (!t.dataset.f) return;
        items[+t.dataset.i][t.dataset.f] = Math.max(0, +t.value || 0);
        const it = items[+t.dataset.i];
        t.closest('tr').querySelector('.sub').textContent = pesos(it.cantidad * it.precio_unitario);
        resumen();
    });
    $('descuento').addEventListener('input', resumen);
    $('cliente').addEventListener('change', e => mostrarCliente(P.clientes.find(c => c.label === e.target.value) || null));
    $('canal').addEventListener('change', toggleEntrega);
    document.querySelectorAll('input[name=medio_contacto_id]').forEach(r => r.addEventListener('change', () => {
        if (r.dataset.codigo === 'PRESENCIAL') { $('canal').value = P.canalLocal; toggleEntrega(); }
    }));
    $('registrarPago')?.addEventListener('change', e => $('pagoWrap').classList.toggle('d-none', !e.target.checked));
    $('pagoMonto')?.addEventListener('input', () => { pagoTocado = true; });
    $('formPedido').addEventListener('submit', e => {
        if (!$('clienteId').value) { e.preventDefault(); alert('Seleccioná un cliente de la lista.'); $('cliente').focus(); }
        else if (!items.length) { e.preventDefault(); alert('Agregá al menos un producto.'); }
    });

    mostrarCliente(P.clientes.find(c => c.id === +$('clienteId').value) || null);
    if ($('registrarPago')?.checked) $('pagoWrap').classList.remove('d-none');
    dibujar();
    toggleEntrega();
})();

/* Pedidos: listado, alta / edición y detalle */

const Pedidos = {
  SIGUIENTE: { 'Pendiente': 'En preparación', 'En preparación': 'En reparto', 'En reparto': 'Entregado' },

  async avanzar(p) {
    const next = Pedidos.SIGUIENTE[p.estado];
    if (!next) return;
    if (next === 'Entregado') {
      const ok = await UI.confirm(`Se marcará el pedido <b>#${fmt.nro(p.numero)}</b> como entregado. Se descontará el stock y se registrará el intercambio de garrafas.`, { title: 'Confirmar entrega', ok: 'Marcar entregado' });
      if (!ok) return;
      Ops.entregarPedido(p);
      App.save();
      UI.toast(`Pedido #${fmt.nro(p.numero)} entregado`);
      if (Q.saldoPedido(p) > 0) Cobros.nuevo({ clienteId: p.clienteId, pedidoId: p.id, onSaved: () => App.render() });
    } else {
      p.estado = next;
      App.save();
      UI.toast(`Pedido #${fmt.nro(p.numero)}: ${next}`);
    }
    App.render();
  },

  async cancelar(p) {
    const ok = await UI.confirm(`¿Cancelar el pedido <b>#${fmt.nro(p.numero)}</b>? Esta acción no se puede deshacer.`, { title: 'Cancelar pedido', ok: 'Cancelar pedido', danger: true });
    if (!ok) return;
    p.estado = 'Cancelado';
    App.save();
    UI.toast('Pedido cancelado', 'info');
    App.render();
  },
};

/* ---------- Listado ---------- */
App.route('/pedidos', v => {
  App.setTitle('Pedidos', [['Ventas'], ['Pedidos']]);
  const hoy = Q.hoy();
  const f = { desde: addDays(hoy, -29), hasta: hoy, estado: '', canal: '', pago: '' };

  v.innerHTML = `
  <div class="toolbar">
    <div class="search"><i class="bi bi-search"></i><input class="form-control form-control-sm" id="q" placeholder="Buscar por N°, cliente, domicilio o celular"></div>
    ${UI.rangoFechas('f', f.desde, f.hasta)}
    <select class="form-select form-select-sm" id="fEstado">${UI.options(ESTADOS_PEDIDO, '', { empty: 'Todos los estados' })}</select>
    <select class="form-select form-select-sm" id="fCanal">${UI.options(CANALES, '', { empty: 'Todos los canales' })}</select>
    <select class="form-select form-select-sm" id="fPago">${UI.options(['Pagado', 'Parcial', 'Impago'], '', { empty: 'Estado de pago' })}</select>
    <div class="ms-auto d-flex gap-2">
      <button class="btn btn-sm btn-light" data-action="pdf"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</button>
      <a href="#/pedidos/nuevo" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Nuevo pedido</a>
    </div>
  </div>
  <div class="row g-3 mb-3" id="resumen"></div>
  <div class="card" id="tbl"></div>`;

  const tbl = UI.table(document.getElementById('tbl'), {
    columns: [
      { label: 'N°', render: p => `<a href="#/pedidos/${p.id}" class="fw-semibold">#${fmt.nro(p.numero)}</a>` },
      { label: 'Fecha', render: p => `${fmt.date(p.fecha)}<div class="text-muted-sm">${p.hora}</div>` },
      { label: 'Cliente', render: p => { const c = Q.cliente(p.clienteId); return `${esc(Q.nombreCliente(c))}<div class="text-muted-sm">${esc(c.celular)}</div>`; } },
      { label: 'Canal', render: p => UI.canal(p.canal) },
      { label: 'Entrega', render: p => p.entrega === 'A domicilio' ? `<i class="bi bi-truck me-1 text-body-secondary"></i>Domicilio` : `<i class="bi bi-shop me-1 text-body-secondary"></i>Retira` },
      { label: 'Productos', render: p => `<span class="small">${UI.itemsResumen(p.items)}</span>` },
      { label: 'Total', cls: 'num', render: p => fmt.money(p.total) },
      { label: 'Pago', render: p => UI.pagoBadge(Q.estadoPago(p)) },
      { label: 'Estado', render: p => UI.estadoBadge(p.estado) },
      { label: '', cls: 'actions', render: p => `
        <a class="btn btn-sm btn-light" href="#/pedidos/${p.id}" title="Ver detalle"><i class="bi bi-eye"></i></a>
        <button class="btn btn-sm btn-light" data-action="pdfPedido" data-id="${p.id}" title="Descargar PDF"><i class="bi bi-file-earmark-pdf"></i></button>
        ${Pedidos.SIGUIENTE[p.estado] ? `<button class="btn btn-sm btn-light" data-action="avanzar" data-id="${p.id}" title="Pasar a ${Pedidos.SIGUIENTE[p.estado]}"><i class="bi bi-arrow-right-circle text-brand"></i></button>` : ''}` },
    ],
    search: p => { const c = Q.cliente(p.clienteId); return `${p.numero} ${fmt.nro(p.numero)} ${Q.nombreCliente(c)} ${c.domicilio} ${c.celular}`; },
    onRow: p => App.go(`#/pedidos/${p.id}`),
    empty: 'No hay pedidos para los filtros seleccionados',
  });

  function aplicar() {
    const rows = Store.db.pedidos.filter(p => p.fecha >= f.desde && p.fecha <= f.hasta
      && (!f.estado || p.estado === f.estado) && (!f.canal || p.canal === f.canal) && (!f.pago || Q.estadoPago(p) === f.pago))
      .sort((a, b) => (b.fecha + b.hora).localeCompare(a.fecha + a.hora) || b.numero - a.numero);
    tbl.setRows(rows);
    const val = rows.filter(p => p.estado !== 'Cancelado');
    const total = val.reduce((s, p) => s + p.total, 0), saldo = val.reduce((s, p) => s + Q.saldoPedido(p), 0);
    const wa = val.filter(p => p.canal === 'WhatsApp').length;
    document.getElementById('resumen').innerHTML = `
      <div class="col-6 col-lg-3">${UI.kpi({ icon: 'receipt', color: 'i-orange', label: 'Pedidos', value: fmt.num(val.length), sub: `${rows.length - val.length} cancelados` })}</div>
      <div class="col-6 col-lg-3">${UI.kpi({ icon: 'currency-dollar', color: 'i-green', label: 'Total vendido', value: fmt.money(total), sub: `Ticket promedio ${fmt.money(val.length ? total / val.length : 0)}` })}</div>
      <div class="col-6 col-lg-3">${UI.kpi({ icon: 'hourglass-split', color: 'i-red', label: 'Saldo pendiente', value: fmt.money(saldo), sub: `${val.filter(p => Q.saldoPedido(p) > 0).length} pedidos con saldo` })}</div>
      <div class="col-6 col-lg-3">${UI.kpi({ icon: 'whatsapp', color: 'i-green', label: 'Por WhatsApp', value: val.length ? fmt.pct(wa / val.length * 100) : '0%', sub: `${val.filter(p => p.canal === 'Teléfono').length} por teléfono · ${val.filter(p => p.canal === 'Local').length} en local` })}</div>`;
  }
  aplicar();

  document.getElementById('q').addEventListener('input', e => tbl.setQuery(e.target.value));
  UI.bindRango('f', (d, h) => { f.desde = d; f.hasta = h; aplicar(); });
  [['fEstado', 'estado'], ['fCanal', 'canal'], ['fPago', 'pago']].forEach(([id, k]) => document.getElementById(id).addEventListener('change', e => { f[k] = e.target.value; aplicar(); }));

  App.actions({
    avanzar: id => Pedidos.avanzar(Q.pedido(id)),
    pdfPedido: id => PDF.pedido(Q.pedido(id)),
    pdf: () => PDF.informe('Listado de pedidos', `Del ${fmt.date(f.desde)} al ${fmt.date(f.hasta)}`, [{
      head: ['N°', 'Fecha', 'Cliente', 'Canal', 'Productos', 'Total', 'Pago', 'Estado'],
      body: tbl.rows.map(p => [fmt.nro(p.numero), fmt.date(p.fecha), Q.nombreCliente(Q.cliente(p.clienteId)), p.canal, UI.itemsResumen(p.items), PDF.money(p.total), Q.estadoPago(p), p.estado]),
      columnStyles: { 5: { halign: 'right' } },
    }], 'pedidos', 'l'),
  });
});

/* ---------- Alta / edición ---------- */
function formPedido(v, pedido) {
  const db = Store.db;
  const editando = !!pedido;
  App.setTitle(editando ? `Editar pedido #${fmt.nro(pedido.numero)}` : 'Nuevo pedido', [['Ventas'], ['Pedidos', '#/pedidos'], [editando ? 'Editar' : 'Nuevo']]);

  const st = editando ? JSON.parse(JSON.stringify(pedido)) : {
    clienteId: null, canal: 'WhatsApp', entrega: 'A domicilio', direccion: '', fecha: Q.hoy(),
    hora: new Date().toTimeString().slice(0, 5), items: [], formaPago: 'Efectivo', observaciones: '', estado: 'Pendiente',
  };
  st.pagaAhora = false; st.montoPago = 0; st.referencia = ''; st.entregadoYa = false;

  const clientes = db.clientes.filter(c => c.activo).sort((a, b) => Q.nombreCliente(a).localeCompare(Q.nombreCliente(b)));
  const labelCli = c => `${Q.nombreCliente(c)} · ${c.celular}`;
  const grupos = Object.entries(CATEGORIAS).map(([k, nombre]) => ({ nombre, k, prods: db.productos.filter(p => p.categoria === k && p.activo) }));

  v.innerHTML = `
  <div class="row g-3">
    <div class="col-xl-8">
      <div class="card mb-3">
        <div class="card-header"><h2>1. Cliente</h2><div class="ms-auto"><button class="btn btn-sm btn-light" data-action="nuevoCliente"><i class="bi bi-person-plus"></i> Nuevo cliente</button></div></div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-7">
              <label class="form-label req">Buscar cliente</label>
              <input class="form-control" id="cliente" list="dlClientes" placeholder="Nombre, apellido o celular…" autocomplete="off" ${editando ? 'disabled' : ''}>
              <datalist id="dlClientes">${clientes.map(c => `<option value="${esc(labelCli(c))}">`).join('')}</datalist>
            </div>
            <div class="col-md-5">
              <label class="form-label">¿Cómo hizo el pedido?</label>
              <div class="btn-group w-100 canal-options" role="group">
                ${CANALES.map(c => `<input type="radio" class="btn-check" name="canal" id="canal${c}" value="${c}" ${st.canal === c ? 'checked' : ''}>
                <label class="btn btn-outline-secondary btn-sm" for="canal${c}">${UI.canal(c)}</label>`).join('')}
              </div>
            </div>
          </div>
          <div id="clienteInfo" class="mt-3"></div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header"><h2>2. Productos</h2><small class="text-body-secondary ms-2">Tocá un producto para agregarlo</small></div>
        <div class="card-body">
          ${grupos.map(g => `<div class="section-title mt-0">${g.nombre}</div><div class="prod-grid mb-3">${g.prods.map(p => {
            const stock = p.envase ? db.envases[p.id].llenas : p.stock;
            return `<button type="button" class="prod-btn cat-${p.categoria} ${stock <= 0 ? 'out' : ''}" data-action="addItem" data-id="${p.id}">
              <i class="bi bi-${UI.prodIcon(p)} pi"></i><b>${esc(p.nombre.replace(' para hogar', ''))}</b><small>${fmt.money(p.precio)} · stock ${stock}</small></button>`;
          }).join('')}</div>`).join('')}
          <div class="table-responsive border rounded"><table class="table align-middle mb-0">
            <thead><tr><th>Producto</th><th style="width:110px">Cantidad</th><th style="width:150px" title="Garrafas vacías que entrega el cliente a cambio">Vacías recibidas</th><th class="num" style="width:130px">Precio</th><th class="num">Subtotal</th><th></th></tr></thead>
            <tbody id="items"></tbody>
          </table></div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h2>3. Entrega</h2></div>
        <div class="card-body row g-3">
          <div class="col-md-4">
            <label class="form-label">Tipo de entrega</label>
            <select class="form-select" id="entrega">${UI.options(['A domicilio', 'Retira en local'], st.entrega)}</select>
          </div>
          <div class="col-md-4"><label class="form-label">Fecha</label><input type="date" class="form-control" id="fecha" value="${st.fecha}"></div>
          <div class="col-md-4"><label class="form-label">Hora</label><input type="time" class="form-control" id="hora" value="${st.hora}"></div>
          <div class="col-12" id="dirWrap"><label class="form-label">Dirección de entrega</label><input class="form-control" id="direccion" value="${esc(st.direccion)}" placeholder="Se completa con el domicilio del cliente"></div>
          <div class="col-12"><label class="form-label">Observaciones</label><textarea class="form-control" id="obs" rows="2" placeholder="Ej.: tocar timbre, dejar en portería, cambio de $20.000…">${esc(st.observaciones)}</textarea></div>
        </div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="card summary-card">
        <div class="card-header"><h2>Resumen</h2></div>
        <div class="card-body">
          <div id="resumenItems" class="small mb-3"></div>
          <div class="d-flex justify-content-between align-items-end border-top pt-3">
            <span class="text-body-secondary">Total</span><span class="summary-total" id="total">$ 0</span>
          </div>
          <div class="section-title">Pago</div>
          <label class="form-label">Forma de pago</label>
          <select class="form-select mb-2" id="formaPago">${UI.options(FORMAS_PAGO, st.formaPago)}</select>
          ${editando ? '' : `
          <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" id="pagaAhora"><label class="form-check-label" for="pagaAhora">Registrar pago ahora</label></div>
          <div id="pagoWrap" class="d-none">
            <div class="input-group mb-2"><span class="input-group-text">$</span><input type="number" min="0" step="100" class="form-control" id="montoPago"></div>
            <input class="form-control mb-2" id="referencia" placeholder="N° de operación (transferencia)">
          </div>
          <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" id="entregadoYa"><label class="form-check-label" for="entregadoYa">Entregado en el momento</label></div>`}
          <div id="alertas"></div>
          <div class="d-grid gap-2">
            <button class="btn btn-primary" data-action="guardar"><i class="bi bi-check2-circle"></i> ${editando ? 'Guardar cambios' : 'Registrar pedido'}</button>
            <button class="btn btn-outline-primary" data-action="guardarPdf"><i class="bi bi-file-earmark-pdf"></i> ${editando ? 'Guardar' : 'Registrar'} y descargar PDF</button>
            <a class="btn btn-light" href="${editando ? `#/pedidos/${pedido.id}` : '#/pedidos'}">Cancelar</a>
          </div>
        </div>
      </div>
    </div>
  </div>`;

  const $ = id => document.getElementById(id);

  function renderCliente() {
    const c = Q.cliente(st.clienteId);
    if (!c) { $('clienteInfo').innerHTML = '<div class="text-muted-sm"><i class="bi bi-info-circle"></i> Seleccioná un cliente para ver su domicilio, garrafas en su poder y saldo.</div>'; return; }
    const saldo = Q.saldoCliente(c.id), r = Q.regularidad(c.id);
    $('clienteInfo').innerHTML = `<div class="border rounded p-3 bg-body-tertiary"><div class="row g-2">
      <div class="col-md-6"><dl class="info-list">
        <dt>Cliente</dt><dd><a href="#/clientes/${c.id}" target="_blank" class="fw-semibold">${esc(Q.nombreCliente(c))}</a></dd>
        <dt>Celular</dt><dd>${esc(c.celular)} <a href="${UI.waLink(c.celular)}" target="_blank" rel="noopener"><i class="bi bi-whatsapp text-success"></i></a></dd>
        <dt>Domicilio</dt><dd>${esc(c.domicilio)}, ${esc(c.barrio)}${c.referencia ? `<div class="text-muted-sm">${esc(c.referencia)}</div>` : ''}</dd></dl></div>
      <div class="col-md-6"><dl class="info-list">
        <dt>Paga con</dt><dd>${UI.formaPago(c.formaPago)}</dd>
        <dt>Garrafas</dt><dd>${Q.garrafas().filter(g => c.envases[g.id]).map(g => `${c.envases[g.id]}× ${g.pesoKg} kg`).join(', ') || 'Ninguna'}</dd>
        <dt>Saldo</dt><dd class="${saldo ? 'text-danger fw-semibold' : 'text-success'}">${saldo ? fmt.money(saldo) + ' adeudado' : 'Sin deuda'}</dd>
        <dt>Regularidad</dt><dd>${r.promedio ? `Cada ${r.promedio} días · último ${fmt.date(r.ultimo)}` : 'Sin datos'}</dd></dl></div>
    </div></div>`;
  }

  function renderItems() {
    $('items').innerHTML = st.items.length ? st.items.map((it, i) => {
      const p = Q.producto(it.productoId);
      return `<tr><td><i class="bi bi-${UI.prodIcon(p)} me-1 text-body-secondary"></i>${esc(p.nombre)}</td>
        <td><input type="number" min="1" class="form-control form-control-sm" data-i="${i}" data-f="cantidad" value="${it.cantidad}"></td>
        <td>${p.envase ? `<input type="number" min="0" class="form-control form-control-sm" data-i="${i}" data-f="devueltos" value="${it.devueltos}">` : '<span class="text-body-tertiary">No aplica</span>'}</td>
        <td><input type="number" min="0" step="100" class="form-control form-control-sm text-end" data-i="${i}" data-f="precio" value="${it.precio}"></td>
        <td class="num fw-semibold">${fmt.money(it.cantidad * it.precio)}</td>
        <td class="actions"><button class="btn btn-sm btn-light" data-action="delItem" data-id="${i}" title="Quitar"><i class="bi bi-trash text-danger"></i></button></td></tr>`;
    }).join('') : '<tr><td colspan="6" class="empty"><i class="bi bi-basket"></i>Todavía no agregaste productos</td></tr>';
    renderResumen();
  }

  function renderResumen() {
    const total = st.items.reduce((s, it) => s + it.cantidad * it.precio, 0);
    st.total = total;
    $('total').textContent = fmt.money(total);
    $('resumenItems').innerHTML = st.items.map(it => `<div class="d-flex justify-content-between"><span>${it.cantidad}× ${esc(Q.producto(it.productoId).nombre)}</span><span>${fmt.money(it.cantidad * it.precio)}</span></div>`).join('') || '<span class="text-body-secondary">Sin productos</span>';
    if ($('montoPago') && !$('montoPago').dataset.touched) $('montoPago').value = total;
    const al = [];
    st.items.forEach(it => {
      const p = Q.producto(it.productoId);
      const stock = p.envase ? db.envases[p.id].llenas : p.stock;
      if (it.cantidad > stock) al.push(`Stock insuficiente de ${p.nombre} (${stock} disponibles).`);
      if (p.envase && it.devueltos < it.cantidad) al.push(`El cliente se queda con ${it.cantidad - it.devueltos} garrafa(s) de ${p.pesoKg} kg sin devolver envase.`);
    });
    $('alertas').innerHTML = al.map(a => `<div class="alert alert-warning py-2 px-3 small mb-2"><i class="bi bi-exclamation-triangle me-1"></i>${esc(a)}</div>`).join('');
  }

  function setCliente(c) {
    st.clienteId = c ? c.id : null;
    if (c) {
      $('cliente').value = labelCli(c);
      if (!editando || !st.direccion) $('direccion').value = st.direccion = `${c.domicilio}, ${c.barrio}`;
      $('formaPago').value = st.formaPago = c.formaPago;
    }
    renderCliente();
  }

  function toggleEntrega() {
    st.entrega = $('entrega').value;
    $('dirWrap').classList.toggle('d-none', st.entrega !== 'A domicilio');
  }

  if (editando) setCliente(Q.cliente(st.clienteId)); else renderCliente();
  const pre = new URLSearchParams(location.hash.split('?')[1] || '').get('cliente');
  if (pre && !editando) setCliente(Q.cliente(pre));
  renderItems(); toggleEntrega();

  $('cliente').addEventListener('change', e => {
    const c = clientes.find(x => labelCli(x) === e.target.value);
    setCliente(c || null);
  });
  $('entrega').addEventListener('change', toggleEntrega);
  v.querySelectorAll('input[name=canal]').forEach(r => r.addEventListener('change', () => {
    st.canal = r.value;
    if (r.value === 'Local') { $('entrega').value = 'Retira en local'; toggleEntrega(); }
  }));
  $('items').addEventListener('input', e => {
    const t = e.target; if (!t.dataset.f) return;
    st.items[+t.dataset.i][t.dataset.f] = Math.max(0, +t.value || 0);
    const it = st.items[+t.dataset.i];
    t.closest('tr').querySelector('.fw-semibold').textContent = fmt.money(it.cantidad * it.precio);
    renderResumen();
  });
  if (!editando) {
    $('pagaAhora').addEventListener('change', e => $('pagoWrap').classList.toggle('d-none', !e.target.checked));
    $('montoPago').addEventListener('input', e => { e.target.dataset.touched = '1'; });
  }

  function guardar(conPdf) {
    if (!st.clienteId) { UI.toast('Seleccioná un cliente', 'error'); $('cliente').focus(); return; }
    if (!st.items.length) { UI.toast('Agregá al menos un producto', 'error'); return; }
    if (st.items.some(it => it.cantidad < 1)) { UI.toast('Revisá las cantidades', 'error'); return; }
    const data = {
      clienteId: st.clienteId, canal: st.canal, entrega: st.entrega,
      direccion: st.entrega === 'A domicilio' ? $('direccion').value.trim() : '',
      fecha: $('fecha').value || Q.hoy(), hora: $('hora').value, formaPago: $('formaPago').value,
      observaciones: $('obs').value.trim(),
      items: st.items.map(it => ({ productoId: it.productoId, cantidad: +it.cantidad, precio: +it.precio, devueltos: Q.producto(it.productoId).envase ? Math.min(+it.devueltos, +it.cantidad) : 0 })),
    };
    data.total = data.items.reduce((s, it) => s + it.cantidad * it.precio, 0);
    let p;
    if (editando) {
      p = Object.assign(pedido, data);
    } else {
      p = { id: Store.nextId('pedidos'), numero: db.pedidos.reduce((m, x) => Math.max(m, x.numero), 0) + 1, estado: 'Pendiente', usuarioId: App.currentUser().id, ...data };
      db.pedidos.push(p);
      if ($('entregadoYa').checked) Ops.entregarPedido(p);
      if ($('pagaAhora').checked && +$('montoPago').value > 0) {
        const monto = Math.min(+$('montoPago').value, p.total);
        db.pagos.push({ id: Store.nextId('pagos'), recibo: db.pagos.reduce((m, x) => Math.max(m, x.recibo), 0) + 1, fecha: Q.hoy(), clienteId: p.clienteId, pedidoId: p.id, monto, formaPago: data.formaPago, referencia: $('referencia').value.trim(), usuarioId: App.currentUser().id });
      }
    }
    App.save();
    UI.toast(editando ? 'Pedido actualizado' : `Pedido #${fmt.nro(p.numero)} registrado`);
    if (conPdf) PDF.pedido(p);
    App.go(`#/pedidos/${p.id}`);
  }

  App.actions({
    addItem: id => {
      const p = Q.producto(id);
      const ex = st.items.find(it => it.productoId === p.id);
      if (ex) { ex.cantidad++; if (p.envase) ex.devueltos++; } else st.items.push({ productoId: p.id, cantidad: 1, devueltos: p.envase ? 1 : 0, precio: p.precio });
      renderItems();
    },
    delItem: i => { st.items.splice(+i, 1); renderItems(); },
    nuevoCliente: () => Clientes.form(null, c => {
      const opt = document.createElement('option'); opt.value = labelCli(c); $('dlClientes').appendChild(opt);
      clientes.push(c); setCliente(c);
    }),
    guardar: () => guardar(false),
    guardarPdf: () => guardar(true),
  });
}

App.route('/pedidos/nuevo', v => formPedido(v, null));
App.route('/pedidos/:id/editar', (v, { id }) => {
  const p = Q.pedido(id);
  if (!p || ['Entregado', 'Cancelado'].includes(p.estado)) { App.go(`#/pedidos/${id}`); return; }
  formPedido(v, p);
});

/* ---------- Detalle ---------- */
App.route('/pedidos/:id', (v, { id }) => {
  const p = Q.pedido(id);
  if (!p) { v.innerHTML = '<div class="card card-body empty">Pedido inexistente</div>'; return; }
  const c = Q.cliente(p.clienteId);
  App.setTitle(`Pedido #${fmt.nro(p.numero)}`, [['Ventas'], ['Pedidos', '#/pedidos'], [`#${fmt.nro(p.numero)}`]]);
  const pagos = Store.db.pagos.filter(x => x.pedidoId === p.id);
  const pagado = Q.pagadoPedido(p.id), saldo = Q.saldoPedido(p);
  const pasos = ['Pendiente', 'En preparación', 'En reparto', 'Entregado'];
  const idx = pasos.indexOf(p.estado);
  const next = Pedidos.SIGUIENTE[p.estado];
  const abierto = !['Entregado', 'Cancelado'].includes(p.estado);
  const u = Q.usuario(p.usuarioId);

  v.innerHTML = `
  <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    ${UI.estadoBadge(p.estado)} ${UI.pagoBadge(Q.estadoPago(p))}
    <span class="text-body-secondary small">Registrado por ${esc(Q.nombreCliente(u))} · ${UI.canal(p.canal)}</span>
    <div class="ms-auto d-flex flex-wrap gap-2">
      ${abierto ? `<a class="btn btn-sm btn-light" href="#/pedidos/${p.id}/editar"><i class="bi bi-pencil"></i> Editar</a>
        <button class="btn btn-sm btn-light text-danger" data-action="cancelar"><i class="bi bi-x-circle"></i> Cancelar</button>` : ''}
      <a class="btn btn-sm btn-light" href="${UI.waLink(c.celular)}" target="_blank" rel="noopener"><i class="bi bi-whatsapp text-success"></i> WhatsApp</a>
      <button class="btn btn-sm btn-light" data-action="pdf"><i class="bi bi-file-earmark-pdf"></i> PDF</button>
      ${saldo > 0 ? `<button class="btn btn-sm btn-outline-primary" data-action="pagar"><i class="bi bi-cash-coin"></i> Registrar pago</button>` : ''}
      ${next ? `<button class="btn btn-sm btn-primary" data-action="avanzar"><i class="bi bi-arrow-right-circle"></i> Pasar a ${next}</button>` : ''}
    </div>
  </div>

  ${p.estado !== 'Cancelado' ? `<div class="card mb-3"><div class="card-body"><div class="stepper">
    ${pasos.map((s, i) => `<div class="step ${i <= idx ? 'done' : ''}"><div class="dot"><i class="bi bi-${['clock', 'box-seam', 'truck', 'check-lg'][i]}"></i></div>${s}</div>`).join('')}
  </div></div></div>` : `<div class="alert alert-secondary"><i class="bi bi-x-circle me-1"></i>Este pedido fue cancelado.</div>`}

  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card mb-3">
        <div class="card-header"><h2>Productos</h2></div>
        <div class="table-responsive"><table class="table align-middle">
          <thead><tr><th>Producto</th><th class="num">Cantidad</th><th class="num">Vacías recibidas</th><th class="num">Precio</th><th class="num">Subtotal</th></tr></thead>
          <tbody>${p.items.map(it => { const pr = Q.producto(it.productoId); return `<tr><td><i class="bi bi-${UI.prodIcon(pr)} me-1 text-body-secondary"></i>${esc(pr.nombre)}</td><td class="num">${it.cantidad}</td><td class="num">${pr.envase ? it.devueltos : '—'}</td><td class="num">${fmt.money(it.precio)}</td><td class="num fw-semibold">${fmt.money(it.cantidad * it.precio)}</td></tr>`; }).join('')}</tbody>
          <tfoot><tr><th colspan="4" class="text-end">Total</th><th class="num fs-5">${fmt.money(p.total)}</th></tr></tfoot>
        </table></div>
      </div>
      <div class="card">
        <div class="card-header"><h2>Pagos del pedido</h2><div class="ms-auto">${saldo > 0 ? `<button class="btn btn-sm btn-primary" data-action="pagar"><i class="bi bi-plus-lg"></i> Registrar pago</button>` : ''}</div></div>
        <div class="table-responsive"><table class="table align-middle">
          <thead><tr><th>Recibo</th><th>Fecha</th><th>Forma</th><th>Referencia</th><th class="num">Monto</th><th></th></tr></thead>
          <tbody>${pagos.length ? pagos.map(pg => `<tr><td>#${fmt.nro(pg.recibo)}</td><td>${fmt.date(pg.fecha)}</td><td>${UI.formaPago(pg.formaPago)}</td><td>${esc(pg.referencia) || '—'}</td><td class="num">${fmt.money(pg.monto)}</td>
            <td class="actions"><button class="btn btn-sm btn-light" data-action="recibo" data-id="${pg.id}" title="Recibo PDF"><i class="bi bi-file-earmark-pdf"></i></button></td></tr>`).join('')
            : '<tr><td colspan="6" class="empty">Sin pagos registrados</td></tr>'}</tbody>
        </table></div>
        <div class="card-body border-top d-flex justify-content-end gap-4">
          <span>Pagado: <b class="text-success">${fmt.money(pagado)}</b></span><span>Saldo: <b class="${saldo ? 'text-danger' : ''}">${fmt.money(saldo)}</b></span>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card mb-3">
        <div class="card-header"><h2>Cliente</h2><div class="ms-auto"><a href="#/clientes/${c.id}" class="btn btn-sm btn-light">Ver ficha</a></div></div>
        <div class="card-body"><dl class="info-list">
          <dt>Nombre</dt><dd class="fw-semibold">${esc(Q.nombreCliente(c))}</dd>
          <dt>Celular</dt><dd>${esc(c.celular)}</dd>
          <dt>Domicilio</dt><dd>${esc(c.domicilio)}, ${esc(c.barrio)}</dd>
          ${c.referencia ? `<dt>Referencia</dt><dd>${esc(c.referencia)}</dd>` : ''}
          <dt>Pago habitual</dt><dd>${UI.formaPago(c.formaPago)}</dd>
        </dl></div>
      </div>
      <div class="card">
        <div class="card-header"><h2>Entrega</h2></div>
        <div class="card-body"><dl class="info-list">
          <dt>Fecha</dt><dd>${fmt.date(p.fecha)} · ${p.hora}</dd>
          <dt>Tipo</dt><dd>${esc(p.entrega)}</dd>
          ${p.direccion ? `<dt>Dirección</dt><dd>${esc(p.direccion)}</dd>` : ''}
          <dt>Forma de pago</dt><dd>${UI.formaPago(p.formaPago)}</dd>
          <dt>Observaciones</dt><dd>${esc(p.observaciones) || '—'}</dd>
        </dl></div>
      </div>
    </div>
  </div>`;

  App.actions({
    avanzar: () => Pedidos.avanzar(p),
    cancelar: () => Pedidos.cancelar(p),
    pdf: () => PDF.pedido(p),
    pagar: () => Cobros.nuevo({ clienteId: p.clienteId, pedidoId: p.id, onSaved: () => App.render() }),
    recibo: pid => PDF.recibo(Store.db.pagos.find(x => x.id === +pid)),
  });
});

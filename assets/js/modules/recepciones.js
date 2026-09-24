/* Recepciones: ingreso de garrafas llenas, carbón y leña entregados por proveedores */

const Recepciones = {
  estadoPago(r) { const pg = Q.pagadoRecepcion(r.id); return pg >= r.total ? 'Pagado' : pg > 0 ? 'Parcial' : 'Impago'; },
  ver(r) {
    const prov = Q.proveedor(r.proveedorId);
    UI.modal({
      title: `Recepción · Remito ${esc(r.remito)}`, size: 'modal-lg',
      body: `<dl class="info-list mb-3"><dt>Proveedor</dt><dd><a href="#/proveedores/${prov.id}" data-bs-dismiss="modal">${esc(prov.razonSocial)}</a></dd><dt>Fecha</dt><dd>${fmt.date(r.fecha)}</dd><dt>Estado de pago</dt><dd>${UI.pagoBadge(Recepciones.estadoPago(r))}</dd>${r.observaciones ? `<dt>Obs.</dt><dd>${esc(r.observaciones)}</dd>` : ''}</dl>
      <div class="table-responsive"><table class="table"><thead><tr><th>Producto</th><th class="num">Recibidas</th><th class="num">Vacías entregadas</th><th class="num">Costo unit.</th><th class="num">Subtotal</th></tr></thead>
      <tbody>${r.items.map(it => { const p = Q.producto(it.productoId); return `<tr><td>${esc(p.nombre)}</td><td class="num">${it.cantidad}</td><td class="num">${p.envase ? it.vaciasEntregadas : '—'}</td><td class="num">${fmt.money(it.costo)}</td><td class="num">${fmt.money(it.cantidad * it.costo)}</td></tr>`; }).join('')}</tbody>
      <tfoot><tr><th colspan="4" class="text-end">Total</th><th class="num">${fmt.money(r.total)}</th></tr><tr><td colspan="4" class="text-end">Pagado</td><td class="num text-success">${fmt.money(Q.pagadoRecepcion(r.id))}</td></tr></tfoot></table></div>`,
      footer: `<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>`,
    });
  },
};

App.route('/recepciones', v => {
  App.setTitle('Recepciones de mercadería', [['Compras'], ['Recepciones']]);
  const db = Store.db, hoy = Q.hoy();
  const f = { desde: addDays(hoy, -29), hasta: hoy, prov: '', pago: '' };
  v.innerHTML = `
  <div class="toolbar">
    ${UI.rangoFechas('f', f.desde, f.hasta)}
    <select class="form-select form-select-sm" id="fProv">${UI.options(db.proveedores, '', { value: p => p.id, label: p => p.razonSocial, empty: 'Todos los proveedores' })}</select>
    <select class="form-select form-select-sm" id="fPago">${UI.options(['Pagado', 'Parcial', 'Impago'], '', { empty: 'Estado de pago' })}</select>
    <div class="ms-auto d-flex gap-2">
      <button class="btn btn-sm btn-light" data-action="pdf"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</button>
      <a class="btn btn-sm btn-primary" href="#/recepciones/nueva"><i class="bi bi-plus-lg"></i> Nueva recepción</a>
    </div>
  </div>
  <div class="row g-3 mb-3" id="kpis"></div>
  <div class="card" id="tbl"></div>`;

  const tbl = UI.table(document.getElementById('tbl'), {
    columns: [
      { label: 'Fecha', render: r => fmt.date(r.fecha) },
      { label: 'Remito', render: r => `<span class="fw-semibold">${esc(r.remito)}</span>` },
      { label: 'Proveedor', render: r => `<a href="#/proveedores/${r.proveedorId}">${esc(Q.proveedor(r.proveedorId).razonSocial)}</a>` },
      { label: 'Productos recibidos', render: r => `<span class="small">${UI.itemsResumen(r.items)}</span>` },
      { label: 'Vacías entregadas', cls: 'num', render: r => r.items.reduce((s, it) => s + (it.vaciasEntregadas || 0), 0) || '—' },
      { label: 'Total', cls: 'num', render: r => fmt.money(r.total) },
      { label: 'Pago', render: r => UI.pagoBadge(Recepciones.estadoPago(r)) },
      { label: '', cls: 'actions', render: r => `${Recepciones.estadoPago(r) !== 'Pagado' ? `<button class="btn btn-sm btn-outline-primary" data-action="pagar" data-id="${r.id}"><i class="bi bi-wallet2"></i> Pagar</button>` : ''}` },
    ],
    onRow: r => Recepciones.ver(r),
    footer: rows => `<tr><th colspan="5" class="text-end">Total</th><th class="num">${fmt.money(rows.reduce((s, r) => s + r.total, 0))}</th><th colspan="2"></th></tr>`,
    empty: 'Sin recepciones en el período',
  });
  function aplicar() {
    const rows = db.recepciones.filter(r => r.fecha >= f.desde && r.fecha <= f.hasta && (!f.prov || r.proveedorId === +f.prov) && (!f.pago || Recepciones.estadoPago(r) === f.pago)).sort((a, b) => b.fecha.localeCompare(a.fecha));
    tbl.setRows(rows);
    const garr = rows.reduce((s, r) => s + r.items.filter(it => Q.producto(it.productoId).envase).reduce((a, it) => a + it.cantidad, 0), 0);
    const deuda = db.proveedores.reduce((s, p) => s + Q.deudaProveedor(p.id), 0);
    document.getElementById('kpis').innerHTML = `
      <div class="col-6 col-lg-3">${UI.kpi({ icon: 'box-arrow-in-down', color: 'i-blue', label: 'Recepciones', value: rows.length })}</div>
      <div class="col-6 col-lg-3">${UI.kpi({ icon: 'fuel-pump', color: 'i-orange', label: 'Garrafas recibidas', value: fmt.num(garr) })}</div>
      <div class="col-6 col-lg-3">${UI.kpi({ icon: 'bag', color: 'i-violet', label: 'Total comprado', value: fmt.money(rows.reduce((s, r) => s + r.total, 0)) })}</div>
      <div class="col-6 col-lg-3">${UI.kpi({ icon: 'exclamation-diamond', color: 'i-red', label: 'Deuda con proveedores', value: fmt.money(deuda) })}</div>`;
  }
  aplicar();
  UI.bindRango('f', (d, h) => { f.desde = d; f.hasta = h; aplicar(); });
  document.getElementById('fProv').addEventListener('change', e => { f.prov = e.target.value; aplicar(); });
  document.getElementById('fPago').addEventListener('change', e => { f.pago = e.target.value; aplicar(); });

  App.actions({
    pagar: id => { const r = Q.recepcion(id); PagosProveedores.nuevo({ proveedorId: r.proveedorId, recepcionId: r.id }); },
    pdf: () => PDF.informe('Recepciones de mercadería', `Del ${fmt.date(f.desde)} al ${fmt.date(f.hasta)}`, [{
      head: ['Fecha', 'Remito', 'Proveedor', 'Productos', 'Total', 'Pago'],
      body: tbl.rows.map(r => [fmt.date(r.fecha), r.remito, Q.proveedor(r.proveedorId).razonSocial, UI.itemsResumen(r.items), PDF.money(r.total), Recepciones.estadoPago(r)]),
      columnStyles: { 4: { halign: 'right' } },
    }], 'recepciones', 'l'),
  });
});

App.route('/recepciones/nueva', v => {
  App.setTitle('Nueva recepción', [['Compras'], ['Recepciones', '#/recepciones'], ['Nueva']]);
  const db = Store.db;
  const pre = new URLSearchParams(location.hash.split('?')[1] || '').get('proveedor');
  const items = [];

  v.innerHTML = `
  <div class="row g-3">
    <div class="col-xl-8">
      <div class="card mb-3"><div class="card-header"><h2>Datos del remito</h2></div><div class="card-body row g-3">
        <div class="col-md-6"><label class="form-label req">Proveedor</label><select class="form-select" id="prov">${UI.options(db.proveedores.filter(p => p.activo), pre, { value: p => p.id, label: p => p.razonSocial, empty: 'Seleccioná…' })}</select></div>
        <div class="col-md-3"><label class="form-label">Fecha</label><input type="date" class="form-control" id="fecha" value="${Q.hoy()}"></div>
        <div class="col-md-3"><label class="form-label">N° de remito</label><input class="form-control" id="remito" placeholder="R-0001-00000000"></div>
      </div></div>
      <div class="card"><div class="card-header"><h2>Productos recibidos</h2><div class="ms-auto"><select class="form-select form-select-sm" id="addProd">${UI.options(db.productos.filter(p => p.activo), '', { value: p => p.id, label: p => p.nombre, empty: '+ Agregar producto' })}</select></div></div>
        <div class="table-responsive"><table class="table align-middle mb-0">
          <thead><tr><th>Producto</th><th style="width:120px">Recibidas</th><th style="width:150px" title="Garrafas vacías que se lleva el proveedor">Vacías entregadas</th><th style="width:150px">Costo unit.</th><th class="num">Subtotal</th><th></th></tr></thead>
          <tbody id="items"></tbody>
        </table></div>
      </div>
    </div>
    <div class="col-xl-4"><div class="card summary-card"><div class="card-header"><h2>Resumen</h2></div><div class="card-body">
      <div id="impacto" class="small mb-3"></div>
      <div class="d-flex justify-content-between align-items-end border-top pt-3 mb-3"><span class="text-body-secondary">Total</span><span class="summary-total" id="total">$ 0</span></div>
      <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" id="pagado"><label class="form-check-label" for="pagado">Pagada en el momento</label></div>
      <select class="form-select mb-2 d-none" id="formaPago">${UI.options(FORMAS_PAGO, 'Transferencia')}</select>
      <label class="form-label">Observaciones</label><textarea class="form-control mb-3" id="obs" rows="2"></textarea>
      <div class="d-grid gap-2"><button class="btn btn-primary" data-action="guardar"><i class="bi bi-check2-circle"></i> Registrar recepción</button><a class="btn btn-light" href="#/recepciones">Cancelar</a></div>
    </div></div></div>
  </div>`;
  const $ = id => document.getElementById(id);

  function draw() {
    $('items').innerHTML = items.length ? items.map((it, i) => {
      const p = Q.producto(it.productoId);
      return `<tr><td class="fw-semibold">${esc(p.nombre)}</td>
        <td><input type="number" min="1" class="form-control form-control-sm" data-i="${i}" data-f="cantidad" value="${it.cantidad}"></td>
        <td>${p.envase ? `<input type="number" min="0" max="${db.envases[p.id].vacias}" class="form-control form-control-sm" data-i="${i}" data-f="vaciasEntregadas" value="${it.vaciasEntregadas}"><div class="text-muted-sm">Hay ${db.envases[p.id].vacias} vacías</div>` : '<span class="text-body-tertiary">No aplica</span>'}</td>
        <td><input type="number" min="0" step="100" class="form-control form-control-sm text-end" data-i="${i}" data-f="costo" value="${it.costo}"></td>
        <td class="num sub">${fmt.money(it.cantidad * it.costo)}</td>
        <td class="actions"><button class="btn btn-sm btn-light" data-action="del" data-id="${i}"><i class="bi bi-trash text-danger"></i></button></td></tr>`;
    }).join('') : '<tr><td colspan="6" class="empty"><i class="bi bi-box"></i>Agregá los productos del remito</td></tr>';
    resumen();
  }
  function resumen() {
    $('total').textContent = fmt.money(items.reduce((s, it) => s + it.cantidad * it.costo, 0));
    $('impacto').innerHTML = items.length ? '<div class="section-title mt-0">Impacto en el stock</div>' + items.map(it => {
      const p = Q.producto(it.productoId);
      if (p.envase) { const e = db.envases[p.id]; return `<div>${esc(p.nombre)}: llenas ${e.llenas} → <b>${e.llenas + it.cantidad}</b>, vacías ${e.vacias} → <b>${e.vacias - it.vaciasEntregadas}</b></div>`; }
      return `<div>${esc(p.nombre)}: stock ${p.stock} → <b>${p.stock + it.cantidad}</b></div>`;
    }).join('') : '';
  }
  draw();

  $('addProd').addEventListener('change', e => {
    const p = Q.producto(e.target.value); e.target.value = '';
    if (!p || items.some(it => it.productoId === p.id)) return;
    items.push({ productoId: p.id, cantidad: 1, vaciasEntregadas: p.envase ? 1 : 0, costo: p.costo });
    draw();
  });
  $('items').addEventListener('input', e => {
    const t = e.target; if (!t.dataset.f) return;
    const it = items[+t.dataset.i];
    it[t.dataset.f] = Math.max(0, +t.value || 0);
    if (t.dataset.f === 'cantidad' && Q.producto(it.productoId).envase) {
      it.vaciasEntregadas = Math.min(it.cantidad, db.envases[it.productoId].vacias);
      const inp = t.closest('tr').querySelector('[data-f=vaciasEntregadas]'); if (inp) inp.value = it.vaciasEntregadas;
    }
    t.closest('tr').querySelector('.sub').textContent = fmt.money(it.cantidad * it.costo);
    resumen();
  });
  $('pagado').addEventListener('change', e => $('formaPago').classList.toggle('d-none', !e.target.checked));

  App.actions({
    del: i => { items.splice(+i, 1); draw(); },
    guardar: () => {
      const provId = +$('prov').value;
      if (!provId) { UI.toast('Seleccioná el proveedor', 'error'); return; }
      if (!items.length) { UI.toast('Agregá al menos un producto', 'error'); return; }
      const falta = items.find(it => Q.producto(it.productoId).envase && it.vaciasEntregadas > db.envases[it.productoId].vacias);
      if (falta) { UI.toast(`No hay tantas vacías de ${Q.producto(falta.productoId).nombre}`, 'error'); return; }
      const r = { id: Store.nextId('recepciones'), fecha: $('fecha').value || Q.hoy(), proveedorId: provId, remito: $('remito').value.trim() || 's/n', items: items.map(it => ({ ...it })), observaciones: $('obs').value.trim(), usuarioId: App.currentUser().id };
      r.total = r.items.reduce((s, it) => s + it.cantidad * it.costo, 0);
      db.recepciones.push(r);
      Ops.recibirMercaderia(r);
      if ($('pagado').checked) db.pagosProveedores.push({ id: Store.nextId('pagosProveedores'), fecha: r.fecha, proveedorId: provId, recepcionId: r.id, monto: r.total, formaPago: $('formaPago').value, comprobante: '', observaciones: '' });
      App.save();
      UI.toast('Recepción registrada y stock actualizado');
      App.go('#/recepciones');
    },
  });
});

/* Proveedores: datos, recepciones y pagos */

const Proveedores = {
  RUBROS: { gas: 'Gas envasado', carbon: 'Carbón', lena: 'Leña', otro: 'Otro' },
  form(p, onSaved) {
    const n = p || { razonSocial: '', cuit: '', contacto: '', telefono: '', celular: '', email: '', direccion: '', rubro: 'gas', condicionPago: 'Contado', alias: '', observaciones: '', activo: true };
    UI.modal({
      title: p ? 'Editar proveedor' : 'Nuevo proveedor', size: 'modal-lg',
      body: `<div class="row g-3">
        <div class="col-md-8"><label class="form-label req">Razón social</label><input class="form-control" name="razonSocial" required value="${esc(n.razonSocial)}"></div>
        <div class="col-md-4"><label class="form-label">CUIT</label><input class="form-control" name="cuit" placeholder="30-00000000-0" value="${esc(n.cuit)}"></div>
        <div class="col-md-4"><label class="form-label">Rubro</label><select class="form-select" name="rubro">${UI.options(Object.entries(Proveedores.RUBROS), n.rubro, { value: x => x[0], label: x => x[1] })}</select></div>
        <div class="col-md-4"><label class="form-label">Persona de contacto</label><input class="form-control" name="contacto" value="${esc(n.contacto)}"></div>
        <div class="col-md-4"><label class="form-label req">Celular</label><input class="form-control" name="celular" required value="${esc(n.celular)}"></div>
        <div class="col-md-4"><label class="form-label">Teléfono</label><input class="form-control" name="telefono" value="${esc(n.telefono)}"></div>
        <div class="col-md-8"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="${esc(n.email)}"></div>
        <div class="col-12"><label class="form-label">Dirección</label><input class="form-control" name="direccion" value="${esc(n.direccion)}"></div>
        <div class="col-md-6"><label class="form-label">Condición de pago</label><input class="form-control" name="condicionPago" placeholder="Contado, 7 días, 15 días…" value="${esc(n.condicionPago)}"></div>
        <div class="col-md-6"><label class="form-label">CBU / Alias para transferencias</label><input class="form-control" name="alias" value="${esc(n.alias)}"></div>
        <div class="col-12"><label class="form-label">Observaciones</label><textarea class="form-control" name="observaciones" rows="2">${esc(n.observaciones)}</textarea></div>
        ${p ? `<div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="activo" id="provAct" ${n.activo ? 'checked' : ''}><label class="form-check-label" for="provAct">Proveedor activo</label></div></div>` : ''}
      </div>`,
      onSubmit: d => {
        let pr;
        if (p) pr = Object.assign(p, d);
        else { pr = { id: Store.nextId('proveedores'), ...d, activo: true }; Store.db.proveedores.push(pr); }
        App.save(); UI.toast('Proveedor guardado');
        onSaved ? onSaved(pr) : App.render();
      },
    });
  },
};

App.route('/proveedores', v => {
  App.setTitle('Proveedores', [['Compras'], ['Proveedores']]);
  const db = Store.db;
  v.innerHTML = `
  <div class="toolbar">
    <div class="search"><i class="bi bi-search"></i><input class="form-control form-control-sm" id="q" placeholder="Buscar proveedor, CUIT o contacto"></div>
    <div class="ms-auto d-flex gap-2"><button class="btn btn-sm btn-primary" data-action="nuevo"><i class="bi bi-plus-lg"></i> Nuevo proveedor</button></div>
  </div>
  <div class="card" id="tbl"></div>`;
  const tbl = UI.table(document.getElementById('tbl'), {
    rows: db.proveedores.slice().sort((a, b) => a.razonSocial.localeCompare(b.razonSocial)),
    columns: [
      { label: 'Proveedor', render: p => `<a href="#/proveedores/${p.id}" class="fw-semibold">${esc(p.razonSocial)}</a>${!p.activo ? ' <span class="badge-soft b-gray">Inactivo</span>' : ''}<div class="text-muted-sm">CUIT ${esc(p.cuit) || '—'}</div>` },
      { label: 'Rubro', render: p => `<span class="badge-soft b-info">${Proveedores.RUBROS[p.rubro] || p.rubro}</span>` },
      { label: 'Contacto', render: p => `${esc(p.contacto) || '—'}<div class="text-muted-sm">${esc(p.celular)} <a href="${UI.waLink(p.celular)}" target="_blank" rel="noopener"><i class="bi bi-whatsapp text-success"></i></a></div>` },
      { label: 'Condición', render: p => esc(p.condicionPago) },
      { label: 'Última recepción', render: p => { const r = db.recepciones.filter(x => x.proveedorId === p.id).map(x => x.fecha).sort().pop(); return r ? fmt.date(r) : '—'; } },
      { label: 'Saldo a pagar', cls: 'num', render: p => { const d = Q.deudaProveedor(p.id); return d ? `<span class="text-danger fw-semibold">${fmt.money(d)}</span>` : '<span class="text-success">$ 0</span>'; } },
      { label: '', cls: 'actions', render: p => `<button class="btn btn-sm btn-light" data-action="editar" data-id="${p.id}" title="Editar"><i class="bi bi-pencil"></i></button>` },
    ],
    search: p => `${p.razonSocial} ${p.cuit} ${p.contacto}`,
    onRow: p => App.go(`#/proveedores/${p.id}`),
  });
  document.getElementById('q').addEventListener('input', e => tbl.setQuery(e.target.value));
  App.actions({
    nuevo: () => Proveedores.form(null, p => App.go(`#/proveedores/${p.id}`)),
    editar: id => Proveedores.form(Q.proveedor(id)),
  });
});

App.route('/proveedores/:id', (v, { id }) => {
  const p = Q.proveedor(id);
  if (!p) { v.innerHTML = '<div class="card card-body empty">Proveedor inexistente</div>'; return; }
  const db = Store.db;
  App.setTitle(p.razonSocial, [['Compras'], ['Proveedores', '#/proveedores'], ['Ficha']]);
  const recs = db.recepciones.filter(r => r.proveedorId === p.id).sort((a, b) => b.fecha.localeCompare(a.fecha));
  const pags = db.pagosProveedores.filter(x => x.proveedorId === p.id).sort((a, b) => b.fecha.localeCompare(a.fecha));
  const deuda = Q.deudaProveedor(p.id);
  const desde = addDays(Q.hoy(), -89);
  const comprado90 = recs.filter(r => r.fecha >= desde).reduce((s, r) => s + r.total, 0);

  v.innerHTML = `
  <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <span class="badge-soft b-info">${Proveedores.RUBROS[p.rubro]}</span>
    <div class="ms-auto d-flex flex-wrap gap-2">
      <button class="btn btn-sm btn-light" data-action="editar"><i class="bi bi-pencil"></i> Editar</button>
      ${deuda ? `<button class="btn btn-sm btn-outline-primary" data-action="pagar"><i class="bi bi-wallet2"></i> Registrar pago</button>` : ''}
      <a class="btn btn-sm btn-primary" href="#/recepciones/nueva?proveedor=${p.id}"><i class="bi bi-box-arrow-in-down"></i> Nueva recepción</a>
    </div>
  </div>
  <div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">${UI.kpi({ icon: 'box-arrow-in-down', color: 'i-blue', label: 'Recepciones', value: recs.length, sub: recs[0] ? `Última: ${fmt.date(recs[0].fecha)}` : '' })}</div>
    <div class="col-6 col-lg-3">${UI.kpi({ icon: 'bag', color: 'i-orange', label: 'Comprado 90 días', value: fmt.money(comprado90) })}</div>
    <div class="col-6 col-lg-3">${UI.kpi({ icon: 'wallet2', color: 'i-green', label: 'Pagado total', value: fmt.money(pags.reduce((s, x) => s + x.monto, 0)), sub: `${pags.length} pagos` })}</div>
    <div class="col-6 col-lg-3">${UI.kpi({ icon: 'exclamation-diamond', color: deuda ? 'i-red' : 'i-green', label: 'Saldo a pagar', value: fmt.money(deuda) })}</div>
  </div>
  <div class="row g-3">
    <div class="col-lg-4"><div class="card"><div class="card-header"><h2>Datos del proveedor</h2></div><div class="card-body"><dl class="info-list">
      <dt>CUIT</dt><dd>${esc(p.cuit) || '—'}</dd><dt>Contacto</dt><dd>${esc(p.contacto) || '—'}</dd>
      <dt>Celular</dt><dd>${esc(p.celular)} <a href="${UI.waLink(p.celular)}" target="_blank" rel="noopener"><i class="bi bi-whatsapp text-success"></i></a></dd>
      <dt>Teléfono</dt><dd>${esc(p.telefono) || '—'}</dd><dt>Email</dt><dd>${esc(p.email) || '—'}</dd>
      <dt>Dirección</dt><dd>${esc(p.direccion) || '—'}</dd><dt>Condición</dt><dd>${esc(p.condicionPago)}</dd>
      <dt>CBU/Alias</dt><dd><code>${esc(p.alias) || '—'}</code></dd><dt>Obs.</dt><dd>${esc(p.observaciones) || '—'}</dd>
    </dl></div></div></div>
    <div class="col-lg-8"><div class="card">
      <div class="card-header p-0 px-2 pt-2 border-0"><ul class="nav nav-tabs w-100">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tRec" type="button">Recepciones (${recs.length})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tPag" type="button">Pagos (${pags.length})</button></li>
      </ul></div>
      <div class="tab-content"><div class="tab-pane fade show active" id="tRec"><div id="tblRec"></div></div><div class="tab-pane fade" id="tPag"><div id="tblPag"></div></div></div>
    </div></div>
  </div>`;

  UI.table(document.getElementById('tblRec'), {
    rows: recs, pageSize: 8,
    columns: [
      { label: 'Fecha', render: r => fmt.date(r.fecha) },
      { label: 'Remito', render: r => esc(r.remito) },
      { label: 'Productos', render: r => `<span class="small">${UI.itemsResumen(r.items)}</span>` },
      { label: 'Total', cls: 'num', render: r => fmt.money(r.total) },
      { label: 'Estado', render: r => { const pg = Q.pagadoRecepcion(r.id); return UI.pagoBadge(pg >= r.total ? 'Pagado' : pg > 0 ? 'Parcial' : 'Impago'); } },
    ],
    empty: 'Sin recepciones',
  });
  UI.table(document.getElementById('tblPag'), {
    rows: pags, pageSize: 8,
    columns: [
      { label: 'Fecha', render: x => fmt.date(x.fecha) },
      { label: 'Recepción', render: x => x.recepcionId ? `Remito ${esc(Q.recepcion(x.recepcionId).remito)}` : 'A cuenta' },
      { label: 'Forma', render: x => UI.formaPago(x.formaPago) },
      { label: 'Comprobante', render: x => esc(x.comprobante) || '—' },
      { label: 'Monto', cls: 'num', render: x => fmt.money(x.monto) },
    ],
    empty: 'Sin pagos',
  });

  App.actions({
    editar: () => Proveedores.form(p),
    pagar: () => PagosProveedores.nuevo({ proveedorId: p.id }),
  });
});

/* Pagos a proveedores */

const PagosProveedores = {
  nuevo({ proveedorId, recepcionId } = {}) {
    const db = Store.db;
    const pendientes = pid => db.recepciones.filter(r => r.proveedorId === +pid && Q.pagadoRecepcion(r.id) < r.total).sort((a, b) => a.fecha.localeCompare(b.fecha));
    const optsRec = pid => `<option value="">A cuenta (sin remito asociado)</option>` + pendientes(pid).map(r => `<option value="${r.id}" ${+recepcionId === r.id ? 'selected' : ''}>${fmt.date(r.fecha)} · ${esc(r.remito)} · saldo ${fmt.money(r.total - Q.pagadoRecepcion(r.id))}</option>`).join('');
    const md = UI.modal({
      title: 'Registrar pago a proveedor',
      body: `<div class="row g-3">
        <div class="col-12"><label class="form-label req">Proveedor</label><select class="form-select" name="proveedorId" required>${UI.options(db.proveedores, proveedorId, { value: p => p.id, label: p => `${p.razonSocial} — debe ${fmt.money(Q.deudaProveedor(p.id))}`, empty: 'Seleccioná…' })}</select></div>
        <div class="col-12"><label class="form-label">Recepción / remito</label><select class="form-select" name="recepcionId">${proveedorId ? optsRec(proveedorId) : ''}</select></div>
        <div class="col-12" id="aliasInfo"></div>
        <div class="col-md-6"><label class="form-label req">Importe</label><div class="input-group"><span class="input-group-text">$</span><input type="number" min="1" class="form-control" name="monto" required></div></div>
        <div class="col-md-6"><label class="form-label">Fecha</label><input type="date" class="form-control" name="fecha" value="${Q.hoy()}"></div>
        <div class="col-md-6"><label class="form-label">Forma de pago</label><select class="form-select" name="formaPago">${UI.options(FORMAS_PAGO, 'Transferencia')}</select></div>
        <div class="col-md-6"><label class="form-label">Comprobante</label><input class="form-control" name="comprobante" placeholder="N° transferencia / recibo"></div>
        <div class="col-12"><label class="form-label">Observaciones</label><input class="form-control" name="observaciones"></div>
      </div>`,
      submit: 'Registrar pago',
      onSubmit: d => {
        db.pagosProveedores.push({ id: Store.nextId('pagosProveedores'), fecha: d.fecha, proveedorId: +d.proveedorId, recepcionId: d.recepcionId ? +d.recepcionId : null, monto: +d.monto, formaPago: d.formaPago, comprobante: d.comprobante.trim(), observaciones: d.observaciones.trim() });
        App.save(); UI.toast('Pago a proveedor registrado'); App.render();
      },
    });
    const f = md.form;
    const upd = () => {
      const p = Q.proveedor(f.proveedorId.value);
      const r = f.recepcionId.value ? Q.recepcion(f.recepcionId.value) : null;
      f.monto.value = r ? r.total - Q.pagadoRecepcion(r.id) : (p ? Q.deudaProveedor(p.id) || '' : '');
      document.getElementById('aliasInfo').innerHTML = p ? `<div class="alert alert-light border small py-2 mb-0">Condición: <b>${esc(p.condicionPago)}</b> · CBU/Alias: <code>${esc(p.alias) || '—'}</code></div>` : '';
    };
    f.proveedorId.addEventListener('change', () => { f.recepcionId.innerHTML = f.proveedorId.value ? optsRec(f.proveedorId.value) : ''; upd(); });
    f.recepcionId.addEventListener('change', upd);
    upd();
  },
};

App.route('/pagos-proveedores', v => {
  App.setTitle('Pagos a proveedores', [['Compras'], ['Pagos a proveedores']]);
  const db = Store.db, hoy = Q.hoy();
  const f = { desde: addDays(hoy, -29), hasta: hoy, prov: '' };
  const deudas = db.proveedores.map(p => ({ p, deuda: Q.deudaProveedor(p.id) }));

  v.innerHTML = `
  <div class="row g-3 mb-3">
    ${deudas.map(({ p, deuda }) => `<div class="col-sm-6 col-xl-3"><div class="card kpi">
      <div class="kpi-icon ${deuda ? 'i-red' : 'i-green'}"><i class="bi bi-truck"></i></div>
      <div class="min-w-0 flex-fill"><div class="kpi-label text-truncate">${esc(p.razonSocial)}</div><div class="kpi-value">${fmt.money(deuda)}</div>
      <div class="kpi-sub">${deuda ? `<a href="#" data-action="pagar" data-id="${p.id}">Registrar pago</a>` : 'Sin deuda'}</div></div></div></div>`).join('')}
  </div>
  <div class="toolbar">
    ${UI.rangoFechas('f', f.desde, f.hasta)}
    <select class="form-select form-select-sm" id="fProv">${UI.options(db.proveedores, '', { value: p => p.id, label: p => p.razonSocial, empty: 'Todos los proveedores' })}</select>
    <div class="ms-auto d-flex gap-2">
      <button class="btn btn-sm btn-light" data-action="pdf"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</button>
      <button class="btn btn-sm btn-primary" data-action="pagar"><i class="bi bi-plus-lg"></i> Registrar pago</button>
    </div>
  </div>
  <div class="card" id="tbl"></div>`;

  const tbl = UI.table(document.getElementById('tbl'), {
    columns: [
      { label: 'Fecha', render: x => fmt.date(x.fecha) },
      { label: 'Proveedor', render: x => `<a href="#/proveedores/${x.proveedorId}">${esc(Q.proveedor(x.proveedorId).razonSocial)}</a>` },
      { label: 'Remito', render: x => x.recepcionId ? esc(Q.recepcion(x.recepcionId).remito) : 'A cuenta' },
      { label: 'Forma', render: x => UI.formaPago(x.formaPago) },
      { label: 'Comprobante', render: x => esc(x.comprobante) || '—' },
      { label: 'Importe', cls: 'num fw-semibold', render: x => fmt.money(x.monto) },
    ],
    footer: rows => `<tr><th colspan="5" class="text-end">Total pagado</th><th class="num">${fmt.money(rows.reduce((s, x) => s + x.monto, 0))}</th></tr>`,
    empty: 'Sin pagos en el período',
  });
  function aplicar() {
    tbl.setRows(db.pagosProveedores.filter(x => x.fecha >= f.desde && x.fecha <= f.hasta && (!f.prov || x.proveedorId === +f.prov)).sort((a, b) => b.fecha.localeCompare(a.fecha)));
  }
  aplicar();
  UI.bindRango('f', (d, h) => { f.desde = d; f.hasta = h; aplicar(); });
  document.getElementById('fProv').addEventListener('change', e => { f.prov = e.target.value; aplicar(); });

  App.actions({
    pagar: id => PagosProveedores.nuevo({ proveedorId: id ? +id : undefined }),
    pdf: () => PDF.informe('Pagos a proveedores', `Del ${fmt.date(f.desde)} al ${fmt.date(f.hasta)}`, [
      { titulo: 'Saldos pendientes', head: ['Proveedor', 'Condición', 'Saldo'], body: deudas.map(({ p, deuda }) => [p.razonSocial, p.condicionPago, PDF.money(deuda)]), columnStyles: { 2: { halign: 'right' } } },
      { titulo: 'Pagos realizados', head: ['Fecha', 'Proveedor', 'Remito', 'Forma', 'Comprobante', 'Importe'], body: tbl.rows.map(x => [fmt.date(x.fecha), Q.proveedor(x.proveedorId).razonSocial, x.recepcionId ? Q.recepcion(x.recepcionId).remito : 'A cuenta', x.formaPago, x.comprobante, PDF.money(x.monto)]), columnStyles: { 5: { halign: 'right' } } },
    ], 'pagos-proveedores'),
  });
});

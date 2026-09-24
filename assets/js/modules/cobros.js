/* Cobros: pagos de clientes, recibos y saldos pendientes */

const Cobros = {
  /* Registrar un pago. Si no se elige pedido, el importe se aplica a los pedidos
     impagos más antiguos del cliente y todo queda bajo un mismo recibo. */
  nuevo({ clienteId, pedidoId, onSaved } = {}) {
    const db = Store.db;
    const clientes = db.clientes.filter(c => c.activo).sort((a, b) => Q.nombreCliente(a).localeCompare(Q.nombreCliente(b)));
    const pendientes = cid => Q.pedidosCliente(cid).filter(p => Q.saldoPedido(p) > 0).sort((a, b) => a.fecha.localeCompare(b.fecha));
    const optsPedidos = cid => `<option value="">A cuenta (se aplica a los pedidos más antiguos)</option>` +
      pendientes(cid).map(p => `<option value="${p.id}" ${+pedidoId === p.id ? 'selected' : ''}>#${fmt.nro(p.numero)} · ${fmt.date(p.fecha)} · saldo ${fmt.money(Q.saldoPedido(p))}</option>`).join('');
    const c0 = Q.cliente(clienteId);
    const md = UI.modal({
      title: 'Registrar pago de cliente',
      body: `<div class="row g-3">
        <div class="col-12"><label class="form-label req">Cliente</label>
          <select class="form-select" name="clienteId" required ${c0 ? 'disabled' : ''}>${UI.options(clientes, clienteId, { value: c => c.id, label: c => `${Q.nombreCliente(c)} — saldo ${fmt.money(Q.saldoCliente(c.id))}`, empty: 'Seleccioná…' })}</select>
          <div class="invalid-feedback">Seleccioná el cliente</div></div>
        <div class="col-12"><label class="form-label">Pedido</label><select class="form-select" name="pedidoId">${c0 ? optsPedidos(c0.id) : ''}</select></div>
        <div class="col-12" id="saldoInfo"></div>
        <div class="col-md-6"><label class="form-label req">Importe</label><div class="input-group"><span class="input-group-text">$</span><input type="number" min="1" step="1" class="form-control" name="monto" required></div></div>
        <div class="col-md-6"><label class="form-label">Fecha</label><input type="date" class="form-control" name="fecha" value="${Q.hoy()}" required></div>
        <div class="col-md-6"><label class="form-label">Forma de pago</label><select class="form-select" name="formaPago">${UI.options(FORMAS_PAGO, c0 ? c0.formaPago : 'Efectivo')}</select></div>
        <div class="col-md-6"><label class="form-label">Referencia</label><input class="form-control" name="referencia" placeholder="N° de operación / comprobante"></div>
        <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="imprimir" id="impRecibo" checked><label class="form-check-label" for="impRecibo">Descargar recibo en PDF</label></div></div>
      </div>`,
      submit: 'Registrar pago',
      onSubmit: d => {
        const cid = c0 ? c0.id : +d.clienteId;
        let monto = +d.monto;
        const deuda = d.pedidoId ? Q.saldoPedido(Q.pedido(d.pedidoId)) : Q.saldoCliente(cid);
        if (monto > deuda) { UI.toast(`El importe supera el saldo adeudado (${fmt.money(deuda)})`, 'error'); return false; }
        const recibo = db.pagos.reduce((m, x) => Math.max(m, x.recibo), 0) + 1;
        const base = { recibo, fecha: d.fecha, clienteId: cid, formaPago: d.formaPago, referencia: d.referencia.trim(), usuarioId: App.currentUser().id };
        const aplicar = d.pedidoId ? [Q.pedido(d.pedidoId)] : pendientes(cid);
        let primero;
        aplicar.forEach(p => {
          if (monto <= 0) return;
          const m = Math.min(monto, Q.saldoPedido(p));
          const pg = { id: Store.nextId('pagos'), ...base, pedidoId: p.id, monto: m };
          db.pagos.push(pg); primero = primero || pg; monto -= m;
        });
        App.save();
        UI.toast(`Pago registrado · Recibo #${fmt.nro(recibo)}`);
        if (d.imprimir && primero) PDF.recibo(primero);
        onSaved && onSaved();
      },
    });
    const f = md.form;
    const refreshSaldo = () => {
      const cid = c0 ? c0.id : +f.clienteId.value;
      const p = f.pedidoId.value ? Q.pedido(f.pedidoId.value) : null;
      const saldo = p ? Q.saldoPedido(p) : (cid ? Q.saldoCliente(cid) : 0);
      f.monto.value = saldo || '';
      document.getElementById('saldoInfo').innerHTML = cid ? `<div class="alert alert-light border py-2 mb-0 small">Saldo ${p ? 'del pedido' : 'total del cliente'}: <b>${fmt.money(saldo)}</b></div>` : '';
    };
    f.clienteId.addEventListener('change', () => {
      const c = Q.cliente(f.clienteId.value);
      f.pedidoId.innerHTML = c ? optsPedidos(c.id) : '';
      if (c) f.formaPago.value = c.formaPago;
      refreshSaldo();
    });
    f.pedidoId.addEventListener('change', refreshSaldo);
    refreshSaldo();
  },
};

App.route('/cobros', v => {
  App.setTitle('Cobros', [['Ventas'], ['Cobros']]);
  const db = Store.db, hoy = Q.hoy();
  const f = { desde: addDays(hoy, -29), hasta: hoy, forma: '' };

  v.innerHTML = `
  <div class="row g-3 mb-3" id="kpis"></div>
  <div class="card">
    <div class="card-header p-0 px-2 pt-2 border-0">
      <ul class="nav nav-tabs w-100">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tPagos" type="button"><i class="bi bi-receipt me-1"></i>Pagos recibidos</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tPend" type="button"><i class="bi bi-hourglass-split me-1"></i>Pendientes de cobro</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tDeud" type="button"><i class="bi bi-people me-1"></i>Saldos por cliente</button></li>
      </ul>
    </div>
    <div class="tab-content">
      <div class="tab-pane fade show active" id="tPagos">
        <div class="toolbar p-3 pb-0">
          <div class="search"><i class="bi bi-search"></i><input class="form-control form-control-sm" id="q" placeholder="Buscar cliente, recibo o referencia"></div>
          ${UI.rangoFechas('f', f.desde, f.hasta)}
          <select class="form-select form-select-sm" id="fForma">${UI.options(FORMAS_PAGO, '', { empty: 'Todas las formas' })}</select>
          <div class="ms-auto d-flex gap-2">
            <button class="btn btn-sm btn-light" data-action="pdf"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</button>
            <button class="btn btn-sm btn-primary" data-action="nuevo"><i class="bi bi-plus-lg"></i> Registrar pago</button>
          </div>
        </div>
        <div id="tblPagos"></div>
      </div>
      <div class="tab-pane fade" id="tPend"><div id="tblPend"></div></div>
      <div class="tab-pane fade" id="tDeud"><div id="tblDeud"></div></div>
    </div>
  </div>`;

  const tbl = UI.table(document.getElementById('tblPagos'), {
    columns: [
      { label: 'Recibo', render: p => `<span class="fw-semibold">#${fmt.nro(p.recibo)}</span>` },
      { label: 'Fecha', render: p => fmt.date(p.fecha) },
      { label: 'Cliente', render: p => `<a href="#/clientes/${p.clienteId}">${esc(Q.nombreCliente(Q.cliente(p.clienteId)))}</a>` },
      { label: 'Pedido', render: p => `<a href="#/pedidos/${p.pedidoId}">#${fmt.nro(Q.pedido(p.pedidoId).numero)}</a>` },
      { label: 'Forma', render: p => UI.formaPago(p.formaPago) },
      { label: 'Referencia', render: p => esc(p.referencia) || '<span class="text-body-tertiary">—</span>' },
      { label: 'Recibió', render: p => esc(Q.usuario(p.usuarioId)?.nombre || '') },
      { label: 'Importe', cls: 'num fw-semibold', render: p => fmt.money(p.monto) },
      { label: '', cls: 'actions', render: p => `<button class="btn btn-sm btn-light" data-action="recibo" data-id="${p.id}" title="Recibo PDF"><i class="bi bi-file-earmark-pdf"></i></button>` },
    ],
    search: p => `${p.recibo} ${Q.nombreCliente(Q.cliente(p.clienteId))} ${p.referencia}`,
    footer: rows => `<tr><th colspan="7" class="text-end">Total</th><th class="num">${fmt.money(rows.reduce((s, p) => s + p.monto, 0))}</th><th></th></tr>`,
    empty: 'No hay pagos en el período',
  });

  function aplicar() {
    const rows = db.pagos.filter(p => p.fecha >= f.desde && p.fecha <= f.hasta && (!f.forma || p.formaPago === f.forma))
      .sort((a, b) => b.fecha.localeCompare(a.fecha) || b.recibo - a.recibo);
    tbl.setRows(rows);
    const tot = rows.reduce((s, p) => s + p.monto, 0);
    const ef = rows.filter(p => p.formaPago === 'Efectivo').reduce((s, p) => s + p.monto, 0);
    const pend = db.pedidos.reduce((s, p) => s + Q.saldoPedido(p), 0);
    const hoyTot = db.pagos.filter(p => p.fecha === hoy).reduce((s, p) => s + p.monto, 0);
    document.getElementById('kpis').innerHTML = `
      <div class="col-6 col-lg-3">${UI.kpi({ icon: 'calendar-check', color: 'i-orange', label: 'Cobrado hoy', value: fmt.money(hoyTot), sub: `${db.pagos.filter(p => p.fecha === hoy).length} pagos` })}</div>
      <div class="col-6 col-lg-3">${UI.kpi({ icon: 'cash-stack', color: 'i-green', label: 'Cobrado en el período', value: fmt.money(tot), sub: `${fmt.date(f.desde)} – ${fmt.date(f.hasta)}` })}</div>
      <div class="col-6 col-lg-3">${UI.kpi({ icon: 'pie-chart', color: 'i-blue', label: 'Efectivo / Transferencia', value: tot ? `${fmt.pct(ef / tot * 100)} / ${fmt.pct((tot - ef) / tot * 100)}` : '—', sub: `${fmt.money(ef)} · ${fmt.money(tot - ef)}` })}</div>
      <div class="col-6 col-lg-3">${UI.kpi({ icon: 'exclamation-diamond', color: 'i-red', label: 'Pendiente de cobro', value: fmt.money(pend), sub: `${db.pedidos.filter(p => Q.saldoPedido(p) > 0).length} pedidos` })}</div>`;
  }
  aplicar();

  UI.table(document.getElementById('tblPend'), {
    rows: db.pedidos.filter(p => Q.saldoPedido(p) > 0).sort((a, b) => a.fecha.localeCompare(b.fecha)),
    columns: [
      { label: 'Pedido', render: p => `<a href="#/pedidos/${p.id}" class="fw-semibold">#${fmt.nro(p.numero)}</a>` },
      { label: 'Fecha', render: p => `${fmt.date(p.fecha)}<div class="text-muted-sm">hace ${daysBetween(p.fecha, hoy)} días</div>` },
      { label: 'Cliente', render: p => { const c = Q.cliente(p.clienteId); return `<a href="#/clientes/${c.id}">${esc(Q.nombreCliente(c))}</a><div class="text-muted-sm">${esc(c.celular)}</div>`; } },
      { label: 'Estado', render: p => UI.estadoBadge(p.estado) },
      { label: 'Total', cls: 'num', render: p => fmt.money(p.total) },
      { label: 'Pagado', cls: 'num', render: p => fmt.money(Q.pagadoPedido(p.id)) },
      { label: 'Saldo', cls: 'num fw-semibold text-danger', render: p => fmt.money(Q.saldoPedido(p)) },
      { label: '', cls: 'actions', render: p => `<a class="btn btn-sm btn-light" href="${UI.waLink(Q.cliente(p.clienteId).celular)}" target="_blank" rel="noopener" title="Recordar por WhatsApp"><i class="bi bi-whatsapp text-success"></i></a>
        <button class="btn btn-sm btn-outline-primary" data-action="cobrar" data-id="${p.id}"><i class="bi bi-cash-coin"></i> Cobrar</button>` },
    ],
    footer: rows => `<tr><th colspan="6" class="text-end">Total pendiente</th><th class="num text-danger">${fmt.money(rows.reduce((s, p) => s + Q.saldoPedido(p), 0))}</th><th></th></tr>`,
    empty: 'No hay pedidos pendientes de cobro',
  });

  UI.table(document.getElementById('tblDeud'), {
    rows: db.clientes.map(c => ({ c, saldo: Q.saldoCliente(c.id), peds: Q.pedidosCliente(c.id).filter(p => Q.saldoPedido(p) > 0) })).filter(x => x.saldo > 0).sort((a, b) => b.saldo - a.saldo),
    columns: [
      { label: 'Cliente', render: x => `<a href="#/clientes/${x.c.id}" class="fw-semibold">${esc(Q.nombreCliente(x.c))}</a>` },
      { label: 'Celular', render: x => esc(x.c.celular) },
      { label: 'Pago habitual', render: x => UI.formaPago(x.c.formaPago) },
      { label: 'Pedidos adeudados', cls: 'num', render: x => x.peds.length },
      { label: 'Deuda más antigua', render: x => fmt.date(x.peds.map(p => p.fecha).sort()[0]) },
      { label: 'Saldo', cls: 'num fw-semibold text-danger', render: x => fmt.money(x.saldo) },
      { label: '', cls: 'actions', render: x => `<button class="btn btn-sm btn-outline-primary" data-action="cobrarCliente" data-id="${x.c.id}"><i class="bi bi-cash-coin"></i> Cobrar</button>` },
    ],
    empty: 'Ningún cliente tiene saldo pendiente',
  });

  document.getElementById('q').addEventListener('input', e => tbl.setQuery(e.target.value));
  UI.bindRango('f', (d, h) => { f.desde = d; f.hasta = h; aplicar(); });
  document.getElementById('fForma').addEventListener('change', e => { f.forma = e.target.value; aplicar(); });

  App.actions({
    nuevo: () => Cobros.nuevo({ onSaved: () => App.render() }),
    cobrar: id => { const p = Q.pedido(id); Cobros.nuevo({ clienteId: p.clienteId, pedidoId: p.id, onSaved: () => App.render() }); },
    cobrarCliente: id => Cobros.nuevo({ clienteId: +id, onSaved: () => App.render() }),
    recibo: id => PDF.recibo(db.pagos.find(p => p.id === +id)),
    pdf: () => PDF.informe('Pagos recibidos', `Del ${fmt.date(f.desde)} al ${fmt.date(f.hasta)}`, [{
      head: ['Recibo', 'Fecha', 'Cliente', 'Pedido', 'Forma', 'Referencia', 'Importe'],
      body: tbl.rows.map(p => [fmt.nro(p.recibo), fmt.date(p.fecha), Q.nombreCliente(Q.cliente(p.clienteId)), p.pedidoId ? fmt.nro(Q.pedido(p.pedidoId).numero) : 'A cuenta', p.formaPago, p.referencia || '', PDF.money(p.monto)]),
      columnStyles: { 6: { halign: 'right' } },
    }], 'pagos-recibidos'),
  });
});

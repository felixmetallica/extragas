/* Clientes: listado, ficha, cuenta corriente y garrafas en su poder */

const Clientes = {
  form(c, onSaved) {
    const n = c || { nombre: '', apellido: '', tipo: 'Particular', dni: '', celular: '', telefono: '', domicilio: '', barrio: '', referencia: '', formaPago: 'Efectivo', observaciones: '', activo: true };
    UI.modal({
      title: c ? 'Editar cliente' : 'Nuevo cliente', size: 'modal-lg',
      body: `<div class="row g-3">
        <div class="col-md-4"><label class="form-label">Tipo</label><select class="form-select" name="tipo">${UI.options(['Particular', 'Comercio'], n.tipo)}</select></div>
        <div class="col-md-4"><label class="form-label req">Nombre / Razón social</label><input class="form-control" name="nombre" required value="${esc(n.nombre)}"><div class="invalid-feedback">Ingresá el nombre</div></div>
        <div class="col-md-4"><label class="form-label">Apellido</label><input class="form-control" name="apellido" value="${esc(n.apellido)}"></div>
        <div class="col-md-4"><label class="form-label">DNI / CUIT</label><input class="form-control" name="dni" value="${esc(n.dni)}"></div>
        <div class="col-md-4"><label class="form-label req">Celular</label><input class="form-control" name="celular" required placeholder="381 555-1234" value="${esc(n.celular)}"><div class="invalid-feedback">El celular es obligatorio</div></div>
        <div class="col-md-4"><label class="form-label">Teléfono alternativo</label><input class="form-control" name="telefono" value="${esc(n.telefono)}"></div>
        <div class="col-md-8"><label class="form-label req">Domicilio</label><input class="form-control" name="domicilio" required placeholder="Calle y número" value="${esc(n.domicilio)}"><div class="invalid-feedback">El domicilio es obligatorio</div></div>
        <div class="col-md-4"><label class="form-label">Barrio / Localidad</label><input class="form-control" name="barrio" value="${esc(n.barrio)}"></div>
        <div class="col-12"><label class="form-label">Referencias del domicilio</label><input class="form-control" name="referencia" placeholder="Ej.: portón verde, casa esquina" value="${esc(n.referencia)}"></div>
        <div class="col-md-4"><label class="form-label">Forma de pago habitual</label><select class="form-select" name="formaPago">${UI.options(FORMAS_PAGO, n.formaPago)}</select></div>
        <div class="col-md-8"><label class="form-label">Observaciones</label><input class="form-control" name="observaciones" value="${esc(n.observaciones)}"></div>
        ${c ? `<div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="activo" id="cliActivo" ${n.activo ? 'checked' : ''}><label class="form-check-label" for="cliActivo">Cliente activo</label></div></div>` : ''}
      </div>`,
      onSubmit: d => {
        const db = Store.db;
        const cel = d.celular.replace(/\D/g, '');
        const dup = db.clientes.find(x => x !== c && x.celular.replace(/\D/g, '') === cel);
        if (dup) { UI.toast(`Ya existe un cliente con ese celular: ${esc(Q.nombreCliente(dup))}`, 'error'); return false; }
        let cli;
        if (c) cli = Object.assign(c, d);
        else { cli = { id: Store.nextId('clientes'), ...d, activo: true, alta: Q.hoy(), envases: { 1: 0, 2: 0, 3: 0 } }; db.clientes.push(cli); }
        App.save();
        UI.toast(c ? 'Cliente actualizado' : 'Cliente registrado');
        onSaved && onSaved(cli);
      },
    });
  },

  ajustarEnvases(c) {
    UI.modal({
      title: `Garrafas en poder de ${esc(Q.nombreCliente(c))}`,
      body: `<p class="text-muted-sm">Corregí la cantidad de envases que tiene el cliente (por ejemplo, si trajo una garrafa de otra marca o devolvió un envase sin comprar).</p>
        ${Q.garrafas().map(g => `<div class="row align-items-center mb-2"><label class="col-6 col-form-label">${esc(g.nombre)}</label><div class="col-6"><input type="number" min="0" class="form-control" name="g${g.id}" value="${c.envases[g.id] || 0}"></div></div>`).join('')}
        <label class="form-label mt-2">Motivo</label><input class="form-control" name="motivo" placeholder="Ej.: devolvió envase" required>`,
      onSubmit: d => {
        Q.garrafas().forEach(g => {
          const nuevo = +d['g' + g.id], dif = nuevo - (c.envases[g.id] || 0);
          if (dif) {
            // Si devuelve envases, ingresan como vacías al depósito
            Ops.movEnvase(g.id, dif < 0 ? 'Devolución de cliente' : 'Ajuste envases cliente', 0, -dif, 0, `${Q.nombreCliente(c)} · ${d.motivo}`);
            c.envases[g.id] = nuevo;
          }
        });
        App.save(); UI.toast('Envases actualizados'); App.render();
      },
    });
  },
};

App.route('/clientes', v => {
  App.setTitle('Clientes', [['Ventas'], ['Clientes']]);
  const f = { pago: '', filtro: '' };
  // Datos calculados por cliente (regularidad y saldo)
  let calc = new Map();
  const K = c => calc.get(c.id);
  v.innerHTML = `
  <div class="toolbar">
    <div class="search"><i class="bi bi-search"></i><input class="form-control form-control-sm" id="q" placeholder="Buscar por nombre, domicilio, barrio o celular"></div>
    <select class="form-select form-select-sm" id="fPago">${UI.options(FORMAS_PAGO, '', { empty: 'Forma de pago' })}</select>
    <select class="form-select form-select-sm" id="fFiltro"><option value="">Todos</option><option value="deuda">Con saldo adeudado</option><option value="atrasado">Atrasados según su regularidad</option><option value="envases">Con garrafas en su poder</option><option value="inactivo">Inactivos</option></select>
    <div class="ms-auto d-flex gap-2">
      <button class="btn btn-sm btn-light" data-action="pdf"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</button>
      <button class="btn btn-sm btn-primary" data-action="nuevo"><i class="bi bi-person-plus"></i> Nuevo cliente</button>
    </div>
  </div>
  <div class="card" id="tbl"></div>`;

  const tbl = UI.table(document.getElementById('tbl'), {
    columns: [
      { label: 'Cliente', render: c => `<a href="#/clientes/${c.id}" class="fw-semibold">${esc(Q.nombreCliente(c))}</a>${!c.activo ? ' <span class="badge-soft b-gray">Inactivo</span>' : ''}<div class="text-muted-sm">${c.tipo}${c.dni ? ' · ' + esc(c.dni) : ''}</div>` },
      { label: 'Domicilio', render: c => `${esc(c.domicilio)}<div class="text-muted-sm">${esc(c.barrio)}</div>` },
      { label: 'Celular', render: c => `<span class="text-nowrap">${esc(c.celular)} <a href="${UI.waLink(c.celular)}" target="_blank" rel="noopener" title="WhatsApp"><i class="bi bi-whatsapp text-success"></i></a></span>` },
      { label: 'Pago habitual', render: c => UI.formaPago(c.formaPago) },
      { label: 'Garrafas', cls: 'num', render: c => Q.totalEnvasesCliente(c) ? `<span title="${Q.garrafas().map(g => `${c.envases[g.id]}× ${g.pesoKg}kg`).join(' · ')}">${Q.totalEnvasesCliente(c)}</span>` : '<span class="text-body-tertiary">0</span>' },
      { label: 'Último pedido', render: c => { const r = K(c).r; return r.ultimo ? `${fmt.date(r.ultimo)}<div class="text-muted-sm">hace ${r.diasSinPedir} días</div>` : '—'; } },
      { label: 'Regularidad', render: c => K(c).r.promedio ? `<span class="text-nowrap">Cada ${K(c).r.promedio} días</span><div>${UI.regBadge(K(c).r.estado)}</div>` : '<span class="text-body-tertiary">Sin datos</span>' },
      { label: 'Saldo', cls: 'num', render: c => K(c).saldo ? `<span class="text-danger fw-semibold">${fmt.money(K(c).saldo)}</span>` : '<span class="text-success">$ 0</span>' },
      { label: '', cls: 'actions', render: c => `<a class="btn btn-sm btn-light" href="#/pedidos/nuevo?cliente=${c.id}" title="Nuevo pedido"><i class="bi bi-cart-plus text-brand"></i></a>
        <button class="btn btn-sm btn-light" data-action="editar" data-id="${c.id}" title="Editar"><i class="bi bi-pencil"></i></button>` },
    ],
    search: c => `${Q.nombreCliente(c)} ${c.domicilio} ${c.barrio} ${c.celular} ${c.dni}`,
    onRow: c => App.go(`#/clientes/${c.id}`),
    empty: 'No se encontraron clientes',
  });

  function aplicar() {
    calc = new Map(Store.db.clientes.map(c => [c.id, { r: Q.regularidad(c.id), saldo: Q.saldoCliente(c.id) }]));
    const rows = Store.db.clientes
      .filter(c => (!f.pago || c.formaPago === f.pago) && (f.filtro === 'inactivo' ? !c.activo : c.activo)
        && (f.filtro !== 'deuda' || K(c).saldo > 0) && (f.filtro !== 'atrasado' || K(c).r.estado === 'Atrasado') && (f.filtro !== 'envases' || Q.totalEnvasesCliente(c) > 0))
      .sort((a, b) => Q.nombreCliente(a).localeCompare(Q.nombreCliente(b)));
    tbl.setRows(rows);
  }
  aplicar();

  document.getElementById('q').addEventListener('input', e => tbl.setQuery(e.target.value));
  document.getElementById('fPago').addEventListener('change', e => { f.pago = e.target.value; aplicar(); });
  document.getElementById('fFiltro').addEventListener('change', e => { f.filtro = e.target.value; aplicar(); });

  App.actions({
    nuevo: () => Clientes.form(null, c => App.go(`#/clientes/${c.id}`)),
    editar: id => Clientes.form(Q.cliente(id), () => App.render()),
    pdf: () => PDF.informe('Listado de clientes', `${tbl.rows.length} clientes`, [{
      head: ['Cliente', 'Domicilio', 'Celular', 'Pago habitual', 'Garrafas', 'Último pedido', 'Frecuencia', 'Saldo'],
      body: tbl.rows.map(c => [Q.nombreCliente(c), `${c.domicilio}, ${c.barrio}`, c.celular, c.formaPago, Q.totalEnvasesCliente(c), fmt.date(K(c).r.ultimo), K(c).r.promedio ? `${K(c).r.promedio} días` : '—', PDF.money(K(c).saldo)]),
      columnStyles: { 4: { halign: 'center' }, 7: { halign: 'right' } },
    }], 'clientes', 'l'),
  });
});

App.route('/clientes/:id', (v, { id }) => {
  const c = Q.cliente(id);
  if (!c) { v.innerHTML = '<div class="card card-body empty">Cliente inexistente</div>'; return; }
  App.setTitle(Q.nombreCliente(c), [['Ventas'], ['Clientes', '#/clientes'], ['Ficha']]);
  const db = Store.db;
  const peds = Q.pedidosCliente(c.id).sort((a, b) => b.fecha.localeCompare(a.fecha));
  const validos = peds.filter(p => p.estado !== 'Cancelado');
  const r = Q.regularidad(c.id), saldo = Q.saldoCliente(c.id);
  const totalComprado = validos.reduce((s, p) => s + p.total, 0);

  // Cuenta corriente: pedidos (debe) y pagos (haber)
  const movs = [
    ...validos.map(p => ({ fecha: p.fecha, concepto: `Pedido #${fmt.nro(p.numero)}`, debe: p.total, haber: 0, link: `#/pedidos/${p.id}` })),
    ...Q.pagosCliente(c.id).map(pg => ({ fecha: pg.fecha, concepto: `Recibo #${fmt.nro(pg.recibo)} · ${pg.formaPago}`, debe: 0, haber: pg.monto, pagoId: pg.id })),
  ].sort((a, b) => a.fecha.localeCompare(b.fecha) || b.debe - a.debe);
  let acum = 0; movs.forEach(m => { acum += m.debe - m.haber; m.saldo = acum; });

  const prodCount = {};
  validos.forEach(p => p.items.forEach(it => { prodCount[it.productoId] = (prodCount[it.productoId] || 0) + it.cantidad; }));
  const favoritos = Object.entries(prodCount).sort((a, b) => b[1] - a[1]).slice(0, 3);
  const efe = Q.pagosCliente(c.id).filter(x => x.formaPago === 'Efectivo').length, tot = Q.pagosCliente(c.id).length;

  v.innerHTML = `
  <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <span class="badge-soft b-info">${c.tipo}</span> ${!c.activo ? '<span class="badge-soft b-gray">Inactivo</span>' : ''} ${r.promedio ? UI.regBadge(r.estado) : ''}
    <span class="text-muted-sm">Cliente desde ${fmt.date(c.alta)}</span>
    <div class="ms-auto d-flex flex-wrap gap-2">
      <a class="btn btn-sm btn-light" href="${UI.waLink(c.celular)}" target="_blank" rel="noopener"><i class="bi bi-whatsapp text-success"></i> WhatsApp</a>
      <button class="btn btn-sm btn-light" data-action="editar"><i class="bi bi-pencil"></i> Editar</button>
      <button class="btn btn-sm btn-light" data-action="pdfCta"><i class="bi bi-file-earmark-pdf"></i> Estado de cuenta</button>
      ${saldo > 0 ? `<button class="btn btn-sm btn-outline-primary" data-action="pagar"><i class="bi bi-cash-coin"></i> Registrar pago</button>` : ''}
      <a class="btn btn-sm btn-primary" href="#/pedidos/nuevo?cliente=${c.id}"><i class="bi bi-cart-plus"></i> Nuevo pedido</a>
    </div>
  </div>
  <div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">${UI.kpi({ icon: 'receipt', color: 'i-orange', label: 'Pedidos', value: validos.length, sub: `Total ${fmt.money(totalComprado)}` })}</div>
    <div class="col-6 col-lg-3">${UI.kpi({ icon: 'calendar2-week', color: 'i-blue', label: 'Pide cada', value: r.promedio ? `${r.promedio} días` : '—', sub: r.proximo ? `Próximo estimado: ${fmt.date(r.proximo)}` : 'Sin historial suficiente' })}</div>
    <div class="col-6 col-lg-3">${UI.kpi({ icon: 'fuel-pump', color: 'i-violet', label: 'Garrafas en su poder', value: Q.totalEnvasesCliente(c), sub: Q.garrafas().filter(g => c.envases[g.id]).map(g => `${c.envases[g.id]}× ${g.pesoKg} kg`).join(' · ') || 'Ninguna' })}</div>
    <div class="col-6 col-lg-3">${UI.kpi({ icon: 'wallet2', color: saldo ? 'i-red' : 'i-green', label: 'Saldo', value: fmt.money(saldo), sub: saldo ? 'Adeudado' : 'Al día' })}</div>
  </div>
  <div class="row g-3">
    <div class="col-lg-4">
      <div class="card mb-3"><div class="card-header"><h2>Datos de contacto</h2></div><div class="card-body"><dl class="info-list">
        <dt>Celular</dt><dd class="fw-semibold">${esc(c.celular)}</dd>
        ${c.telefono ? `<dt>Teléfono</dt><dd>${esc(c.telefono)}</dd>` : ''}
        <dt>Domicilio</dt><dd>${esc(c.domicilio)}</dd>
        <dt>Barrio</dt><dd>${esc(c.barrio) || '—'}</dd>
        <dt>Referencia</dt><dd>${esc(c.referencia) || '—'}</dd>
        <dt>DNI/CUIT</dt><dd>${esc(c.dni) || '—'}</dd>
        <dt>Obs.</dt><dd>${esc(c.observaciones) || '—'}</dd>
      </dl></div></div>
      <div class="card mb-3"><div class="card-header"><h2>Hábitos de compra</h2></div><div class="card-body"><dl class="info-list">
        <dt>Pago habitual</dt><dd>${UI.formaPago(c.formaPago)}</dd>
        <dt>Pagos</dt><dd>${tot ? `${Math.round(efe / tot * 100)}% efectivo · ${100 - Math.round(efe / tot * 100)}% transf.` : '—'}</dd>
        <dt>Último pedido</dt><dd>${r.ultimo ? `${fmt.date(r.ultimo)} (hace ${r.diasSinPedir} días)` : '—'}</dd>
        <dt>Compra más</dt><dd>${favoritos.map(([pid, n]) => `${esc(Q.producto(pid).nombre)} <span class="text-body-secondary">(${n})</span>`).join('<br>') || '—'}</dd>
      </dl></div></div>
      <div class="card"><div class="card-header"><h2>Garrafas en su poder</h2><div class="ms-auto"><button class="btn btn-sm btn-light" data-action="envases"><i class="bi bi-sliders"></i> Ajustar</button></div></div>
        <ul class="list-group list-group-flush">${Q.garrafas().map(g => `<li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-fuel-pump me-2 text-brand"></i>${esc(g.nombre)}</span><b>${c.envases[g.id] || 0}</b></li>`).join('')}</ul>
      </div>
    </div>
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header p-0 px-2 pt-2 border-0">
          <ul class="nav nav-tabs w-100" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tPedidos" type="button">Pedidos (${peds.length})</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tCta" type="button">Cuenta corriente</button></li>
          </ul>
        </div>
        <div class="tab-content">
          <div class="tab-pane fade show active" id="tPedidos"><div id="tblPed"></div></div>
          <div class="tab-pane fade" id="tCta"><div id="tblCta"></div></div>
        </div>
      </div>
    </div>
  </div>`;

  UI.table(document.getElementById('tblPed'), {
    rows: peds, pageSize: 10,
    columns: [
      { label: 'N°', render: p => `<a href="#/pedidos/${p.id}" class="fw-semibold">#${fmt.nro(p.numero)}</a>` },
      { label: 'Fecha', render: p => fmt.date(p.fecha) },
      { label: 'Canal', render: p => UI.canal(p.canal) },
      { label: 'Productos', render: p => `<span class="small">${UI.itemsResumen(p.items)}</span>` },
      { label: 'Total', cls: 'num', render: p => fmt.money(p.total) },
      { label: 'Pago', render: p => UI.pagoBadge(Q.estadoPago(p)) },
      { label: 'Estado', render: p => UI.estadoBadge(p.estado) },
    ],
    onRow: p => App.go(`#/pedidos/${p.id}`), empty: 'El cliente no tiene pedidos',
  });
  UI.table(document.getElementById('tblCta'), {
    rows: movs.slice().reverse(), pageSize: 12,
    columns: [
      { label: 'Fecha', render: m => fmt.date(m.fecha) },
      { label: 'Concepto', render: m => m.link ? `<a href="${m.link}">${esc(m.concepto)}</a>` : `${esc(m.concepto)} <button class="btn btn-sm btn-link p-0 ms-1" data-action="recibo" data-id="${m.pagoId}" title="Recibo PDF"><i class="bi bi-file-earmark-pdf"></i></button>` },
      { label: 'Debe', cls: 'num', render: m => m.debe ? fmt.money(m.debe) : '' },
      { label: 'Haber', cls: 'num', render: m => m.haber ? `<span class="text-success">${fmt.money(m.haber)}</span>` : '' },
      { label: 'Saldo', cls: 'num fw-semibold', render: m => fmt.money(m.saldo) },
    ],
    empty: 'Sin movimientos',
  });

  App.actions({
    editar: () => Clientes.form(c, () => App.render()),
    envases: () => Clientes.ajustarEnvases(c),
    pagar: () => Cobros.nuevo({ clienteId: c.id, onSaved: () => App.render() }),
    recibo: pid => PDF.recibo(db.pagos.find(x => x.id === +pid)),
    pdfCta: () => PDF.informe('Estado de cuenta', Q.nombreCliente(c), [
      { resumen: [['Cliente', Q.nombreCliente(c)], ['Domicilio', `${c.domicilio}, ${c.barrio}`], ['Celular', c.celular], ['Total comprado', PDF.money(totalComprado)], ['Saldo adeudado', PDF.money(saldo)]] },
      { titulo: 'Movimientos', head: ['Fecha', 'Concepto', 'Debe', 'Haber', 'Saldo'], body: movs.map(m => [fmt.date(m.fecha), m.concepto, m.debe ? PDF.money(m.debe) : '', m.haber ? PDF.money(m.haber) : '', PDF.money(m.saldo)]), columnStyles: { 2: { halign: 'right' }, 3: { halign: 'right' }, 4: { halign: 'right' } } },
    ], `estado-cuenta-${c.id}`),
  });
});

/* Garrafas: stock de envases llenos, vacíos aptos, no aptos y en poder de clientes */

const Garrafas = {
  TIPOS: {
    'Marcar como no apta': { desc: 'Pasa envases vacíos a no aptos (golpeados, válvula dañada, prueba hidráulica vencida).', calc: n => [0, -n, n] },
    'Recuperar envase': { desc: 'Un envase no apto fue reparado y vuelve a estar apto para intercambio.', calc: n => [0, n, -n] },
    'Baja de envase': { desc: 'Se descarta definitivamente un envase no apto.', calc: n => [0, 0, -n] },
    'Compra de envases vacíos': { desc: 'Ingresan envases nuevos o usados al parque, vacíos.', calc: n => [0, n, 0] },
    'Envío a recarga': { desc: 'Se entregan vacías al proveedor fuera de una recepción.', calc: n => [0, -n, 0] },
  },

  movimiento(productoId) {
    const tipos = Object.keys(Garrafas.TIPOS);
    const md = UI.modal({
      title: 'Registrar movimiento de envases',
      body: `<div class="row g-3">
        <div class="col-md-6"><label class="form-label">Garrafa</label><select class="form-select" name="productoId">${UI.options(Q.garrafas(), productoId, { value: g => g.id, label: g => g.nombre })}</select></div>
        <div class="col-md-6"><label class="form-label">Cantidad</label><input type="number" min="1" class="form-control" name="cantidad" value="1" required></div>
        <div class="col-12"><label class="form-label">Movimiento</label><select class="form-select" name="tipo">${UI.options(tipos, tipos[0])}</select><div class="form-text" id="tipoDesc"></div></div>
        <div class="col-12"><label class="form-label">Detalle / motivo</label><input class="form-control" name="detalle" placeholder="Ej.: válvula pierde"></div>
        <div class="col-12" id="prev"></div>
      </div>`,
      submit: 'Registrar',
      onSubmit: d => {
        const [ll, va, na] = Garrafas.TIPOS[d.tipo].calc(+d.cantidad);
        const e = Store.db.envases[d.productoId];
        if (e.llenas + ll < 0 || e.vacias + va < 0 || e.noAptas + na < 0) { UI.toast('No hay suficientes envases para ese movimiento', 'error'); return false; }
        Ops.movEnvase(d.productoId, d.tipo, ll, va, na, d.detalle.trim());
        App.save(); UI.toast('Movimiento registrado'); App.render();
      },
    });
    const f = md.form;
    const upd = () => {
      document.getElementById('tipoDesc').textContent = Garrafas.TIPOS[f.tipo.value].desc;
      const [ll, va, na] = Garrafas.TIPOS[f.tipo.value].calc(+f.cantidad.value || 0);
      const e = Store.db.envases[f.productoId.value];
      const cell = (l, a, b) => `<div class="col"><div class="cyl-stat"><b>${a} → ${a + b}</b><span>${l}</span></div></div>`;
      document.getElementById('prev').innerHTML = `<div class="row g-2">${cell('Llenas', e.llenas, ll)}${cell('Vacías aptas', e.vacias, va)}${cell('No aptas', e.noAptas, na)}</div>`;
    };
    ['tipo', 'cantidad', 'productoId'].forEach(n => f[n].addEventListener('input', upd));
    upd();
  },

  inventario() {
    const db = Store.db;
    UI.modal({
      title: 'Ajuste por conteo de inventario', size: 'modal-lg',
      body: `<p class="text-muted-sm">Ingresá lo que contaste físicamente en el depósito. Las diferencias quedan registradas como ajuste.</p>
      <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Garrafa</th><th>Llenas</th><th>Vacías aptas</th><th>No aptas</th></tr></thead><tbody>
      ${Q.garrafas().map(g => { const e = db.envases[g.id]; return `<tr><td class="fw-semibold">${esc(g.nombre)}</td>
        <td><input type="number" min="0" class="form-control form-control-sm" name="l${g.id}" value="${e.llenas}"></td>
        <td><input type="number" min="0" class="form-control form-control-sm" name="v${g.id}" value="${e.vacias}"></td>
        <td><input type="number" min="0" class="form-control form-control-sm" name="n${g.id}" value="${e.noAptas}"></td></tr>`; }).join('')}
      </tbody></table></div><label class="form-label">Observaciones</label><input class="form-control" name="obs" placeholder="Ej.: conteo mensual">`,
      submit: 'Guardar ajuste',
      onSubmit: d => {
        let cambios = 0;
        Q.garrafas().forEach(g => {
          const e = db.envases[g.id];
          const dl = +d['l' + g.id] - e.llenas, dv = +d['v' + g.id] - e.vacias, dn = +d['n' + g.id] - e.noAptas;
          if (dl || dv || dn) { cambios++; Ops.movEnvase(g.id, 'Ajuste de inventario', dl, dv, dn, d.obs || 'Conteo físico'); }
        });
        App.save(); UI.toast(cambios ? 'Inventario ajustado' : 'Sin diferencias', cambios ? 'success' : 'info'); App.render();
      },
    });
  },
};

App.route('/garrafas', v => {
  App.setTitle('Garrafas', [['Depósito'], ['Garrafas']]);
  const db = Store.db, hoy = Q.hoy();
  const f = { desde: addDays(hoy, -29), hasta: hoy, prod: '', tipo: '' };
  const totLl = Q.garrafas().reduce((s, g) => s + db.envases[g.id].llenas, 0);
  const totVa = Q.garrafas().reduce((s, g) => s + db.envases[g.id].vacias, 0);
  const totNa = Q.garrafas().reduce((s, g) => s + db.envases[g.id].noAptas, 0);
  const totCl = Q.garrafas().reduce((s, g) => s + Q.envasesEnClientes(g.id), 0);

  v.innerHTML = `
  <div class="toolbar">
    <div class="text-body-secondary small"><i class="bi bi-info-circle me-1"></i>Parque total: <b>${totLl + totVa + totNa + totCl}</b> envases · en depósito <b>${totLl + totVa + totNa}</b> · en clientes <b>${totCl}</b></div>
    <div class="ms-auto d-flex gap-2 flex-wrap">
      <button class="btn btn-sm btn-light" data-action="pdf"><i class="bi bi-file-earmark-pdf"></i> Informe de stock</button>
      <button class="btn btn-sm btn-light" data-action="inventario"><i class="bi bi-clipboard-check"></i> Ajuste por inventario</button>
      <a class="btn btn-sm btn-light" href="#/recepciones/nueva"><i class="bi bi-box-arrow-in-down"></i> Recepción de proveedor</a>
      <button class="btn btn-sm btn-primary" data-action="mov"><i class="bi bi-arrow-left-right"></i> Registrar movimiento</button>
    </div>
  </div>
  <div class="row g-3 mb-3">
    ${Q.garrafas().map(g => {
      const e = db.envases[g.id], cl = Q.envasesEnClientes(g.id);
      const bajo = e.llenas <= g.stockMin;
      return `<div class="col-lg-4"><div class="card cyl-card h-100"><div class="card-body">
        <div class="cyl-head"><div class="cyl-icon"><i class="bi bi-fuel-pump-fill"></i></div>
          <div class="flex-fill"><div class="fw-bold fs-5">${esc(g.nombre)}</div><div class="text-muted-sm">Mínimo de llenas: ${g.stockMin} · Precio ${fmt.money(g.precio)}</div></div>
          ${bajo ? '<span class="badge-soft b-impago"><i class="bi bi-exclamation-triangle"></i> Reponer</span>' : '<span class="badge-soft b-pagado">OK</span>'}</div>
        <div class="cyl-stats">
          <div class="cyl-stat full"><b>${e.llenas}</b><span>Llenas</span></div>
          <div class="cyl-stat empty-ok"><b>${e.vacias}</b><span>Vacías aptas</span></div>
          <div class="cyl-stat bad"><b>${e.noAptas}</b><span>No aptas</span></div>
          <div class="cyl-stat client"><b>${cl}</b><span>En clientes</span></div>
        </div>
        <div class="d-flex justify-content-between mt-3 small text-body-secondary"><span>Total en depósito: <b class="text-body">${e.llenas + e.vacias + e.noAptas}</b></span><span>Parque: <b class="text-body">${e.llenas + e.vacias + e.noAptas + cl}</b></span></div>
        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-sm btn-light flex-fill" data-action="mov" data-id="${g.id}"><i class="bi bi-arrow-left-right"></i> Movimiento</button>
          <button class="btn btn-sm btn-light flex-fill" data-action="noApta" data-id="${g.id}"><i class="bi bi-x-octagon text-danger"></i> Marcar no apta</button>
        </div>
      </div></div></div>`;
    }).join('')}
  </div>
  <div class="row g-3 mb-3">
    <div class="col-lg-5"><div class="card h-100"><div class="card-header"><h2>Distribución del parque de envases</h2></div><div class="card-body"><div class="chart-box sm"><canvas id="chParque"></canvas></div></div></div></div>
    <div class="col-lg-7"><div class="card h-100"><div class="card-header"><h2>Salidas vs. recepciones (últimos 30 días)</h2></div><div class="card-body"><div class="chart-box sm"><canvas id="chFlujo"></canvas></div></div></div></div>
  </div>
  <div class="card">
    <div class="card-header p-0 px-2 pt-2 border-0"><ul class="nav nav-tabs w-100">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tMov" type="button">Movimientos</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tCli" type="button">En poder de clientes (${totCl})</button></li>
    </ul></div>
    <div class="tab-content">
      <div class="tab-pane fade show active" id="tMov">
        <div class="toolbar p-3 pb-0">
          ${UI.rangoFechas('f', f.desde, f.hasta)}
          <select class="form-select form-select-sm" id="fProd">${UI.options(Q.garrafas(), '', { value: g => g.id, label: g => g.nombre, empty: 'Todas las garrafas' })}</select>
          <select class="form-select form-select-sm" id="fTipo">${UI.options([...new Set(db.movEnvases.map(m => m.tipo))].sort(), '', { empty: 'Todos los movimientos' })}</select>
        </div>
        <div id="tblMov"></div>
      </div>
      <div class="tab-pane fade" id="tCli"><div class="toolbar p-3 pb-0"><div class="search"><i class="bi bi-search"></i><input class="form-control form-control-sm" id="qCli" placeholder="Buscar cliente"></div></div><div id="tblCli"></div></div>
    </div>
  </div>`;

  const sign = n => n > 0 ? `<span class="text-success fw-semibold">+${n}</span>` : n < 0 ? `<span class="text-danger fw-semibold">${n}</span>` : '<span class="text-body-tertiary">0</span>';
  const tblMov = UI.table(document.getElementById('tblMov'), {
    columns: [
      { label: 'Fecha', render: m => fmt.date(m.fecha) },
      { label: 'Garrafa', render: m => esc(Q.producto(m.productoId).nombre) },
      { label: 'Movimiento', render: m => `<span class="badge-soft ${m.tipo.startsWith('Recep') ? 'b-pagado' : m.tipo.startsWith('Entrega') ? 'b-info' : 'b-gray'}">${esc(m.tipo)}</span>` },
      { label: 'Detalle', render: m => `<span class="small">${esc(m.detalle)}</span>` },
      { label: 'Llenas', cls: 'num', render: m => sign(m.llenas) },
      { label: 'Vacías', cls: 'num', render: m => sign(m.vacias) },
      { label: 'No aptas', cls: 'num', render: m => sign(m.noAptas) },
    ],
    empty: 'Sin movimientos en el período',
  });
  function aplicar() {
    tblMov.setRows(db.movEnvases.filter(m => m.fecha >= f.desde && m.fecha <= f.hasta && (!f.prod || m.productoId === +f.prod) && (!f.tipo || m.tipo === f.tipo)).sort((a, b) => b.fecha.localeCompare(a.fecha) || b.id - a.id));
  }
  aplicar();
  UI.bindRango('f', (d, h) => { f.desde = d; f.hasta = h; aplicar(); });
  document.getElementById('fProd').addEventListener('change', e => { f.prod = e.target.value; aplicar(); });
  document.getElementById('fTipo').addEventListener('change', e => { f.tipo = e.target.value; aplicar(); });

  const tblCli = UI.table(document.getElementById('tblCli'), {
    rows: db.clientes.filter(c => Q.totalEnvasesCliente(c) > 0).sort((a, b) => Q.totalEnvasesCliente(b) - Q.totalEnvasesCliente(a)),
    columns: [
      { label: 'Cliente', render: c => `<a href="#/clientes/${c.id}" class="fw-semibold">${esc(Q.nombreCliente(c))}</a><div class="text-muted-sm">${esc(c.domicilio)}</div>` },
      ...Q.garrafas().map(g => ({ label: `${g.pesoKg} kg`, cls: 'num', render: c => c.envases[g.id] || '<span class="text-body-tertiary">0</span>' })),
      { label: 'Total', cls: 'num fw-semibold', render: c => Q.totalEnvasesCliente(c) },
      { label: 'Último pedido', render: c => { const r = Q.regularidad(c.id); return r.ultimo ? `${fmt.date(r.ultimo)} <span class="text-muted-sm">(hace ${r.diasSinPedir} d)</span>` : '—'; } },
      { label: '', cls: 'actions', render: c => `<button class="btn btn-sm btn-light" data-action="ajCli" data-id="${c.id}" title="Ajustar envases"><i class="bi bi-sliders"></i></button>` },
    ],
    search: c => Q.nombreCliente(c) + ' ' + c.domicilio,
    empty: 'Ningún cliente tiene garrafas',
  });
  document.getElementById('qCli').addEventListener('input', e => tblCli.setQuery(e.target.value));

  const labels = Q.garrafas().map(g => g.nombre);
  App.chart(document.getElementById('chParque'), {
    type: 'bar',
    data: { labels, datasets: [
      { label: 'Llenas', data: Q.garrafas().map(g => db.envases[g.id].llenas), backgroundColor: '#40c057' },
      { label: 'Vacías aptas', data: Q.garrafas().map(g => db.envases[g.id].vacias), backgroundColor: '#4dabf7' },
      { label: 'No aptas', data: Q.garrafas().map(g => db.envases[g.id].noAptas), backgroundColor: '#fa5252' },
      { label: 'En clientes', data: Q.garrafas().map(g => Q.envasesEnClientes(g.id)), backgroundColor: '#9775fa' },
    ] },
    options: { maintainAspectRatio: false, indexAxis: 'y', scales: { x: { stacked: true, grid: { color: '#f1f3f5' } }, y: { stacked: true, grid: { display: false } } }, plugins: { legend: { position: 'bottom' } } },
  });
  const desde30 = addDays(hoy, -29);
  const m30 = db.movEnvases.filter(m => m.fecha >= desde30);
  App.chart(document.getElementById('chFlujo'), {
    type: 'bar',
    data: { labels, datasets: [
      { label: 'Llenas entregadas a clientes', data: Q.garrafas().map(g => -m30.filter(m => m.productoId === g.id && m.tipo === 'Entrega a cliente').reduce((s, m) => s + m.llenas, 0)), backgroundColor: '#e8590c', borderRadius: 4 },
      { label: 'Llenas recibidas de proveedores', data: Q.garrafas().map(g => m30.filter(m => m.productoId === g.id && m.tipo === 'Recepción de proveedor').reduce((s, m) => s + m.llenas, 0)), backgroundColor: '#1971c2', borderRadius: 4 },
    ] },
    options: { maintainAspectRatio: false, scales: { y: { grid: { color: '#f1f3f5' } }, x: { grid: { display: false } } }, plugins: { legend: { position: 'bottom' } } },
  });

  App.actions({
    mov: id => Garrafas.movimiento(id || 1),
    noApta: id => { Garrafas.movimiento(id); },
    inventario: () => Garrafas.inventario(),
    ajCli: id => Clientes.ajustarEnvases(Q.cliente(id)),
    pdf: () => PDF.informe('Stock de garrafas', `Al ${fmt.date(hoy)}`, [
      { titulo: 'Stock por tipo de garrafa', head: ['Garrafa', 'Llenas', 'Vacías aptas', 'No aptas', 'En depósito', 'En clientes', 'Parque total', 'Mínimo'],
        body: Q.garrafas().map(g => { const e = db.envases[g.id], cl = Q.envasesEnClientes(g.id); return [g.nombre, e.llenas, e.vacias, e.noAptas, e.llenas + e.vacias + e.noAptas, cl, e.llenas + e.vacias + e.noAptas + cl, g.stockMin]; }) },
      { titulo: 'Garrafas en poder de clientes', head: ['Cliente', 'Domicilio', ...Q.garrafas().map(g => `${g.pesoKg} kg`), 'Total'],
        body: tblCli.rows.map(c => [Q.nombreCliente(c), c.domicilio, ...Q.garrafas().map(g => c.envases[g.id] || 0), Q.totalEnvasesCliente(c)]) },
    ], 'stock-garrafas'),
  });
});

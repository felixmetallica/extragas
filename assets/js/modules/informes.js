/* Informes: pedidos, productos más vendidos, regularidad de pedidos y gestión de pagos */

const Informes = {
  LISTA: [
    { k: 'pedidos', icon: 'cart3', t: 'Pedidos de clientes', d: 'Cantidad, importes, canales y estados' },
    { k: 'productos', icon: 'trophy', t: 'Productos más vendidos', d: 'Ranking por unidades e importe' },
    { k: 'regularidad', icon: 'calendar2-week', t: 'Regularidad de pedidos', d: 'Frecuencia de compra por cliente' },
    { k: 'pagos', icon: 'cash-coin', t: 'Gestión de pagos', d: 'Cobros, deudas y pagos a proveedores' },
    { k: 'garrafas', icon: 'fuel-pump', t: 'Stock de garrafas', d: 'Envases en depósito y en clientes' },
  ],
  rango: { desde: addDays(localISO(new Date()), -29), hasta: localISO(new Date()) },
};

App.route('/informes', () => App.go('#/informes/pedidos'));
App.route('/informes/:tipo', (v, { tipo }) => {
  const inf = Informes.LISTA.find(x => x.k === tipo) || Informes.LISTA[0];
  App.setTitle('Informes', [['Análisis'], ['Informes', '#/informes'], [inf.t]]);
  const R = Informes.rango;

  v.innerHTML = `
  <div class="row g-3">
    <div class="col-lg-3">
      <div class="card"><div class="card-body p-2"><div class="list-group report-nav">
        ${Informes.LISTA.map(x => `<a href="#/informes/${x.k}" class="list-group-item list-group-item-action ${x.k === inf.k ? 'active' : ''}"><i class="bi bi-${x.icon} fs-5"></i><span><span class="d-block">${x.t}</span><small class="text-body-secondary fw-normal">${x.d}</small></span></a>`).join('')}
      </div></div></div>
    </div>
    <div class="col-lg-9">
      <div class="card mb-3"><div class="card-body d-flex flex-wrap gap-2 align-items-center py-2">
        <div><div class="fw-bold">${inf.t}</div><div class="text-muted-sm">${inf.d}</div></div>
        <div class="ms-auto d-flex flex-wrap gap-2 align-items-center">
          ${inf.k !== 'garrafas' ? UI.rangoFechas('r', R.desde, R.hasta) : ''}
          <button class="btn btn-sm btn-primary" data-action="pdf"><i class="bi bi-file-earmark-pdf"></i> Descargar PDF</button>
        </div>
      </div></div>
      <div id="rep"></div>
    </div>
  </div>`;
  if (inf.k !== 'garrafas') {
    const sel = document.getElementById('rRango');
    sel.value = 'custom';
    UI.bindRango('r', (d, h) => { R.desde = d; R.hasta = h; App.render(); });
  }

  const db = Store.db;
  const rep = document.getElementById('rep');
  const enRango = f => f >= R.desde && f <= R.hasta;
  const peds = db.pedidos.filter(p => enRango(p.fecha));
  const validos = peds.filter(p => p.estado !== 'Cancelado');
  const periodo = `Del ${fmt.date(R.desde)} al ${fmt.date(R.hasta)}`;
  const nDias = daysBetween(R.desde, R.hasta) + 1;
  const dias = [...Array(Math.min(nDias, 366))].map((_, i) => addDays(R.desde, i));
  const kpiRow = arr => `<div class="row g-3 mb-3">${arr.map(k => `<div class="col-6 col-xl-3">${UI.kpi(k)}</div>`).join('')}</div>`;
  let pdf = () => {};

  if (inf.k === 'pedidos') {
    const total = validos.reduce((s, p) => s + p.total, 0);
    const porCanal = CANALES.map(c => validos.filter(p => p.canal === c).length);
    const porEstado = ESTADOS_PEDIDO.map(e => peds.filter(p => p.estado === e).length);
    const domicilio = validos.filter(p => p.entrega === 'A domicilio').length;
    const clientesUnicos = new Set(validos.map(p => p.clienteId)).size;
    rep.innerHTML = kpiRow([
      { icon: 'receipt', color: 'i-orange', label: 'Pedidos', value: fmt.num(validos.length), sub: `${(validos.length / nDias).toFixed(1)} por día` },
      { icon: 'currency-dollar', color: 'i-green', label: 'Importe total', value: fmt.money(total), sub: `Ticket prom. ${fmt.money(validos.length ? total / validos.length : 0)}` },
      { icon: 'people', color: 'i-blue', label: 'Clientes atendidos', value: clientesUnicos },
      { icon: 'truck', color: 'i-violet', label: 'Con envío', value: validos.length ? fmt.pct(domicilio / validos.length * 100) : '—', sub: `${validos.length - domicilio} retiraron en local` },
    ]) + `
    <div class="card mb-3"><div class="card-header"><h2>Pedidos por día</h2></div><div class="card-body"><div class="chart-box"><canvas id="c1"></canvas></div></div></div>
    <div class="row g-3 mb-3">
      <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Por canal</h2></div><div class="card-body"><div class="chart-box sm"><canvas id="c2"></canvas></div></div></div></div>
      <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Por estado</h2></div><div class="card-body"><div class="chart-box sm"><canvas id="c3"></canvas></div></div></div></div>
    </div>
    <div class="card"><div class="card-header"><h2>Detalle de pedidos</h2></div><div id="t1"></div></div>`;
    App.chart(document.getElementById('c1'), { type: 'bar', data: { labels: dias.map(d => fmt.date(d).slice(0, 5)), datasets: [{ label: 'Pedidos', data: dias.map(d => validos.filter(p => p.fecha === d).length), backgroundColor: '#e8590c', borderRadius: 4 }] }, options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f3f5' } } } } });
    App.chart(document.getElementById('c2'), { type: 'doughnut', data: { labels: CANALES, datasets: [{ data: porCanal, backgroundColor: ['#1971c2', '#25d366', '#e8590c'] }] }, options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } } } });
    App.chart(document.getElementById('c3'), { type: 'bar', data: { labels: ESTADOS_PEDIDO, datasets: [{ data: porEstado, backgroundColor: ['#fcc419', '#4dabf7', '#9775fa', '#40c057', '#adb5bd'], borderRadius: 4 }] }, options: { maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { ticks: { precision: 0 } }, y: { grid: { display: false } } } } });
    UI.table(document.getElementById('t1'), {
      rows: peds.slice().sort((a, b) => b.fecha.localeCompare(a.fecha)), pageSize: 10,
      columns: [
        { label: 'N°', render: p => `<a href="#/pedidos/${p.id}">#${fmt.nro(p.numero)}</a>` }, { label: 'Fecha', render: p => fmt.date(p.fecha) },
        { label: 'Cliente', render: p => esc(Q.nombreCliente(Q.cliente(p.clienteId))) }, { label: 'Canal', render: p => UI.canal(p.canal) },
        { label: 'Productos', render: p => `<span class="small">${UI.itemsResumen(p.items)}</span>` }, { label: 'Total', cls: 'num', render: p => fmt.money(p.total) }, { label: 'Estado', render: p => UI.estadoBadge(p.estado) },
      ],
    });
    pdf = () => PDF.informe('Informe de pedidos', periodo, [
      { resumen: [['Pedidos', fmt.num(validos.length)], ['Importe total', PDF.money(total)], ['Ticket promedio', PDF.money(validos.length ? total / validos.length : 0)], ['Clientes atendidos', clientesUnicos], ...CANALES.map((c, i) => [`Pedidos por ${c}`, porCanal[i]]), ['Cancelados', porEstado[4]]] },
      { titulo: 'Detalle', head: ['N°', 'Fecha', 'Cliente', 'Canal', 'Productos', 'Total', 'Estado'], body: peds.map(p => [fmt.nro(p.numero), fmt.date(p.fecha), Q.nombreCliente(Q.cliente(p.clienteId)), p.canal, UI.itemsResumen(p.items), PDF.money(p.total), p.estado]), columnStyles: { 5: { halign: 'right' } } },
    ], 'informe-pedidos');
  }

  if (inf.k === 'productos') {
    const acc = {};
    validos.forEach(p => p.items.forEach(it => { const a = acc[it.productoId] = acc[it.productoId] || { u: 0, $: 0, peds: 0 }; a.u += it.cantidad; a.$ += it.cantidad * it.precio; a.peds++; }));
    const rank = Object.entries(acc).map(([id, a]) => ({ p: Q.producto(id), ...a })).sort((a, b) => b.u - a.u);
    const tot$ = rank.reduce((s, r) => s + r.$, 0), maxU = rank[0]?.u || 1;
    const porCat = Object.keys(CATEGORIAS).map(k => rank.filter(r => r.p.categoria === k).reduce((s, r) => s + r.$, 0));
    const kg = rank.filter(r => r.p.categoria === 'gas').reduce((s, r) => s + r.u * r.p.pesoKg, 0);
    rep.innerHTML = kpiRow([
      { icon: 'trophy', color: 'i-orange', label: 'Más vendido', value: rank[0] ? esc(rank[0].p.nombre) : '—', sub: rank[0] ? `${rank[0].u} unidades` : '' },
      { icon: 'fuel-pump', color: 'i-blue', label: 'Garrafas vendidas', value: fmt.num(rank.filter(r => r.p.categoria === 'gas').reduce((s, r) => s + r.u, 0)), sub: `${fmt.num(kg)} kg de gas` },
      { icon: 'fire', color: 'i-gray', label: 'Bolsas de carbón', value: fmt.num(rank.filter(r => r.p.categoria === 'carbon').reduce((s, r) => s + r.u, 0)) },
      { icon: 'tree', color: 'i-green', label: 'Bolsas de leña', value: fmt.num(rank.filter(r => r.p.categoria === 'lena').reduce((s, r) => s + r.u, 0)) },
    ]) + `
    <div class="row g-3 mb-3">
      <div class="col-md-7"><div class="card h-100"><div class="card-header"><h2>Unidades vendidas por producto</h2></div><div class="card-body"><div class="chart-box"><canvas id="c1"></canvas></div></div></div></div>
      <div class="col-md-5"><div class="card h-100"><div class="card-header"><h2>Facturación por categoría</h2></div><div class="card-body"><div class="chart-box"><canvas id="c2"></canvas></div></div></div></div>
    </div>
    <div class="card"><div class="card-header"><h2>Ranking de productos</h2></div><div class="table-responsive"><table class="table align-middle">
      <thead><tr><th>#</th><th>Producto</th><th>Categoría</th><th class="num">Unidades</th><th></th><th class="num">En pedidos</th><th class="num">Importe</th><th class="num">% del total</th></tr></thead>
      <tbody>${rank.map((r, i) => `<tr><td class="fw-bold text-brand">${i + 1}</td><td class="fw-semibold">${esc(r.p.nombre)}</td><td>${CATEGORIAS[r.p.categoria]}</td><td class="num">${r.u}</td>
        <td style="width:18%"><div class="rank-bar"><div style="width:${r.u / maxU * 100}%"></div></div></td><td class="num">${r.peds}</td><td class="num">${fmt.money(r.$)}</td><td class="num">${fmt.pct(r.$ / tot$ * 100)}</td></tr>`).join('') || '<tr><td colspan="8" class="empty">Sin ventas</td></tr>'}</tbody>
    </table></div></div>`;
    App.chart(document.getElementById('c1'), { type: 'bar', data: { labels: rank.map(r => r.p.nombre.replace(' para hogar', '')), datasets: [{ data: rank.map(r => r.u), backgroundColor: rank.map(r => ({ gas: '#e8590c', carbon: '#495057', lena: '#8d5524' }[r.p.categoria])), borderRadius: 4 }] }, options: { maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { y: { grid: { display: false } }, x: { grid: { color: '#f1f3f5' } } } } });
    App.chart(document.getElementById('c2'), { type: 'doughnut', data: { labels: Object.values(CATEGORIAS), datasets: [{ data: porCat, backgroundColor: ['#e8590c', '#495057', '#8d5524'] }] }, options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: c => ` ${c.label}: ${fmt.money(c.raw)}` } } } } });
    pdf = () => PDF.informe('Productos más vendidos', periodo, [
      { resumen: Object.values(CATEGORIAS).map((c, i) => [`Facturación ${c}`, PDF.money(porCat[i])]).concat([['Total', PDF.money(tot$)]]) },
      { titulo: 'Ranking', head: ['#', 'Producto', 'Categoría', 'Unidades', 'Pedidos', 'Importe', '%'], body: rank.map((r, i) => [i + 1, r.p.nombre, CATEGORIAS[r.p.categoria], r.u, r.peds, PDF.money(r.$), fmt.pct(r.$ / tot$ * 100)]), columnStyles: { 3: { halign: 'right' }, 4: { halign: 'right' }, 5: { halign: 'right' }, 6: { halign: 'right' } } },
    ], 'productos-mas-vendidos');
  }

  if (inf.k === 'regularidad') {
    const filas = db.clientes.filter(c => c.activo).map(c => {
      const pc = validos.filter(p => p.clienteId === c.id);
      return { c, n: pc.length, $: pc.reduce((s, p) => s + p.total, 0), r: Q.regularidad(c.id) };
    }).filter(x => x.r.cantidad > 0).sort((a, b) => (a.r.promedio || 999) - (b.r.promedio || 999));
    const conProm = filas.filter(x => x.r.promedio);
    const prom = conProm.length ? Math.round(conProm.reduce((s, x) => s + x.r.promedio, 0) / conProm.length) : 0;
    const atras = filas.filter(x => x.r.estado === 'Atrasado');
    const semana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    const porDia = semana.map((_, i) => validos.filter(p => new Date(p.fecha + 'T00:00').getDay() === i).length);
    const rangos = [['≤ 7 días', 0, 7], ['8–14', 8, 14], ['15–21', 15, 21], ['22–30', 22, 30], ['> 30', 31, 9999]];
    const hist = rangos.map(([, a, b]) => conProm.filter(x => x.r.promedio >= a && x.r.promedio <= b).length);
    rep.innerHTML = kpiRow([
      { icon: 'arrow-repeat', color: 'i-orange', label: 'Frecuencia promedio', value: `${prom} días`, sub: 'entre pedidos de un mismo cliente' },
      { icon: 'people', color: 'i-blue', label: 'Clientes activos', value: filas.filter(x => x.n > 0).length, sub: 'con pedidos en el período' },
      { icon: 'alarm', color: 'i-red', label: 'Atrasados', value: atras.length, sub: 'superaron su frecuencia habitual' },
      { icon: 'calendar-event', color: 'i-violet', label: 'Día de más pedidos', value: semana[porDia.indexOf(Math.max(...porDia))], sub: `${Math.max(...porDia)} pedidos` },
    ]) + `
    <div class="row g-3 mb-3">
      <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Pedidos por día de la semana</h2></div><div class="card-body"><div class="chart-box sm"><canvas id="c1"></canvas></div></div></div></div>
      <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Clientes según su frecuencia</h2></div><div class="card-body"><div class="chart-box sm"><canvas id="c2"></canvas></div></div></div></div>
    </div>
    <div class="card"><div class="card-header"><h2>Regularidad por cliente</h2><div class="ms-auto"><select class="form-select form-select-sm" id="fReg"><option value="">Todos</option><option>Atrasado</option><option>Por pedir</option><option>Al día</option></select></div></div><div id="t1"></div></div>`;
    App.chart(document.getElementById('c1'), { type: 'bar', data: { labels: semana, datasets: [{ data: porDia, backgroundColor: '#e8590c', borderRadius: 4 }] }, options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { ticks: { precision: 0 }, grid: { color: '#f1f3f5' } } } } });
    App.chart(document.getElementById('c2'), { type: 'bar', data: { labels: rangos.map(r => r[0]), datasets: [{ label: 'Clientes', data: hist, backgroundColor: '#1971c2', borderRadius: 4 }] }, options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, title: { display: true, text: 'Pide cada…' } }, y: { ticks: { precision: 0 }, grid: { color: '#f1f3f5' } } } } });
    const t = UI.table(document.getElementById('t1'), {
      rows: filas, pageSize: 12,
      columns: [
        { label: 'Cliente', render: x => `<a href="#/clientes/${x.c.id}" class="fw-semibold">${esc(Q.nombreCliente(x.c))}</a><div class="text-muted-sm">${esc(x.c.celular)}</div>` },
        { label: 'Pedidos período', cls: 'num', render: x => x.n },
        { label: 'Importe', cls: 'num', render: x => fmt.money(x.$) },
        { label: 'Pide cada', cls: 'num', render: x => x.r.promedio ? `${x.r.promedio} días` : '—' },
        { label: 'Último', render: x => fmt.date(x.r.ultimo) },
        { label: 'Próximo estimado', render: x => fmt.date(x.r.proximo) },
        { label: 'Estado', render: x => x.r.promedio ? UI.regBadge(x.r.estado) : '—' },
        { label: '', cls: 'actions', render: x => `<a class="btn btn-sm btn-light" href="${UI.waLink(x.c.celular)}" target="_blank" rel="noopener"><i class="bi bi-whatsapp text-success"></i></a>` },
      ],
    });
    document.getElementById('fReg').addEventListener('change', e => t.setRows(filas.filter(x => !e.target.value || x.r.estado === e.target.value)));
    pdf = () => PDF.informe('Regularidad de pedidos', periodo, [
      { resumen: [['Frecuencia promedio', `${prom} días`], ['Clientes con pedidos', filas.filter(x => x.n > 0).length], ['Clientes atrasados', atras.length], ...semana.map((d, i) => [`Pedidos los ${d}`, porDia[i]])] },
      { titulo: 'Detalle por cliente', head: ['Cliente', 'Celular', 'Pedidos', 'Importe', 'Pide cada', 'Último', 'Próximo', 'Estado'], body: t.rows.map(x => [Q.nombreCliente(x.c), x.c.celular, x.n, PDF.money(x.$), x.r.promedio ? `${x.r.promedio} días` : '—', fmt.date(x.r.ultimo), fmt.date(x.r.proximo), x.r.estado]), columnStyles: { 2: { halign: 'right' }, 3: { halign: 'right' } } },
    ], 'regularidad-pedidos', 'l');
  }

  if (inf.k === 'pagos') {
    const pagos = db.pagos.filter(p => enRango(p.fecha));
    const cobrado = pagos.reduce((s, p) => s + p.monto, 0);
    const ef = pagos.filter(p => p.formaPago === 'Efectivo').reduce((s, p) => s + p.monto, 0);
    const vendido = validos.reduce((s, p) => s + p.total, 0);
    const pendPer = validos.reduce((s, p) => s + Q.saldoPedido(p), 0);
    const pProv = db.pagosProveedores.filter(p => enRango(p.fecha));
    const pagadoProv = pProv.reduce((s, p) => s + p.monto, 0);
    const deudores = db.clientes.map(c => ({ c, s: Q.saldoCliente(c.id) })).filter(x => x.s > 0).sort((a, b) => b.s - a.s);
    const habit = FORMAS_PAGO.map(f => db.clientes.filter(c => c.activo && c.formaPago === f).length);
    rep.innerHTML = kpiRow([
      { icon: 'cash-stack', color: 'i-green', label: 'Cobrado', value: fmt.money(cobrado), sub: `${pagos.length} pagos` },
      { icon: 'percent', color: 'i-blue', label: 'Cobrabilidad', value: vendido ? fmt.pct((vendido - pendPer) / vendido * 100) : '—', sub: `de ${fmt.money(vendido)} vendidos` },
      { icon: 'exclamation-diamond', color: 'i-red', label: 'Deuda de clientes', value: fmt.money(deudores.reduce((s, x) => s + x.s, 0)), sub: `${deudores.length} clientes` },
      { icon: 'truck', color: 'i-violet', label: 'Pagado a proveedores', value: fmt.money(pagadoProv), sub: `Adeudado: ${fmt.money(db.proveedores.reduce((s, p) => s + Q.deudaProveedor(p.id), 0))}` },
    ]) + `
    <div class="card mb-3"><div class="card-header"><h2>Ingresos vs. egresos por día</h2></div><div class="card-body"><div class="chart-box"><canvas id="c1"></canvas></div></div></div>
    <div class="row g-3 mb-3">
      <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Cobros por forma de pago</h2></div><div class="card-body"><div class="chart-box sm"><canvas id="c2"></canvas></div></div></div></div>
      <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Forma de pago habitual de los clientes</h2></div><div class="card-body"><div class="chart-box sm"><canvas id="c3"></canvas></div></div></div></div>
    </div>
    <div class="card"><div class="card-header"><h2>Clientes con saldo pendiente</h2></div><div id="t1"></div></div>`;
    App.chart(document.getElementById('c1'), { type: 'line', data: { labels: dias.map(d => fmt.date(d).slice(0, 5)), datasets: [
      { label: 'Cobros a clientes', data: dias.map(d => pagos.filter(p => p.fecha === d).reduce((s, p) => s + p.monto, 0)), borderColor: '#2b8a3e', backgroundColor: 'rgba(43,138,62,.1)', fill: true, tension: .3 },
      { label: 'Pagos a proveedores', data: dias.map(d => pProv.filter(p => p.fecha === d).reduce((s, p) => s + p.monto, 0)), borderColor: '#c92a2a', backgroundColor: 'rgba(201,42,42,.05)', fill: true, tension: .3 },
    ] }, options: { maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, scales: { y: { ticks: { callback: v => '$' + v / 1000 + 'k' }, grid: { color: '#f1f3f5' } }, x: { grid: { display: false } } }, plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: c => ` ${c.dataset.label}: ${fmt.money(c.raw)}` } } } } });
    App.chart(document.getElementById('c2'), { type: 'doughnut', data: { labels: FORMAS_PAGO, datasets: [{ data: [ef, cobrado - ef], backgroundColor: ['#40c057', '#1971c2'] }] }, options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' }, tooltip: { callbacks: { label: c => ` ${c.label}: ${fmt.money(c.raw)}` } } } } });
    App.chart(document.getElementById('c3'), { type: 'doughnut', data: { labels: FORMAS_PAGO, datasets: [{ data: habit, backgroundColor: ['#40c057', '#1971c2'] }] }, options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' }, tooltip: { callbacks: { label: c => ` ${c.label}: ${c.raw} clientes` } } } } });
    UI.table(document.getElementById('t1'), {
      rows: deudores, pageSize: 10,
      columns: [
        { label: 'Cliente', render: x => `<a href="#/clientes/${x.c.id}" class="fw-semibold">${esc(Q.nombreCliente(x.c))}</a>` },
        { label: 'Celular', render: x => esc(x.c.celular) }, { label: 'Pago habitual', render: x => UI.formaPago(x.c.formaPago) },
        { label: 'Saldo', cls: 'num fw-semibold text-danger', render: x => fmt.money(x.s) },
      ],
      empty: 'No hay clientes con deuda',
    });
    pdf = () => PDF.informe('Gestión de pagos', periodo, [
      { resumen: [['Total vendido', PDF.money(vendido)], ['Total cobrado', PDF.money(cobrado)], ['Cobrado en efectivo', PDF.money(ef)], ['Cobrado por transferencia', PDF.money(cobrado - ef)], ['Pendiente de cobro (período)', PDF.money(pendPer)], ['Pagado a proveedores', PDF.money(pagadoProv)], ['Resultado de caja', PDF.money(cobrado - pagadoProv)]] },
      { titulo: 'Clientes con saldo pendiente', head: ['Cliente', 'Celular', 'Pago habitual', 'Saldo'], body: deudores.map(x => [Q.nombreCliente(x.c), x.c.celular, x.c.formaPago, PDF.money(x.s)]), columnStyles: { 3: { halign: 'right' } } },
      { titulo: 'Pagos a proveedores', head: ['Fecha', 'Proveedor', 'Forma', 'Importe'], body: pProv.map(x => [fmt.date(x.fecha), Q.proveedor(x.proveedorId).razonSocial, x.formaPago, PDF.money(x.monto)]), columnStyles: { 3: { halign: 'right' } } },
    ], 'gestion-pagos');
  }

  if (inf.k === 'garrafas') {
    const filas = Q.garrafas().map(g => { const e = db.envases[g.id], cl = Q.envasesEnClientes(g.id); return { g, e, cl, dep: e.llenas + e.vacias + e.noAptas }; });
    rep.innerHTML = kpiRow([
      { icon: 'droplet-fill', color: 'i-green', label: 'Llenas', value: filas.reduce((s, x) => s + x.e.llenas, 0) },
      { icon: 'droplet', color: 'i-blue', label: 'Vacías aptas', value: filas.reduce((s, x) => s + x.e.vacias, 0) },
      { icon: 'x-octagon', color: 'i-red', label: 'No aptas', value: filas.reduce((s, x) => s + x.e.noAptas, 0) },
      { icon: 'house', color: 'i-violet', label: 'En clientes', value: filas.reduce((s, x) => s + x.cl, 0) },
    ]) + `<div class="card"><div class="table-responsive"><table class="table align-middle">
      <thead><tr><th>Garrafa</th><th class="num">Llenas</th><th class="num">Vacías aptas</th><th class="num">No aptas</th><th class="num">En depósito</th><th class="num">En clientes</th><th class="num">Parque total</th><th class="num">Mínimo</th></tr></thead>
      <tbody>${filas.map(x => `<tr><td class="fw-semibold">${esc(x.g.nombre)}</td><td class="num ${x.e.llenas <= x.g.stockMin ? 'text-danger fw-bold' : ''}">${x.e.llenas}</td><td class="num">${x.e.vacias}</td><td class="num">${x.e.noAptas}</td><td class="num">${x.dep}</td><td class="num">${x.cl}</td><td class="num fw-semibold">${x.dep + x.cl}</td><td class="num">${x.g.stockMin}</td></tr>`).join('')}</tbody>
    </table></div><div class="card-body border-top"><a href="#/garrafas">Ir al control de garrafas <i class="bi bi-arrow-right"></i></a></div></div>`;
    pdf = () => PDF.informe('Stock de garrafas', `Al ${fmt.date(Q.hoy())}`, [{ head: ['Garrafa', 'Llenas', 'Vacías aptas', 'No aptas', 'En depósito', 'En clientes', 'Parque', 'Mínimo'], body: filas.map(x => [x.g.nombre, x.e.llenas, x.e.vacias, x.e.noAptas, x.dep, x.cl, x.dep + x.cl, x.g.stockMin]) }], 'stock-garrafas');
  }

  App.actions({ pdf: () => pdf() });
});

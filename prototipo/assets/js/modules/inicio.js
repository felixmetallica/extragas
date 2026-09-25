/* Inicio: panel con el resumen del día */

App.route('/inicio', v => {
  const db = Store.db, hoy = Q.hoy();
  App.setTitle('Inicio', [[fmt.dateLong(hoy)]]);

  const pedHoy = db.pedidos.filter(p => p.fecha === hoy && p.estado !== 'Cancelado');
  const abiertos = db.pedidos.filter(p => ['Pendiente', 'En preparación', 'En reparto'].includes(p.estado));
  const mes = hoy.slice(0, 7);
  const ventasMes = db.pedidos.filter(p => p.fecha.startsWith(mes) && p.estado !== 'Cancelado').reduce((s, p) => s + p.total, 0);
  const cobHoy = db.pagos.filter(p => p.fecha === hoy);
  const cobHoyEf = cobHoy.filter(p => p.formaPago === 'Efectivo').reduce((s, p) => s + p.monto, 0);
  const cobHoyTr = cobHoy.filter(p => p.formaPago === 'Transferencia').reduce((s, p) => s + p.monto, 0);
  const porCobrar = db.pedidos.reduce((s, p) => s + Q.saldoPedido(p), 0);
  const deudores = db.clientes.filter(c => Q.saldoCliente(c.id) > 0).length;

  const bajoStock = db.productos.filter(p => p.activo && (p.envase ? db.envases[p.id].llenas : p.stock) <= p.stockMin);
  const porPedir = db.clientes.map(c => ({ c, r: Q.regularidad(c.id) })).filter(x => x.r.estado === 'Atrasado' || x.r.estado === 'Por pedir')
    .sort((a, b) => b.r.atraso - a.r.atraso).slice(0, 6);

  v.innerHTML = `
  <div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">${UI.kpi({ icon: 'cart-check', color: 'i-orange', label: 'Pedidos de hoy', value: pedHoy.length, sub: `${abiertos.length} en curso sin entregar` })}</div>
    <div class="col-sm-6 col-xl-3">${UI.kpi({ icon: 'cash-stack', color: 'i-green', label: 'Cobrado hoy', value: fmt.money(cobHoyEf + cobHoyTr), sub: `Efectivo ${fmt.money(cobHoyEf)} · Transf. ${fmt.money(cobHoyTr)}` })}</div>
    <div class="col-sm-6 col-xl-3">${UI.kpi({ icon: 'exclamation-diamond', color: 'i-red', label: 'Pendiente de cobro', value: fmt.money(porCobrar), sub: `${deudores} clientes con saldo` })}</div>
    <div class="col-sm-6 col-xl-3">${UI.kpi({ icon: 'graph-up-arrow', color: 'i-blue', label: 'Ventas del mes', value: fmt.money(ventasMes), sub: new Date().toLocaleDateString('es-AR', { month: 'long', year: 'numeric' }) })}</div>
  </div>

  <div class="row g-3">
    <div class="col-xl-8">
      <div class="card mb-3">
        <div class="card-header"><h2><i class="bi bi-list-task me-1 text-brand"></i> Pedidos en curso</h2>
          <div class="ms-auto"><a href="#/pedidos" class="btn btn-sm btn-light">Ver todos</a><a href="#/pedidos/nuevo" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Nuevo</a></div></div>
        <div class="table-responsive"><table class="table table-hover align-middle">
          <thead><tr><th>N°</th><th>Cliente</th><th>Canal</th><th style="min-width:150px">Productos</th><th class="num">Total</th><th>Estado</th><th></th></tr></thead>
          <tbody>${abiertos.length ? abiertos.sort((a, b) => (a.fecha + a.hora).localeCompare(b.fecha + b.hora)).map(p => {
            const c = Q.cliente(p.clienteId);
            const next = { 'Pendiente': 'En preparación', 'En preparación': 'En reparto', 'En reparto': 'Entregado' }[p.estado];
            return `<tr><td><a href="#/pedidos/${p.id}" class="fw-semibold">#${fmt.nro(p.numero)}</a><div class="text-muted-sm">${p.hora}</div></td>
              <td style="min-width:160px">${esc(Q.nombreCliente(c))}<div class="text-muted-sm">${esc(p.entrega === 'A domicilio' ? c.domicilio : 'Retira en local')}</div></td>
              <td>${UI.canal(p.canal)}</td><td class="small">${UI.itemsResumen(p.items)}</td>
              <td class="num">${fmt.money(p.total)}</td><td>${UI.estadoBadge(p.estado)}</td>
              <td class="actions"><button class="btn btn-sm btn-outline-primary" data-action="avanzar" data-id="${p.id}" title="Pasar a ${next}"><i class="bi bi-arrow-right-circle"></i> ${next === 'Entregado' ? 'Entregar' : 'Avanzar'}</button></td></tr>`;
          }).join('') : `<tr><td colspan="7" class="empty"><i class="bi bi-check2-all"></i>No hay pedidos pendientes</td></tr>`}</tbody>
        </table></div>
      </div>
      <div class="card">
        <div class="card-header"><h2><i class="bi bi-bar-chart me-1 text-brand"></i> Ventas de los últimos 14 días</h2></div>
        <div class="card-body"><div class="chart-box sm"><canvas id="chVentas"></canvas></div></div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="card mb-3">
        <div class="card-header"><h2><i class="bi bi-fuel-pump me-1 text-brand"></i> Garrafas en depósito</h2><div class="ms-auto"><a href="#/garrafas" class="btn btn-sm btn-light">Detalle</a></div></div>
        <div class="card-body">
          ${Q.garrafas().map(g => {
            const e = db.envases[g.id], enCli = Q.envasesEnClientes(g.id);
            const total = e.llenas + e.vacias + e.noAptas + enCli;
            const w = n => (n / total * 100).toFixed(1) + '%';
            return `<div class="mb-3"><div class="d-flex justify-content-between"><b>${esc(g.nombre)}</b>
              ${e.llenas <= g.stockMin ? '<span class="badge-soft b-impago">Stock bajo</span>' : ''}</div>
              <div class="stackbar"><div style="width:${w(e.llenas)};background:#40c057"></div><div style="width:${w(e.vacias)};background:#4dabf7"></div><div style="width:${w(e.noAptas)};background:#fa5252"></div><div style="width:${w(enCli)};background:#9775fa"></div></div>
              <div class="d-flex justify-content-between small mt-1 text-body-secondary"><span><span class="legend-dot" style="background:#40c057"></span>${e.llenas} llenas</span><span><span class="legend-dot" style="background:#4dabf7"></span>${e.vacias} vacías</span><span><span class="legend-dot" style="background:#fa5252"></span>${e.noAptas} no aptas</span><span><span class="legend-dot" style="background:#9775fa"></span>${enCli} clientes</span></div></div>`;
          }).join('')}
        </div>
      </div>
      <div class="card mb-3">
        <div class="card-header"><h2><i class="bi bi-alarm me-1 text-brand"></i> Clientes que suelen pedir</h2></div>
        <ul class="list-group list-group-flush">
          ${porPedir.length ? porPedir.map(({ c, r }) => `<li class="list-group-item d-flex align-items-center gap-2">
            <div class="flex-fill min-w-0"><a href="#/clientes/${c.id}" class="fw-semibold">${esc(Q.nombreCliente(c))}</a>
            <div class="text-muted-sm">Pide cada ${r.promedio} días · último ${fmt.date(r.ultimo)}</div></div>
            ${UI.regBadge(r.estado)}
            <a class="btn btn-sm btn-light" href="${UI.waLink(c.celular)}" target="_blank" rel="noopener" title="Escribir por WhatsApp"><i class="bi bi-whatsapp text-success"></i></a></li>`).join('')
            : '<li class="list-group-item empty">Sin avisos</li>'}
        </ul>
      </div>
      <div class="card">
        <div class="card-header"><h2><i class="bi bi-exclamation-triangle me-1 text-brand"></i> Stock bajo</h2></div>
        <ul class="list-group list-group-flush">
          ${bajoStock.length ? bajoStock.map(p => `<li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-${UI.prodIcon(p)} me-2"></i>${esc(p.nombre)}</span>
            <span class="fw-semibold text-danger">${p.envase ? db.envases[p.id].llenas : p.stock} <small class="text-body-secondary fw-normal">/ mín. ${p.stockMin}</small></span></li>`).join('')
            : '<li class="list-group-item empty">Todo el stock está por encima del mínimo</li>'}
        </ul>
      </div>
    </div>
  </div>`;

  const dias = [...Array(14)].map((_, i) => addDays(hoy, i - 13));
  const porDia = f => db.pedidos.filter(p => p.fecha === f && p.estado !== 'Cancelado');
  App.chart(document.getElementById('chVentas'), {
    type: 'bar',
    data: {
      labels: dias.map(d => fmt.date(d).slice(0, 5)),
      datasets: [{ label: 'Ventas ($)', data: dias.map(d => porDia(d).reduce((s, p) => s + p.total, 0)), backgroundColor: '#e8590c', borderRadius: 5, yAxisID: 'y' },
        { label: 'Pedidos', type: 'line', data: dias.map(d => porDia(d).length), borderColor: '#1971c2', backgroundColor: '#1971c2', tension: .3, yAxisID: 'y1' }],
    },
    options: { maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
      scales: { y: { ticks: { callback: v => '$' + v / 1000 + 'k' }, grid: { color: '#f1f3f5' } }, y1: { position: 'right', grid: { display: false }, beginAtZero: true }, x: { grid: { display: false } } },
      plugins: { tooltip: { callbacks: { label: c => c.datasetIndex === 0 ? ` ${fmt.money(c.raw)}` : ` ${c.raw} pedidos` } } } },
  });

  App.actions({
    avanzar: id => Pedidos.avanzar(Q.pedido(id)),
  });
});

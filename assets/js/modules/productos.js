/* Productos y precios: catálogo, precios, stock de carbón y leña */

const Productos = {
  form(p) {
    const n = p || { categoria: 'carbon', nombre: '', pesoKg: '', precio: '', costo: '', stockMin: 5, stock: 0, activo: true };
    UI.modal({
      title: p ? `Editar ${esc(p.nombre)}` : 'Nuevo producto',
      body: `<div class="row g-3">
        <div class="col-md-6"><label class="form-label">Categoría</label><select class="form-select" name="categoria" ${p ? 'disabled' : ''}>${UI.options(Object.entries(CATEGORIAS), n.categoria, { value: x => x[0], label: x => x[1] })}</select></div>
        <div class="col-md-6"><label class="form-label req">Presentación (kg)</label><input type="number" min="1" class="form-control" name="pesoKg" required value="${n.pesoKg}"></div>
        <div class="col-12"><label class="form-label req">Nombre</label><input class="form-control" name="nombre" required value="${esc(n.nombre)}"></div>
        <div class="col-md-6"><label class="form-label req">Precio de venta</label><div class="input-group"><span class="input-group-text">$</span><input type="number" min="0" step="100" class="form-control" name="precio" required value="${n.precio}"></div></div>
        <div class="col-md-6"><label class="form-label">Costo de compra</label><div class="input-group"><span class="input-group-text">$</span><input type="number" min="0" step="100" class="form-control" name="costo" value="${n.costo}"></div></div>
        <div class="col-md-6"><label class="form-label">Stock mínimo ${n.envase ? '(garrafas llenas)' : ''}</label><input type="number" min="0" class="form-control" name="stockMin" value="${n.stockMin}"></div>
        ${!p ? `<div class="col-md-6"><label class="form-label">Stock inicial</label><input type="number" min="0" class="form-control" name="stock" value="0"></div>` : ''}
        ${p ? `<div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="activo" id="prodAct" ${n.activo ? 'checked' : ''}><label class="form-check-label" for="prodAct">Disponible para la venta</label></div></div>` : ''}
      </div>`,
      onSubmit: d => {
        const num = ['pesoKg', 'precio', 'costo', 'stockMin', 'stock'];
        num.forEach(k => { if (k in d) d[k] = +d[k] || 0; });
        if (p) Object.assign(p, d);
        else {
          const np = { id: Store.nextId('productos'), ...d, envase: d.categoria === 'gas', activo: true };
          if (np.envase) { Store.db.envases[np.id] = { llenas: d.stock, vacias: 0, noAptas: 0 }; delete np.stock; Store.db.clientes.forEach(c => { c.envases[np.id] = 0; }); }
          Store.db.productos.push(np);
        }
        App.save(); UI.toast('Producto guardado'); App.render();
      },
    });
  },

  ajusteStock(p) {
    UI.modal({
      title: `Ajustar stock · ${esc(p.nombre)}`, size: 'modal-sm',
      body: `<p class="text-muted-sm mb-2">Stock actual: <b>${p.stock}</b> bolsas</p>
        <label class="form-label">Tipo de ajuste</label><select class="form-select mb-2" name="tipo"><option value="set">Fijar cantidad contada</option><option value="baja">Baja por rotura / pérdida</option></select>
        <label class="form-label">Cantidad</label><input type="number" min="0" class="form-control mb-2" name="cant" required value="${p.stock}">
        <label class="form-label">Motivo</label><input class="form-control" name="motivo">`,
      onSubmit: d => {
        p.stock = d.tipo === 'set' ? +d.cant : Math.max(0, p.stock - +d.cant);
        App.save(); UI.toast('Stock actualizado'); App.render();
      },
    });
  },

  actualizarPrecios() {
    const prods = Store.db.productos.filter(p => p.activo);
    const md = UI.modal({
      title: 'Actualización de precios', size: 'modal-lg',
      body: `<div class="row g-2 align-items-end mb-3">
        <div class="col-sm-5"><label class="form-label">Aplicar a</label><select class="form-select" id="apCat"><option value="">Todos los productos</option>${UI.options(Object.entries(CATEGORIAS), '', { value: x => x[0], label: x => x[1] })}</select></div>
        <div class="col-sm-4"><label class="form-label">Aumento %</label><div class="input-group"><input type="number" step="0.5" class="form-control" id="apPct" value="10"><span class="input-group-text">%</span></div></div>
        <div class="col-sm-3"><button type="button" class="btn btn-outline-primary w-100" id="apBtn">Aplicar</button></div>
      </div>
      <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Producto</th><th class="num">Precio actual</th><th style="width:170px">Nuevo precio</th></tr></thead><tbody>
      ${prods.map(p => `<tr data-cat="${p.categoria}"><td>${esc(p.nombre)}</td><td class="num">${fmt.money(p.precio)}</td><td><input type="number" min="0" step="100" class="form-control form-control-sm text-end" name="p${p.id}" value="${p.precio}"></td></tr>`).join('')}
      </tbody></table></div><div class="form-text">Los precios nuevos se aplican a los pedidos que se carguen desde ahora; los pedidos ya registrados conservan su precio.</div>`,
      submit: 'Guardar precios',
      onSubmit: d => { prods.forEach(p => { p.precio = +d['p' + p.id] || p.precio; }); App.save(); UI.toast('Precios actualizados'); App.render(); },
    });
    md.form.querySelector('#apBtn').addEventListener('click', () => {
      const cat = md.form.querySelector('#apCat').value, pct = +md.form.querySelector('#apPct').value || 0;
      prods.forEach(p => { if (!cat || p.categoria === cat) md.form['p' + p.id].value = Math.round(p.precio * (1 + pct / 100) / 100) * 100; });
    });
  },
};

App.route('/productos', v => {
  App.setTitle('Productos y precios', [['Depósito'], ['Productos y precios']]);
  const db = Store.db;
  const desde = addDays(Q.hoy(), -29);
  const vend = {};
  db.pedidos.filter(p => p.fecha >= desde && p.estado !== 'Cancelado').forEach(p => p.items.forEach(it => { vend[it.productoId] = (vend[it.productoId] || 0) + it.cantidad; }));

  v.innerHTML = `
  <div class="toolbar">
    <div class="text-body-secondary small">La venta de los últimos 30 días se muestra en cada producto.</div>
    <div class="ms-auto d-flex gap-2">
      <button class="btn btn-sm btn-light" data-action="precios"><i class="bi bi-percent"></i> Actualizar precios</button>
      <button class="btn btn-sm btn-primary" data-action="nuevo"><i class="bi bi-plus-lg"></i> Nuevo producto</button>
    </div>
  </div>
  ${Object.entries(CATEGORIAS).map(([k, nombre]) => `
  <div class="card mb-3">
    <div class="card-header"><h2><i class="bi bi-${{ gas: 'fuel-pump-fill', carbon: 'fire', lena: 'tree-fill' }[k]} me-1 text-brand"></i> ${nombre}</h2>
      ${k === 'gas' ? '<div class="ms-auto"><a href="#/garrafas" class="btn btn-sm btn-light">Ver control de envases</a></div>' : ''}</div>
    <div class="table-responsive"><table class="table table-hover align-middle">
      <thead><tr><th>Producto</th><th class="num">Precio venta</th><th class="num">Costo</th><th class="num">Margen</th><th class="num">${k === 'gas' ? 'Llenas' : 'Stock'}</th><th class="num">Mínimo</th><th class="num">Vendidos 30 d</th><th>Estado</th><th></th></tr></thead>
      <tbody>${db.productos.filter(p => p.categoria === k).map(p => {
        const stock = p.envase ? db.envases[p.id].llenas : p.stock;
        const margen = p.precio ? (p.precio - p.costo) / p.precio * 100 : 0;
        return `<tr class="${p.activo ? '' : 'opacity-50'}"><td class="fw-semibold">${esc(p.nombre)}<div class="text-muted-sm">${p.pesoKg} kg</div></td>
          <td class="num fw-semibold">${fmt.money(p.precio)}</td><td class="num">${fmt.money(p.costo)}</td><td class="num">${fmt.pct(margen)}</td>
          <td class="num ${stock <= p.stockMin ? 'text-danger fw-bold' : ''}">${stock}</td><td class="num">${p.stockMin}</td><td class="num">${vend[p.id] || 0}</td>
          <td>${!p.activo ? '<span class="badge-soft b-gray">Inactivo</span>' : stock <= p.stockMin ? '<span class="badge-soft b-impago">Stock bajo</span>' : '<span class="badge-soft b-pagado">Disponible</span>'}</td>
          <td class="actions">${!p.envase ? `<button class="btn btn-sm btn-light" data-action="stock" data-id="${p.id}" title="Ajustar stock"><i class="bi bi-box-seam"></i></button>` : ''}
            <button class="btn btn-sm btn-light" data-action="editar" data-id="${p.id}" title="Editar"><i class="bi bi-pencil"></i></button></td></tr>`;
      }).join('')}</tbody></table></div>
  </div>`).join('')}`;

  App.actions({
    nuevo: () => Productos.form(null),
    editar: id => Productos.form(Q.producto(id)),
    stock: id => Productos.ajusteStock(Q.producto(id)),
    precios: () => Productos.actualizarPrecios(),
  });
});

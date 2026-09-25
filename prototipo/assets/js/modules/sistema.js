/* Sistema: usuarios y configuración */

App.route('/usuarios', v => {
  App.setTitle('Usuarios', [['Sistema'], ['Usuarios']]);
  const db = Store.db;
  const form = u => {
    const n = u || { nombre: '', apellido: '', usuario: '', email: '', rol: 'Empleado', activo: true };
    UI.modal({
      title: u ? 'Editar usuario' : 'Nuevo usuario',
      body: `<div class="row g-3">
        <div class="col-md-6"><label class="form-label req">Nombre</label><input class="form-control" name="nombre" required value="${esc(n.nombre)}"></div>
        <div class="col-md-6"><label class="form-label req">Apellido</label><input class="form-control" name="apellido" required value="${esc(n.apellido)}"></div>
        <div class="col-md-6"><label class="form-label req">Usuario</label><input class="form-control" name="usuario" required autocomplete="off" value="${esc(n.usuario)}"></div>
        <div class="col-md-6"><label class="form-label">Rol</label><select class="form-select" name="rol">${UI.options(['Administrador', 'Empleado'], n.rol)}</select></div>
        <div class="col-12"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="${esc(n.email)}"></div>
        <div class="col-md-6"><label class="form-label ${u ? '' : 'req'}">Contraseña</label><input type="password" class="form-control" name="password" autocomplete="new-password" ${u ? 'placeholder="Sin cambios"' : 'required'} minlength="6"></div>
        <div class="col-md-6"><label class="form-label ${u ? '' : 'req'}">Repetir contraseña</label><input type="password" class="form-control" name="password2" autocomplete="new-password" ${u ? '' : 'required'}></div>
        ${u ? `<div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="activo" id="uAct" ${n.activo ? 'checked' : ''}><label class="form-check-label" for="uAct">Usuario activo</label></div></div>` : ''}
        <div class="col-12"><div class="alert alert-light border small mb-0"><b>Administrador:</b> acceso total, precios, usuarios y configuración. <b>Empleado:</b> pedidos, clientes, cobros, garrafas y recepciones.</div></div>
      </div>`,
      onSubmit: d => {
        if (d.password !== d.password2) { UI.toast('Las contraseñas no coinciden', 'error'); return false; }
        if (db.usuarios.some(x => x !== u && x.usuario === d.usuario)) { UI.toast('Ese nombre de usuario ya existe', 'error'); return false; }
        // La contraseña la gestiona el backend (hash); en el prototipo no se almacena
        delete d.password; delete d.password2;
        if (u) Object.assign(u, d); else db.usuarios.push({ id: Store.nextId('usuarios'), ...d, activo: true, ultimoAcceso: null });
        App.save(); UI.toast('Usuario guardado'); App.render();
      },
    });
  };
  v.innerHTML = `<div class="toolbar"><div class="text-body-secondary small">Personas que usan el sistema: el dueño y los empleados que atienden los pedidos.</div>
    <div class="ms-auto"><button class="btn btn-sm btn-primary" data-action="nuevo"><i class="bi bi-person-plus"></i> Nuevo usuario</button></div></div>
    <div class="card" id="tbl"></div>`;
  UI.table(document.getElementById('tbl'), {
    rows: db.usuarios,
    columns: [
      { label: 'Usuario', render: u => `<div class="d-flex align-items-center gap-2"><span class="avatar">${esc(u.nombre[0] + (u.apellido[0] || ''))}</span><div><div class="fw-semibold">${esc(u.nombre)} ${esc(u.apellido)}</div><div class="text-muted-sm">@${esc(u.usuario)}</div></div></div>` },
      { label: 'Rol', render: u => `<span class="badge-soft ${u.rol === 'Administrador' ? 'b-parcial' : 'b-info'}">${u.rol}</span>` },
      { label: 'Email', render: u => esc(u.email) || '—' },
      { label: 'Pedidos cargados', cls: 'num', render: u => db.pedidos.filter(p => p.usuarioId === u.id).length },
      { label: 'Último acceso', render: u => fmt.date(u.ultimoAcceso) },
      { label: 'Estado', render: u => u.activo ? '<span class="badge-soft b-pagado">Activo</span>' : '<span class="badge-soft b-gray">Inactivo</span>' },
      { label: '', cls: 'actions', render: u => `<button class="btn btn-sm btn-light" data-action="editar" data-id="${u.id}"><i class="bi bi-pencil"></i></button>` },
    ],
  });
  App.actions({ nuevo: () => form(null), editar: id => form(Q.usuario(id)) });
});

App.route('/configuracion', v => {
  App.setTitle('Configuración', [['Sistema'], ['Configuración']]);
  const db = Store.db, e = db.empresa;
  v.innerHTML = `
  <div class="row g-3">
    <div class="col-lg-7">
      <form class="card" id="fEmp">
        <div class="card-header"><h2>Datos de la empresa</h2><small class="text-body-secondary">Se muestran en los PDF de pedidos, recibos e informes</small></div>
        <div class="card-body row g-3">
          <div class="col-md-6"><label class="form-label">Nombre comercial</label><input class="form-control" name="nombre" value="${esc(e.nombre)}"></div>
          <div class="col-md-6"><label class="form-label">CUIT</label><input class="form-control" name="cuit" value="${esc(e.cuit)}"></div>
          <div class="col-12"><label class="form-label">Razón social / descripción</label><input class="form-control" name="razonSocial" value="${esc(e.razonSocial)}"></div>
          <div class="col-md-6"><label class="form-label">Dirección</label><input class="form-control" name="direccion" value="${esc(e.direccion)}"></div>
          <div class="col-md-6"><label class="form-label">Localidad</label><input class="form-control" name="localidad" value="${esc(e.localidad)}"></div>
          <div class="col-md-4"><label class="form-label">Teléfono</label><input class="form-control" name="telefono" value="${esc(e.telefono)}"></div>
          <div class="col-md-4"><label class="form-label">WhatsApp</label><input class="form-control" name="whatsapp" value="${esc(e.whatsapp)}"></div>
          <div class="col-md-4"><label class="form-label">Email</label><input class="form-control" name="email" value="${esc(e.email)}"></div>
          <div class="col-12"><label class="form-label">Horario de atención</label><input class="form-control" name="horario" value="${esc(e.horario)}"></div>
        </div>
        <div class="card-body border-top text-end"><button class="btn btn-primary"><i class="bi bi-check2"></i> Guardar datos</button></div>
      </form>
    </div>
    <div class="col-lg-5">
      <form class="card mb-3" id="fPar">
        <div class="card-header"><h2>Parámetros</h2></div>
        <div class="card-body">
          <label class="form-label">Tolerancia de regularidad (días)</label>
          <input type="number" min="0" class="form-control" name="toleranciaDias" value="${db.parametros.toleranciaDias}">
          <div class="form-text mb-3">Días de margen antes de marcar a un cliente como "Atrasado" respecto de su frecuencia habitual.</div>
          <div class="section-title">Stock mínimo de garrafas llenas</div>
          ${Q.garrafas().map(g => `<div class="row align-items-center mb-2"><label class="col-7 col-form-label">${esc(g.nombre)}</label><div class="col-5"><input type="number" min="0" class="form-control form-control-sm" name="min${g.id}" value="${g.stockMin}"></div></div>`).join('')}
        </div>
        <div class="card-body border-top text-end"><button class="btn btn-primary"><i class="bi bi-check2"></i> Guardar parámetros</button></div>
      </form>
      <div class="card">
        <div class="card-header"><h2>Copia de seguridad</h2></div>
        <div class="card-body d-grid gap-2">
          <button class="btn btn-light text-start" data-action="exportar"><i class="bi bi-download me-2"></i>Descargar copia de los datos (JSON)</button>
          <label class="btn btn-light text-start mb-0"><i class="bi bi-upload me-2"></i>Restaurar copia<input type="file" accept=".json" id="imp" hidden></label>
          <button class="btn btn-light text-start text-danger" data-action="reset"><i class="bi bi-arrow-counterclockwise me-2"></i>Restablecer datos de demostración</button>
        </div>
      </div>
    </div>
  </div>`;

  document.getElementById('fEmp').addEventListener('submit', ev => {
    ev.preventDefault();
    Object.assign(db.empresa, Object.fromEntries(new FormData(ev.target).entries()));
    App.save(); UI.toast('Datos de la empresa guardados');
  });
  document.getElementById('fPar').addEventListener('submit', ev => {
    ev.preventDefault();
    const d = Object.fromEntries(new FormData(ev.target).entries());
    db.parametros.toleranciaDias = +d.toleranciaDias || 0;
    Q.garrafas().forEach(g => { g.stockMin = +d['min' + g.id] || 0; });
    App.save(); UI.toast('Parámetros guardados');
  });
  document.getElementById('imp').addEventListener('change', ev => {
    const file = ev.target.files[0]; if (!file) return;
    file.text().then(t => {
      try {
        const data = JSON.parse(t);
        if (!data.pedidos || !data.clientes) throw new Error('formato');
        Store.replace(data); UI.toast('Datos restaurados'); App.go('#/inicio');
      } catch (err) { UI.toast('El archivo no es una copia válida', 'error'); }
    });
  });
  App.actions({
    exportar: () => {
      const blob = new Blob([JSON.stringify(db, null, 2)], { type: 'application/json' });
      const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = `extragas-backup-${Q.hoy()}.json`; a.click();
      setTimeout(() => URL.revokeObjectURL(a.href), 1000);
    },
    reset: async () => {
      if (await UI.confirm('Se borrarán todos los datos cargados y se regenerarán los datos de demostración.', { title: 'Restablecer datos', ok: 'Restablecer', danger: true })) {
        Store.reset(); UI.toast('Datos de demostración restablecidos', 'info'); App.go('#/inicio');
      }
    },
  });
});

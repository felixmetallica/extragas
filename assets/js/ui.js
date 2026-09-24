/* Utilidades de interfaz: formato, badges, tablas, modales y avisos */

const fmt = {
  money: n => new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(n || 0),
  num: n => new Intl.NumberFormat('es-AR').format(n || 0),
  date: iso => { if (!iso) return '—'; const [y, m, d] = iso.split('-'); return `${d}/${m}/${y}`; },
  dateLong: iso => new Date(iso + 'T00:00').toLocaleDateString('es-AR', { weekday: 'long', day: 'numeric', month: 'long' }),
  pct: n => `${Math.round(n)}%`,
  nro: (n, len = 6) => String(n).padStart(len, '0'),
};

function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, ch => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
}

const UI = {
  estadoBadge(estado) {
    const cls = { 'Pendiente': 'b-pendiente', 'En preparación': 'b-preparacion', 'En reparto': 'b-reparto', 'Entregado': 'b-entregado', 'Cancelado': 'b-cancelado' }[estado] || 'b-gray';
    return `<span class="badge-soft ${cls}">${esc(estado)}</span>`;
  },
  pagoBadge(estado) {
    const cls = { 'Pagado': 'b-pagado', 'Parcial': 'b-parcial', 'Impago': 'b-impago' }[estado];
    return cls ? `<span class="badge-soft ${cls}">${estado}</span>` : '<span class="text-body-tertiary">—</span>';
  },
  regBadge(estado) {
    const cls = { 'Al día': 'b-pagado', 'Por pedir': 'b-parcial', 'Atrasado': 'b-impago' }[estado] || 'b-gray';
    return `<span class="badge-soft ${cls}">${estado}</span>`;
  },
  canal(c) {
    const ic = { 'Teléfono': 'telephone', 'WhatsApp': 'whatsapp', 'Local': 'shop' }[c] || 'question';
    return `<span class="canal"><i class="bi bi-${ic}"></i>${esc(c)}</span>`;
  },
  formaPago(f) {
    const ic = f === 'Efectivo' ? 'cash' : 'bank';
    return `<span class="text-nowrap"><i class="bi bi-${ic} me-1 text-body-secondary"></i>${esc(f)}</span>`;
  },
  prodIcon(p) {
    return { gas: 'fuel-pump-fill', carbon: 'fire', lena: 'tree-fill' }[p.categoria];
  },
  waLink(cel) {
    const n = String(cel || '').replace(/\D/g, '');
    return n ? `https://wa.me/549${n}` : '#';
  },
  kpi({ icon, color, label, value, sub = '' }) {
    return `<div class="card kpi"><div class="kpi-icon ${color}"><i class="bi bi-${icon}"></i></div>
      <div class="min-w-0"><div class="kpi-label">${label}</div><div class="kpi-value">${value}</div><div class="kpi-sub">${sub}</div></div></div>`;
  },
  options(list, selected, { value = x => x, label = x => x, empty } = {}) {
    return (empty !== undefined ? `<option value="">${esc(empty)}</option>` : '') +
      list.map(x => `<option value="${esc(value(x))}" ${String(value(x)) === String(selected ?? '') ? 'selected' : ''}>${esc(label(x))}</option>`).join('');
  },
  itemsResumen(items) {
    return items.map(it => `${it.cantidad}× ${esc(Q.producto(it.productoId).nombre)}`).join(', ');
  },

  /* Tabla con búsqueda y paginación. Devuelve un controlador. */
  table(el, { columns, rows = [], search, pageSize = 12, empty = 'Sin registros', onRow, footer }) {
    let data = rows, q = '', page = 1;
    el.innerHTML = `<div class="table-responsive"><table class="table table-hover align-middle">
      <thead><tr>${columns.map(c => `<th class="${c.cls || ''}">${c.label}</th>`).join('')}</tr></thead>
      <tbody></tbody><tfoot></tfoot></table></div>
      <div class="tbl-footer"><small class="text-body-secondary tbl-info"></small><ul class="pagination pagination-sm mb-0"></ul></div>`;
    const tbody = el.querySelector('tbody'), info = el.querySelector('.tbl-info'), pag = el.querySelector('.pagination'), tfoot = el.querySelector('tfoot');
    function draw() {
      const filtered = q && search ? data.filter(r => search(r).toLowerCase().includes(q)) : data;
      const pages = Math.max(1, Math.ceil(filtered.length / pageSize));
      page = Math.min(page, pages);
      const slice = filtered.slice((page - 1) * pageSize, page * pageSize);
      tbody.innerHTML = slice.length
        ? slice.map((r, i) => `<tr ${onRow ? `class="row-link" data-row="${(page - 1) * pageSize + i}"` : ''}>${columns.map(c => `<td class="${c.cls || ''}">${c.render ? c.render(r) : esc(r[c.key])}</td>`).join('')}</tr>`).join('')
        : `<tr><td colspan="${columns.length}" class="empty"><i class="bi bi-inbox"></i>${empty}</td></tr>`;
      tfoot.innerHTML = footer && filtered.length ? footer(filtered) : '';
      info.textContent = filtered.length ? `Mostrando ${(page - 1) * pageSize + 1}–${(page - 1) * pageSize + slice.length} de ${filtered.length}` : '';
      let html = '';
      if (pages > 1) {
        const btn = (p, label, dis, act) => `<li class="page-item ${dis ? 'disabled' : ''} ${act ? 'active' : ''}"><a class="page-link" href="#" data-page="${p}">${label}</a></li>`;
        html += btn(page - 1, '‹', page === 1);
        const from = Math.max(1, Math.min(page - 2, pages - 4)), to = Math.min(pages, from + 4);
        for (let p = from; p <= to; p++) html += btn(p, p, false, p === page);
        html += btn(page + 1, '›', page === pages);
      }
      pag.innerHTML = html;
      el._filtered = filtered;
    }
    pag.addEventListener('click', e => {
      const a = e.target.closest('[data-page]'); if (!a) return;
      e.preventDefault(); page = +a.dataset.page; draw();
    });
    if (onRow) tbody.addEventListener('click', e => {
      if (e.target.closest('a,button,input,select')) return;
      const tr = e.target.closest('[data-row]'); if (tr) onRow(el._filtered[+tr.dataset.row]);
    });
    draw();
    return {
      setRows(r) { data = r; page = 1; draw(); },
      setQuery(s) { q = s.trim().toLowerCase(); page = 1; draw(); },
      get rows() { return el._filtered; },
    };
  },

  /* Modal con formulario. onSubmit(datos, form) -> false para mantenerlo abierto */
  modal({ title, body, size = '', submit = 'Guardar', onSubmit, onShow, cancel = 'Cancelar', footer }) {
    const wrap = document.createElement('div');
    wrap.className = 'modal fade'; wrap.tabIndex = -1;
    wrap.innerHTML = `<div class="modal-dialog modal-dialog-scrollable ${size}"><form class="modal-content" novalidate>
      <div class="modal-header"><h5 class="modal-title">${title}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
      <div class="modal-body">${body}</div>
      <div class="modal-footer">${footer ?? `<button type="button" class="btn btn-light" data-bs-dismiss="modal">${cancel}</button>
        ${onSubmit ? `<button type="submit" class="btn btn-primary">${submit}</button>` : ''}`}</div></form></div>`;
    document.body.appendChild(wrap);
    const m = new bootstrap.Modal(wrap);
    const form = wrap.querySelector('form');
    form.addEventListener('submit', e => {
      e.preventDefault();
      if (!form.checkValidity()) { form.classList.add('was-validated'); return; }
      const data = Object.fromEntries(new FormData(form).entries());
      form.querySelectorAll('input[type=checkbox][name]').forEach(cb => { data[cb.name] = cb.checked; });
      if (onSubmit(data, form) !== false) m.hide();
    });
    wrap.addEventListener('hidden.bs.modal', () => { m.dispose(); wrap.remove(); });
    if (onShow) wrap.addEventListener('shown.bs.modal', () => onShow(form), { once: true });
    m.show();
    return { el: wrap, form, hide: () => m.hide() };
  },

  confirm(message, { title = 'Confirmar', ok = 'Aceptar', danger = false } = {}) {
    return new Promise(resolve => {
      let result = false;
      const md = UI.modal({
        title, body: `<p class="mb-0">${message}</p>`, size: 'modal-sm',
        footer: `<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn ${danger ? 'btn-danger' : 'btn-primary'}">${ok}</button>`,
        onSubmit: () => { result = true; },
      });
      md.el.addEventListener('hidden.bs.modal', () => resolve(result));
    });
  },

  toast(msg, type = 'success') {
    const icon = { success: 'check-circle-fill text-success', error: 'x-circle-fill text-danger', info: 'info-circle-fill text-primary', warning: 'exclamation-triangle-fill text-warning' }[type];
    const el = document.createElement('div');
    el.className = 'toast align-items-center border-0 shadow';
    el.innerHTML = `<div class="d-flex"><div class="toast-body"><i class="bi bi-${icon} me-2"></i>${msg}</div><button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    document.getElementById('toasts').appendChild(el);
    const t = new bootstrap.Toast(el, { delay: 3200 });
    el.addEventListener('hidden.bs.toast', () => el.remove());
    t.show();
  },

  // Selector de rango de fechas con atajos
  rangoFechas(idPrefix, desde, hasta) {
    return `<div class="input-group input-group-sm w-auto flex-nowrap date-range">
      <select class="form-select form-select-sm" id="${idPrefix}Rango" style="max-width:150px">
        <option value="7">Últimos 7 días</option><option value="30" selected>Últimos 30 días</option>
        <option value="90">Últimos 90 días</option><option value="mes">Este mes</option><option value="hoy">Hoy</option><option value="custom">Personalizado</option>
      </select>
      <input type="date" class="form-control form-control-sm" id="${idPrefix}Desde" value="${desde}">
      <input type="date" class="form-control form-control-sm" id="${idPrefix}Hasta" value="${hasta}">
    </div>`;
  },
  bindRango(idPrefix, onChange) {
    const sel = document.getElementById(idPrefix + 'Rango'), d = document.getElementById(idPrefix + 'Desde'), h = document.getElementById(idPrefix + 'Hasta');
    sel.addEventListener('change', () => {
      const hoy = Q.hoy();
      if (sel.value === 'custom') return;
      h.value = hoy;
      d.value = sel.value === 'hoy' ? hoy : sel.value === 'mes' ? hoy.slice(0, 8) + '01' : addDays(hoy, -(+sel.value - 1));
      onChange(d.value, h.value);
    });
    [d, h].forEach(x => x.addEventListener('change', () => { sel.value = 'custom'; onChange(d.value, h.value); }));
  },
};

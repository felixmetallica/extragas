/* Enrutador y marco general de la aplicación */

const App = (() => {
  const routes = [];
  let charts = [];
  const view = () => document.getElementById('view');

  function route(pattern, handler) {
    // '/pedidos/:id' -> regex
    const keys = [];
    const rx = new RegExp('^' + pattern.replace(/:(\w+)/g, (_, k) => { keys.push(k); return '([^/]+)'; }) + '$');
    routes.push({ rx, keys, handler, pattern });
  }

  function setTitle(title, crumbs = []) {
    document.getElementById('pageTitle').textContent = title;
    document.getElementById('pageCrumb').innerHTML = crumbs.map(([t, h]) => h ? `<a href="${h}">${esc(t)}</a>` : esc(t)).join(' <i class="bi bi-chevron-right small"></i> ');
    document.title = `${title} · ExtraGas`;
  }

  function chart(canvas, config) {
    const c = new Chart(canvas, config);
    charts.push(c);
    return c;
  }

  function render() {
    charts.forEach(c => c.destroy()); charts = [];
    document.querySelectorAll('.modal.show').forEach(m => bootstrap.Modal.getInstance(m)?.hide());
    const path = (location.hash.replace(/^#/, '') || '/inicio').split('?')[0];
    const v = view();
    v.onclick = null; v.oninput = null; v.onchange = null;
    for (const r of routes) {
      const m = path.match(r.rx);
      if (m) {
        const params = {}; r.keys.forEach((k, i) => { params[k] = decodeURIComponent(m[i + 1]); });
        const section = path.split('/')[1];
        document.querySelectorAll('.nav-menu a').forEach(a => a.classList.toggle('active', a.dataset.route === section));
        v.innerHTML = '';
        r.handler(v, params);
        window.scrollTo(0, 0);
        closeMenu();
        refreshBadges();
        return;
      }
    }
    setTitle('Página no encontrada');
    v.innerHTML = `<div class="card card-body empty"><i class="bi bi-signpost-split"></i>La sección solicitada no existe. <a href="#/inicio">Volver al inicio</a></div>`;
  }

  // Delegación de clicks: <button data-action="nombre" data-id="1">
  function actions(map) {
    view().onclick = e => {
      const el = e.target.closest('[data-action]');
      if (!el || !view().contains(el)) return;
      const fn = map[el.dataset.action];
      if (fn) { e.preventDefault(); fn(el.dataset.id, el, e); }
    };
  }

  function refreshBadges() {
    const n = Store.db.pedidos.filter(p => ['Pendiente', 'En preparación', 'En reparto'].includes(p.estado)).length;
    document.getElementById('navPendientes').textContent = n || '';
  }

  function closeMenu() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarBackdrop').classList.remove('open');
  }

  function currentUser() {
    let u = null;
    try { u = JSON.parse(sessionStorage.getItem('extragas_user')); } catch (e) { u = null; }
    return (u && Q.usuario(u.id)) || Store.db.usuarios[0];
  }

  function start() {
    const u = currentUser();
    document.getElementById('userName').textContent = Q.nombreCliente(u);
    document.getElementById('userRole').textContent = u.rol;
    document.getElementById('userAvatar').textContent = u.nombre[0] + (u.apellido || '')[0];
    if (u.rol !== 'Administrador') document.querySelectorAll('.admin-only').forEach(x => x.classList.add('d-none'));
    document.getElementById('btnMenu').addEventListener('click', () => {
      document.getElementById('sidebar').classList.add('open');
      document.getElementById('sidebarBackdrop').classList.add('open');
    });
    document.getElementById('sidebarBackdrop').addEventListener('click', closeMenu);
    document.getElementById('btnLogout').addEventListener('click', e => {
      e.preventDefault();
      try { sessionStorage.removeItem('extragas_user'); } catch (err) { /* ignorar */ }
      location.href = 'login.html';
    });
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.color = '#6b7280';
    Chart.defaults.plugins.legend.labels.boxWidth = 10;
    window.addEventListener('hashchange', render);
    render();
  }

  const save = () => { Store.save(); refreshBadges(); };
  const go = hash => { if (location.hash === hash) render(); else location.hash = hash; };

  return { route, setTitle, chart, actions, start, save, go, render, currentUser };
})();

const COLORS = ['#e8590c', '#1971c2', '#2b8a3e', '#6741d9', '#f59f00', '#0c8599', '#c2255c', '#495057'];

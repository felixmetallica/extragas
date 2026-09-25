/* Comportamiento general de la interfaz */
(() => {
    const side = document.getElementById('sidebar');
    const back = document.getElementById('sidebarBackdrop');
    document.getElementById('btnMenu')?.addEventListener('click', () => { side.classList.add('open'); back.classList.add('open'); });
    back?.addEventListener('click', () => { side.classList.remove('open'); back.classList.remove('open'); });

    // Confirmación en formularios y botones: data-confirm="mensaje"
    document.addEventListener('submit', e => {
        const msg = e.target.dataset.confirm;
        if (msg && !confirm(msg)) e.preventDefault();
    });

    // Filas clickeables: <tr data-href="...">
    document.addEventListener('click', e => {
        const tr = e.target.closest('tr[data-href]');
        if (tr && !e.target.closest('a,button,input,select,form')) location.href = tr.dataset.href;
    });

    // Filtros que se envían al cambiar: <form data-auto-submit>
    document.querySelectorAll('form[data-auto-submit] select, form[data-auto-submit] input[type=date]').forEach(el =>
        el.addEventListener('change', () => el.form.requestSubmit()));

    // Rango rápido de fechas: <select data-rango="idDesde,idHasta">
    document.querySelectorAll('select[data-rango]').forEach(sel => sel.addEventListener('change', () => {
        const [d, h] = sel.dataset.rango.split(',').map(id => document.getElementById(id));
        const z = n => String(n).padStart(2, '0'), iso = x => `${x.getFullYear()}-${z(x.getMonth() + 1)}-${z(x.getDate())}`;
        const hoy = new Date(), desde = new Date();
        if (sel.value === 'mes') desde.setDate(1);
        else if (sel.value !== '') desde.setDate(hoy.getDate() - (+sel.value - 1));
        else return;
        d.value = iso(desde); h.value = iso(hoy);
        sel.form.requestSubmit();
    }));

    // Gráficos: <canvas data-chart='{json de Chart.js}'>
    if (window.Chart) {
        Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
        Chart.defaults.color = '#6b7280';
        Chart.defaults.plugins.legend.labels.boxWidth = 10;
        Chart.defaults.maintainAspectRatio = false;
        const pesos = v => '$ ' + Number(v).toLocaleString('es-AR');
        document.querySelectorAll('canvas[data-chart]').forEach(c => {
            const cfg = JSON.parse(c.dataset.chart);
            if (cfg.pesos) {
                cfg.options = cfg.options || {};
                cfg.options.plugins = { ...(cfg.options.plugins || {}), tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label || ctx.label}: ${pesos(ctx.raw)}` } } };
                const eje = cfg.options.indexAxis === 'y' ? 'x' : 'y';
                if (cfg.type !== 'doughnut') { cfg.options.scales = cfg.options.scales || {}; cfg.options.scales[eje] = { ...(cfg.options.scales[eje] || {}), ticks: { callback: pesos } }; }
            }
            new Chart(c, cfg);
        });
    }
})();

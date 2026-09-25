<div class="input-group input-group-sm w-auto flex-nowrap date-range">
    <select class="form-select form-select-sm" data-rango="fDesde,fHasta" style="max-width:150px" aria-label="Período">
        <option value="">Período…</option><option value="1">Hoy</option><option value="7">Últimos 7 días</option>
        <option value="30">Últimos 30 días</option><option value="90">Últimos 90 días</option><option value="mes">Este mes</option>
    </select>
    <input type="date" class="form-control form-control-sm" id="fDesde" name="desde" value="<?= e($desde) ?>" aria-label="Desde">
    <input type="date" class="form-control form-control-sm" id="fHasta" name="hasta" value="<?= e($hasta) ?>" aria-label="Hasta">
</div>

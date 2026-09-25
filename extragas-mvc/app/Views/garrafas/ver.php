<?php use App\Core\Auth; use App\Core\Vista; $g = $garrafa; ?>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-body">
            <div class="cyl-head mb-3"><div class="cyl-icon"><i class="bi bi-fuel-pump-fill"></i></div>
                <div><div class="fw-bold fs-4"><?= e($g['codigo']) ?></div><div class="text-muted-sm">Garrafa de <?= $g['capacidad_kg'] ?> kg</div></div></div>
            <dl class="info-list">
                <dt>Estado</dt><dd><?= badge_color($g['estado_nombre'], $g['estado_color']) ?> <?= $g['activo'] ? '' : badge('Fuera del parque') ?></dd>
                <dt>Cliente</dt><dd><?= $g['cliente_id'] ? '<a href="'.url('clientes/'.$g['cliente_id']).'">'.e(nombre($g, 'cliente_')).'</a>' : '—' ?></dd>
                <dt>Proveedor</dt><dd><?= e($g['proveedor_nombre'] ?? '—') ?></dd>
                <dt>Alta</dt><dd><?= fecha($g['fecha_compra']) ?></dd>
                <dt>Últ. movimiento</dt><dd><?= fecha($g['fecha_ultimo_movimiento'], true) ?></dd>
                <dt>Obs.</dt><dd><?= e($g['observaciones'] ?: '—') ?></dd>
            </dl>
        </div></div>

        <?php if ($posibles || Auth::esAdmin()): ?>
            <form method="POST" action="<?= url('garrafas/'.$g['id'].'/movimiento') ?>" class="card">
                <?= csrf_campo() ?>
                <div class="card-header"><h2>Registrar movimiento</h2></div>
                <div class="card-body">
                    <label class="form-label" for="tipo">Movimiento</label>
                    <select class="form-select mb-2" name="tipo" id="tipo">
                        <?php foreach ($posibles as $codigo => $regla): ?><option value="<?= $codigo ?>"><?= e($tipos[$codigo]['nombre']) ?> → <?= e($estados[$regla['hacia']]['nombre']) ?></option><?php endforeach ?>
                        <?php if (Auth::esAdmin()): ?><option value="AJUSTE">Ajuste de estado (administrador)</option><?php endif ?>
                    </select>
                    <?php if (Auth::esAdmin()): ?>
                        <div id="ajusteWrap" class="d-none"><label class="form-label" for="estadoAjuste">Estado correcto</label>
                            <select class="form-select mb-2" name="estado_ajuste" id="estadoAjuste"><?php foreach (['LLENA', 'VACIA', 'NO_APTA'] as $c): ?><option value="<?= $c ?>"><?= e($estados[$c]['nombre']) ?></option><?php endforeach ?></select></div>
                    <?php endif ?>
                    <label class="form-label" for="obs">Motivo / detalle</label>
                    <input class="form-control mb-3" name="observaciones" id="obs" placeholder="Ej.: válvula pierde, prueba hidráulica vencida">
                    <button class="btn btn-primary w-100">Registrar</button>
                </div>
            </form>
        <?php endif ?>
    </div>
    <div class="col-lg-8">
        <div class="card"><div class="card-header"><h2>Historial de movimientos</h2></div>
            <?php Vista::parcial('movimientos', ['movimientos' => $movimientos]) ?>
        </div>
    </div>
</div>
<?php Vista::inicio('scripts') ?>
<script>
    const tipo = document.getElementById('tipo'), aj = document.getElementById('ajusteWrap');
    const toggle = () => aj?.classList.toggle('d-none', tipo.value !== 'AJUSTE');
    tipo?.addEventListener('change', toggle); if (tipo) toggle();
</script>
<?php Vista::fin() ?>

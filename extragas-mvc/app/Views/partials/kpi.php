<div class="card kpi">
    <div class="kpi-icon <?= $color ?? 'i-orange' ?>"><i class="bi bi-<?= $icono ?>"></i></div>
    <div><div class="kpi-label"><?= e($label) ?></div><div class="kpi-value"><?= e($valor) ?></div><?php if (isset($sub)): ?><div class="kpi-sub"><?= e($sub) ?></div><?php endif ?></div>
</div>

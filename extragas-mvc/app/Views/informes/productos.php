<?php use App\Core\Vista; Vista::parcial('informe_inicio', get_defined_vars());
$top = $ranking[0] ?? null; $max = max(1, (float) ($top['vendida'] ?? 1));
$iconos = ['Gas envasado' => 'fuel-pump', 'Carbón' => 'fire', 'Leña' => 'tree']; ?>
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => 'trophy', 'label' => 'Más vendido', 'valor' => $top['producto_nombre'] ?? '—', 'sub' => $top ? num($top['vendida']).' unidades' : '']) ?></div>
    <?php foreach ($porTipo as $t => $monto): ?>
        <div class="col-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => $iconos[$t] ?? 'box', 'color' => 'i-blue', 'label' => $t, 'valor' => pesos($monto),
            'sub' => num(array_sum(array_column(array_filter($ranking, fn ($r) => $r['tipo_producto'] === $t), 'vendida'))).' unidades']) ?></div>
    <?php endforeach ?>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-7"><div class="card h-100"><div class="card-header"><h2>Unidades vendidas</h2></div><div class="card-body"><div class="chart-box"><canvas data-chart='<?= json($graficoRanking) ?>'></canvas></div></div></div></div>
    <div class="col-md-5"><div class="card h-100"><div class="card-header"><h2>Facturación por tipo</h2></div><div class="card-body"><div class="chart-box"><canvas data-chart='<?= json($graficoTipos) ?>'></canvas></div></div></div></div>
</div>
<div class="card"><div class="card-header"><h2>Ranking de productos</h2></div>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>#</th><th>Producto</th><th>Tipo</th><th class="num">Unidades</th><th></th><th class="num" title="Envases llenos entregados">Env. entregados</th><th class="num" title="Envases vacíos recibidos">Env. recibidos</th><th class="num">Importe</th><th class="num">%</th></tr></thead>
        <tbody><?php foreach ($ranking as $i => $r): ?>
            <tr><td class="fw-bold text-brand"><?= $i + 1 ?></td><td class="fw-semibold"><?= e($r['producto_nombre']) ?></td><td><?= e($r['tipo_producto']) ?></td><td class="num"><?= num($r['vendida']) ?></td>
                <td style="width:15%"><div class="rank-bar"><div style="width:<?= $r['vendida'] / $max * 100 ?>%"></div></div></td>
                <td class="num"><?= num($r['entregada']) ?></td><td class="num"><?= num($r['devuelta']) ?></td><td class="num"><?= pesos($r['monto']) ?></td><td class="num"><?= $total ? round($r['monto'] / $total * 100) : 0 ?>%</td></tr>
        <?php endforeach ?>
        <?php if (! $ranking): ?><tr><td colspan="9" class="empty">Sin ventas en el período</td></tr><?php endif ?></tbody>
    </table></div></div>
<?php Vista::parcial('informe_fin') ?>

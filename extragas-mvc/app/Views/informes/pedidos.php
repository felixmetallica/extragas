<?php use App\Core\Vista; Vista::parcial('informe_inicio', get_defined_vars()); $r = $resumen; ?>
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => 'receipt', 'label' => 'Pedidos', 'valor' => num($r['n']), 'sub' => round($r['n'] / max(1, $nDias), 1).' por día']) ?></div>
    <div class="col-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => 'currency-dollar', 'color' => 'i-green', 'label' => 'Importe total', 'valor' => pesos($r['total']), 'sub' => 'Ticket prom. '.pesos($r['n'] ? round($r['total'] / $r['n']) : 0)]) ?></div>
    <div class="col-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => 'people', 'color' => 'i-blue', 'label' => 'Clientes atendidos', 'valor' => $r['clientes']]) ?></div>
    <div class="col-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => 'hourglass-split', 'color' => 'i-red', 'label' => 'Saldo pendiente', 'valor' => pesos($r['saldo'])]) ?></div>
</div>
<div class="card mb-3"><div class="card-header"><h2>Pedidos por día</h2></div><div class="card-body"><div class="chart-box"><canvas data-chart='<?= json($graficoDias) ?>'></canvas></div></div></div>
<div class="row g-3 mb-3">
    <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Por medio de contacto</h2></div><div class="card-body"><div class="chart-box sm"><canvas data-chart='<?= json($graficoMedios) ?>'></canvas></div></div></div></div>
    <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Por estado</h2></div><div class="card-body"><div class="chart-box sm"><canvas data-chart='<?= json($graficoEstados) ?>'></canvas></div></div></div></div>
</div>
<div class="card mb-3"><div class="card-header"><h2>Por empleado</h2></div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Empleado</th><th class="num">Pedidos</th><th class="num">Importe</th></tr></thead>
        <tbody><?php foreach ($porEmpleado as $e): ?><tr><td><?= e($e['empleado']) ?></td><td class="num"><?= $e['n'] ?></td><td class="num"><?= pesos($e['total']) ?></td></tr><?php endforeach ?></tbody></table></div></div>
<div class="card"><div class="card-header"><h2>Detalle (<?= count($detalle) ?>)</h2></div>
    <div class="table-responsive" style="max-height:520px"><table class="table table-hover align-middle">
        <thead><tr><th>N°</th><th>Fecha</th><th>Cliente</th><th>Empleado</th><th>Estado</th><th class="num">Total</th><th>Pago</th></tr></thead>
        <tbody><?php foreach ($detalle as $p): ?>
            <tr><td><a href="<?= url('pedidos/'.$p['id']) ?>"><?= e($p['numero']) ?></a></td><td><?= fecha($p['fecha']) ?></td><td><?= e($p['cliente']) ?></td><td class="small"><?= e($p['empleado']) ?></td>
                <td><?= e($p['estado_nombre']) ?></td><td class="num"><?= pesos($p['total']) ?></td><td><?= badge_pago(['PAGADO' => 'Pagado', 'PARCIAL' => 'Parcial', 'PENDIENTE' => 'Pendiente'][$p['estado_pago']]) ?></td></tr>
        <?php endforeach ?></tbody>
    </table></div></div>
<?php Vista::parcial('informe_fin') ?>

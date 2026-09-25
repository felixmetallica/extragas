<?php use App\Core\Vista; Vista::parcial('informe_inicio', get_defined_vars()); ?>
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => 'arrow-repeat', 'label' => 'Frecuencia promedio', 'valor' => $promedio ? $promedio.' días' : '—', 'sub' => 'entre pedidos de un cliente']) ?></div>
    <div class="col-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => 'people', 'color' => 'i-blue', 'label' => 'Clientes activos', 'valor' => $activos, 'sub' => 'con pedidos en el período']) ?></div>
    <div class="col-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => 'alarm', 'color' => 'i-red', 'label' => 'Atrasados', 'valor' => $atrasados, 'sub' => "más de {$tolerancia} días sobre su frecuencia"]) ?></div>
    <div class="col-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => 'calendar-event', 'color' => 'i-violet', 'label' => 'Día de más pedidos', 'valor' => $diaPico, 'sub' => $picoN.' pedidos']) ?></div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Pedidos por día de la semana</h2></div><div class="card-body"><div class="chart-box sm"><canvas data-chart='<?= json($graficoSemana) ?>'></canvas></div></div></div></div>
    <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Clientes según cada cuánto piden</h2></div><div class="card-body"><div class="chart-box sm"><canvas data-chart='<?= json($graficoFrecuencia) ?>'></canvas></div></div></div></div>
</div>
<div class="card"><div class="card-header"><h2>Regularidad por cliente</h2></div>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>Cliente</th><th class="num" title="Total histórico (en el período)">Pedidos</th><th class="num">Pide cada</th><th>Último</th><th>Próximo</th><th>Estado</th><th class="num">Saldo</th><th></th></tr></thead>
        <tbody><?php foreach ($filas as $f): ?>
            <tr><td><a href="<?= url('clientes/'.$f['cliente_id']) ?>" class="fw-semibold"><?= e(nombre($f)) ?></a><div class="text-muted-sm"><?= e($f['telefono_principal']) ?></div></td>
                <td class="num text-nowrap"><?= $f['total_pedidos'] ?> <span class="text-body-secondary">(<?= $f['n_periodo'] ?>)</span></td>
                <td class="num"><?= $f['promedio'] ? $f['promedio'].' días' : '—' ?></td>
                <td><?= fecha($f['ultimo_pedido']) ?></td><td><?= fecha($f['r']['proximo']) ?></td><td><?= $f['promedio'] ? badge_regularidad($f['r']['estado']) : '—' ?></td>
                <td class="num"><?= $f['saldo_pendiente'] > 0 ? pesos($f['saldo_pendiente']) : '' ?></td>
                <td class="actions"><a class="btn btn-sm btn-light" href="<?= e(wa_link($f['telefono_principal'])) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp text-success"></i></a></td></tr>
        <?php endforeach ?></tbody>
    </table></div></div>
<?php Vista::parcial('informe_fin') ?>

<?php use App\Core\Vista; Vista::parcial('informe_inicio', get_defined_vars()); ?>
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => 'cash-stack', 'color' => 'i-green', 'label' => 'Cobrado', 'valor' => pesos($cobrado), 'sub' => array_sum(array_column($porForma, 'cantidad')).' pagos']) ?></div>
    <div class="col-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => 'percent', 'color' => 'i-blue', 'label' => 'Cobrabilidad', 'valor' => $vendido['total'] > 0 ? round(($vendido['total'] - $vendido['saldo']) / $vendido['total'] * 100).'%' : '—', 'sub' => 'de '.pesos($vendido['total']).' vendidos']) ?></div>
    <div class="col-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => 'exclamation-diamond', 'color' => 'i-red', 'label' => 'Deuda de clientes', 'valor' => pesos(array_sum(array_column($deudores, 'saldo_total'))), 'sub' => count($deudores).' clientes']) ?></div>
    <div class="col-6 col-xl-3"><?php Vista::parcial('kpi', ['icono' => 'truck', 'color' => 'i-violet', 'label' => 'Pagado a proveedores', 'valor' => pesos($pagadoProv), 'sub' => 'Adeudado: '.pesos(array_sum(array_column($proveedores, 'saldo_total')))]) ?></div>
</div>
<div class="card mb-3"><div class="card-header"><h2>Ingresos y egresos por día</h2></div><div class="card-body"><div class="chart-box"><canvas data-chart='<?= json($graficoFlujo) ?>'></canvas></div></div></div>
<div class="row g-3 mb-3">
    <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Cobros por forma de pago</h2></div><div class="card-body"><div class="chart-box sm"><canvas data-chart='<?= json($graficoFormas) ?>'></canvas></div></div></div></div>
    <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Forma de pago habitual de los clientes</h2></div><div class="card-body"><div class="chart-box sm"><canvas data-chart='<?= json($graficoHabitual) ?>'></canvas></div></div></div></div>
</div>
<div class="row g-3">
    <div class="col-md-7"><div class="card h-100"><div class="card-header"><h2>Clientes con saldo pendiente</h2></div>
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Cliente</th><th class="num">Pedidos</th><th class="num">Saldo</th></tr></thead>
            <tbody><?php foreach ($deudores as $d): ?><tr><td><a href="<?= url('clientes/'.$d['cliente_id']) ?>"><?= e($d['cliente']) ?></a><div class="text-muted-sm"><?= e($d['telefono_principal']) ?></div></td><td class="num"><?= $d['pedidos_pendientes'] ?></td><td class="num text-danger fw-semibold"><?= pesos($d['saldo_total']) ?></td></tr><?php endforeach ?>
            <?php if (! $deudores): ?><tr><td colspan="3" class="empty">Sin deudas</td></tr><?php endif ?></tbody></table></div></div></div>
    <div class="col-md-5"><div class="card h-100"><div class="card-header"><h2>Deuda con proveedores</h2></div>
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Proveedor</th><th class="num">Saldo</th></tr></thead>
            <tbody><?php foreach ($proveedores as $p): ?><tr><td><a href="<?= url('proveedores/'.$p['proveedor_id']) ?>"><?= e($p['razon_social']) ?></a></td><td class="num fw-semibold"><?= pesos($p['saldo_total']) ?></td></tr><?php endforeach ?>
            <?php if (! $proveedores): ?><tr><td colspan="2" class="empty">Sin deudas</td></tr><?php endif ?></tbody></table></div></div></div>
</div>
<?php Vista::parcial('informe_fin') ?>

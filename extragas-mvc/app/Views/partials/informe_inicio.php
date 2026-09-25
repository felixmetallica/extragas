<?php use App\Core\Vista; ?>
<div class="row g-3">
    <div class="col-lg-3">
        <div class="card"><div class="card-body p-2"><div class="list-group report-nav">
            <?php foreach ($informes as $clave => [$icono, $nombre, $desc]): ?>
                <a href="<?= url('informes/'.$clave, ['desde' => $_GET['desde'] ?? null, 'hasta' => $_GET['hasta'] ?? null]) ?>" class="list-group-item list-group-item-action <?= $clave === $tipo ? 'active' : '' ?>">
                    <i class="bi bi-<?= $icono ?> fs-5"></i><span><span class="d-block"><?= e($nombre) ?></span><small class="text-body-secondary fw-normal"><?= e($desc) ?></small></span></a>
            <?php endforeach ?>
        </div></div></div>
    </div>
    <div class="col-lg-9">
        <form class="card mb-3" method="GET" data-auto-submit><div class="card-body d-flex flex-wrap gap-2 align-items-center py-2">
            <div><div class="fw-bold"><?= e($informes[$tipo][1]) ?></div><div class="text-muted-sm"><?= e($informes[$tipo][2]) ?></div></div>
            <div class="ms-auto d-flex flex-wrap gap-2 align-items-center">
                <?php if ($tipo !== 'garrafas') Vista::parcial('rango', compact('desde', 'hasta')) ?>
                <a class="btn btn-sm btn-primary" href="<?= e(url_actual(['pdf' => 1])) ?>"><i class="bi bi-file-earmark-pdf"></i> Descargar PDF</a>
            </div>
        </div></form>

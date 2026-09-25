<?php
use App\Core\Auth;
use App\Core\Sesion;
use App\Core\Vista;
use App\Models\Configuracion;
use App\Models\Pedido;

$empresa = Configuracion::empresa();
$enCurso = Pedido::cantidadEnCurso();
$menu = [
    'General' => [['', 'speedometer2', 'Inicio']],
    'Ventas' => [['pedidos', 'cart3', 'Pedidos'], ['clientes', 'people', 'Clientes'], ['cobros', 'cash-coin', 'Cobros']],
    'Depósito' => [['garrafas', 'fuel-pump', 'Garrafas'], ['productos', 'box-seam', 'Productos y precios']],
    'Compras' => [['proveedores', 'truck', 'Proveedores'], ['recepciones', 'box-arrow-in-down', 'Recepciones'], ['pagos-proveedores', 'wallet2', 'Pagos a proveedores']],
    'Análisis' => [['informes', 'bar-chart-line', 'Informes']],
];
if (Auth::esAdmin()) {
    $menu['Sistema'] = [['usuarios', 'person-badge', 'Usuarios'], ['empleados', 'person-vcard', 'Empleados'], ['configuracion', 'gear', 'Configuración']];
}
$errores = Sesion::leer('errores', []);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'Inicio') ?> · <?= e($empresa['nombre']) ?></title>
    <link rel="icon" href="<?= asset('img/logo.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="app">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="<?= url() ?>">
            <img src="<?= asset('img/logo.svg') ?>" alt="" width="34" height="34">
            <span><strong><?= e($empresa['nombre']) ?></strong><small>Gestión de Pedidos</small></span>
        </a>
        <nav class="nav-menu">
            <?php foreach ($menu as $grupo => $items): ?>
                <div class="nav-group"><?= e($grupo) ?></div>
                <?php foreach ($items as [$ruta, $icono, $texto]): ?>
                    <a href="<?= url($ruta) ?>" class="<?= seccion_activa($ruta) ? 'active' : '' ?>">
                        <i class="bi bi-<?= $icono ?>"></i> <?= e($texto) ?>
                        <?php if ($ruta === 'pedidos' && $enCurso): ?><span class="badge rounded-pill ms-auto"><?= $enCurso ?></span><?php endif ?>
                    </a>
                <?php endforeach ?>
            <?php endforeach ?>
        </nav>
        <div class="sidebar-foot"><i class="bi bi-info-circle"></i> La facturación se realiza en la web de ARCA.</div>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="main">
        <header class="topbar">
            <button class="btn btn-icon d-lg-none" id="btnMenu" aria-label="Abrir menú"><i class="bi bi-list"></i></button>
            <div class="topbar-title">
                <h1><?= e($titulo ?? 'Inicio') ?></h1>
                <div class="crumb">
                    <?php $i = 0; foreach ($migas ?? [] as $texto => $enlace): ?>
                        <?= $i++ ? '<i class="bi bi-chevron-right small"></i>' : '' ?>
                        <?= $enlace ? '<a href="'.e($enlace).'">'.e($texto).'</a>' : e($texto) ?>
                    <?php endforeach ?>
                </div>
            </div>
            <div class="topbar-actions">
                <a href="<?= url('pedidos/nuevo') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i><span class="d-none d-sm-inline"> Nuevo pedido</span></a>
                <div class="dropdown">
                    <button class="btn user-btn" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar"><?= e(Auth::iniciales()) ?></span>
                        <span class="d-none d-md-inline text-start lh-sm">
                            <span class="d-block fw-semibold"><?= e(Auth::nombre()) ?></span>
                            <small class="text-body-secondary"><?= e(Auth::usuario()['rol_nombre']) ?></small>
                        </span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><form method="POST" action="<?= url('logout') ?>"><?= csrf_campo() ?><button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</button></form></li>
                    </ul>
                </div>
            </div>
        </header>
        <main class="content">
            <?php if ($ok = Sesion::leer('ok')): ?>
                <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i><?= e($ok) ?>
                    <?php if ($pdf = Sesion::leer('pdf')): ?><a href="<?= e($pdf) ?>" class="alert-link ms-2"><i class="bi bi-file-earmark-pdf"></i> Descargar comprobante</a><?php endif ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>
            <?php endif ?>
            <?php if ($errores): ?>
                <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php if (count($errores) === 1): ?><?= e(reset($errores)) ?><?php else: ?>Revisá los datos ingresados:
                        <ul class="mb-0 mt-1"><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach ?></ul><?php endif ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>
            <?php endif ?>
            <?= $contenido ?>
        </main>
    </div>
</div>

<script src="<?= asset('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('vendor/chartjs/chart.umd.js') ?>"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<?= Vista::seccion('scripts') ?>
</body>
</html>

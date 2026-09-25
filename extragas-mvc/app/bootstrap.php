<?php
/*
 * Arranque: configuración, autoload de clases, sesión y manejo de errores.
 */

declare(strict_types=1);

$config = require RAIZ.'/config/config.php';
date_default_timezone_set($config['app']['zona_horaria']);
mb_internal_encoding('UTF-8');

// Autoload: App\Controllers\PedidoController → app/Controllers/PedidoController.php
spl_autoload_register(function (string $clase) {
    if (str_starts_with($clase, 'App\\')) {
        $ruta = RAIZ.'/app/'.str_replace('\\', '/', substr($clase, 4)).'.php';
        if (is_file($ruta)) {
            require $ruta;
        }
    }
});

require RAIZ.'/app/helpers.php';
App\Core\Config::cargar($config);

ini_set('display_errors', $config['app']['debug'] ? '1' : '0');
error_reporting(E_ALL);

session_name('extragas_sesion');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

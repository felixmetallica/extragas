<?php
/*
 * ExtraGas · Sistema de Gestión de Pedidos
 * Controlador frontal: toda petición entra por aquí.
 */

declare(strict_types=1);

// Servidor embebido de PHP (php -S): servir los archivos estáticos directamente
if (PHP_SAPI === 'cli-server') {
    $archivo = __DIR__.parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($archivo) && str_starts_with(realpath($archivo), __DIR__.DIRECTORY_SEPARATOR.'assets')) {
        return false;
    }
}

define('RAIZ', __DIR__);
require RAIZ.'/app/bootstrap.php';

App\Core\App::ejecutar(require RAIZ.'/app/rutas.php');

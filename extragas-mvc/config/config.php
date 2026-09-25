<?php
/*
 * Configuración del sistema. Ajustar los datos de conexión a MySQL.
 */
return [
    'app' => [
        'nombre' => 'ExtraGas',
        'zona_horaria' => 'America/Argentina/Buenos_Aires',
        // true si Apache tiene mod_rewrite (URLs como /pedidos/5).
        // false para usar /index.php/pedidos/5 sin configuración extra.
        'url_amigables' => true,
        'debug' => true,
    ],
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'puerto' => (int) (getenv('DB_PUERTO') ?: 3306),
        'base' => getenv('DB_BASE') ?: 'extragas',
        'usuario' => getenv('DB_USUARIO') ?: 'root',
        'clave' => getenv('DB_CLAVE') ?: '',
    ],
];

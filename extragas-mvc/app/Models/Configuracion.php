<?php

namespace App\Models;

use App\Core\Config;
use App\Core\DB;

/** Datos de la empresa (una sola fila) usados en los PDF y parámetros del sistema */
final class Configuracion
{
    private static ?array $empresa = null;

    public static function empresa(): array
    {
        return self::$empresa ??= DB::uno('SELECT * FROM configuracion_empresa ORDER BY id LIMIT 1')
            ?? ['nombre' => Config::get('app.nombre'), 'razon_social' => null, 'cuit' => null, 'direccion' => null, 'localidad' => null,
                'telefono' => null, 'whatsapp' => null, 'email' => null, 'horario' => null, 'dias_tolerancia_regularidad' => 3];
    }

    public static function guardar(array $datos): void
    {
        $id = DB::valor('SELECT id FROM configuracion_empresa ORDER BY id LIMIT 1');
        $id ? DB::actualizar('configuracion_empresa', $datos, (int) $id) : DB::insertar('configuracion_empresa', $datos);
        self::$empresa = null;
    }

    public static function toleranciaRegularidad(): int
    {
        return (int) (self::empresa()['dias_tolerancia_regularidad'] ?? 3);
    }
}

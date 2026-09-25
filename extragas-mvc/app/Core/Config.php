<?php

namespace App\Core;

/** Acceso a config/config.php y armado de URLs */
final class Config
{
    private static array $datos = [];

    public static function cargar(array $datos): void
    {
        self::$datos = $datos;
    }

    public static function get(string $clave, mixed $defecto = null): mixed
    {
        $valor = self::$datos;
        foreach (explode('.', $clave) as $parte) {
            if (! is_array($valor) || ! array_key_exists($parte, $valor)) {
                return $defecto;
            }
            $valor = $valor[$parte];
        }

        return $valor;
    }

    /** Carpeta base donde está instalado el sistema (ej.: /extragas) */
    public static function base(): string
    {
        return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    }
}

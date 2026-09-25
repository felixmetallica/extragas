<?php

namespace App\Models;

use App\Core\DB;

/**
 * Tablas de catálogo (codigo + nombre): roles, estados_pedido, estados_garrafa,
 * formas_pago, canales_venta, medios_contacto_pedido, tipos_*, provincias.
 */
final class Catalogo
{
    private static array $cache = [];

    /** @return array<string, array> filas indexadas por código */
    public static function todos(string $tabla): array
    {
        if (! isset(self::$cache[$tabla])) {
            self::$cache[$tabla] = [];
            foreach (DB::todos("SELECT * FROM `{$tabla}` ORDER BY id") as $fila) {
                self::$cache[$tabla][$fila['codigo']] = $fila;
            }
        }

        return self::$cache[$tabla];
    }

    public static function id(string $tabla, string $codigo): int
    {
        return (int) (self::todos($tabla)[$codigo]['id'] ?? throw new \RuntimeException("{$tabla} sin el código {$codigo}. ¿Importaste el archivo SQL completo?"));
    }

    public static function porId(string $tabla, int $id): ?array
    {
        foreach (self::todos($tabla) as $fila) {
            if ((int) $fila['id'] === $id) {
                return $fila;
            }
        }

        return null;
    }

    /** id => nombre, para listas desplegables */
    public static function opciones(string $tabla, bool $soloActivos = false): array
    {
        $opciones = [];
        foreach (self::todos($tabla) as $f) {
            if (! $soloActivos || ! array_key_exists('activo', $f) || $f['activo']) {
                $opciones[$f['id']] = $f['nombre'];
            }
        }
        if ($tabla === 'provincias') {
            asort($opciones);
        }

        return $opciones;
    }

    public static function limpiar(): void
    {
        self::$cache = [];
    }
}

<?php

namespace App\Models;

use App\Core\Auth;
use App\Core\DB;

/** Base de los modelos: acceso por id, borrado lógico y auditoría */
abstract class Modelo
{
    protected const TABLA = '';

    /** Las tablas principales tienen deleted_at (borrado lógico) */
    protected const BORRADO_LOGICO = true;

    public static function buscar(int $id): ?array
    {
        $extra = static::BORRADO_LOGICO ? ' AND deleted_at IS NULL' : '';

        return DB::uno('SELECT * FROM `'.static::TABLA."` WHERE id = :id{$extra}", ['id' => $id]);
    }

    public static function eliminar(int $id): void
    {
        DB::actualizar(static::TABLA, ['deleted_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::id()], $id);
    }

    /** created_by / updated_by del usuario logueado */
    protected static function alta(array $datos): array
    {
        return $datos + ['created_by' => Auth::id(), 'updated_by' => Auth::id()];
    }

    protected static function modificacion(array $datos): array
    {
        return $datos + ['updated_by' => Auth::id()];
    }

    /** Toma sólo las claves permitidas de un arreglo */
    protected static function solo(array $datos, array $claves): array
    {
        return array_intersect_key($datos, array_flip($claves));
    }
}

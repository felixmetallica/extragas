<?php

namespace App\Models;

use App\Core\DB;

class Empleado extends Modelo
{
    protected const TABLA = 'empleados';

    public const CAMPOS = ['nombre', 'apellido', 'dni', 'cuil', 'telefono', 'email', 'calle', 'numero', 'piso', 'depto', 'ciudad', 'codigo_postal',
        'provincia_id', 'fecha_ingreso', 'observaciones'];

    public static function todos(): array
    {
        return DB::todos('SELECT e.*, u.username, (SELECT COUNT(*) FROM pedidos p WHERE p.empleado_id = e.id) AS pedidos
            FROM empleados e LEFT JOIN usuarios u ON u.id = e.usuario_id WHERE e.deleted_at IS NULL ORDER BY e.activo DESC, e.apellido');
    }

    /** Empleados sin usuario (o vinculados al usuario indicado) */
    public static function paraVincular(?int $usuarioId): array
    {
        return DB::pares("SELECT id, CONCAT(nombre, ' ', apellido) FROM empleados WHERE deleted_at IS NULL AND (usuario_id IS NULL OR usuario_id = :u) ORDER BY apellido",
            ['u' => $usuarioId ?? 0]);
    }

    public static function crear(array $d): int
    {
        return DB::insertar('empleados', self::alta(self::solo($d, self::CAMPOS) + ['activo' => 1]));
    }

    public static function actualizar(int $id, array $d, bool $activo): void
    {
        DB::actualizar('empleados', self::modificacion(self::solo($d, self::CAMPOS) + ['activo' => (int) $activo]), $id);
    }
}

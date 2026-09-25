<?php

namespace App\Models;

use App\Core\Auth;
use App\Core\DB;

class Usuario extends Modelo
{
    protected const TABLA = 'usuarios';

    public static function todos(): array
    {
        return DB::todos('SELECT u.*, r.codigo AS rol_codigo, r.nombre AS rol_nombre, e.id AS empleado_id, e.nombre AS empleado_nombre, e.apellido AS empleado_apellido
            FROM usuarios u JOIN roles r ON r.id = u.rol_id LEFT JOIN empleados e ON e.usuario_id = u.id AND e.deleted_at IS NULL
            WHERE u.deleted_at IS NULL ORDER BY u.activo DESC, u.username');
    }

    public static function buscar(int $id): ?array
    {
        return DB::uno('SELECT u.*, e.id AS empleado_id FROM usuarios u LEFT JOIN empleados e ON e.usuario_id = u.id AND e.deleted_at IS NULL
            WHERE u.id = :id AND u.deleted_at IS NULL', ['id' => $id]);
    }

    public static function crear(array $d): int
    {
        $id = DB::insertar('usuarios', self::alta(['username' => $d['username'], 'email' => $d['email'], 'rol_id' => $d['rol_id'],
            'password_hash' => password_hash($d['password'], PASSWORD_BCRYPT), 'activo' => 1]));
        self::vincular($id, $d['empleado_id'] ? (int) $d['empleado_id'] : null);

        return $id;
    }

    public static function actualizar(int $id, array $d, bool $activo): void
    {
        $datos = ['username' => $d['username'], 'email' => $d['email'], 'rol_id' => $d['rol_id'], 'activo' => (int) $activo];
        if (! empty($d['password'])) {
            $datos['password_hash'] = password_hash($d['password'], PASSWORD_BCRYPT);
        }
        DB::actualizar('usuarios', self::modificacion($datos), $id);
        self::vincular($id, $d['empleado_id'] ? (int) $d['empleado_id'] : null);
    }

    private static function vincular(int $usuarioId, ?int $empleadoId): void
    {
        DB::ejecutar('UPDATE empleados SET usuario_id = NULL WHERE usuario_id = :u AND id <> :e', ['u' => $usuarioId, 'e' => $empleadoId ?? 0]);
        if ($empleadoId) {
            DB::ejecutar('UPDATE empleados SET usuario_id = :u, updated_by = :by WHERE id = :e', ['u' => $usuarioId, 'e' => $empleadoId, 'by' => Auth::id()]);
        }
    }
}

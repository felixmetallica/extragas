<?php

namespace App\Core;

/** Autenticación contra la tabla usuarios (contraseña en password_hash con bcrypt) */
final class Auth
{
    private static ?array $usuario = null;

    public static function intentar(string $username, string $clave): bool
    {
        $u = DB::uno('SELECT * FROM usuarios WHERE username = :u AND activo = 1 AND deleted_at IS NULL', ['u' => $username]);
        if (! $u || ! password_verify($clave, $u['password_hash'])) {
            return false;
        }
        if (password_needs_rehash($u['password_hash'], PASSWORD_BCRYPT)) {
            DB::actualizar('usuarios', ['password_hash' => password_hash($clave, PASSWORD_BCRYPT)], (int) $u['id']);
        }
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = (int) $u['id'];
        DB::actualizar('usuarios', ['ultimo_login' => date('Y-m-d H:i:s')], (int) $u['id']);
        self::$usuario = null;

        return true;
    }

    public static function salir(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }

    public static function id(): ?int
    {
        return $_SESSION['usuario_id'] ?? null;
    }

    /** Usuario logueado con su rol y empleado vinculado */
    public static function usuario(): ?array
    {
        if (! self::id()) {
            return null;
        }

        return self::$usuario ??= DB::uno(
            "SELECT u.*, r.codigo AS rol_codigo, r.nombre AS rol_nombre, e.id AS empleado_id, e.nombre AS empleado_nombre, e.apellido AS empleado_apellido
             FROM usuarios u JOIN roles r ON r.id = u.rol_id LEFT JOIN empleados e ON e.usuario_id = u.id AND e.deleted_at IS NULL
             WHERE u.id = :id AND u.activo = 1 AND u.deleted_at IS NULL", ['id' => self::id()]);
    }

    public static function esAdmin(): bool
    {
        return (self::usuario()['rol_codigo'] ?? null) === 'ADMIN';
    }

    /** Empleado vinculado: pedidos, recepciones y movimientos lo requieren */
    public static function empleadoId(): int
    {
        return (int) (self::usuario()['empleado_id']
            ?? throw new ErrorNegocio('Tu usuario no tiene un empleado asociado. Pedile al administrador que lo vincule en Sistema › Usuarios.'));
    }

    public static function nombre(): string
    {
        $u = self::usuario();

        return $u['empleado_id'] ? trim($u['empleado_nombre'].' '.$u['empleado_apellido']) : ($u['username'] ?? '');
    }

    public static function iniciales(): string
    {
        $u = self::usuario();

        return mb_strtoupper($u['empleado_id'] ? mb_substr($u['empleado_nombre'], 0, 1).mb_substr($u['empleado_apellido'], 0, 1) : mb_substr($u['username'], 0, 2));
    }
}

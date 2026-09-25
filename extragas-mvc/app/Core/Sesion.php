<?php

namespace App\Core;

/** Mensajes flash, datos anteriores de formularios y token CSRF */
final class Sesion
{
    public static function flash(string $clave, mixed $valor): void
    {
        $_SESSION['_flash'][$clave] = $valor;
    }

    /** Lee un mensaje flash de la petición anterior */
    public static function leer(string $clave, mixed $defecto = null): mixed
    {
        return $_SESSION['_flash_anterior'][$clave] ?? $defecto;
    }

    /** Se llama al inicio de cada petición: los flash de la anterior pasan a ser legibles */
    public static function rotar(): void
    {
        $_SESSION['_flash_anterior'] = $_SESSION['_flash'] ?? [];
        $_SESSION['_flash'] = [];
    }

    public static function guardarEntrada(array $datos): void
    {
        unset($datos['_token'], $datos['password'], $datos['password_confirmacion']);
        self::flash('_entrada', $datos);
    }

    public static function token(): string
    {
        return $_SESSION['_token'] ??= bin2hex(random_bytes(20));
    }

    public static function tokenValido(?string $token): bool
    {
        return is_string($token) && hash_equals(self::token(), $token);
    }
}

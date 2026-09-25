<?php

namespace App\Core;

/**
 * Validación de formularios. Reglas separadas por "|":
 * requerido, texto:max, numero, entero, min:n, max:n, email, fecha, en:a,b,c,
 * existe:tabla, unico:tabla,columna[,idIgnorado], confirmado, arreglo
 */
final class Validador
{
    public static function validar(array $entrada, array $reglas, array $nombres = []): array
    {
        $limpio = [];
        $errores = [];
        foreach ($reglas as $campo => $regla) {
            $valor = $entrada[$campo] ?? null;
            if (is_string($valor)) {
                $valor = trim($valor);
                if ($valor === '') {
                    $valor = null;
                }
            }
            $nombre = $nombres[$campo] ?? str_replace('_', ' ', $campo);
            $lista = explode('|', $regla);

            if ($valor === null || $valor === []) {
                if (in_array('requerido', $lista, true)) {
                    $errores[$campo] = "El campo {$nombre} es obligatorio.";
                }
                $limpio[$campo] = in_array('arreglo', $lista, true) ? [] : null;

                continue;
            }

            foreach ($lista as $r) {
                [$tipo, $arg] = array_pad(explode(':', $r, 2), 2, null);
                $error = match ($tipo) {
                    'texto' => is_string($valor) && mb_strlen($valor) <= (int) $arg ? null : "{$nombre}: máximo {$arg} caracteres.",
                    'numero' => is_numeric($valor) ? null : "{$nombre} debe ser un número.",
                    'entero' => filter_var($valor, FILTER_VALIDATE_INT) !== false ? null : "{$nombre} debe ser un número entero.",
                    'min' => is_numeric($valor) && $valor >= (float) $arg ? null : "{$nombre} debe ser como mínimo {$arg}.",
                    'max' => is_numeric($valor) && $valor <= (float) $arg ? null : "{$nombre} debe ser como máximo {$arg}.",
                    'email' => filter_var($valor, FILTER_VALIDATE_EMAIL) ? null : "{$nombre} no es un email válido.",
                    'fecha' => strtotime((string) $valor) !== false ? null : "{$nombre} no es una fecha válida.",
                    'en' => in_array((string) $valor, explode(',', $arg), true) ? null : "{$nombre} tiene un valor no permitido.",
                    'existe' => DB::valor("SELECT COUNT(*) FROM `{$arg}` WHERE id = :v", ['v' => $valor]) ? null : "{$nombre} seleccionado no existe.",
                    'unico' => self::unico($valor, $arg) ? null : "Ya existe un registro con ese {$nombre}.",
                    'confirmado' => $valor === ($entrada[$campo.'_confirmacion'] ?? null) ? null : "La confirmación de {$nombre} no coincide.",
                    'arreglo' => is_array($valor) ? null : "{$nombre} es inválido.",
                    default => null,
                };
                if ($error) {
                    $errores[$campo] = $error;
                    break;
                }
            }
            $limpio[$campo] = $valor;
        }

        if ($errores) {
            throw new ErrorValidacion($errores);
        }

        return $limpio;
    }

    private static function unico(mixed $valor, string $arg): bool
    {
        [$tabla, $columna, $ignorar] = array_pad(explode(',', $arg), 3, null);
        $params = ['v' => $valor];
        $sql = "SELECT COUNT(*) FROM `{$tabla}` WHERE `{$columna}` = :v";
        if (in_array($tabla, ['clientes', 'proveedores', 'usuarios', 'empleados', 'productos', 'garrafas'], true)) {
            $sql .= ' AND deleted_at IS NULL';
        }
        if ($ignorar) {
            $sql .= ' AND id <> :i';
            $params['i'] = $ignorar;
        }

        return ! DB::valor($sql, $params);
    }
}

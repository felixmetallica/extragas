<?php

namespace App\Core;

use PDO;

/**
 * Acceso a MySQL con PDO. Todas las consultas usan parámetros preparados.
 */
final class DB
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (! self::$pdo) {
            $c = Config::get('db');
            self::$pdo = new PDO(
                "mysql:host={$c['host']};port={$c['puerto']};dbname={$c['base']};charset=utf8mb4",
                $c['usuario'], $c['clave'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => true],
            );
        }

        return self::$pdo;
    }

    public static function consulta(string $sql, array $params = []): \PDOStatement
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);

        return $st;
    }

    /** @return array<int, array<string, mixed>> */
    public static function todos(string $sql, array $params = []): array
    {
        return self::consulta($sql, $params)->fetchAll();
    }

    public static function uno(string $sql, array $params = []): ?array
    {
        return self::consulta($sql, $params)->fetch() ?: null;
    }

    public static function valor(string $sql, array $params = []): mixed
    {
        $v = self::consulta($sql, $params)->fetchColumn();

        return $v === false ? null : $v;
    }

    /** Pares clave => valor (primera y segunda columna) */
    public static function pares(string $sql, array $params = []): array
    {
        return self::consulta($sql, $params)->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public static function ejecutar(string $sql, array $params = []): int
    {
        return self::consulta($sql, $params)->rowCount();
    }

    public static function insertar(string $tabla, array $datos): int
    {
        $cols = array_keys($datos);
        $sql = sprintf('INSERT INTO `%s` (`%s`) VALUES (%s)', $tabla, implode('`, `', $cols), implode(', ', array_map(fn ($c) => ":$c", $cols)));
        self::consulta($sql, $datos);

        return (int) self::pdo()->lastInsertId();
    }

    public static function actualizar(string $tabla, array $datos, int $id): int
    {
        $set = implode(', ', array_map(fn ($c) => "`$c` = :$c", array_keys($datos)));
        $datos['__id'] = $id;

        return self::ejecutar("UPDATE `$tabla` SET $set WHERE id = :__id", $datos);
    }

    public static function transaccion(callable $fn): mixed
    {
        $pdo = self::pdo();
        if ($pdo->inTransaction()) {
            return $fn();
        }
        $pdo->beginTransaction();
        try {
            $r = $fn();
            $pdo->commit();

            return $r;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Genera ":p0, :p1, ..." y agrega los valores a $params (para IN (...)) */
    public static function lista(array $valores, array &$params, string $prefijo = 'l'): string
    {
        if (! $valores) {
            return 'NULL';
        }
        $marcas = [];
        foreach (array_values($valores) as $i => $v) {
            $params["{$prefijo}{$i}"] = $v;
            $marcas[] = ":{$prefijo}{$i}";
        }

        return implode(', ', $marcas);
    }
}

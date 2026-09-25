<?php

namespace App\Models;

use App\Core\DB;
use App\Core\Paginador;

class Proveedor extends Modelo
{
    protected const TABLA = 'proveedores';

    public const CAMPOS = ['codigo', 'razon_social', 'nombre_fantasia', 'cuit', 'telefono_principal', 'telefono_secundario', 'email', 'calle', 'numero', 'piso',
        'depto', 'ciudad', 'codigo_postal', 'provincia_id', 'referencias', 'contacto_nombre', 'contacto_telefono', 'contacto_email', 'observaciones'];

    public static function buscar(int $id): ?array
    {
        return DB::uno('SELECT p.*, pr.nombre AS provincia_nombre FROM proveedores p LEFT JOIN provincias pr ON pr.id = p.provincia_id
            WHERE p.id = :id AND p.deleted_at IS NULL', ['id' => $id]);
    }

    public static function listar(?string $q, ?Paginador &$pag = null): array
    {
        $params = [];
        $where = 'p.deleted_at IS NULL';
        if ($q) {
            $where .= ' AND (p.razon_social LIKE :q OR p.nombre_fantasia LIKE :q OR p.cuit LIKE :q OR p.contacto_nombre LIKE :q)';
            $params['q'] = "%{$q}%";
        }
        $pag = new Paginador((int) DB::valor("SELECT COUNT(*) FROM proveedores p WHERE {$where}", $params));

        return DB::todos("SELECT p.*, pr.nombre AS provincia_nombre, COALESCE(s.saldo_total, 0) AS saldo,
                (SELECT MAX(fecha) FROM recepciones_proveedor r WHERE r.proveedor_id = p.id AND r.deleted_at IS NULL) AS ultima_recepcion
            FROM proveedores p LEFT JOIN provincias pr ON pr.id = p.provincia_id LEFT JOIN v_saldo_proveedores s ON s.proveedor_id = p.id
            WHERE {$where} ORDER BY p.activo DESC, p.razon_social".$pag->sql(), $params);
    }

    /** id => razón social */
    public static function opciones(bool $soloActivos = true): array
    {
        return DB::pares('SELECT id, razon_social FROM proveedores WHERE deleted_at IS NULL'.($soloActivos ? ' AND activo = 1' : '').' ORDER BY razon_social');
    }

    public static function crear(array $d): int
    {
        return DB::insertar('proveedores', self::alta(self::solo($d, self::CAMPOS) + ['activo' => 1]));
    }

    public static function actualizar(int $id, array $d, bool $activo): void
    {
        DB::actualizar('proveedores', self::modificacion(self::solo($d, self::CAMPOS) + ['activo' => (int) $activo]), $id);
    }

    public static function saldo(int $id): float
    {
        return (float) DB::valor('SELECT COALESCE(saldo_total, 0) FROM v_saldo_proveedores WHERE proveedor_id = :id', ['id' => $id]);
    }

    public static function domicilio(array $p): string
    {
        return implode(', ', array_filter([trim(($p['calle'] ?? '').' '.($p['numero'] ?? '')), $p['ciudad'] ?? null, $p['provincia_nombre'] ?? null]));
    }
}

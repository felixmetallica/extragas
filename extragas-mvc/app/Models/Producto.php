<?php

namespace App\Models;

use App\Core\DB;

class Producto extends Modelo
{
    protected const TABLA = 'productos';

    public const CAMPOS = ['codigo', 'nombre', 'descripcion', 'tipo_producto_id', 'capacidad_kg', 'unidad_venta', 'precio_actual', 'costo_actual', 'stock_minimo'];

    private const SELECT = 'SELECT p.*, t.codigo AS tipo_codigo, t.nombre AS tipo_nombre FROM productos p JOIN tipos_producto t ON t.id = p.tipo_producto_id';

    public static function buscar(int $id): ?array
    {
        return DB::uno(self::SELECT.' WHERE p.id = :id AND p.deleted_at IS NULL', ['id' => $id]);
    }

    public static function todos(bool $soloActivos = false): array
    {
        return DB::todos(self::SELECT.' WHERE p.deleted_at IS NULL'.($soloActivos ? ' AND p.activo = 1' : '').' ORDER BY p.tipo_producto_id, p.capacidad_kg');
    }

    public static function deGarrafa(int $capacidad): ?array
    {
        return DB::uno(self::SELECT.' WHERE p.maneja_garrafa_individual = 1 AND p.capacidad_kg = :c AND p.deleted_at IS NULL', ['c' => $capacidad]);
    }

    /** Productos de garrafa indexados por capacidad: [10 => ..., 15 => ..., 45 => ...] */
    public static function garrafasPorCapacidad(): array
    {
        $res = [];
        foreach (self::todos() as $p) {
            if ($p['maneja_garrafa_individual']) {
                $res[(int) $p['capacidad_kg']] = $p;
            }
        }

        return $res;
    }

    /** Garrafas llenas para gas, stock_actual para carbón y leña */
    public static function stockDisponible(array $p, ?array $stockGarrafas = null): float
    {
        if ($p['maneja_garrafa_individual']) {
            $stockGarrafas ??= Garrafa::stock();

            return (float) ($stockGarrafas[(int) $p['capacidad_kg']]['LLENA'] ?? 0);
        }

        return (float) $p['stock_actual'];
    }

    public static function crear(array $d, float $stockInicial): int
    {
        $esGas = Catalogo::porId('tipos_producto', (int) $d['tipo_producto_id'])['codigo'] === 'GAS';

        return DB::insertar('productos', self::alta(self::solo($d, self::CAMPOS) + [
            'maneja_garrafa_individual' => (int) $esGas, 'activo' => 1, 'stock_actual' => $esGas ? 0 : $stockInicial,
        ]));
    }

    public static function actualizar(int $id, array $d, bool $activo): void
    {
        DB::actualizar('productos', self::modificacion(self::solo($d, self::CAMPOS) + ['activo' => (int) $activo]), $id);
    }

    public static function actualizarPrecios(array $precios): void
    {
        DB::transaccion(function () use ($precios) {
            foreach ($precios as $id => $precio) {
                if (is_numeric($precio) && $precio >= 0) {
                    DB::actualizar('productos', self::modificacion(['precio_actual' => $precio]), (int) $id);
                }
            }
        });
    }

    public static function sumarStock(int $id, float $cantidad): void
    {
        DB::ejecutar('UPDATE productos SET stock_actual = stock_actual + :c WHERE id = :id', ['c' => $cantidad, 'id' => $id]);
    }

    /** Unidades vendidas por producto en los últimos 30 días */
    public static function vendidos30(): array
    {
        return DB::pares("SELECT pi.producto_id, SUM(pi.cantidad) FROM pedido_items pi JOIN pedidos p ON p.id = pi.pedido_id
            WHERE pi.tipo_linea = 'VENTA' AND p.deleted_at IS NULL AND p.estado_pedido_id <> :c AND p.fecha >= :d GROUP BY pi.producto_id",
            ['c' => Catalogo::id('estados_pedido', 'CANCELADO'), 'd' => date('Y-m-d', strtotime('-29 days'))]);
    }
}

<?php

namespace App\Models;

use App\Core\DB;
use App\Core\ErrorNegocio;
use App\Core\Paginador;

/**
 * Recepciones de mercadería de proveedores. El número lo asigna el trigger
 * trg_recepciones_bi y monto_pagado los triggers de pagos_proveedor.
 */
class Recepcion extends Modelo
{
    protected const TABLA = 'recepciones_proveedor';

    private const PRODUCTOS = "(SELECT GROUP_CONCAT(CONCAT(TRIM(TRAILING '.' FROM TRIM(TRAILING '0' FROM ri.cantidad)), '× ', pr.nombre) SEPARATOR ', ')
        FROM recepcion_items ri JOIN productos pr ON pr.id = ri.producto_id WHERE ri.recepcion_id = r.id) AS productos";

    private const SELECT = 'SELECT r.*, pv.razon_social AS proveedor_nombre, pv.cuit AS proveedor_cuit, e.nombre AS empleado_nombre, e.apellido AS empleado_apellido, '.self::PRODUCTOS.",
        (SELECT COUNT(*) FROM movimientos_garrafa m JOIN tipos_movimiento_garrafa t ON t.id = m.tipo_movimiento_id WHERE m.recepcion_id = r.id AND t.codigo = 'ENTREGA_PROVEEDOR') AS vacias_entregadas
        FROM recepciones_proveedor r JOIN proveedores pv ON pv.id = r.proveedor_id JOIN empleados e ON e.id = r.empleado_id";

    public static function buscar(int $id): ?array
    {
        return DB::uno(self::SELECT.' WHERE r.id = :id AND r.deleted_at IS NULL', ['id' => $id]);
    }

    private static function filtros(array $f, array &$params): string
    {
        $where = ['r.deleted_at IS NULL'];
        if (! empty($f['desde'])) {
            $where[] = 'r.fecha BETWEEN :desde AND :hasta';
            $params += ['desde' => $f['desde'].' 00:00:00', 'hasta' => $f['hasta'].' 23:59:59'];
        }
        if (! empty($f['proveedor'])) {
            $where[] = 'r.proveedor_id = :pv';
            $params['pv'] = $f['proveedor'];
        }
        if (! empty($f['pago'])) {
            $where[] = match ($f['pago']) {
                'Pagado' => 'r.saldo <= 0',
                'Parcial' => 'r.saldo > 0 AND r.monto_pagado > 0',
                default => 'r.saldo > 0 AND r.monto_pagado = 0',
            };
        }

        return implode(' AND ', $where);
    }

    public static function listar(array $f, ?Paginador &$pag = null, bool $todos = false, string $parametro = 'pagina', int $porPagina = 15): array
    {
        $params = [];
        $w = self::filtros($f, $params);
        $limite = '';
        if (! $todos) {
            $pag = new Paginador((int) DB::valor("SELECT COUNT(*) FROM recepciones_proveedor r WHERE {$w}", $params), $porPagina, $parametro);
            $limite = $pag->sql();
        }

        return DB::todos(self::SELECT." WHERE {$w} ORDER BY r.fecha DESC{$limite}", $params);
    }

    public static function resumen(array $f): array
    {
        $params = [];
        $w = self::filtros($f, $params);

        return DB::uno("SELECT COUNT(*) n, COALESCE(SUM(r.total), 0) total,
            (SELECT COUNT(*) FROM garrafas g WHERE g.recepcion_id IN (SELECT r.id FROM recepciones_proveedor r WHERE {$w})) garrafas
            FROM recepciones_proveedor r WHERE {$w}", $params);
    }

    public static function items(int $id): array
    {
        return DB::todos('SELECT ri.*, pr.nombre AS producto_nombre FROM recepcion_items ri JOIN productos pr ON pr.id = ri.producto_id WHERE ri.recepcion_id = :id', ['id' => $id]);
    }

    public static function garrafasIngresadas(int $id): array
    {
        return DB::todos('SELECT id, codigo FROM garrafas WHERE recepcion_id = :id ORDER BY codigo', ['id' => $id]);
    }

    public static function vaciasEntregadas(int $id): array
    {
        return DB::todos("SELECT g.id, g.codigo FROM movimientos_garrafa m JOIN garrafas g ON g.id = m.garrafa_id JOIN tipos_movimiento_garrafa t ON t.id = m.tipo_movimiento_id
            WHERE m.recepcion_id = :id AND t.codigo = 'ENTREGA_PROVEEDOR' ORDER BY g.codigo", ['id' => $id]);
    }

    public static function pagos(int $id): array
    {
        return DB::todos('SELECT pp.*, fp.nombre AS forma_nombre FROM pagos_proveedor pp JOIN formas_pago fp ON fp.id = pp.forma_pago_id
            WHERE pp.recepcion_id = :id AND pp.deleted_at IS NULL ORDER BY pp.fecha', ['id' => $id]);
    }

    public static function conSaldo(?int $proveedorId = null): array
    {
        $params = [];
        $sql = 'SELECT * FROM recepciones_proveedor WHERE deleted_at IS NULL AND saldo > 0';
        if ($proveedorId) {
            $sql .= ' AND proveedor_id = :p';
            $params['p'] = $proveedorId;
        }

        return DB::todos($sql.' ORDER BY fecha', $params);
    }

    /**
     * Registra la recepción:
     *  - garrafas recibidas: alta como llenas (códigos automáticos o los informados)
     *  - vacías entregadas: salen las vacías aptas más antiguas
     *  - carbón / leña: suma stock y actualiza el costo
     *
     * @param  array  $items  [['producto_id', 'cantidad', 'precio_unitario', 'codigos']]
     * @param  array<int,int>  $vacias  capacidad => cantidad de vacías que se lleva el proveedor
     */
    public static function registrar(array $d, array $items, array $vacias, int $empleadoId): int
    {
        return DB::transaccion(function () use ($d, $items, $vacias, $empleadoId) {
            $id = DB::insertar('recepciones_proveedor', self::alta([
                'fecha' => $d['fecha'], 'proveedor_id' => $d['proveedor_id'], 'empleado_id' => $empleadoId,
                'numero_factura_proveedor' => $d['numero_factura_proveedor'] ?? null, 'observaciones' => $d['observaciones'] ?? null,
            ]));
            $numero = DB::valor('SELECT numero FROM recepciones_proveedor WHERE id = :id', ['id' => $id]);

            $subtotal = 0;
            foreach ($items as $it) {
                $prod = Producto::buscar((int) $it['producto_id']) ?? throw new ErrorNegocio('Producto inexistente.');
                DB::insertar('recepcion_items', ['recepcion_id' => $id, 'producto_id' => $prod['id'], 'cantidad' => $it['cantidad'], 'precio_unitario' => $it['precio_unitario']]);
                DB::actualizar('productos', ['costo_actual' => $it['precio_unitario']], (int) $prod['id']);
                $subtotal += $it['cantidad'] * $it['precio_unitario'];

                if ($prod['maneja_garrafa_individual']) {
                    $codigos = Garrafa::codigosDeTexto($it['codigos'] ?? '');
                    if ($codigos && count($codigos) !== (int) $it['cantidad']) {
                        throw new ErrorNegocio('Informaste '.count($codigos)." códigos para {$it['cantidad']} garrafas de ".num($prod['capacidad_kg']).' kg.');
                    }
                    for ($i = 0; $i < (int) $it['cantidad']; $i++) {
                        if (isset($codigos[$i]) && Garrafa::codigoExiste($codigos[$i])) {
                            throw new ErrorNegocio("El código {$codigos[$i]} ya existe.");
                        }
                        Garrafa::darDeAlta((int) $prod['capacidad_kg'], 'LLENA', ['codigo' => $codigos[$i] ?? null, 'proveedor_id' => $d['proveedor_id'],
                            'recepcion_id' => $id, 'empleado_id' => $empleadoId, 'observaciones' => "Recepción {$numero}"], $d['fecha']);
                    }
                } else {
                    Producto::sumarStock((int) $prod['id'], (float) $it['cantidad']);
                }
            }

            foreach ($vacias as $cap => $cantidad) {
                if ((int) $cantidad <= 0) {
                    continue;
                }
                $lista = Garrafa::enEstado('VACIA', (int) $cap, null, (int) $cantidad);
                if (count($lista) < (int) $cantidad) {
                    throw new ErrorNegocio('Hay sólo '.count($lista)." garrafas vacías de {$cap} kg para entregar al proveedor.");
                }
                foreach ($lista as $g) {
                    Garrafa::mover($g, 'ENTREGA_PROVEEDOR', 'EN_PROVEEDOR', ['recepcion_id' => $id, 'empleado_id' => $empleadoId,
                        'observaciones' => "Entregada al proveedor en recepción {$numero}"], $d['fecha']);
                }
            }

            $descuento = min((float) ($d['descuento'] ?? 0), $subtotal);
            DB::actualizar('recepciones_proveedor', ['subtotal' => $subtotal, 'descuento' => $descuento, 'total' => $subtotal - $descuento], $id);

            return $id;
        });
    }
}

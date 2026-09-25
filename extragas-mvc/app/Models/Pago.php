<?php

namespace App\Models;

use App\Core\DB;
use App\Core\ErrorNegocio;
use App\Core\Paginador;

/**
 * Pagos de clientes. numero_recibo lo asigna el trigger trg_pagos_bi y
 * monto_pagado del pedido lo recalculan los triggers de pagos.
 */
class Pago extends Modelo
{
    protected const TABLA = 'pagos';

    private const SELECT = 'SELECT pa.*, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido, c.dni AS cliente_dni, p.numero AS pedido_numero, p.fecha AS pedido_fecha,
        fp.nombre AS forma_nombre, fp.codigo AS forma_codigo, u.username, e.nombre AS empleado_nombre, e.apellido AS empleado_apellido
        FROM pagos pa JOIN clientes c ON c.id = pa.cliente_id LEFT JOIN pedidos p ON p.id = pa.pedido_id
        JOIN formas_pago fp ON fp.id = pa.forma_pago_id LEFT JOIN usuarios u ON u.id = pa.created_by LEFT JOIN empleados e ON e.usuario_id = u.id';

    private static function filtros(array $f, array &$params): string
    {
        $where = ['pa.deleted_at IS NULL', 'pa.fecha BETWEEN :desde AND :hasta'];
        $params += ['desde' => $f['desde'].' 00:00:00', 'hasta' => $f['hasta'].' 23:59:59'];
        if (! empty($f['forma_pago'])) {
            $where[] = 'pa.forma_pago_id = :fp';
            $params['fp'] = $f['forma_pago'];
        }
        if (! empty($f['q'])) {
            $where[] = '(pa.numero_recibo LIKE :rq OR pa.referencia LIKE :rq OR '.Cliente::condicionBusqueda($f['q'], $params).')';
            $params['rq'] = "%{$f['q']}%";
        }

        return implode(' AND ', $where);
    }

    public static function listar(array $f, ?Paginador &$pag = null, bool $todos = false): array
    {
        $params = [];
        $w = self::filtros($f, $params);
        $limite = '';
        if (! $todos) {
            $pag = new Paginador((int) DB::valor("SELECT COUNT(*) FROM pagos pa JOIN clientes c ON c.id = pa.cliente_id WHERE {$w}", $params));
            $limite = $pag->sql();
        }

        return DB::todos(self::SELECT." WHERE {$w} ORDER BY pa.fecha DESC, pa.id DESC{$limite}", $params);
    }

    /** Total por forma de pago en el período filtrado */
    public static function porForma(array $f): array
    {
        $params = [];
        $w = self::filtros($f, $params);

        return DB::pares("SELECT fp.nombre, SUM(pa.monto) FROM pagos pa JOIN clientes c ON c.id = pa.cliente_id JOIN formas_pago fp ON fp.id = pa.forma_pago_id
            WHERE {$w} GROUP BY fp.nombre ORDER BY SUM(pa.monto) DESC", $params);
    }

    public static function porIds(array $ids): array
    {
        $params = [];

        return $ids ? DB::todos(self::SELECT.' WHERE pa.deleted_at IS NULL AND pa.id IN ('.DB::lista($ids, $params).') ORDER BY pa.id', $params) : [];
    }

    public static function cobradoHoy(): float
    {
        return (float) DB::valor('SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE deleted_at IS NULL AND DATE(fecha) = CURDATE()');
    }

    /**
     * Registra un cobro. Sin pedido, el importe se aplica a los pedidos impagos
     * más antiguos del cliente (un recibo por pedido).
     *
     * @return int[] ids de los pagos creados
     */
    public static function cobrar(int $clienteId, ?array $pedido, float $monto, int $formaPagoId, ?string $referencia, string $fecha, ?string $observaciones = null): array
    {
        $pedidos = $pedido ? [$pedido] : Pedido::conSaldo($clienteId);
        $deuda = array_sum(array_column($pedidos, 'saldo'));
        if ($monto <= 0) {
            throw new ErrorNegocio('El importe debe ser mayor a cero.');
        }
        if ($monto > $deuda + 0.001) {
            throw new ErrorNegocio('El importe supera el saldo adeudado ('.pesos($deuda).').');
        }

        return DB::transaccion(function () use ($clienteId, $pedidos, $monto, $formaPagoId, $referencia, $fecha, $observaciones) {
            $ids = [];
            foreach ($pedidos as $p) {
                if ($monto <= 0) {
                    break;
                }
                $aplicado = min($monto, (float) $p['saldo']);
                $ids[] = DB::insertar('pagos', self::alta(['fecha' => $fecha, 'cliente_id' => $clienteId, 'pedido_id' => $p['id'], 'forma_pago_id' => $formaPagoId,
                    'monto' => $aplicado, 'referencia' => $referencia, 'observaciones' => $observaciones]));
                $monto -= $aplicado;
            }

            return $ids;
        });
    }

    /** Anulación: borrado lógico; el trigger recalcula el monto pagado del pedido */
    public static function anular(int $id): void
    {
        self::eliminar($id);
    }
}

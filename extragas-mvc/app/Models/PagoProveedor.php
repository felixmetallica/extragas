<?php

namespace App\Models;

use App\Core\DB;
use App\Core\ErrorNegocio;
use App\Core\Paginador;

/** Pagos a proveedores. El número lo asigna el trigger trg_pagos_proveedor_bi. */
class PagoProveedor extends Modelo
{
    protected const TABLA = 'pagos_proveedor';

    private const SELECT = 'SELECT pp.*, pv.razon_social AS proveedor_nombre, r.numero AS recepcion_numero, fp.nombre AS forma_nombre
        FROM pagos_proveedor pp JOIN proveedores pv ON pv.id = pp.proveedor_id LEFT JOIN recepciones_proveedor r ON r.id = pp.recepcion_id
        JOIN formas_pago fp ON fp.id = pp.forma_pago_id';

    public static function listar(array $f, ?Paginador &$pag = null, bool $todos = false, string $parametro = 'pagina', int $porPagina = 15): array
    {
        $params = [];
        $where = ['pp.deleted_at IS NULL'];
        if (! empty($f['desde'])) {
            $where[] = 'pp.fecha BETWEEN :desde AND :hasta';
            $params += ['desde' => $f['desde'].' 00:00:00', 'hasta' => $f['hasta'].' 23:59:59'];
        }
        if (! empty($f['proveedor'])) {
            $where[] = 'pp.proveedor_id = :pv';
            $params['pv'] = $f['proveedor'];
        }
        $w = implode(' AND ', $where);
        $limite = '';
        if (! $todos) {
            $pag = new Paginador((int) DB::valor("SELECT COUNT(*) FROM pagos_proveedor pp WHERE {$w}", $params), $porPagina, $parametro);
            $limite = $pag->sql();
        }

        return DB::todos(self::SELECT." WHERE {$w} ORDER BY pp.fecha DESC, pp.id DESC{$limite}", $params);
    }

    /**
     * Pago a proveedor. Sin recepción, se aplica a las recepciones impagas más antiguas.
     *
     * @return string[] números de pago generados
     */
    public static function pagar(int $proveedorId, ?array $recepcion, float $monto, int $formaPagoId, ?string $referencia, string $fecha, ?string $observaciones = null): array
    {
        $recepciones = $recepcion ? [$recepcion] : Recepcion::conSaldo($proveedorId);
        $deuda = array_sum(array_column($recepciones, 'saldo'));
        if ($monto <= 0) {
            throw new ErrorNegocio('El importe debe ser mayor a cero.');
        }
        if ($monto > $deuda + 0.001) {
            throw new ErrorNegocio('El importe supera el saldo adeudado al proveedor ('.pesos($deuda).').');
        }

        return DB::transaccion(function () use ($proveedorId, $recepciones, $monto, $formaPagoId, $referencia, $fecha, $observaciones) {
            $numeros = [];
            foreach ($recepciones as $r) {
                if ($monto <= 0) {
                    break;
                }
                $aplicado = min($monto, (float) $r['saldo']);
                $id = DB::insertar('pagos_proveedor', self::alta(['fecha' => $fecha, 'proveedor_id' => $proveedorId, 'recepcion_id' => $r['id'],
                    'forma_pago_id' => $formaPagoId, 'monto' => $aplicado, 'referencia' => $referencia, 'observaciones' => $observaciones]));
                $numeros[] = DB::valor('SELECT numero FROM pagos_proveedor WHERE id = :id', ['id' => $id]);
                $monto -= $aplicado;
            }

            return $numeros;
        });
    }
}

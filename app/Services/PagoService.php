<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Pago;
use App\Models\PagoProveedor;
use App\Models\Pedido;
use App\Models\Proveedor;
use App\Models\RecepcionProveedor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Registro de pagos. monto_pagado de pedidos y recepciones lo recalculan los
 * triggers de pagos / pagos_proveedor; los números de recibo también.
 */
class PagoService
{
    /**
     * Pago de cliente. Sin pedido, el importe se aplica a los pedidos impagos más antiguos
     * (un recibo por pedido).
     *
     * @return Collection<int, Pago>
     */
    public function cobrar(Cliente $cliente, ?Pedido $pedido, float $monto, int $formaPagoId, ?string $referencia = null, $fecha = null, ?string $observaciones = null): Collection
    {
        $pedidos = $pedido ? collect([$pedido]) : $cliente->pedidos()->conSaldo()->orderBy('fecha')->get();
        $deuda = $pedidos->sum('saldo');
        if ($monto <= 0) {
            throw new \DomainException('El importe debe ser mayor a cero.');
        }
        if ($monto > $deuda + 0.001) {
            throw new \DomainException('El importe supera el saldo adeudado ($ '.number_format($deuda, 2, ',', '.').').');
        }

        return DB::transaction(function () use ($cliente, $pedidos, $monto, $formaPagoId, $referencia, $fecha, $observaciones) {
            $pagos = collect();
            foreach ($pedidos as $p) {
                if ($monto <= 0) {
                    break;
                }
                $aplicado = min($monto, (float) $p->saldo);
                $pagos->push(Pago::create([
                    'fecha' => $fecha ?? now(), 'cliente_id' => $cliente->id, 'pedido_id' => $p->id,
                    'forma_pago_id' => $formaPagoId, 'monto' => $aplicado, 'referencia' => $referencia, 'observaciones' => $observaciones,
                ])->refresh());
                $monto -= $aplicado;
            }

            return $pagos;
        });
    }

    public function anular(Pago $pago): void
    {
        $pago->delete(); // borrado lógico: el trigger recalcula el monto pagado
    }

    /**
     * Pago a proveedor. Sin recepción, el importe se aplica a las recepciones impagas más antiguas.
     *
     * @return Collection<int, PagoProveedor>
     */
    public function pagarProveedor(Proveedor $proveedor, ?RecepcionProveedor $recepcion, float $monto, int $formaPagoId, ?string $referencia = null, $fecha = null, ?string $observaciones = null): Collection
    {
        $recepciones = $recepcion ? collect([$recepcion]) : $proveedor->recepciones()->where('saldo', '>', 0)->orderBy('fecha')->get();
        $deuda = $recepciones->sum('saldo');
        if ($monto <= 0) {
            throw new \DomainException('El importe debe ser mayor a cero.');
        }
        if ($monto > $deuda + 0.001) {
            throw new \DomainException('El importe supera el saldo adeudado al proveedor ($ '.number_format($deuda, 2, ',', '.').').');
        }

        return DB::transaction(function () use ($proveedor, $recepciones, $monto, $formaPagoId, $referencia, $fecha, $observaciones) {
            $pagos = collect();
            foreach ($recepciones as $r) {
                if ($monto <= 0) {
                    break;
                }
                $aplicado = min($monto, (float) $r->saldo);
                $pagos->push(PagoProveedor::create([
                    'fecha' => $fecha ?? now(), 'proveedor_id' => $proveedor->id, 'recepcion_id' => $r->id,
                    'forma_pago_id' => $formaPagoId, 'monto' => $aplicado, 'referencia' => $referencia, 'observaciones' => $observaciones,
                ])->refresh());
                $monto -= $aplicado;
            }

            return $pagos;
        });
    }
}

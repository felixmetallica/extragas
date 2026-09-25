<?php

namespace App\Models;

use App\Core\DB;

/** Consultas para el panel de inicio y los informes (usan las vistas v_*) */
final class Informe
{
    private static function cancelado(): int
    {
        return Catalogo::id('estados_pedido', 'CANCELADO');
    }

    private static function rango(string $desde, string $hasta): array
    {
        return ['desde' => "{$desde} 00:00:00", 'hasta' => "{$hasta} 23:59:59"];
    }

    /** Totales de ventas por día */
    public static function ventasPorDia(string $desde, string $hasta): array
    {
        $filas = DB::todos('SELECT DATE(fecha) dia, COUNT(*) n, SUM(total) total FROM pedidos WHERE deleted_at IS NULL AND estado_pedido_id <> :c
            AND fecha BETWEEN :desde AND :hasta GROUP BY dia', self::rango($desde, $hasta) + ['c' => self::cancelado()]);

        return array_column($filas, null, 'dia');
    }

    public static function ventasMes(): float
    {
        return (float) DB::valor('SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE deleted_at IS NULL AND estado_pedido_id <> :c AND fecha >= :d',
            ['c' => self::cancelado(), 'd' => date('Y-m-01')]);
    }

    public static function pedidosHoy(): int
    {
        return (int) DB::valor('SELECT COUNT(*) FROM pedidos WHERE deleted_at IS NULL AND estado_pedido_id <> :c AND DATE(fecha) = CURDATE()', ['c' => self::cancelado()]);
    }

    public static function cobrosHoyPorForma(): array
    {
        return DB::pares('SELECT fp.codigo, SUM(p.monto) FROM pagos p JOIN formas_pago fp ON fp.id = p.forma_pago_id
            WHERE p.deleted_at IS NULL AND DATE(p.fecha) = CURDATE() GROUP BY fp.codigo');
    }

    public static function saldosClientes(): array
    {
        return DB::todos('SELECT * FROM v_saldo_clientes');
    }

    public static function saldosProveedores(): array
    {
        return DB::todos('SELECT * FROM v_saldo_proveedores');
    }

    /* ---------- Pedidos ---------- */

    public static function pedidosResumen(string $desde, string $hasta): array
    {
        return DB::uno("SELECT COUNT(*) n, COALESCE(SUM(total), 0) total, COUNT(DISTINCT cliente_id) clientes, COALESCE(SUM(saldo), 0) saldo
            FROM v_pedidos_resumen WHERE estado_codigo <> 'CANCELADO' AND fecha BETWEEN :desde AND :hasta", self::rango($desde, $hasta));
    }

    public static function pedidosPorEstado(string $desde, string $hasta): array
    {
        return DB::pares('SELECT estado_nombre, COUNT(*) FROM v_pedidos_resumen WHERE fecha BETWEEN :desde AND :hasta GROUP BY estado_nombre', self::rango($desde, $hasta));
    }

    public static function pedidosPorMedio(string $desde, string $hasta): array
    {
        return DB::pares('SELECT mc.nombre, COUNT(*) FROM pedidos p JOIN medios_contacto_pedido mc ON mc.id = p.medio_contacto_id
            WHERE p.deleted_at IS NULL AND p.estado_pedido_id <> :c AND p.fecha BETWEEN :desde AND :hasta GROUP BY mc.nombre',
            self::rango($desde, $hasta) + ['c' => self::cancelado()]);
    }

    public static function pedidosPorEmpleado(string $desde, string $hasta): array
    {
        return DB::todos("SELECT empleado, COUNT(*) n, SUM(total) total FROM v_pedidos_resumen WHERE estado_codigo <> 'CANCELADO'
            AND fecha BETWEEN :desde AND :hasta GROUP BY empleado ORDER BY n DESC", self::rango($desde, $hasta));
    }

    public static function pedidosDetalle(string $desde, string $hasta): array
    {
        return DB::todos('SELECT * FROM v_pedidos_resumen WHERE fecha BETWEEN :desde AND :hasta ORDER BY fecha DESC', self::rango($desde, $hasta));
    }

    /* ---------- Productos ---------- */

    public static function productosRanking(string $desde, string $hasta): array
    {
        return DB::todos('SELECT producto_id, producto_nombre, tipo_producto, SUM(cantidad_vendida) vendida, SUM(cantidad_entregada) entregada,
                SUM(cantidad_devuelta) devuelta, SUM(monto_total) monto
            FROM v_productos_mas_vendidos WHERE fecha BETWEEN :desde AND :hasta
            GROUP BY producto_id, producto_nombre, tipo_producto ORDER BY vendida DESC', ['desde' => $desde, 'hasta' => $hasta]);
    }

    /* ---------- Regularidad ---------- */

    public static function regularidad(string $desde, string $hasta): array
    {
        $vista = DB::todos('SELECT v.*, c.nombre, c.apellido, c.telefono_principal FROM v_regularidad_clientes v JOIN clientes c ON c.id = v.cliente_id
            WHERE v.total_pedidos > 0');
        $enPeriodo = DB::pares('SELECT cliente_id, COUNT(*) FROM pedidos WHERE deleted_at IS NULL AND estado_pedido_id <> :c AND fecha BETWEEN :desde AND :hasta GROUP BY cliente_id',
            self::rango($desde, $hasta) + ['c' => self::cancelado()]);
        $calc = Cliente::regularidad(array_column($vista, 'cliente_id'));
        foreach ($vista as &$f) {
            $f['promedio'] = $f['dias_promedio_entre_pedidos'] !== null ? (int) round((float) $f['dias_promedio_entre_pedidos']) : null;
            $f['r'] = $calc[$f['cliente_id']];
            $f['n_periodo'] = (int) ($enPeriodo[$f['cliente_id']] ?? 0);
        }
        unset($f);
        usort($vista, fn ($a, $b) => ($a['promedio'] ?? 9999) <=> ($b['promedio'] ?? 9999));

        return $vista;
    }

    /** Pedidos por día de la semana (1 = domingo … 7 = sábado) */
    public static function pedidosPorDiaSemana(string $desde, string $hasta): array
    {
        $datos = DB::pares('SELECT DAYOFWEEK(fecha) d, COUNT(*) FROM pedidos WHERE deleted_at IS NULL AND estado_pedido_id <> :c AND fecha BETWEEN :desde AND :hasta GROUP BY d',
            self::rango($desde, $hasta) + ['c' => self::cancelado()]);

        return array_map(fn ($d) => (int) ($datos[$d] ?? 0), range(1, 7));
    }

    /* ---------- Pagos ---------- */

    public static function cobrosPorForma(string $desde, string $hasta): array
    {
        return DB::todos('SELECT forma_pago_nombre, SUM(cantidad_pagos) cantidad, SUM(monto_total) total FROM v_pagos_por_forma_pago
            WHERE fecha BETWEEN :desde AND :hasta GROUP BY forma_pago_nombre ORDER BY total DESC', ['desde' => $desde, 'hasta' => $hasta]);
    }

    public static function cobrosPorDia(string $desde, string $hasta): array
    {
        return DB::pares('SELECT fecha, SUM(monto_total) FROM v_pagos_por_forma_pago WHERE fecha BETWEEN :desde AND :hasta GROUP BY fecha', ['desde' => $desde, 'hasta' => $hasta]);
    }

    public static function pagosProveedoresPorDia(string $desde, string $hasta): array
    {
        return DB::pares('SELECT DATE(fecha) d, SUM(monto) FROM pagos_proveedor WHERE deleted_at IS NULL AND fecha BETWEEN :desde AND :hasta GROUP BY d', self::rango($desde, $hasta));
    }

    public static function vendidoPeriodo(string $desde, string $hasta): array
    {
        return DB::uno('SELECT COALESCE(SUM(total), 0) total, COALESCE(SUM(saldo), 0) saldo FROM pedidos WHERE deleted_at IS NULL AND estado_pedido_id <> :c
            AND fecha BETWEEN :desde AND :hasta', self::rango($desde, $hasta) + ['c' => self::cancelado()]);
    }

    public static function formasHabituales(): array
    {
        return DB::pares('SELECT fp.nombre, COUNT(*) FROM clientes c JOIN formas_pago fp ON fp.id = c.forma_pago_habitual_id
            WHERE c.activo = 1 AND c.deleted_at IS NULL GROUP BY fp.nombre');
    }

    /* ---------- Garrafas ---------- */

    public static function garrafasEnClientes(): array
    {
        return DB::todos('SELECT * FROM v_garrafas_en_clientes');
    }

    /** Movimientos de los últimos 30 días por capacidad y tipo */
    public static function flujoGarrafas(): array
    {
        $res = [];
        foreach (DB::todos('SELECT g.capacidad_kg cap, t.codigo, COUNT(*) n FROM movimientos_garrafa m JOIN garrafas g ON g.id = m.garrafa_id
            JOIN tipos_movimiento_garrafa t ON t.id = m.tipo_movimiento_id WHERE m.fecha >= :d GROUP BY cap, t.codigo', ['d' => date('Y-m-d', strtotime('-29 days'))]) as $f) {
            $res[$f['codigo']][(int) $f['cap']] = (int) $f['n'];
        }

        return $res;
    }
}

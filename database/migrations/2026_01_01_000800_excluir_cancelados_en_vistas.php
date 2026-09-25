<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrección sobre las vistas originales: un pedido CANCELADO conserva total y
 * saldo, por lo que figuraba como deuda del cliente y como venta. Estas versiones
 * lo excluyen de saldos, cuenta corriente, regularidad y productos vendidos.
 */
return new class extends Migration
{
    private const NO_CANCELADO = "p.estado_pedido_id <> (SELECT id FROM estados_pedido WHERE codigo = 'CANCELADO')";

    public function up(): void
    {
        $nc = self::NO_CANCELADO;

        DB::statement(<<<SQL
            CREATE OR REPLACE VIEW v_saldo_clientes AS
            SELECT c.id AS cliente_id, CONCAT(c.apellido, ', ', c.nombre) AS cliente, c.telefono_principal,
                   COUNT(p.id) AS pedidos_pendientes, COALESCE(SUM(p.saldo), 0) AS saldo_total
            FROM clientes c
            LEFT JOIN pedidos p ON p.cliente_id = c.id AND p.deleted_at IS NULL AND p.saldo > 0 AND {$nc}
            WHERE c.deleted_at IS NULL
            GROUP BY c.id, cliente, c.telefono_principal
            HAVING saldo_total > 0
            ORDER BY saldo_total DESC
        SQL);

        DB::statement(<<<SQL
            CREATE OR REPLACE VIEW v_regularidad_clientes AS
            SELECT c.id AS cliente_id, CONCAT(c.apellido, ', ', c.nombre) AS cliente, COUNT(p.id) AS total_pedidos,
                   MAX(p.fecha) AS ultimo_pedido, MIN(p.fecha) AS primer_pedido,
                   CASE WHEN COUNT(p.id) > 1 THEN (TO_DAYS(MAX(p.fecha)) - TO_DAYS(MIN(p.fecha))) / (COUNT(p.id) - 1) END AS dias_promedio_entre_pedidos,
                   SUM(p.total) AS total_facturado, SUM(p.saldo) AS saldo_pendiente
            FROM clientes c
            LEFT JOIN pedidos p ON p.cliente_id = c.id AND p.deleted_at IS NULL AND {$nc}
            WHERE c.deleted_at IS NULL
            GROUP BY c.id, cliente
        SQL);

        DB::statement(<<<SQL
            CREATE OR REPLACE VIEW v_cuenta_corriente_cliente AS
            SELECT c.id AS cliente_id, CONCAT(c.apellido, ', ', c.nombre) AS cliente, p.id AS pedido_id, p.numero AS comprobante,
                   p.fecha, 'PEDIDO' AS tipo_movimiento, p.total AS debe, 0 AS haber, p.observaciones
            FROM pedidos p JOIN clientes c ON c.id = p.cliente_id
            WHERE p.deleted_at IS NULL AND {$nc}
            UNION ALL
            SELECT c.id, CONCAT(c.apellido, ', ', c.nombre), pa.pedido_id, pa.numero_recibo, pa.fecha, 'PAGO', 0, pa.monto, pa.observaciones
            FROM pagos pa JOIN clientes c ON c.id = pa.cliente_id
            WHERE pa.deleted_at IS NULL
            ORDER BY cliente_id, fecha
        SQL);

        DB::statement(<<<SQL
            CREATE OR REPLACE VIEW v_productos_mas_vendidos AS
            SELECT CAST(p.fecha AS DATE) AS fecha, pi.producto_id, pr.codigo AS producto_codigo, pr.nombre AS producto_nombre,
                   tp.nombre AS tipo_producto,
                   SUM(CASE WHEN pi.tipo_linea = 'VENTA' THEN pi.cantidad ELSE 0 END) AS cantidad_vendida,
                   SUM(CASE WHEN pi.tipo_linea = 'ENTREGA' THEN pi.cantidad ELSE 0 END) AS cantidad_entregada,
                   SUM(CASE WHEN pi.tipo_linea = 'DEVOLUCION' THEN pi.cantidad ELSE 0 END) AS cantidad_devuelta,
                   SUM(pi.subtotal) AS monto_total
            FROM pedido_items pi
            JOIN pedidos p ON p.id = pi.pedido_id
            JOIN productos pr ON pr.id = pi.producto_id
            JOIN tipos_producto tp ON tp.id = pr.tipo_producto_id
            WHERE p.deleted_at IS NULL AND {$nc}
            GROUP BY CAST(p.fecha AS DATE), pi.producto_id, pr.codigo, pr.nombre, tp.nombre
        SQL);
    }

    public function down(): void
    {
        // Las versiones originales se recrean ejecutando la migración de vistas
        (require __DIR__.'/2026_01_01_000600_create_vistas.php')->up();
    }
};

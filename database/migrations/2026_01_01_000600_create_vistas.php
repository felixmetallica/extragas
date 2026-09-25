<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Vistas para informes (copiadas de extragas.sql).
 */
return new class extends Migration
{
    private array $vistas = [
        'v_cuenta_corriente_cliente' => <<<'SQL'
SELECT `c`.`id` AS `cliente_id`, concat(`c`.`apellido`,', ',`c`.`nombre`) AS `cliente`, `p`.`id` AS `pedido_id`, `p`.`numero` AS `comprobante`, `p`.`fecha` AS `fecha`, 'PEDIDO' AS `tipo_movimiento`, `p`.`total` AS `debe`, 0 AS `haber`, `p`.`observaciones` AS `observaciones` FROM (`pedidos` `p` join `clientes` `c` on((`c`.`id` = `p`.`cliente_id`))) WHERE (`p`.`deleted_at` is null)union all select `c`.`id` AS `cliente_id`,concat(`c`.`apellido`,', ',`c`.`nombre`) AS `cliente`,`pa`.`pedido_id` AS `pedido_id`,`pa`.`numero_recibo` AS `comprobante`,`pa`.`fecha` AS `fecha`,'PAGO' AS `tipo_movimiento`,0 AS `debe`,`pa`.`monto` AS `haber`,`pa`.`observaciones` AS `observaciones` from (`pagos` `pa` join `clientes` `c` on((`c`.`id` = `pa`.`cliente_id`))) where (`pa`.`deleted_at` is null) order by `cliente_id`,`fecha`
SQL,
        'v_garrafas_en_clientes' => <<<'SQL'
SELECT `g`.`id` AS `garrafa_id`, `g`.`codigo` AS `codigo`, `g`.`capacidad_kg` AS `capacidad_kg`, `g`.`cliente_id` AS `cliente_id`, concat(`c`.`apellido`,', ',`c`.`nombre`) AS `cliente`, `g`.`fecha_ultimo_movimiento` AS `fecha_ultimo_movimiento`, (to_days(curdate()) - to_days(`g`.`fecha_ultimo_movimiento`)) AS `dias_en_cliente` FROM ((`garrafas` `g` join `clientes` `c` on((`c`.`id` = `g`.`cliente_id`))) join `estados_garrafa` `eg` on((`eg`.`id` = `g`.`estado_garrafa_id`))) WHERE ((`eg`.`codigo` = 'EN_CLIENTE') AND (`g`.`deleted_at` is null) AND (`g`.`activo` = true) AND (`c`.`deleted_at` is null)) ORDER BY `c`.`apellido` ASC, `c`.`nombre` ASC, `g`.`capacidad_kg` ASC
SQL,
        'v_pagos_por_forma_pago' => <<<'SQL'
SELECT cast(`p`.`fecha` as date) AS `fecha`, `fp`.`codigo` AS `forma_pago_codigo`, `fp`.`nombre` AS `forma_pago_nombre`, count(`p`.`id`) AS `cantidad_pagos`, coalesce(sum(`p`.`monto`),0) AS `monto_total` FROM (`pagos` `p` join `formas_pago` `fp` on((`fp`.`id` = `p`.`forma_pago_id`))) WHERE (`p`.`deleted_at` is null) GROUP BY cast(`p`.`fecha` as date), `fp`.`codigo`, `fp`.`nombre`
SQL,
        'v_pedidos_resumen' => <<<'SQL'
SELECT `p`.`id` AS `id`, `p`.`numero` AS `numero`, `p`.`fecha` AS `fecha`, `p`.`fecha_entrega` AS `fecha_entrega`, `p`.`entregado` AS `entregado`, `p`.`cliente_id` AS `cliente_id`, concat(`c`.`apellido`,', ',`c`.`nombre`) AS `cliente`, `c`.`telefono_principal` AS `cliente_telefono`, `p`.`empleado_id` AS `empleado_id`, concat(`e`.`apellido`,', ',`e`.`nombre`) AS `empleado`, `p`.`estado_pedido_id` AS `estado_pedido_id`, `ep`.`codigo` AS `estado_codigo`, `ep`.`nombre` AS `estado_nombre`, `p`.`canal_venta_id` AS `canal_venta_id`, `cv`.`codigo` AS `canal_codigo`, `p`.`subtotal` AS `subtotal`, `p`.`descuento` AS `descuento`, `p`.`total` AS `total`, `p`.`monto_pagado` AS `monto_pagado`, `p`.`saldo` AS `saldo`, (case when (`p`.`saldo` <= 0) then 'PAGADO' when (`p`.`monto_pagado` > 0) then 'PARCIAL' else 'PENDIENTE' end) AS `estado_pago` FROM ((((`pedidos` `p` join `clientes` `c` on((`c`.`id` = `p`.`cliente_id`))) join `empleados` `e` on((`e`.`id` = `p`.`empleado_id`))) join `estados_pedido` `ep` on((`ep`.`id` = `p`.`estado_pedido_id`))) join `canales_venta` `cv` on((`cv`.`id` = `p`.`canal_venta_id`))) WHERE (`p`.`deleted_at` is null)
SQL,
        'v_productos_mas_vendidos' => <<<'SQL'
SELECT cast(`p`.`fecha` as date) AS `fecha`, `pi`.`producto_id` AS `producto_id`, `pr`.`codigo` AS `producto_codigo`, `pr`.`nombre` AS `producto_nombre`, `tp`.`nombre` AS `tipo_producto`, sum((case when (`pi`.`tipo_linea` = 'VENTA') then `pi`.`cantidad` else 0 end)) AS `cantidad_vendida`, sum((case when (`pi`.`tipo_linea` = 'ENTREGA') then `pi`.`cantidad` else 0 end)) AS `cantidad_entregada`, sum((case when (`pi`.`tipo_linea` = 'DEVOLUCION') then `pi`.`cantidad` else 0 end)) AS `cantidad_devuelta`, sum(`pi`.`subtotal`) AS `monto_total` FROM (((`pedido_items` `pi` join `pedidos` `p` on((`p`.`id` = `pi`.`pedido_id`))) join `productos` `pr` on((`pr`.`id` = `pi`.`producto_id`))) join `tipos_producto` `tp` on((`tp`.`id` = `pr`.`tipo_producto_id`))) WHERE (`p`.`deleted_at` is null) GROUP BY cast(`p`.`fecha` as date), `pi`.`producto_id`, `pr`.`codigo`, `pr`.`nombre`, `tp`.`nombre`
SQL,
        'v_recepciones_resumen' => <<<'SQL'
SELECT `r`.`id` AS `id`, `r`.`numero` AS `numero`, `r`.`fecha` AS `fecha`, `r`.`proveedor_id` AS `proveedor_id`, `pr`.`razon_social` AS `proveedor`, `pr`.`cuit` AS `proveedor_cuit`, `r`.`empleado_id` AS `empleado_id`, concat(`e`.`apellido`,', ',`e`.`nombre`) AS `empleado`, `r`.`numero_factura_proveedor` AS `numero_factura_proveedor`, `r`.`subtotal` AS `subtotal`, `r`.`descuento` AS `descuento`, `r`.`total` AS `total`, `r`.`monto_pagado` AS `monto_pagado`, `r`.`saldo` AS `saldo`, (case when (`r`.`saldo` <= 0) then 'PAGADO' when (`r`.`monto_pagado` > 0) then 'PARCIAL' else 'PENDIENTE' end) AS `estado_pago` FROM ((`recepciones_proveedor` `r` join `proveedores` `pr` on((`pr`.`id` = `r`.`proveedor_id`))) join `empleados` `e` on((`e`.`id` = `r`.`empleado_id`))) WHERE (`r`.`deleted_at` is null)
SQL,
        'v_regularidad_clientes' => <<<'SQL'
SELECT `c`.`id` AS `cliente_id`, concat(`c`.`apellido`,', ',`c`.`nombre`) AS `cliente`, count(`p`.`id`) AS `total_pedidos`, max(`p`.`fecha`) AS `ultimo_pedido`, min(`p`.`fecha`) AS `primer_pedido`, (case when (count(`p`.`id`) > 1) then ((to_days(max(`p`.`fecha`)) - to_days(min(`p`.`fecha`))) / (count(`p`.`id`) - 1)) else NULL end) AS `dias_promedio_entre_pedidos`, sum(`p`.`total`) AS `total_facturado`, sum(`p`.`saldo`) AS `saldo_pendiente` FROM (`clientes` `c` left join `pedidos` `p` on(((`p`.`cliente_id` = `c`.`id`) and (`p`.`deleted_at` is null)))) WHERE (`c`.`deleted_at` is null) GROUP BY `c`.`id`, `cliente`
SQL,
        'v_saldo_clientes' => <<<'SQL'
SELECT `c`.`id` AS `cliente_id`, concat(`c`.`apellido`,', ',`c`.`nombre`) AS `cliente`, `c`.`telefono_principal` AS `telefono_principal`, count(`p`.`id`) AS `pedidos_pendientes`, coalesce(sum(`p`.`saldo`),0) AS `saldo_total` FROM (`clientes` `c` left join `pedidos` `p` on(((`p`.`cliente_id` = `c`.`id`) and (`p`.`deleted_at` is null) and (`p`.`saldo` > 0)))) WHERE (`c`.`deleted_at` is null) GROUP BY `c`.`id`, `cliente`, `c`.`telefono_principal` HAVING (`saldo_total` > 0) ORDER BY `saldo_total` DESC
SQL,
        'v_saldo_proveedores' => <<<'SQL'
SELECT `pr`.`id` AS `proveedor_id`, `pr`.`razon_social` AS `razon_social`, `pr`.`cuit` AS `cuit`, count(`r`.`id`) AS `recepciones_pendientes`, coalesce(sum(`r`.`saldo`),0) AS `saldo_total` FROM (`proveedores` `pr` left join `recepciones_proveedor` `r` on(((`r`.`proveedor_id` = `pr`.`id`) and (`r`.`deleted_at` is null) and (`r`.`saldo` > 0)))) WHERE (`pr`.`deleted_at` is null) GROUP BY `pr`.`id`, `pr`.`razon_social`, `pr`.`cuit` HAVING (`saldo_total` > 0) ORDER BY `saldo_total` DESC
SQL,
        'v_stock_garrafas' => <<<'SQL'
SELECT `g`.`capacidad_kg` AS `capacidad_kg`, `g`.`estado_garrafa_id` AS `estado_garrafa_id`, `eg`.`codigo` AS `estado_codigo`, `eg`.`nombre` AS `estado_nombre`, `eg`.`color` AS `estado_color`, count(0) AS `cantidad` FROM (`garrafas` `g` join `estados_garrafa` `eg` on((`eg`.`id` = `g`.`estado_garrafa_id`))) WHERE ((`g`.`deleted_at` is null) AND (`g`.`activo` = true)) GROUP BY `g`.`capacidad_kg`, `g`.`estado_garrafa_id`, `eg`.`codigo`, `eg`.`nombre`, `eg`.`color` ORDER BY `g`.`capacidad_kg` ASC, `eg`.`nombre` ASC
SQL,
    ];

    public function up(): void
    {
        foreach ($this->vistas as $nombre => $sql) {
            DB::statement("CREATE OR REPLACE VIEW `{$nombre}` AS {$sql}");
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->vistas) as $nombre) {
            DB::statement("DROP VIEW IF EXISTS `{$nombre}`");
        }
    }
};

-- =====================================================================
--  ExtraGas · Sistema de Gestión de Pedidos (PHP MVC)
--  Base de datos para MySQL 5.7 / 8.x y MariaDB 10.6+
--  Sin registros de ejemplo (catálogos, productos, empresa y usuario admin)
--  Generado: 25/09/2026
--
--  Importar con phpMyAdmin (pestaña Importar) o por consola:
--      mysql -u root -p < vacio.sql
--  El script crea la base "extragas" si no existe.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

CREATE DATABASE IF NOT EXISTS `extragas` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `extragas`;

-- ---------------------------------------------------------------------
--  1. Tablas y vistas
-- ---------------------------------------------------------------------
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `canales_venta` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `canales_venta_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cliente_contactos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint(20) unsigned NOT NULL,
  `tipo_contacto_id` bigint(20) unsigned NOT NULL,
  `valor` varchar(150) NOT NULL,
  `es_principal` tinyint(1) NOT NULL DEFAULT 0,
  `observaciones` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cliente_contactos_cliente_id_foreign` (`cliente_id`),
  KEY `cliente_contactos_tipo_contacto_id_foreign` (`tipo_contacto_id`),
  CONSTRAINT `cliente_contactos_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cliente_contactos_tipo_contacto_id_foreign` FOREIGN KEY (`tipo_contacto_id`) REFERENCES `tipos_contacto_cliente` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `dni` varchar(15) DEFAULT NULL,
  `cuit_cuil` varchar(15) DEFAULT NULL,
  `telefono_principal` varchar(25) NOT NULL,
  `telefono_secundario` varchar(25) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `calle` varchar(150) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `piso` varchar(10) DEFAULT NULL,
  `depto` varchar(10) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `provincia_id` bigint(20) unsigned DEFAULT NULL,
  `forma_pago_habitual_id` bigint(20) unsigned DEFAULT NULL,
  `referencias` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_alta` date NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clientes_provincia_id_foreign` (`provincia_id`),
  KEY `clientes_created_by_foreign` (`created_by`),
  KEY `clientes_updated_by_foreign` (`updated_by`),
  KEY `clientes_apellido_nombre_index` (`apellido`,`nombre`),
  KEY `clientes_codigo_index` (`codigo`),
  KEY `clientes_dni_index` (`dni`),
  KEY `clientes_telefono_principal_index` (`telefono_principal`),
  KEY `clientes_deleted_at_index` (`deleted_at`),
  KEY `clientes_forma_pago_habitual_id_foreign` (`forma_pago_habitual_id`),
  CONSTRAINT `clientes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `clientes_forma_pago_habitual_id_foreign` FOREIGN KEY (`forma_pago_habitual_id`) REFERENCES `formas_pago` (`id`),
  CONSTRAINT `clientes_provincia_id_foreign` FOREIGN KEY (`provincia_id`) REFERENCES `provincias` (`id`),
  CONSTRAINT `clientes_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracion_empresa` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `razon_social` varchar(150) DEFAULT NULL,
  `cuit` varchar(15) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `localidad` varchar(100) DEFAULT NULL,
  `telefono` varchar(25) DEFAULT NULL,
  `whatsapp` varchar(25) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `horario` varchar(150) DEFAULT NULL,
  `dias_tolerancia_regularidad` smallint(5) unsigned NOT NULL DEFAULT 3,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleados` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `dni` varchar(15) DEFAULT NULL,
  `cuil` varchar(15) DEFAULT NULL,
  `telefono` varchar(25) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `calle` varchar(150) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `piso` varchar(10) DEFAULT NULL,
  `depto` varchar(10) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `provincia_id` bigint(20) unsigned DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `observaciones` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `empleados_dni_unique` (`dni`),
  KEY `empleados_provincia_id_foreign` (`provincia_id`),
  KEY `empleados_usuario_id_foreign` (`usuario_id`),
  KEY `empleados_created_by_foreign` (`created_by`),
  KEY `empleados_updated_by_foreign` (`updated_by`),
  KEY `empleados_apellido_nombre_index` (`apellido`,`nombre`),
  KEY `empleados_deleted_at_index` (`deleted_at`),
  CONSTRAINT `empleados_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `empleados_provincia_id_foreign` FOREIGN KEY (`provincia_id`) REFERENCES `provincias` (`id`),
  CONSTRAINT `empleados_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `empleados_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `estados_garrafa` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `es_disponible_para_venta` tinyint(1) NOT NULL DEFAULT 0,
  `requiere_cliente` tinyint(1) NOT NULL DEFAULT 0,
  `color` varchar(7) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `estados_garrafa_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `estados_pedido` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `es_final` tinyint(1) NOT NULL DEFAULT 0,
  `color` varchar(7) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `estados_pedido_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `formas_pago` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `requiere_referencia` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `formas_pago_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `garrafas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `capacidad_kg` tinyint(3) unsigned NOT NULL,
  `proveedor_id` bigint(20) unsigned DEFAULT NULL,
  `recepcion_id` bigint(20) unsigned DEFAULT NULL,
  `fecha_compra` date NOT NULL,
  `estado_garrafa_id` bigint(20) unsigned NOT NULL,
  `cliente_id` bigint(20) unsigned DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_ultimo_movimiento` datetime DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `garrafas_codigo_unique` (`codigo`),
  KEY `garrafas_proveedor_id_foreign` (`proveedor_id`),
  KEY `garrafas_recepcion_id_foreign` (`recepcion_id`),
  KEY `garrafas_estado_garrafa_id_foreign` (`estado_garrafa_id`),
  KEY `garrafas_cliente_id_foreign` (`cliente_id`),
  KEY `garrafas_created_by_foreign` (`created_by`),
  KEY `garrafas_updated_by_foreign` (`updated_by`),
  KEY `garrafas_capacidad_kg_index` (`capacidad_kg`),
  KEY `garrafas_deleted_at_index` (`deleted_at`),
  CONSTRAINT `garrafas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `garrafas_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `garrafas_estado_garrafa_id_foreign` FOREIGN KEY (`estado_garrafa_id`) REFERENCES `estados_garrafa` (`id`),
  CONSTRAINT `garrafas_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  CONSTRAINT `garrafas_recepcion_id_foreign` FOREIGN KEY (`recepcion_id`) REFERENCES `recepciones_proveedor` (`id`),
  CONSTRAINT `garrafas_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `medios_contacto_pedido` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `medios_contacto_pedido_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimientos_garrafa` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `garrafa_id` bigint(20) unsigned NOT NULL,
  `fecha` datetime NOT NULL,
  `tipo_movimiento_id` bigint(20) unsigned NOT NULL,
  `pedido_id` bigint(20) unsigned DEFAULT NULL,
  `recepcion_id` bigint(20) unsigned DEFAULT NULL,
  `cliente_id` bigint(20) unsigned DEFAULT NULL,
  `estado_origen_id` bigint(20) unsigned DEFAULT NULL,
  `estado_destino_id` bigint(20) unsigned NOT NULL,
  `empleado_id` bigint(20) unsigned DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `movimientos_garrafa_tipo_movimiento_id_foreign` (`tipo_movimiento_id`),
  KEY `movimientos_garrafa_pedido_id_foreign` (`pedido_id`),
  KEY `movimientos_garrafa_recepcion_id_foreign` (`recepcion_id`),
  KEY `movimientos_garrafa_cliente_id_foreign` (`cliente_id`),
  KEY `movimientos_garrafa_estado_origen_id_foreign` (`estado_origen_id`),
  KEY `movimientos_garrafa_estado_destino_id_foreign` (`estado_destino_id`),
  KEY `movimientos_garrafa_empleado_id_foreign` (`empleado_id`),
  KEY `movimientos_garrafa_created_by_foreign` (`created_by`),
  KEY `movimientos_garrafa_garrafa_id_fecha_index` (`garrafa_id`,`fecha`),
  KEY `movimientos_garrafa_fecha_index` (`fecha`),
  CONSTRAINT `movimientos_garrafa_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `movimientos_garrafa_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `movimientos_garrafa_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`),
  CONSTRAINT `movimientos_garrafa_estado_destino_id_foreign` FOREIGN KEY (`estado_destino_id`) REFERENCES `estados_garrafa` (`id`),
  CONSTRAINT `movimientos_garrafa_estado_origen_id_foreign` FOREIGN KEY (`estado_origen_id`) REFERENCES `estados_garrafa` (`id`),
  CONSTRAINT `movimientos_garrafa_garrafa_id_foreign` FOREIGN KEY (`garrafa_id`) REFERENCES `garrafas` (`id`),
  CONSTRAINT `movimientos_garrafa_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  CONSTRAINT `movimientos_garrafa_recepcion_id_foreign` FOREIGN KEY (`recepcion_id`) REFERENCES `recepciones_proveedor` (`id`),
  CONSTRAINT `movimientos_garrafa_tipo_movimiento_id_foreign` FOREIGN KEY (`tipo_movimiento_id`) REFERENCES `tipos_movimiento_garrafa` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `numero_recibo` varchar(20) DEFAULT NULL,
  `fecha` datetime NOT NULL,
  `cliente_id` bigint(20) unsigned NOT NULL,
  `pedido_id` bigint(20) unsigned DEFAULT NULL,
  `forma_pago_id` bigint(20) unsigned NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `observaciones` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pagos_pedido_id_foreign` (`pedido_id`),
  KEY `pagos_forma_pago_id_foreign` (`forma_pago_id`),
  KEY `pagos_created_by_foreign` (`created_by`),
  KEY `pagos_updated_by_foreign` (`updated_by`),
  KEY `pagos_cliente_id_fecha_index` (`cliente_id`,`fecha`),
  KEY `pagos_numero_recibo_index` (`numero_recibo`),
  KEY `pagos_fecha_index` (`fecha`),
  KEY `pagos_deleted_at_index` (`deleted_at`),
  CONSTRAINT `pagos_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `pagos_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `pagos_forma_pago_id_foreign` FOREIGN KEY (`forma_pago_id`) REFERENCES `formas_pago` (`id`),
  CONSTRAINT `pagos_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  CONSTRAINT `pagos_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos_proveedor` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) DEFAULT NULL,
  `fecha` datetime NOT NULL,
  `proveedor_id` bigint(20) unsigned NOT NULL,
  `recepcion_id` bigint(20) unsigned DEFAULT NULL,
  `forma_pago_id` bigint(20) unsigned NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `observaciones` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pagos_proveedor_proveedor_id_foreign` (`proveedor_id`),
  KEY `pagos_proveedor_recepcion_id_foreign` (`recepcion_id`),
  KEY `pagos_proveedor_forma_pago_id_foreign` (`forma_pago_id`),
  KEY `pagos_proveedor_created_by_foreign` (`created_by`),
  KEY `pagos_proveedor_updated_by_foreign` (`updated_by`),
  KEY `pagos_proveedor_numero_index` (`numero`),
  KEY `pagos_proveedor_fecha_index` (`fecha`),
  KEY `pagos_proveedor_deleted_at_index` (`deleted_at`),
  CONSTRAINT `pagos_proveedor_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `pagos_proveedor_forma_pago_id_foreign` FOREIGN KEY (`forma_pago_id`) REFERENCES `formas_pago` (`id`),
  CONSTRAINT `pagos_proveedor_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  CONSTRAINT `pagos_proveedor_recepcion_id_foreign` FOREIGN KEY (`recepcion_id`) REFERENCES `recepciones_proveedor` (`id`),
  CONSTRAINT `pagos_proveedor_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedido_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint(20) unsigned NOT NULL,
  `producto_id` bigint(20) unsigned NOT NULL,
  `tipo_linea` enum('ENTREGA','DEVOLUCION','VENTA') NOT NULL DEFAULT 'VENTA',
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) GENERATED ALWAYS AS (`cantidad` * `precio_unitario`) STORED,
  `observaciones` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pedido_items_pedido_id_foreign` (`pedido_id`),
  KEY `pedido_items_producto_id_foreign` (`producto_id`),
  KEY `pedido_items_tipo_linea_index` (`tipo_linea`),
  CONSTRAINT `pedido_items_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pedido_items_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedidos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) DEFAULT NULL,
  `fecha` datetime NOT NULL,
  `fecha_entrega` datetime DEFAULT NULL,
  `entregado` tinyint(1) NOT NULL DEFAULT 0,
  `cliente_id` bigint(20) unsigned NOT NULL,
  `empleado_id` bigint(20) unsigned NOT NULL,
  `estado_pedido_id` bigint(20) unsigned NOT NULL,
  `canal_venta_id` bigint(20) unsigned NOT NULL,
  `medio_contacto_id` bigint(20) unsigned DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `monto_pagado` decimal(12,2) NOT NULL DEFAULT 0.00,
  `saldo` decimal(12,2) GENERATED ALWAYS AS (`total` - `monto_pagado`) STORED,
  `observaciones` text DEFAULT NULL,
  `direccion_entrega` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedidos_estado_pedido_id_foreign` (`estado_pedido_id`),
  KEY `pedidos_canal_venta_id_foreign` (`canal_venta_id`),
  KEY `pedidos_medio_contacto_id_foreign` (`medio_contacto_id`),
  KEY `pedidos_created_by_foreign` (`created_by`),
  KEY `pedidos_updated_by_foreign` (`updated_by`),
  KEY `pedidos_cliente_id_fecha_index` (`cliente_id`,`fecha`),
  KEY `pedidos_empleado_id_fecha_index` (`empleado_id`,`fecha`),
  KEY `pedidos_numero_index` (`numero`),
  KEY `pedidos_fecha_index` (`fecha`),
  KEY `pedidos_deleted_at_index` (`deleted_at`),
  CONSTRAINT `pedidos_canal_venta_id_foreign` FOREIGN KEY (`canal_venta_id`) REFERENCES `canales_venta` (`id`),
  CONSTRAINT `pedidos_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `pedidos_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `pedidos_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`),
  CONSTRAINT `pedidos_estado_pedido_id_foreign` FOREIGN KEY (`estado_pedido_id`) REFERENCES `estados_pedido` (`id`),
  CONSTRAINT `pedidos_medio_contacto_id_foreign` FOREIGN KEY (`medio_contacto_id`) REFERENCES `medios_contacto_pedido` (`id`),
  CONSTRAINT `pedidos_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `tipo_producto_id` bigint(20) unsigned NOT NULL,
  `capacidad_kg` decimal(8,2) DEFAULT NULL,
  `unidad_venta` varchar(20) NOT NULL DEFAULT 'UNIDAD',
  `precio_actual` decimal(12,2) NOT NULL DEFAULT 0.00,
  `costo_actual` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock_actual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock_minimo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `maneja_garrafa_individual` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `productos_codigo_unique` (`codigo`),
  KEY `productos_tipo_producto_id_foreign` (`tipo_producto_id`),
  KEY `productos_created_by_foreign` (`created_by`),
  KEY `productos_updated_by_foreign` (`updated_by`),
  KEY `productos_codigo_nombre_index` (`codigo`,`nombre`),
  KEY `productos_deleted_at_index` (`deleted_at`),
  CONSTRAINT `productos_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `productos_tipo_producto_id_foreign` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipos_producto` (`id`),
  CONSTRAINT `productos_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedores` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) DEFAULT NULL,
  `razon_social` varchar(150) NOT NULL,
  `nombre_fantasia` varchar(150) DEFAULT NULL,
  `cuit` varchar(15) NOT NULL,
  `telefono_principal` varchar(25) DEFAULT NULL,
  `telefono_secundario` varchar(25) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `calle` varchar(150) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `piso` varchar(10) DEFAULT NULL,
  `depto` varchar(10) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `provincia_id` bigint(20) unsigned DEFAULT NULL,
  `referencias` text DEFAULT NULL,
  `contacto_nombre` varchar(150) DEFAULT NULL,
  `contacto_telefono` varchar(25) DEFAULT NULL,
  `contacto_email` varchar(150) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `proveedores_cuit_unique` (`cuit`),
  KEY `proveedores_provincia_id_foreign` (`provincia_id`),
  KEY `proveedores_created_by_foreign` (`created_by`),
  KEY `proveedores_updated_by_foreign` (`updated_by`),
  KEY `proveedores_codigo_index` (`codigo`),
  KEY `proveedores_razon_social_index` (`razon_social`),
  KEY `proveedores_deleted_at_index` (`deleted_at`),
  CONSTRAINT `proveedores_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `proveedores_provincia_id_foreign` FOREIGN KEY (`provincia_id`) REFERENCES `provincias` (`id`),
  CONSTRAINT `proveedores_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `provincias` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(4) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `pais` varchar(100) NOT NULL DEFAULT 'Argentina',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `provincias_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `recepcion_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `recepcion_id` bigint(20) unsigned NOT NULL,
  `producto_id` bigint(20) unsigned NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) GENERATED ALWAYS AS (`cantidad` * `precio_unitario`) STORED,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `recepcion_items_recepcion_id_foreign` (`recepcion_id`),
  KEY `recepcion_items_producto_id_foreign` (`producto_id`),
  CONSTRAINT `recepcion_items_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `recepcion_items_recepcion_id_foreign` FOREIGN KEY (`recepcion_id`) REFERENCES `recepciones_proveedor` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `recepciones_proveedor` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) DEFAULT NULL,
  `fecha` datetime NOT NULL,
  `proveedor_id` bigint(20) unsigned NOT NULL,
  `empleado_id` bigint(20) unsigned NOT NULL,
  `numero_factura_proveedor` varchar(50) DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `monto_pagado` decimal(12,2) NOT NULL DEFAULT 0.00,
  `saldo` decimal(12,2) GENERATED ALWAYS AS (`total` - `monto_pagado`) STORED,
  `observaciones` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recepciones_proveedor_empleado_id_foreign` (`empleado_id`),
  KEY `recepciones_proveedor_created_by_foreign` (`created_by`),
  KEY `recepciones_proveedor_updated_by_foreign` (`updated_by`),
  KEY `recepciones_proveedor_proveedor_id_fecha_index` (`proveedor_id`,`fecha`),
  KEY `recepciones_proveedor_numero_index` (`numero`),
  KEY `recepciones_proveedor_fecha_index` (`fecha`),
  KEY `recepciones_proveedor_deleted_at_index` (`deleted_at`),
  CONSTRAINT `recepciones_proveedor_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `recepciones_proveedor_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`),
  CONSTRAINT `recepciones_proveedor_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  CONSTRAINT `recepciones_proveedor_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `secuencias` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `prefijo` varchar(20) NOT NULL,
  `anio` smallint(5) unsigned NOT NULL,
  `ultimo_valor` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_secuencias_nombre_anio` (`nombre`,`anio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_contacto_cliente` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipos_contacto_cliente_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_movimiento_garrafa` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipos_movimiento_garrafa_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_producto` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipos_producto_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `rol_id` bigint(20) unsigned NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuarios_username_unique` (`username`),
  KEY `usuarios_rol_id_foreign` (`rol_id`),
  KEY `usuarios_deleted_at_index` (`deleted_at`),
  KEY `usuarios_created_by_foreign` (`created_by`),
  KEY `usuarios_updated_by_foreign` (`updated_by`),
  CONSTRAINT `usuarios_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `usuarios_rol_id_foreign` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `usuarios_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_cuenta_corriente_cliente` AS SELECT
 1 AS `cliente_id`,
  1 AS `cliente`,
  1 AS `pedido_id`,
  1 AS `comprobante`,
  1 AS `fecha`,
  1 AS `tipo_movimiento`,
  1 AS `debe`,
  1 AS `haber`,
  1 AS `observaciones` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_garrafas_en_clientes` AS SELECT
 1 AS `garrafa_id`,
  1 AS `codigo`,
  1 AS `capacidad_kg`,
  1 AS `cliente_id`,
  1 AS `cliente`,
  1 AS `fecha_ultimo_movimiento`,
  1 AS `dias_en_cliente` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_pagos_por_forma_pago` AS SELECT
 1 AS `fecha`,
  1 AS `forma_pago_codigo`,
  1 AS `forma_pago_nombre`,
  1 AS `cantidad_pagos`,
  1 AS `monto_total` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_pedidos_resumen` AS SELECT
 1 AS `id`,
  1 AS `numero`,
  1 AS `fecha`,
  1 AS `fecha_entrega`,
  1 AS `entregado`,
  1 AS `cliente_id`,
  1 AS `cliente`,
  1 AS `cliente_telefono`,
  1 AS `empleado_id`,
  1 AS `empleado`,
  1 AS `estado_pedido_id`,
  1 AS `estado_codigo`,
  1 AS `estado_nombre`,
  1 AS `canal_venta_id`,
  1 AS `canal_codigo`,
  1 AS `subtotal`,
  1 AS `descuento`,
  1 AS `total`,
  1 AS `monto_pagado`,
  1 AS `saldo`,
  1 AS `estado_pago` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_productos_mas_vendidos` AS SELECT
 1 AS `fecha`,
  1 AS `producto_id`,
  1 AS `producto_codigo`,
  1 AS `producto_nombre`,
  1 AS `tipo_producto`,
  1 AS `cantidad_vendida`,
  1 AS `cantidad_entregada`,
  1 AS `cantidad_devuelta`,
  1 AS `monto_total` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_recepciones_resumen` AS SELECT
 1 AS `id`,
  1 AS `numero`,
  1 AS `fecha`,
  1 AS `proveedor_id`,
  1 AS `proveedor`,
  1 AS `proveedor_cuit`,
  1 AS `empleado_id`,
  1 AS `empleado`,
  1 AS `numero_factura_proveedor`,
  1 AS `subtotal`,
  1 AS `descuento`,
  1 AS `total`,
  1 AS `monto_pagado`,
  1 AS `saldo`,
  1 AS `estado_pago` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_regularidad_clientes` AS SELECT
 1 AS `cliente_id`,
  1 AS `cliente`,
  1 AS `total_pedidos`,
  1 AS `ultimo_pedido`,
  1 AS `primer_pedido`,
  1 AS `dias_promedio_entre_pedidos`,
  1 AS `total_facturado`,
  1 AS `saldo_pendiente` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_saldo_clientes` AS SELECT
 1 AS `cliente_id`,
  1 AS `cliente`,
  1 AS `telefono_principal`,
  1 AS `pedidos_pendientes`,
  1 AS `saldo_total` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_saldo_proveedores` AS SELECT
 1 AS `proveedor_id`,
  1 AS `razon_social`,
  1 AS `cuit`,
  1 AS `recepciones_pendientes`,
  1 AS `saldo_total` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_stock_garrafas` AS SELECT
 1 AS `capacidad_kg`,
  1 AS `estado_garrafa_id`,
  1 AS `estado_codigo`,
  1 AS `estado_nombre`,
  1 AS `estado_color`,
  1 AS `cantidad` */;
SET character_set_client = @saved_cs_client;
/*!50001 DROP VIEW IF EXISTS `v_cuenta_corriente_cliente`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013   */
/*!50001 VIEW `v_cuenta_corriente_cliente` AS select `c`.`id` AS `cliente_id`,concat(`c`.`apellido`,', ',`c`.`nombre`) AS `cliente`,`p`.`id` AS `pedido_id`,`p`.`numero` AS `comprobante`,`p`.`fecha` AS `fecha`,'PEDIDO' AS `tipo_movimiento`,`p`.`total` AS `debe`,0 AS `haber`,`p`.`observaciones` AS `observaciones` from (`pedidos` `p` join `clientes` `c` on(`c`.`id` = `p`.`cliente_id`)) where `p`.`deleted_at` is null and `p`.`estado_pedido_id` <> (select `estados_pedido`.`id` from `estados_pedido` where `estados_pedido`.`codigo` = 'CANCELADO') union all select `c`.`id` AS `id`,concat(`c`.`apellido`,', ',`c`.`nombre`) AS `CONCAT(c.apellido, ', ', c.nombre)`,`pa`.`pedido_id` AS `pedido_id`,`pa`.`numero_recibo` AS `numero_recibo`,`pa`.`fecha` AS `fecha`,'PAGO' AS `PAGO`,0 AS `0`,`pa`.`monto` AS `monto`,`pa`.`observaciones` AS `observaciones` from (`pagos` `pa` join `clientes` `c` on(`c`.`id` = `pa`.`cliente_id`)) where `pa`.`deleted_at` is null order by `cliente_id`,`fecha` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_garrafas_en_clientes`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013   */
/*!50001 VIEW `v_garrafas_en_clientes` AS select `g`.`id` AS `garrafa_id`,`g`.`codigo` AS `codigo`,`g`.`capacidad_kg` AS `capacidad_kg`,`g`.`cliente_id` AS `cliente_id`,concat(`c`.`apellido`,', ',`c`.`nombre`) AS `cliente`,`g`.`fecha_ultimo_movimiento` AS `fecha_ultimo_movimiento`,to_days(curdate()) - to_days(`g`.`fecha_ultimo_movimiento`) AS `dias_en_cliente` from ((`garrafas` `g` join `clientes` `c` on(`c`.`id` = `g`.`cliente_id`)) join `estados_garrafa` `eg` on(`eg`.`id` = `g`.`estado_garrafa_id`)) where `eg`.`codigo` = 'EN_CLIENTE' and `g`.`deleted_at` is null and `g`.`activo` = 1 and `c`.`deleted_at` is null order by `c`.`apellido`,`c`.`nombre`,`g`.`capacidad_kg` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_pagos_por_forma_pago`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013   */
/*!50001 VIEW `v_pagos_por_forma_pago` AS select cast(`p`.`fecha` as date) AS `fecha`,`fp`.`codigo` AS `forma_pago_codigo`,`fp`.`nombre` AS `forma_pago_nombre`,count(`p`.`id`) AS `cantidad_pagos`,coalesce(sum(`p`.`monto`),0) AS `monto_total` from (`pagos` `p` join `formas_pago` `fp` on(`fp`.`id` = `p`.`forma_pago_id`)) where `p`.`deleted_at` is null group by cast(`p`.`fecha` as date),`fp`.`codigo`,`fp`.`nombre` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_pedidos_resumen`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013   */
/*!50001 VIEW `v_pedidos_resumen` AS select `p`.`id` AS `id`,`p`.`numero` AS `numero`,`p`.`fecha` AS `fecha`,`p`.`fecha_entrega` AS `fecha_entrega`,`p`.`entregado` AS `entregado`,`p`.`cliente_id` AS `cliente_id`,concat(`c`.`apellido`,', ',`c`.`nombre`) AS `cliente`,`c`.`telefono_principal` AS `cliente_telefono`,`p`.`empleado_id` AS `empleado_id`,concat(`e`.`apellido`,', ',`e`.`nombre`) AS `empleado`,`p`.`estado_pedido_id` AS `estado_pedido_id`,`ep`.`codigo` AS `estado_codigo`,`ep`.`nombre` AS `estado_nombre`,`p`.`canal_venta_id` AS `canal_venta_id`,`cv`.`codigo` AS `canal_codigo`,`p`.`subtotal` AS `subtotal`,`p`.`descuento` AS `descuento`,`p`.`total` AS `total`,`p`.`monto_pagado` AS `monto_pagado`,`p`.`saldo` AS `saldo`,case when `p`.`saldo` <= 0 then 'PAGADO' when `p`.`monto_pagado` > 0 then 'PARCIAL' else 'PENDIENTE' end AS `estado_pago` from ((((`pedidos` `p` join `clientes` `c` on(`c`.`id` = `p`.`cliente_id`)) join `empleados` `e` on(`e`.`id` = `p`.`empleado_id`)) join `estados_pedido` `ep` on(`ep`.`id` = `p`.`estado_pedido_id`)) join `canales_venta` `cv` on(`cv`.`id` = `p`.`canal_venta_id`)) where `p`.`deleted_at` is null */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_productos_mas_vendidos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013   */
/*!50001 VIEW `v_productos_mas_vendidos` AS select cast(`p`.`fecha` as date) AS `fecha`,`pi`.`producto_id` AS `producto_id`,`pr`.`codigo` AS `producto_codigo`,`pr`.`nombre` AS `producto_nombre`,`tp`.`nombre` AS `tipo_producto`,sum(case when `pi`.`tipo_linea` = 'VENTA' then `pi`.`cantidad` else 0 end) AS `cantidad_vendida`,sum(case when `pi`.`tipo_linea` = 'ENTREGA' then `pi`.`cantidad` else 0 end) AS `cantidad_entregada`,sum(case when `pi`.`tipo_linea` = 'DEVOLUCION' then `pi`.`cantidad` else 0 end) AS `cantidad_devuelta`,sum(`pi`.`subtotal`) AS `monto_total` from (((`pedido_items` `pi` join `pedidos` `p` on(`p`.`id` = `pi`.`pedido_id`)) join `productos` `pr` on(`pr`.`id` = `pi`.`producto_id`)) join `tipos_producto` `tp` on(`tp`.`id` = `pr`.`tipo_producto_id`)) where `p`.`deleted_at` is null and `p`.`estado_pedido_id` <> (select `estados_pedido`.`id` from `estados_pedido` where `estados_pedido`.`codigo` = 'CANCELADO') group by cast(`p`.`fecha` as date),`pi`.`producto_id`,`pr`.`codigo`,`pr`.`nombre`,`tp`.`nombre` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_recepciones_resumen`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013   */
/*!50001 VIEW `v_recepciones_resumen` AS select `r`.`id` AS `id`,`r`.`numero` AS `numero`,`r`.`fecha` AS `fecha`,`r`.`proveedor_id` AS `proveedor_id`,`pr`.`razon_social` AS `proveedor`,`pr`.`cuit` AS `proveedor_cuit`,`r`.`empleado_id` AS `empleado_id`,concat(`e`.`apellido`,', ',`e`.`nombre`) AS `empleado`,`r`.`numero_factura_proveedor` AS `numero_factura_proveedor`,`r`.`subtotal` AS `subtotal`,`r`.`descuento` AS `descuento`,`r`.`total` AS `total`,`r`.`monto_pagado` AS `monto_pagado`,`r`.`saldo` AS `saldo`,case when `r`.`saldo` <= 0 then 'PAGADO' when `r`.`monto_pagado` > 0 then 'PARCIAL' else 'PENDIENTE' end AS `estado_pago` from ((`recepciones_proveedor` `r` join `proveedores` `pr` on(`pr`.`id` = `r`.`proveedor_id`)) join `empleados` `e` on(`e`.`id` = `r`.`empleado_id`)) where `r`.`deleted_at` is null */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_regularidad_clientes`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013   */
/*!50001 VIEW `v_regularidad_clientes` AS select `c`.`id` AS `cliente_id`,concat(`c`.`apellido`,', ',`c`.`nombre`) AS `cliente`,count(`p`.`id`) AS `total_pedidos`,max(`p`.`fecha`) AS `ultimo_pedido`,min(`p`.`fecha`) AS `primer_pedido`,case when count(`p`.`id`) > 1 then (to_days(max(`p`.`fecha`)) - to_days(min(`p`.`fecha`))) / (count(`p`.`id`) - 1) end AS `dias_promedio_entre_pedidos`,sum(`p`.`total`) AS `total_facturado`,sum(`p`.`saldo`) AS `saldo_pendiente` from (`clientes` `c` left join `pedidos` `p` on(`p`.`cliente_id` = `c`.`id` and `p`.`deleted_at` is null and `p`.`estado_pedido_id` <> (select `estados_pedido`.`id` from `estados_pedido` where `estados_pedido`.`codigo` = 'CANCELADO'))) where `c`.`deleted_at` is null group by `c`.`id`,concat(`c`.`apellido`,', ',`c`.`nombre`) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_saldo_clientes`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013   */
/*!50001 VIEW `v_saldo_clientes` AS select `c`.`id` AS `cliente_id`,concat(`c`.`apellido`,', ',`c`.`nombre`) AS `cliente`,`c`.`telefono_principal` AS `telefono_principal`,count(`p`.`id`) AS `pedidos_pendientes`,coalesce(sum(`p`.`saldo`),0) AS `saldo_total` from (`clientes` `c` left join `pedidos` `p` on(`p`.`cliente_id` = `c`.`id` and `p`.`deleted_at` is null and `p`.`saldo` > 0 and `p`.`estado_pedido_id` <> (select `estados_pedido`.`id` from `estados_pedido` where `estados_pedido`.`codigo` = 'CANCELADO'))) where `c`.`deleted_at` is null group by `c`.`id`,concat(`c`.`apellido`,', ',`c`.`nombre`),`c`.`telefono_principal` having `saldo_total` > 0 order by coalesce(sum(`p`.`saldo`),0) desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_saldo_proveedores`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013   */
/*!50001 VIEW `v_saldo_proveedores` AS select `pr`.`id` AS `proveedor_id`,`pr`.`razon_social` AS `razon_social`,`pr`.`cuit` AS `cuit`,count(`r`.`id`) AS `recepciones_pendientes`,coalesce(sum(`r`.`saldo`),0) AS `saldo_total` from (`proveedores` `pr` left join `recepciones_proveedor` `r` on(`r`.`proveedor_id` = `pr`.`id` and `r`.`deleted_at` is null and `r`.`saldo` > 0)) where `pr`.`deleted_at` is null group by `pr`.`id`,`pr`.`razon_social`,`pr`.`cuit` having `saldo_total` > 0 order by coalesce(sum(`r`.`saldo`),0) desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_stock_garrafas`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013   */
/*!50001 VIEW `v_stock_garrafas` AS select `g`.`capacidad_kg` AS `capacidad_kg`,`g`.`estado_garrafa_id` AS `estado_garrafa_id`,`eg`.`codigo` AS `estado_codigo`,`eg`.`nombre` AS `estado_nombre`,`eg`.`color` AS `estado_color`,count(0) AS `cantidad` from (`garrafas` `g` join `estados_garrafa` `eg` on(`eg`.`id` = `g`.`estado_garrafa_id`)) where `g`.`deleted_at` is null and `g`.`activo` = 1 group by `g`.`capacidad_kg`,`g`.`estado_garrafa_id`,`eg`.`codigo`,`eg`.`nombre`,`eg`.`color` order by `g`.`capacidad_kg`,`eg`.`nombre` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

-- ---------------------------------------------------------------------
--  2. Datos (las columnas calculadas 'saldo' y 'subtotal' las genera MySQL)
-- ---------------------------------------------------------------------

-- Datos de `canales_venta`
INSERT INTO `canales_venta` (`id`, `codigo`, `nombre`, `descripcion`, `created_at`, `updated_at`) VALUES
('1', 'DOMICILIO', 'Envío a domicilio', 'Se entrega en el domicilio del cliente', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('2', 'RETIRO_LOCAL', 'Retira en el local', 'El cliente pasa a retirar el pedido', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('3', 'MOSTRADOR', 'Venta en mostrador', 'Venta y entrega inmediata en el local', '2026-09-25 05:34:05', '2026-09-25 05:34:05');

-- Datos de `configuracion_empresa`
INSERT INTO `configuracion_empresa` (`id`, `nombre`, `razon_social`, `cuit`, `direccion`, `localidad`, `telefono`, `whatsapp`, `email`, `horario`, `dias_tolerancia_regularidad`, `created_at`, `updated_at`) VALUES
('1', 'ExtraGas', 'ExtraGas — Venta de gas envasado, carbón y leña', '20-28456123-7', 'Av. Belgrano 1450', 'San Miguel de Tucumán', '381 421-5566', '381 555-1020', 'contacto@extragas.com.ar', 'Lun a Sáb de 8 a 20 hs', '3', '2026-09-25 02:34:05', '2026-09-25 02:34:05');

-- Datos de `empleados`
INSERT INTO `empleados` (`id`, `nombre`, `apellido`, `dni`, `cuil`, `telefono`, `email`, `calle`, `numero`, `piso`, `depto`, `ciudad`, `codigo_postal`, `provincia_id`, `fecha_ingreso`, `usuario_id`, `activo`, `observaciones`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES
('1', 'Roberto', 'Medina', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2015-03-01', '1', '1', NULL, '2026-09-25 02:34:05', '2026-09-25 02:34:05', NULL, NULL, NULL);

-- Datos de `estados_garrafa`
INSERT INTO `estados_garrafa` (`id`, `codigo`, `nombre`, `descripcion`, `es_disponible_para_venta`, `requiere_cliente`, `color`, `created_at`, `updated_at`) VALUES
('1', 'LLENA', 'Llena', 'Llena en depósito, lista para la venta', '1', '0', '#40c057', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('2', 'VACIA', 'Vacía apta', 'Vacía en depósito, apta para intercambio', '0', '0', '#4dabf7', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('3', 'EN_CLIENTE', 'En cliente', 'En poder de un cliente', '0', '1', '#9775fa', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('4', 'NO_APTA', 'No apta', 'Dañada o con prueba hidráulica vencida', '0', '0', '#fa5252', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('5', 'EN_PROVEEDOR', 'Entregada al proveedor', 'Entregada vacía al proveedor en un intercambio', '0', '0', '#adb5bd', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('6', 'BAJA', 'Baja', 'Descartada definitivamente', '0', '0', '#495057', '2026-09-25 05:34:05', '2026-09-25 05:34:05');

-- Datos de `estados_pedido`
INSERT INTO `estados_pedido` (`id`, `codigo`, `nombre`, `descripcion`, `es_final`, `color`, `created_at`, `updated_at`) VALUES
('1', 'PENDIENTE', 'Pendiente', NULL, '0', '#fab005', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('2', 'EN_PREPARACION', 'En preparación', NULL, '0', '#228be6', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('3', 'EN_REPARTO', 'En reparto', NULL, '0', '#7950f2', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('4', 'ENTREGADO', 'Entregado', NULL, '1', '#40c057', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('5', 'CANCELADO', 'Cancelado', NULL, '1', '#868e96', '2026-09-25 05:34:05', '2026-09-25 05:34:05');

-- Datos de `formas_pago`
INSERT INTO `formas_pago` (`id`, `codigo`, `nombre`, `descripcion`, `requiere_referencia`, `activo`, `created_at`, `updated_at`) VALUES
('1', 'EFECTIVO', 'Efectivo', NULL, '0', '1', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('2', 'TRANSFERENCIA', 'Transferencia', NULL, '1', '1', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('3', 'MERCADO_PAGO', 'Mercado Pago / QR', NULL, '1', '1', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('4', 'DEBITO', 'Tarjeta de débito', NULL, '0', '1', '2026-09-25 05:34:05', '2026-09-25 05:34:05');

-- Datos de `medios_contacto_pedido`
INSERT INTO `medios_contacto_pedido` (`id`, `codigo`, `nombre`, `descripcion`, `created_at`, `updated_at`) VALUES
('1', 'TELEFONO', 'Teléfono', 'Llamada telefónica', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('2', 'WHATSAPP', 'WhatsApp', 'Mensaje de WhatsApp', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('3', 'PRESENCIAL', 'En el local', 'El cliente concurre al establecimiento', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('4', 'OTRO', 'Otro', 'Redes sociales u otro medio', '2026-09-25 05:34:05', '2026-09-25 05:34:05');

-- Datos de `productos`
INSERT INTO `productos` (`id`, `codigo`, `nombre`, `descripcion`, `tipo_producto_id`, `capacidad_kg`, `unidad_venta`, `precio_actual`, `costo_actual`, `stock_actual`, `stock_minimo`, `maneja_garrafa_individual`, `activo`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES
('1', 'GAR10', 'Garrafa 10 kg', NULL, '1', '10.00', 'GARRAFA', '16500.00', '12800.00', '0.00', '15.00', '1', '1', '2026-09-25 02:34:05', '2026-09-25 02:34:05', NULL, NULL, NULL),
('2', 'GAR15', 'Garrafa 15 kg', NULL, '1', '15.00', 'GARRAFA', '24000.00', '18900.00', '0.00', '10.00', '1', '1', '2026-09-25 02:34:05', '2026-09-25 02:34:05', NULL, NULL, NULL),
('3', 'GAR45', 'Garrafa 45 kg', NULL, '1', '45.00', 'GARRAFA', '72000.00', '58500.00', '0.00', '4.00', '1', '1', '2026-09-25 02:34:05', '2026-09-25 02:34:05', NULL, NULL, NULL),
('4', 'CAR03', 'Carbón 3 kg', NULL, '2', '3.00', 'BOLSA', '4200.00', '2600.00', '0.00', '15.00', '0', '1', '2026-09-25 02:34:05', '2026-09-25 02:34:05', NULL, NULL, NULL),
('5', 'CAR05', 'Carbón 5 kg', NULL, '2', '5.00', 'BOLSA', '6500.00', '4100.00', '0.00', '15.00', '0', '1', '2026-09-25 02:34:05', '2026-09-25 02:34:05', NULL, NULL, NULL),
('6', 'CAR10', 'Carbón 10 kg', NULL, '2', '10.00', 'BOLSA', '12000.00', '7800.00', '0.00', '10.00', '0', '1', '2026-09-25 02:34:05', '2026-09-25 02:34:05', NULL, NULL, NULL),
('7', 'CAR25', 'Carbón 25 kg', NULL, '2', '25.00', 'BOLSA', '27500.00', '18000.00', '0.00', '4.00', '0', '1', '2026-09-25 02:34:05', '2026-09-25 02:34:05', NULL, NULL, NULL),
('8', 'LEN25', 'Leña para hogar 25 kg', NULL, '3', '25.00', 'BOLSA', '11500.00', '7000.00', '0.00', '10.00', '0', '1', '2026-09-25 02:34:05', '2026-09-25 02:34:05', NULL, NULL, NULL);

-- Datos de `provincias`
INSERT INTO `provincias` (`id`, `codigo`, `nombre`, `pais`, `created_at`, `updated_at`) VALUES
('1', 'CABA', 'Ciudad Autónoma de Buenos Aires', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('2', 'BA', 'Buenos Aires', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('3', 'CAT', 'Catamarca', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('4', 'CHA', 'Chaco', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('5', 'CHU', 'Chubut', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('6', 'COR', 'Córdoba', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('7', 'CRR', 'Corrientes', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('8', 'ER', 'Entre Ríos', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('9', 'FOR', 'Formosa', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('10', 'JUJ', 'Jujuy', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('11', 'LP', 'La Pampa', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('12', 'LR', 'La Rioja', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('13', 'MZA', 'Mendoza', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('14', 'MIS', 'Misiones', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('15', 'NQN', 'Neuquén', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('16', 'RN', 'Río Negro', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('17', 'SAL', 'Salta', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('18', 'SJ', 'San Juan', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('19', 'SL', 'San Luis', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('20', 'SC', 'Santa Cruz', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('21', 'SF', 'Santa Fe', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('22', 'SE', 'Santiago del Estero', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('23', 'TF', 'Tierra del Fuego', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('24', 'TUC', 'Tucumán', 'Argentina', '2026-09-25 05:34:05', '2026-09-25 05:34:05');

-- Datos de `roles`
INSERT INTO `roles` (`id`, `codigo`, `nombre`, `descripcion`, `created_at`, `updated_at`) VALUES
('1', 'ADMIN', 'Administrador', 'Dueño: acceso total, precios, usuarios y configuración', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('2', 'EMPLEADO', 'Empleado', 'Atención de pedidos, cobros, garrafas y recepciones', '2026-09-25 05:34:05', '2026-09-25 05:34:05');

-- Datos de `tipos_contacto_cliente`
INSERT INTO `tipos_contacto_cliente` (`id`, `codigo`, `nombre`, `created_at`, `updated_at`) VALUES
('1', 'CELULAR', 'Celular', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('2', 'TELEFONO_FIJO', 'Teléfono fijo', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('3', 'WHATSAPP', 'WhatsApp', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('4', 'EMAIL', 'Email', '2026-09-25 05:34:05', '2026-09-25 05:34:05');

-- Datos de `tipos_movimiento_garrafa`
INSERT INTO `tipos_movimiento_garrafa` (`id`, `codigo`, `nombre`, `descripcion`, `created_at`, `updated_at`) VALUES
('1', 'ALTA', 'Alta de envase', 'Ingreso de una garrafa al parque', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('2', 'ENTREGA_CLIENTE', 'Entrega a cliente', 'Garrafa llena entregada en un pedido', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('3', 'DEVOLUCION_CLIENTE', 'Devolución de cliente', 'Envase vacío recibido de un cliente', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('4', 'ENTREGA_PROVEEDOR', 'Entrega a proveedor', 'Envase vacío entregado al proveedor', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('5', 'MARCAR_NO_APTA', 'Marcada no apta', 'El envase no está en condiciones', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('6', 'REPARACION', 'Reparación', 'El envase vuelve a estar apto', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('7', 'BAJA', 'Baja', 'Descarte definitivo', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('8', 'AJUSTE', 'Ajuste', 'Corrección por inventario', '2026-09-25 05:34:05', '2026-09-25 05:34:05');

-- Datos de `tipos_producto`
INSERT INTO `tipos_producto` (`id`, `codigo`, `nombre`, `descripcion`, `created_at`, `updated_at`) VALUES
('1', 'GAS', 'Gas envasado', 'Garrafas de 10, 15 y 45 kg', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('2', 'CARBON', 'Carbón', 'Bolsas de 3, 5, 10 y 25 kg', '2026-09-25 05:34:05', '2026-09-25 05:34:05'),
('3', 'LENA', 'Leña', 'Leña para hogar en bolsa de 25 kg', '2026-09-25 05:34:05', '2026-09-25 05:34:05');

-- Datos de `usuarios`
INSERT INTO `usuarios` (`id`, `username`, `password_hash`, `email`, `rol_id`, `activo`, `ultimo_login`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES
('1', 'admin', '$2y$12$T5O0iBVq4/ASNbHpQen44OG.bsA7pwXEasqsMJeWN/UfV/8ndZDx6', 'admin@extragas.com.ar', '1', '1', NULL, '2026-09-25 02:34:05', '2026-09-25 02:34:05', NULL, NULL, NULL);

-- ---------------------------------------------------------------------
--  3. Triggers (se crean al final para no alterar los datos importados)
-- ---------------------------------------------------------------------
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 */ /*!50003 TRIGGER `trg_garrafas_bi_validate` BEFORE INSERT ON `garrafas` FOR EACH ROW BEGIN
  DECLARE v_requiere_cliente BOOLEAN;

  SELECT requiere_cliente INTO v_requiere_cliente
  FROM estados_garrafa
  WHERE id = NEW.estado_garrafa_id;

  IF v_requiere_cliente IS TRUE AND NEW.cliente_id IS NULL THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'El estado de garrafa requiere un cliente_id';
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 */ /*!50003 TRIGGER `trg_mov_garrafa_ai` AFTER INSERT ON `movimientos_garrafa` FOR EACH ROW BEGIN
  UPDATE garrafas
  SET fecha_ultimo_movimiento = NEW.fecha,
      estado_garrafa_id = NEW.estado_destino_id
  WHERE id = NEW.garrafa_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 */ /*!50003 TRIGGER `trg_pagos_bi` BEFORE INSERT ON `pagos` FOR EACH ROW BEGIN
  DECLARE v_anio SMALLINT UNSIGNED;
  DECLARE v_siguiente INT UNSIGNED;

  SET v_anio = YEAR(NEW.fecha);

  INSERT INTO secuencias (nombre, prefijo, anio, ultimo_valor)
  VALUES ('pagos_cliente', 'REC', v_anio, 1)
  ON DUPLICATE KEY UPDATE ultimo_valor = ultimo_valor + 1;

  SELECT ultimo_valor INTO v_siguiente
  FROM secuencias
  WHERE nombre = 'pagos_cliente' AND anio = v_anio;

  SET NEW.numero_recibo = CONCAT('REC-', v_anio, '-', LPAD(v_siguiente, 5, '0'));
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 */ /*!50003 TRIGGER `trg_pagos_ai` AFTER INSERT ON `pagos` FOR EACH ROW BEGIN
  IF NEW.pedido_id IS NOT NULL AND NEW.deleted_at IS NULL THEN
    UPDATE pedidos
    SET monto_pagado = (
      SELECT COALESCE(SUM(monto), 0)
      FROM pagos
      WHERE pedido_id = NEW.pedido_id AND deleted_at IS NULL
    )
    WHERE id = NEW.pedido_id;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 */ /*!50003 TRIGGER `trg_pagos_au` AFTER UPDATE ON `pagos` FOR EACH ROW BEGIN
  IF NEW.pedido_id IS NOT NULL THEN
    UPDATE pedidos
    SET monto_pagado = (
      SELECT COALESCE(SUM(monto), 0)
      FROM pagos
      WHERE pedido_id = NEW.pedido_id AND deleted_at IS NULL
    )
    WHERE id = NEW.pedido_id;
  END IF;
  IF OLD.pedido_id IS NOT NULL AND OLD.pedido_id <> NEW.pedido_id THEN
    UPDATE pedidos
    SET monto_pagado = (
      SELECT COALESCE(SUM(monto), 0)
      FROM pagos
      WHERE pedido_id = OLD.pedido_id AND deleted_at IS NULL
    )
    WHERE id = OLD.pedido_id;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 */ /*!50003 TRIGGER `trg_pagos_ad` AFTER DELETE ON `pagos` FOR EACH ROW BEGIN
  IF OLD.pedido_id IS NOT NULL THEN
    UPDATE pedidos
    SET monto_pagado = (
      SELECT COALESCE(SUM(monto), 0)
      FROM pagos
      WHERE pedido_id = OLD.pedido_id AND deleted_at IS NULL
    )
    WHERE id = OLD.pedido_id;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 */ /*!50003 TRIGGER `trg_pagos_proveedor_bi` BEFORE INSERT ON `pagos_proveedor` FOR EACH ROW BEGIN
  DECLARE v_anio SMALLINT UNSIGNED;
  DECLARE v_siguiente INT UNSIGNED;

  SET v_anio = YEAR(NEW.fecha);

  INSERT INTO secuencias (nombre, prefijo, anio, ultimo_valor)
  VALUES ('pagos_proveedor', 'PAG-PROV', v_anio, 1)
  ON DUPLICATE KEY UPDATE ultimo_valor = ultimo_valor + 1;

  SELECT ultimo_valor INTO v_siguiente
  FROM secuencias
  WHERE nombre = 'pagos_proveedor' AND anio = v_anio;

  SET NEW.numero = CONCAT('PAG-PROV-', v_anio, '-', LPAD(v_siguiente, 5, '0'));
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 */ /*!50003 TRIGGER `trg_pagos_proveedor_ai` AFTER INSERT ON `pagos_proveedor` FOR EACH ROW BEGIN
  IF NEW.recepcion_id IS NOT NULL AND NEW.deleted_at IS NULL THEN
    UPDATE recepciones_proveedor
    SET monto_pagado = (
      SELECT COALESCE(SUM(monto), 0)
      FROM pagos_proveedor
      WHERE recepcion_id = NEW.recepcion_id AND deleted_at IS NULL
    )
    WHERE id = NEW.recepcion_id;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 */ /*!50003 TRIGGER `trg_pagos_proveedor_au` AFTER UPDATE ON `pagos_proveedor` FOR EACH ROW BEGIN
  IF NEW.recepcion_id IS NOT NULL THEN
    UPDATE recepciones_proveedor
    SET monto_pagado = (
      SELECT COALESCE(SUM(monto), 0)
      FROM pagos_proveedor
      WHERE recepcion_id = NEW.recepcion_id AND deleted_at IS NULL
    )
    WHERE id = NEW.recepcion_id;
  END IF;
  IF OLD.recepcion_id IS NOT NULL AND OLD.recepcion_id <> NEW.recepcion_id THEN
    UPDATE recepciones_proveedor
    SET monto_pagado = (
      SELECT COALESCE(SUM(monto), 0)
      FROM pagos_proveedor
      WHERE recepcion_id = OLD.recepcion_id AND deleted_at IS NULL
    )
    WHERE id = OLD.recepcion_id;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 */ /*!50003 TRIGGER `trg_pagos_proveedor_ad` AFTER DELETE ON `pagos_proveedor` FOR EACH ROW BEGIN
  IF OLD.recepcion_id IS NOT NULL THEN
    UPDATE recepciones_proveedor
    SET monto_pagado = (
      SELECT COALESCE(SUM(monto), 0)
      FROM pagos_proveedor
      WHERE recepcion_id = OLD.recepcion_id AND deleted_at IS NULL
    )
    WHERE id = OLD.recepcion_id;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 */ /*!50003 TRIGGER `trg_pedidos_bi` BEFORE INSERT ON `pedidos` FOR EACH ROW BEGIN
  DECLARE v_anio SMALLINT UNSIGNED;
  DECLARE v_siguiente INT UNSIGNED;

  SET v_anio = YEAR(NEW.fecha);

  INSERT INTO secuencias (nombre, prefijo, anio, ultimo_valor)
  VALUES ('pedidos', 'PED', v_anio, 1)
  ON DUPLICATE KEY UPDATE ultimo_valor = ultimo_valor + 1;

  SELECT ultimo_valor INTO v_siguiente
  FROM secuencias
  WHERE nombre = 'pedidos' AND anio = v_anio;

  SET NEW.numero = CONCAT('PED-', v_anio, '-', LPAD(v_siguiente, 5, '0'));
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 */ /*!50003 TRIGGER `trg_recepciones_bi` BEFORE INSERT ON `recepciones_proveedor` FOR EACH ROW BEGIN
  DECLARE v_anio SMALLINT UNSIGNED;
  DECLARE v_siguiente INT UNSIGNED;

  SET v_anio = YEAR(NEW.fecha);

  INSERT INTO secuencias (nombre, prefijo, anio, ultimo_valor)
  VALUES ('recepciones_proveedor', 'REC-PROV', v_anio, 1)
  ON DUPLICATE KEY UPDATE ultimo_valor = ultimo_valor + 1;

  SELECT ultimo_valor INTO v_siguiente
  FROM secuencias
  WHERE nombre = 'recepciones_proveedor' AND anio = v_anio;

  SET NEW.numero = CONCAT('REC-PROV-', v_anio, '-', LPAD(v_siguiente, 5, '0'));
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;

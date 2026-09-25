-- =====================================================================
--  ExtraGas · Sistema de Gestión de Pedidos (PHP MVC)
--  Base de datos MySQL 8 / MariaDB 10.6+
--  Estructura completa + 90 días de registros de ejemplo (clientes, pedidos, garrafas, cobros, proveedores)
--  Generado: 25/09/2026
--
--  Importar con phpMyAdmin (pestaña Importar) o por consola:
--      mysql -u root -p < extragas_datos_ejemplo.sql
--  El script crea la base "extragas" si no existe.
-- =====================================================================

SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS `extragas` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `extragas`;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `canales_venta`;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `canales_venta` WRITE;
/*!40000 ALTER TABLE `canales_venta` DISABLE KEYS */;
INSERT INTO `canales_venta` VALUES
(1,'DOMICILIO','Envío a domicilio','Se entrega en el domicilio del cliente','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(2,'RETIRO_LOCAL','Retira en el local','El cliente pasa a retirar el pedido','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(3,'MOSTRADOR','Venta en mostrador','Venta y entrega inmediata en el local','2026-09-25 03:37:54','2026-09-25 03:37:54');
/*!40000 ALTER TABLE `canales_venta` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cliente_contactos`;
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

LOCK TABLES `cliente_contactos` WRITE;
/*!40000 ALTER TABLE `cliente_contactos` DISABLE KEYS */;
/*!40000 ALTER TABLE `cliente_contactos` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `clientes`;
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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES
(1,'C0001','María','González','20206420',NULL,'381 641-8189',NULL,NULL,'Santiago del Estero','2868',NULL,'2B','San Miguel de Tucumán','4000',24,1,'Portón verde',NULL,'2026-05-12',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(2,'C0002','José','Rodríguez','35510037',NULL,'381 663-1641',NULL,NULL,'Laprida','965',NULL,NULL,'Yerba Buena','4000',24,2,'Timbre 2',NULL,'2025-11-25',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(3,'C0003','Ana','López','37834844',NULL,'381 433-0825',NULL,NULL,'Crisóstomo Álvarez','2850',NULL,'3B','San Miguel de Tucumán','4000',24,1,'Portón verde',NULL,'2025-02-05',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(4,'C0004','Carlos','Martínez','43905946',NULL,'381 411-1508',NULL,NULL,'Av. Mate de Luna','1591',NULL,NULL,'San Miguel de Tucumán','4000',24,1,'Frente a la plaza',NULL,'2026-03-02',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(5,'C0005','Laura','Pérez','30604941',NULL,'381 563-0232',NULL,NULL,'Jujuy','2171',NULL,NULL,'San Miguel de Tucumán','4000',24,1,'Casa esquina',NULL,'2025-10-21',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(6,'C0006','Jorge','Sánchez','27105225',NULL,'381 408-5074',NULL,NULL,'San Juan','501',NULL,NULL,'San Miguel de Tucumán','4000',24,1,'Portón verde',NULL,'2026-02-09',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(7,'C0007','Silvia','Romero','33723065',NULL,'381 603-5530',NULL,NULL,'Congreso','2110',NULL,NULL,'Tafí Viejo','4000',24,2,'Portón verde',NULL,'2026-05-08',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(8,'C0008','Miguel','Díaz','24422005',NULL,'381 695-1710',NULL,NULL,'Mendoza','1087',NULL,NULL,'Yerba Buena','4000',24,2,'Frente a la plaza',NULL,'2025-04-16',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(9,'C0009','Patricia','Álvarez','22080783',NULL,'381 441-0897',NULL,NULL,'Crisóstomo Álvarez','2144',NULL,NULL,'Tafí Viejo','4000',24,1,'Frente a la plaza',NULL,'2024-12-18',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(10,'C0010','Ricardo','Torres','38636910',NULL,'381 544-1494',NULL,NULL,'Salta','1742',NULL,NULL,'San Miguel de Tucumán','4000',24,1,NULL,NULL,'2026-05-27',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(11,'C0011','Graciela','Ruiz','33482253',NULL,'381 646-5441',NULL,NULL,'Crisóstomo Álvarez','1647',NULL,NULL,'San Miguel de Tucumán','4000',24,2,'Frente a la plaza',NULL,'2026-04-26',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(12,'C0012','Diego','Ramírez','27560610',NULL,'381 554-2894',NULL,NULL,'Laprida','2774',NULL,NULL,'Tafí Viejo','4000',24,1,NULL,NULL,'2026-02-04',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(13,'C0013','Sofía','Flores','37687245',NULL,'381 582-6006',NULL,NULL,'Salta','2313',NULL,NULL,'Tafí Viejo','4000',24,2,NULL,NULL,'2025-03-12',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(14,'C0014','Hugo','Acosta','19080506',NULL,'381 564-6018',NULL,NULL,'Congreso','160',NULL,NULL,'Tafí Viejo','4000',24,1,'Casa esquina',NULL,'2025-02-24',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(15,'C0015','Norma','Benítez','33644450',NULL,'381 607-1907',NULL,NULL,'Córdoba','355',NULL,NULL,'Tafí Viejo','4000',24,2,NULL,NULL,'2025-06-21',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(16,'C0016','Pablo','Medina','39257418',NULL,'381 462-0361',NULL,NULL,'Córdoba','2623',NULL,NULL,'San Miguel de Tucumán','4000',24,1,NULL,NULL,'2025-04-02',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(17,'C0017','Mónica','Herrera','34449533',NULL,'381 534-7859',NULL,NULL,'Jujuy','416',NULL,'4B','San Miguel de Tucumán','4000',24,1,NULL,NULL,'2025-04-29',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(18,'C0018','Daniel','Suárez','33458643',NULL,'381 646-7602',NULL,NULL,'Av. Aconquija','1693',NULL,NULL,'San Miguel de Tucumán','4000',24,2,'Frente a la plaza',NULL,'2024-09-20',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(19,'C0019','Claudia','Aguirre','37855007',NULL,'381 427-4563',NULL,NULL,'Laprida','317',NULL,'4B','San Miguel de Tucumán','4000',24,2,'Timbre 2',NULL,'2024-08-13',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(20,'C0020','Rubén','Giménez','41213229',NULL,'381 470-5490',NULL,NULL,'Crisóstomo Álvarez','458',NULL,NULL,'Tafí Viejo','4000',24,1,NULL,NULL,'2025-01-12',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(21,'C0021','Parrilla','Don Tito',NULL,'30-63936247-5','381 483-1151',NULL,NULL,'Congreso','1462',NULL,NULL,'San Miguel de Tucumán','4000',24,2,NULL,'Cliente comercial, entregar por la mañana.','2026-03-26',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(22,'C0022','Rotisería','La Esquina',NULL,'30-51774435-7','381 439-9117',NULL,NULL,'Crisóstomo Álvarez','2316',NULL,NULL,'San Miguel de Tucumán','4000',24,1,NULL,'Cliente comercial, entregar por la mañana.','2025-03-26',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL);
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `configuracion_empresa`;
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

LOCK TABLES `configuracion_empresa` WRITE;
/*!40000 ALTER TABLE `configuracion_empresa` DISABLE KEYS */;
INSERT INTO `configuracion_empresa` VALUES
(1,'ExtraGas','ExtraGas — Venta de gas envasado, carbón y leña','20-28456123-7','Av. Belgrano 1450','San Miguel de Tucumán','381 421-5566','381 555-1020','contacto@extragas.com.ar','Lun a Sáb de 8 a 20 hs',3,'2026-09-25 00:37:54','2026-09-25 00:37:54');
/*!40000 ALTER TABLE `configuracion_empresa` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `empleados`;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `empleados` WRITE;
/*!40000 ALTER TABLE `empleados` DISABLE KEYS */;
INSERT INTO `empleados` VALUES
(1,'Roberto','Medina',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2015-03-01',1,1,NULL,'2026-09-25 00:37:54','2026-09-25 00:37:54',NULL,NULL,NULL),
(2,'Lucía','Fernández','30111222',NULL,'381 520-5888',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2021-06-01',2,1,NULL,'2026-09-25 00:38:07','2026-09-25 00:38:07',NULL,NULL,NULL),
(3,'Martín','Ríos','33444555',NULL,'381 535-1963',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2021-06-01',3,1,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL);
/*!40000 ALTER TABLE `empleados` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `estados_garrafa`;
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `estados_garrafa` WRITE;
/*!40000 ALTER TABLE `estados_garrafa` DISABLE KEYS */;
INSERT INTO `estados_garrafa` VALUES
(1,'LLENA','Llena','Llena en depósito, lista para la venta',1,0,'#40c057','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(2,'VACIA','Vacía apta','Vacía en depósito, apta para intercambio',0,0,'#4dabf7','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(3,'EN_CLIENTE','En cliente','En poder de un cliente',0,1,'#9775fa','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(4,'NO_APTA','No apta','Dañada o con prueba hidráulica vencida',0,0,'#fa5252','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(5,'EN_PROVEEDOR','Entregada al proveedor','Entregada vacía al proveedor en un intercambio',0,0,'#adb5bd','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(6,'BAJA','Baja','Descartada definitivamente',0,0,'#495057','2026-09-25 03:37:54','2026-09-25 03:37:54');
/*!40000 ALTER TABLE `estados_garrafa` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `estados_pedido`;
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `estados_pedido` WRITE;
/*!40000 ALTER TABLE `estados_pedido` DISABLE KEYS */;
INSERT INTO `estados_pedido` VALUES
(1,'PENDIENTE','Pendiente',NULL,0,'#fab005','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(2,'EN_PREPARACION','En preparación',NULL,0,'#228be6','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(3,'EN_REPARTO','En reparto',NULL,0,'#7950f2','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(4,'ENTREGADO','Entregado',NULL,1,'#40c057','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(5,'CANCELADO','Cancelado',NULL,1,'#868e96','2026-09-25 03:37:54','2026-09-25 03:37:54');
/*!40000 ALTER TABLE `estados_pedido` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `formas_pago`;
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `formas_pago` WRITE;
/*!40000 ALTER TABLE `formas_pago` DISABLE KEYS */;
INSERT INTO `formas_pago` VALUES
(1,'EFECTIVO','Efectivo',NULL,0,1,'2026-09-25 03:37:54','2026-09-25 03:37:54'),
(2,'TRANSFERENCIA','Transferencia',NULL,1,1,'2026-09-25 03:37:54','2026-09-25 03:37:54'),
(3,'MERCADO_PAGO','Mercado Pago / QR',NULL,1,1,'2026-09-25 03:37:54','2026-09-25 03:37:54'),
(4,'DEBITO','Tarjeta de débito',NULL,0,1,'2026-09-25 03:37:54','2026-09-25 03:37:54');
/*!40000 ALTER TABLE `formas_pago` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `garrafas`;
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
) ENGINE=InnoDB AUTO_INCREMENT=274 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `garrafas` WRITE;
/*!40000 ALTER TABLE `garrafas` DISABLE KEYS */;
INSERT INTO `garrafas` VALUES
(1,'G15-00001',15,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-12 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(2,'G10-00001',10,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-19 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(3,'G15-00002',15,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-05 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(4,'G10-00002',10,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-05 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(5,'G10-00003',10,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-05 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(6,'G10-00004',10,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-12 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(7,'G10-00005',10,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-19 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(8,'G15-00003',15,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-19 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(9,'G15-00004',15,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-05 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(10,'G10-00006',10,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-12 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(11,'G15-00005',15,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-12 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(12,'G10-00007',10,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-05 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(13,'G10-00008',10,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-12 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(14,'G15-00006',15,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-19 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(15,'G10-00009',10,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-12 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(16,'G10-00010',10,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-19 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(17,'G15-00007',15,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-05 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(18,'G15-00008',15,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-05 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(19,'G15-00009',15,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-12 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(20,'G10-00011',10,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-05 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(21,'G45-00001',45,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-10 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(22,'G45-00002',45,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-20 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(23,'G45-00003',45,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-10 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(24,'G45-00004',45,NULL,NULL,'2026-06-27',5,NULL,0,'2026-07-10 09:30:00','Envase en poder del cliente al iniciar','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(25,'G10-00012',10,1,NULL,'2026-06-27',5,NULL,0,'2026-07-26 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(26,'G10-00013',10,1,NULL,'2026-06-27',5,NULL,0,'2026-08-09 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(27,'G10-00014',10,1,NULL,'2026-06-27',5,NULL,0,'2026-07-19 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(28,'G10-00015',10,1,NULL,'2026-06-27',5,NULL,0,'2026-07-26 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(29,'G10-00016',10,1,NULL,'2026-06-27',5,NULL,0,'2026-07-19 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(30,'G10-00017',10,1,NULL,'2026-06-27',5,NULL,0,'2026-08-02 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(31,'G10-00018',10,1,NULL,'2026-06-27',5,NULL,0,'2026-07-26 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(32,'G10-00019',10,1,NULL,'2026-06-27',5,NULL,0,'2026-07-26 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(33,'G10-00020',10,1,NULL,'2026-06-27',5,NULL,0,'2026-07-26 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(34,'G10-00021',10,1,NULL,'2026-06-27',5,NULL,0,'2026-07-26 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(35,'G10-00022',10,1,NULL,'2026-06-27',5,NULL,0,'2026-08-09 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(36,'G10-00023',10,1,NULL,'2026-06-27',5,NULL,0,'2026-08-30 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:11',NULL,NULL,NULL),
(37,'G10-00024',10,1,NULL,'2026-06-27',5,NULL,0,'2026-08-02 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(38,'G10-00025',10,1,NULL,'2026-06-27',5,NULL,0,'2026-08-09 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(39,'G15-00010',15,1,NULL,'2026-06-27',5,NULL,0,'2026-08-02 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(40,'G15-00011',15,1,NULL,'2026-06-27',5,NULL,0,'2026-07-12 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(41,'G15-00012',15,1,NULL,'2026-06-27',5,NULL,0,'2026-07-19 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(42,'G15-00013',15,1,NULL,'2026-06-27',5,NULL,0,'2026-07-26 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(43,'G15-00014',15,1,NULL,'2026-06-27',5,NULL,0,'2026-07-19 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(44,'G15-00015',15,1,NULL,'2026-06-27',5,NULL,0,'2026-08-02 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(45,'G15-00016',15,1,NULL,'2026-06-27',5,NULL,0,'2026-08-16 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(46,'G15-00017',15,1,NULL,'2026-06-27',5,NULL,0,'2026-07-26 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(47,'G15-00018',15,1,NULL,'2026-06-27',5,NULL,0,'2026-07-26 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(48,'G45-00005',45,2,NULL,'2026-06-27',5,NULL,0,'2026-07-30 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(49,'G45-00006',45,2,NULL,'2026-06-27',5,NULL,0,'2026-07-30 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(50,'G45-00007',45,2,NULL,'2026-06-27',5,NULL,0,'2026-07-20 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(51,'G45-00008',45,2,NULL,'2026-06-27',5,NULL,0,'2026-07-20 09:30:00','Parque inicial','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(52,'G10-00026',10,1,1,'2026-06-28',5,NULL,0,'2026-08-02 09:30:00','Recepción REC-PROV-2026-00001','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(53,'G10-00027',10,1,1,'2026-06-28',5,NULL,0,'2026-08-16 09:30:00','Recepción REC-PROV-2026-00001','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(54,'G10-00028',10,1,1,'2026-06-28',5,NULL,0,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00001','2026-09-25 00:38:08','2026-09-25 00:38:12',NULL,NULL,NULL),
(55,'G10-00029',10,1,1,'2026-06-28',5,NULL,0,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00001','2026-09-25 00:38:08','2026-09-25 00:38:11',NULL,NULL,NULL),
(56,'G10-00030',10,1,1,'2026-06-28',5,NULL,0,'2026-08-02 09:30:00','Recepción REC-PROV-2026-00001','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(57,'G10-00031',10,1,1,'2026-06-28',5,NULL,0,'2026-08-02 09:30:00','Recepción REC-PROV-2026-00001','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(58,'G10-00032',10,1,1,'2026-06-28',5,NULL,0,'2026-08-16 09:30:00','Recepción REC-PROV-2026-00001','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(59,'G10-00033',10,1,1,'2026-06-28',5,NULL,0,'2026-08-09 09:30:00','Recepción REC-PROV-2026-00001','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(60,'G15-00019',15,1,1,'2026-06-28',5,NULL,0,'2026-07-26 09:30:00','Recepción REC-PROV-2026-00001','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(61,'G15-00020',15,1,1,'2026-06-28',5,NULL,0,'2026-08-16 09:30:00','Recepción REC-PROV-2026-00001','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(62,'G15-00021',15,1,1,'2026-06-28',5,NULL,0,'2026-07-26 09:30:00','Recepción REC-PROV-2026-00001','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(63,'G15-00022',15,1,1,'2026-06-28',5,NULL,0,'2026-07-26 09:30:00','Recepción REC-PROV-2026-00001','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(64,'G15-00023',15,1,1,'2026-06-28',5,NULL,0,'2026-08-02 09:30:00','Recepción REC-PROV-2026-00001','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(65,'G15-00024',15,1,1,'2026-06-28',5,NULL,0,'2026-08-16 09:30:00','Recepción REC-PROV-2026-00001','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(66,'G15-00025',15,1,1,'2026-06-28',5,NULL,0,'2026-08-09 09:30:00','Recepción REC-PROV-2026-00001','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(67,'G10-00034',10,NULL,NULL,'2026-06-28',5,NULL,0,'2026-07-05 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00001)','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(68,'G15-00026',15,NULL,NULL,'2026-06-29',5,NULL,0,'2026-07-05 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00004)','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(69,'G45-00009',45,2,2,'2026-06-30',5,NULL,0,'2026-07-20 09:30:00','Recepción REC-PROV-2026-00002','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(70,'G45-00010',45,2,2,'2026-06-30',5,NULL,0,'2026-07-30 09:30:00','Recepción REC-PROV-2026-00002','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(71,'G45-00011',45,2,2,'2026-06-30',5,NULL,0,'2026-07-30 09:30:00','Recepción REC-PROV-2026-00002','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(72,'G10-00035',10,1,5,'2026-07-05',5,NULL,0,'2026-08-09 09:30:00','Recepción REC-PROV-2026-00005','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(73,'G10-00036',10,1,5,'2026-07-05',5,NULL,0,'2026-08-23 09:30:00','Recepción REC-PROV-2026-00005','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(74,'G10-00037',10,1,5,'2026-07-05',5,NULL,0,'2026-08-23 09:30:00','Recepción REC-PROV-2026-00005','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(75,'G10-00038',10,1,5,'2026-07-05',5,NULL,0,'2026-08-09 09:30:00','Recepción REC-PROV-2026-00005','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(76,'G10-00039',10,1,5,'2026-07-05',5,NULL,0,'2026-08-23 09:30:00','Recepción REC-PROV-2026-00005','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(77,'G10-00040',10,1,5,'2026-07-05',5,NULL,0,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00005','2026-09-25 00:38:08','2026-09-25 00:38:12',NULL,NULL,NULL),
(78,'G15-00027',15,1,5,'2026-07-05',5,NULL,0,'2026-08-02 09:30:00','Recepción REC-PROV-2026-00005','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(79,'G15-00028',15,1,5,'2026-07-05',5,NULL,0,'2026-08-09 09:30:00','Recepción REC-PROV-2026-00005','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(80,'G15-00029',15,1,5,'2026-07-05',5,NULL,0,'2026-08-09 09:30:00','Recepción REC-PROV-2026-00005','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(81,'G15-00030',15,1,5,'2026-07-05',5,NULL,0,'2026-08-09 09:30:00','Recepción REC-PROV-2026-00005','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(82,'G15-00031',15,1,5,'2026-07-05',5,NULL,0,'2026-08-16 09:30:00','Recepción REC-PROV-2026-00005','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(83,'G15-00032',15,NULL,NULL,'2026-07-05',5,NULL,0,'2026-07-12 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00012)','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(84,'G15-00033',15,NULL,NULL,'2026-07-06',5,NULL,0,'2026-07-12 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00013)','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(85,'G15-00034',15,NULL,NULL,'2026-07-06',5,NULL,0,'2026-07-12 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00015)','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(86,'G45-00012',45,2,6,'2026-07-10',5,NULL,0,'2026-08-09 09:30:00','Recepción REC-PROV-2026-00006','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(87,'G45-00013',45,2,6,'2026-07-10',5,NULL,0,'2026-08-09 09:30:00','Recepción REC-PROV-2026-00006','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(88,'G45-00014',45,2,6,'2026-07-10',5,NULL,0,'2026-08-19 09:30:00','Recepción REC-PROV-2026-00006','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(89,'G10-00041',10,1,7,'2026-07-12',5,NULL,0,'2026-08-16 09:30:00','Recepción REC-PROV-2026-00007','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(90,'G10-00042',10,1,7,'2026-07-12',5,NULL,0,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00007','2026-09-25 00:38:08','2026-09-25 00:38:11',NULL,NULL,NULL),
(91,'G10-00043',10,1,7,'2026-07-12',5,NULL,0,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00007','2026-09-25 00:38:08','2026-09-25 00:38:11',NULL,NULL,NULL),
(92,'G10-00044',10,1,7,'2026-07-12',5,NULL,0,'2026-08-23 09:30:00','Recepción REC-PROV-2026-00007','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(93,'G10-00045',10,1,7,'2026-07-12',5,NULL,0,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00007','2026-09-25 00:38:08','2026-09-25 00:38:12',NULL,NULL,NULL),
(94,'G10-00046',10,1,7,'2026-07-12',5,NULL,0,'2026-08-23 09:30:00','Recepción REC-PROV-2026-00007','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(95,'G15-00035',15,1,7,'2026-07-12',5,NULL,0,'2026-08-16 09:30:00','Recepción REC-PROV-2026-00007','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(96,'G15-00036',15,1,7,'2026-07-12',5,NULL,0,'2026-08-23 09:30:00','Recepción REC-PROV-2026-00007','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(97,'G15-00037',15,1,7,'2026-07-12',5,NULL,0,'2026-08-23 09:30:00','Recepción REC-PROV-2026-00007','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(98,'G15-00038',15,1,7,'2026-07-12',5,NULL,0,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00007','2026-09-25 00:38:08','2026-09-25 00:38:11',NULL,NULL,NULL),
(99,'G15-00039',15,1,7,'2026-07-12',5,NULL,0,'2026-08-16 09:30:00','Recepción REC-PROV-2026-00007','2026-09-25 00:38:08','2026-09-25 00:38:10',NULL,NULL,NULL),
(100,'G15-00040',15,1,7,'2026-07-12',5,NULL,0,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00007','2026-09-25 00:38:08','2026-09-25 00:38:11',NULL,NULL,NULL),
(101,'G15-00041',15,1,7,'2026-07-12',5,NULL,0,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00007','2026-09-25 00:38:08','2026-09-25 00:38:11',NULL,NULL,NULL),
(102,'G15-00042',15,NULL,NULL,'2026-07-12',5,NULL,0,'2026-07-19 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00023)','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(103,'G10-00047',10,NULL,NULL,'2026-07-13',5,NULL,0,'2026-07-19 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00024)','2026-09-25 00:38:08','2026-09-25 00:38:09',NULL,NULL,NULL),
(104,'G10-00048',10,NULL,NULL,'2026-07-16',5,NULL,0,'2026-07-19 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00029)','2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(105,'G10-00049',10,1,8,'2026-07-19',5,NULL,0,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00008','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(106,'G10-00050',10,1,8,'2026-07-19',3,16,1,'2026-08-11 11:20:00','Recepción REC-PROV-2026-00008','2026-09-25 00:38:09','2026-09-25 00:38:10',NULL,NULL,NULL),
(107,'G10-00051',10,1,8,'2026-07-19',5,NULL,0,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00008','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(108,'G10-00052',10,1,8,'2026-07-19',5,NULL,0,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00008','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(109,'G10-00053',10,1,8,'2026-07-19',5,NULL,0,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00008','2026-09-25 00:38:09','2026-09-25 00:38:12',NULL,NULL,NULL),
(110,'G10-00054',10,1,8,'2026-07-19',5,NULL,0,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00008','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(111,'G10-00055',10,1,8,'2026-07-19',5,NULL,0,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00008','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(112,'G15-00043',15,1,8,'2026-07-19',5,NULL,0,'2026-08-23 09:30:00','Recepción REC-PROV-2026-00008','2026-09-25 00:38:09','2026-09-25 00:38:10',NULL,NULL,NULL),
(113,'G15-00044',15,1,8,'2026-07-19',5,NULL,0,'2026-08-23 09:30:00','Recepción REC-PROV-2026-00008','2026-09-25 00:38:09','2026-09-25 00:38:10',NULL,NULL,NULL),
(114,'G15-00045',15,1,8,'2026-07-19',5,NULL,0,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00008','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(115,'G15-00046',15,1,8,'2026-07-19',5,NULL,0,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00008','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(116,'G15-00047',15,1,8,'2026-07-19',5,NULL,0,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00008','2026-09-25 00:38:09','2026-09-25 00:38:12',NULL,NULL,NULL),
(117,'G10-00056',10,NULL,NULL,'2026-07-19',5,NULL,0,'2026-07-26 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00037)','2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(118,'G45-00015',45,2,9,'2026-07-20',5,NULL,0,'2026-08-09 09:30:00','Recepción REC-PROV-2026-00009','2026-09-25 00:38:09','2026-09-25 00:38:10',NULL,NULL,NULL),
(119,'G45-00016',45,2,9,'2026-07-20',5,NULL,0,'2026-08-09 09:30:00','Recepción REC-PROV-2026-00009','2026-09-25 00:38:09','2026-09-25 00:38:10',NULL,NULL,NULL),
(120,'G45-00017',45,2,9,'2026-07-20',5,NULL,0,'2026-08-19 09:30:00','Recepción REC-PROV-2026-00009','2026-09-25 00:38:09','2026-09-25 00:38:10',NULL,NULL,NULL),
(121,'G45-00018',45,2,9,'2026-07-20',5,NULL,0,'2026-08-19 09:30:00','Recepción REC-PROV-2026-00009','2026-09-25 00:38:09','2026-09-25 00:38:10',NULL,NULL,NULL),
(122,'G10-00057',10,NULL,NULL,'2026-07-24',5,NULL,0,'2026-08-02 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00041)','2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(123,'G10-00058',10,1,11,'2026-07-26',5,NULL,0,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00011','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(124,'G10-00059',10,1,11,'2026-07-26',5,NULL,0,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00011','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(125,'G10-00060',10,1,11,'2026-07-26',5,NULL,0,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00011','2026-09-25 00:38:09','2026-09-25 00:38:12',NULL,NULL,NULL),
(126,'G10-00061',10,1,11,'2026-07-26',5,NULL,0,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00011','2026-09-25 00:38:09','2026-09-25 00:38:12',NULL,NULL,NULL),
(127,'G10-00062',10,1,11,'2026-07-26',5,NULL,0,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00011','2026-09-25 00:38:09','2026-09-25 00:38:12',NULL,NULL,NULL),
(128,'G10-00063',10,1,11,'2026-07-26',5,NULL,0,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00011','2026-09-25 00:38:09','2026-09-25 00:38:12',NULL,NULL,NULL),
(129,'G10-00064',10,1,11,'2026-07-26',3,2,1,'2026-08-23 10:31:00','Recepción REC-PROV-2026-00011','2026-09-25 00:38:09','2026-09-25 00:38:10',NULL,NULL,NULL),
(130,'G15-00048',15,1,11,'2026-07-26',5,NULL,0,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00011','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(131,'G15-00049',15,1,11,'2026-07-26',5,NULL,0,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00011','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(132,'G15-00050',15,1,11,'2026-07-26',5,NULL,0,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00011','2026-09-25 00:38:09','2026-09-25 00:38:12',NULL,NULL,NULL),
(133,'G15-00051',15,1,11,'2026-07-26',5,NULL,0,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00011','2026-09-25 00:38:09','2026-09-25 00:38:12',NULL,NULL,NULL),
(134,'G15-00052',15,1,11,'2026-07-26',5,NULL,0,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00011','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(135,'G15-00053',15,1,11,'2026-07-26',5,NULL,0,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00011','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(136,'G15-00054',15,1,11,'2026-07-26',5,NULL,0,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00011','2026-09-25 00:38:09','2026-09-25 00:38:12',NULL,NULL,NULL),
(137,'G45-00019',45,2,13,'2026-07-30',5,NULL,0,'2026-08-29 09:30:00','Recepción REC-PROV-2026-00013','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(138,'G45-00020',45,2,13,'2026-07-30',5,NULL,0,'2026-08-29 09:30:00','Recepción REC-PROV-2026-00013','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(139,'G45-00021',45,2,13,'2026-07-30',5,NULL,0,'2026-08-29 09:30:00','Recepción REC-PROV-2026-00013','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(140,'G45-00022',45,2,13,'2026-07-30',5,NULL,0,'2026-09-08 09:30:00','Recepción REC-PROV-2026-00013','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(141,'G10-00065',10,1,14,'2026-08-02',3,12,1,'2026-08-23 10:53:00','Recepción REC-PROV-2026-00014','2026-09-25 00:38:09','2026-09-25 00:38:10',NULL,NULL,NULL),
(142,'G10-00066',10,1,14,'2026-08-02',5,NULL,0,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00014','2026-09-25 00:38:09','2026-09-25 00:38:12',NULL,NULL,NULL),
(143,'G10-00067',10,1,14,'2026-08-02',3,4,1,'2026-08-31 09:01:00','Recepción REC-PROV-2026-00014','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(144,'G10-00068',10,1,14,'2026-08-02',5,NULL,0,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00014','2026-09-25 00:38:09','2026-09-25 00:38:12',NULL,NULL,NULL),
(145,'G10-00069',10,1,14,'2026-08-02',5,NULL,0,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00014','2026-09-25 00:38:09','2026-09-25 00:38:12',NULL,NULL,NULL),
(146,'G10-00070',10,1,14,'2026-08-02',3,7,1,'2026-09-01 09:25:00','Recepción REC-PROV-2026-00014','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(147,'G10-00071',10,1,14,'2026-08-02',2,NULL,1,'2026-09-20 11:01:00','Recepción REC-PROV-2026-00014','2026-09-25 00:38:09','2026-09-25 00:38:12',NULL,NULL,NULL),
(148,'G15-00055',15,1,14,'2026-08-02',5,NULL,0,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00014','2026-09-25 00:38:09','2026-09-25 00:38:12',NULL,NULL,NULL),
(149,'G15-00056',15,1,14,'2026-08-02',5,NULL,0,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00014','2026-09-25 00:38:09','2026-09-25 00:38:12',NULL,NULL,NULL),
(150,'G15-00057',15,1,14,'2026-08-02',5,NULL,0,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00014','2026-09-25 00:38:09','2026-09-25 00:38:12',NULL,NULL,NULL),
(151,'G15-00058',15,1,14,'2026-08-02',3,11,1,'2026-08-25 09:36:00','Recepción REC-PROV-2026-00014','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(152,'G15-00059',15,1,14,'2026-08-02',5,NULL,0,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00014','2026-09-25 00:38:09','2026-09-25 00:38:12',NULL,NULL,NULL),
(153,'G15-00060',15,1,14,'2026-08-02',5,NULL,0,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00014','2026-09-25 00:38:09','2026-09-25 00:38:11',NULL,NULL,NULL),
(154,'G10-00072',10,1,15,'2026-08-09',3,13,1,'2026-09-02 09:17:00','Recepción REC-PROV-2026-00015','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(155,'G10-00073',10,1,15,'2026-08-09',3,6,1,'2026-09-05 09:21:00','Recepción REC-PROV-2026-00015','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(156,'G10-00074',10,1,15,'2026-08-09',3,6,1,'2026-09-05 09:21:00','Recepción REC-PROV-2026-00015','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(157,'G10-00075',10,1,15,'2026-08-09',3,16,1,'2026-09-05 11:38:00','Recepción REC-PROV-2026-00015','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(158,'G10-00076',10,1,15,'2026-08-09',5,NULL,0,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00015','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(159,'G10-00077',10,1,15,'2026-08-09',3,20,1,'2026-09-07 10:57:00','Recepción REC-PROV-2026-00015','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(160,'G10-00078',10,1,15,'2026-08-09',3,20,1,'2026-09-07 10:57:00','Recepción REC-PROV-2026-00015','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(161,'G10-00079',10,1,15,'2026-08-09',3,10,1,'2026-09-10 10:17:00','Recepción REC-PROV-2026-00015','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(162,'G15-00061',15,1,15,'2026-08-09',2,NULL,1,'2026-09-20 10:42:00','Recepción REC-PROV-2026-00015','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(163,'G15-00062',15,1,15,'2026-08-09',3,8,1,'2026-08-30 10:06:00','Recepción REC-PROV-2026-00015','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(164,'G15-00063',15,1,15,'2026-08-09',3,19,1,'2026-09-04 08:46:00','Recepción REC-PROV-2026-00015','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(165,'G15-00064',15,1,15,'2026-08-09',3,14,1,'2026-09-05 10:12:00','Recepción REC-PROV-2026-00015','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(166,'G15-00065',15,1,15,'2026-08-09',3,14,1,'2026-09-05 10:12:00','Recepción REC-PROV-2026-00015','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(167,'G45-00023',45,2,16,'2026-08-09',5,NULL,0,'2026-08-29 09:30:00','Recepción REC-PROV-2026-00016','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(168,'G45-00024',45,2,16,'2026-08-09',5,NULL,0,'2026-09-08 09:30:00','Recepción REC-PROV-2026-00016','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(169,'G45-00025',45,2,16,'2026-08-09',5,NULL,0,'2026-09-18 09:30:00','Recepción REC-PROV-2026-00016','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(170,'G45-00026',45,2,16,'2026-08-09',5,NULL,0,'2026-09-08 09:30:00','Recepción REC-PROV-2026-00016','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(171,'G10-00080',10,NULL,NULL,'2026-08-15',5,NULL,0,'2026-08-16 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00075)','2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(172,'G10-00081',10,1,18,'2026-08-16',3,2,1,'2026-09-11 09:46:00','Recepción REC-PROV-2026-00018','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(173,'G10-00082',10,1,18,'2026-08-16',3,12,1,'2026-09-12 09:54:00','Recepción REC-PROV-2026-00018','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(174,'G10-00083',10,1,18,'2026-08-16',3,5,1,'2026-09-14 09:47:00','Recepción REC-PROV-2026-00018','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(175,'G10-00084',10,1,18,'2026-08-16',3,4,1,'2026-09-15 09:33:00','Recepción REC-PROV-2026-00018','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(176,'G10-00085',10,1,18,'2026-08-16',3,13,1,'2026-09-17 10:35:00','Recepción REC-PROV-2026-00018','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(177,'G10-00086',10,1,18,'2026-08-16',3,7,1,'2026-09-18 09:45:00','Recepción REC-PROV-2026-00018','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(178,'G10-00087',10,1,18,'2026-08-16',3,15,1,'2026-09-18 11:18:00','Recepción REC-PROV-2026-00018','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(179,'G10-00088',10,1,18,'2026-08-16',3,15,1,'2026-09-18 11:18:00','Recepción REC-PROV-2026-00018','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(180,'G15-00066',15,1,18,'2026-08-16',2,NULL,1,'2026-09-24 09:51:00','Recepción REC-PROV-2026-00018','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(181,'G15-00067',15,1,18,'2026-08-16',4,NULL,1,'2026-09-19 00:38:12','Recepción REC-PROV-2026-00018','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(182,'G15-00068',15,1,18,'2026-08-16',4,NULL,1,'2026-09-19 00:38:12','Recepción REC-PROV-2026-00018','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(183,'G15-00069',15,1,18,'2026-08-16',3,9,1,'2026-09-08 10:42:00','Recepción REC-PROV-2026-00018','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(184,'G15-00070',15,1,18,'2026-08-16',3,1,1,'2026-09-10 10:10:00','Recepción REC-PROV-2026-00018','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(185,'G15-00071',15,1,18,'2026-08-16',3,11,1,'2026-09-11 10:56:00','Recepción REC-PROV-2026-00018','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(186,'G45-00027',45,2,19,'2026-08-19',5,NULL,0,'2026-09-18 09:30:00','Recepción REC-PROV-2026-00019','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(187,'G45-00028',45,2,19,'2026-08-19',2,NULL,1,'2026-09-08 10:43:00','Recepción REC-PROV-2026-00019','2026-09-25 00:38:10','2026-09-25 00:38:11',NULL,NULL,NULL),
(188,'G45-00029',45,2,19,'2026-08-19',5,NULL,0,'2026-09-18 09:30:00','Recepción REC-PROV-2026-00019','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(189,'G10-00089',10,NULL,NULL,'2026-08-20',5,NULL,0,'2026-08-23 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00085)','2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(190,'G10-00090',10,1,20,'2026-08-23',3,10,1,'2026-09-20 11:01:00','Recepción REC-PROV-2026-00020','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(191,'G10-00091',10,1,20,'2026-08-23',1,NULL,1,'2026-08-23 09:30:00','Recepción REC-PROV-2026-00020','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(192,'G10-00092',10,1,20,'2026-08-23',1,NULL,1,'2026-08-23 09:30:00','Recepción REC-PROV-2026-00020','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(193,'G10-00093',10,1,20,'2026-08-23',1,NULL,1,'2026-08-23 09:30:00','Recepción REC-PROV-2026-00020','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(194,'G10-00094',10,1,20,'2026-08-23',1,NULL,1,'2026-08-23 09:30:00','Recepción REC-PROV-2026-00020','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(195,'G10-00095',10,1,20,'2026-08-23',1,NULL,1,'2026-08-23 09:30:00','Recepción REC-PROV-2026-00020','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(196,'G15-00072',15,1,20,'2026-08-23',3,8,1,'2026-09-13 09:21:00','Recepción REC-PROV-2026-00020','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(197,'G15-00073',15,1,20,'2026-08-23',3,17,1,'2026-09-14 10:03:00','Recepción REC-PROV-2026-00020','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(198,'G15-00074',15,1,20,'2026-08-23',3,3,1,'2026-09-17 09:29:00','Recepción REC-PROV-2026-00020','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(199,'G15-00075',15,1,20,'2026-08-23',3,3,1,'2026-09-17 09:29:00','Recepción REC-PROV-2026-00020','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(200,'G15-00076',15,1,20,'2026-08-23',3,19,1,'2026-09-18 11:39:00','Recepción REC-PROV-2026-00020','2026-09-25 00:38:10','2026-09-25 00:38:12',NULL,NULL,NULL),
(201,'G45-00030',45,2,22,'2026-08-29',5,NULL,0,'2026-09-18 09:30:00','Recepción REC-PROV-2026-00022','2026-09-25 00:38:11','2026-09-25 00:38:12',NULL,NULL,NULL),
(202,'G45-00031',45,2,22,'2026-08-29',4,NULL,1,'2026-09-15 00:38:12','Recepción REC-PROV-2026-00022','2026-09-25 00:38:11','2026-09-25 00:38:12',NULL,NULL,NULL),
(203,'G45-00032',45,2,22,'2026-08-29',2,NULL,1,'2026-09-22 10:19:00','Recepción REC-PROV-2026-00022','2026-09-25 00:38:11','2026-09-25 00:38:12',NULL,NULL,NULL),
(204,'G45-00033',45,2,22,'2026-08-29',2,NULL,1,'2026-09-22 10:19:00','Recepción REC-PROV-2026-00022','2026-09-25 00:38:11','2026-09-25 00:38:12',NULL,NULL,NULL),
(205,'G10-00096',10,1,23,'2026-08-30',1,NULL,1,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00023','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(206,'G10-00097',10,1,23,'2026-08-30',1,NULL,1,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00023','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(207,'G10-00098',10,1,23,'2026-08-30',1,NULL,1,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00023','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(208,'G10-00099',10,1,23,'2026-08-30',1,NULL,1,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00023','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(209,'G10-00100',10,1,23,'2026-08-30',1,NULL,1,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00023','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(210,'G10-00101',10,1,23,'2026-08-30',1,NULL,1,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00023','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(211,'G10-00102',10,1,23,'2026-08-30',1,NULL,1,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00023','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(212,'G10-00103',10,1,23,'2026-08-30',1,NULL,1,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00023','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(213,'G15-00077',15,1,23,'2026-08-30',3,1,1,'2026-09-20 10:42:00','Recepción REC-PROV-2026-00023','2026-09-25 00:38:11','2026-09-25 00:38:12',NULL,NULL,NULL),
(214,'G15-00078',15,1,23,'2026-08-30',3,9,1,'2026-09-21 09:15:00','Recepción REC-PROV-2026-00023','2026-09-25 00:38:11','2026-09-25 00:38:12',NULL,NULL,NULL),
(215,'G15-00079',15,1,23,'2026-08-30',3,18,1,'2026-09-21 10:17:00','Recepción REC-PROV-2026-00023','2026-09-25 00:38:11','2026-09-25 00:38:12',NULL,NULL,NULL),
(216,'G15-00080',15,1,23,'2026-08-30',3,17,1,'2026-09-24 09:51:00','Recepción REC-PROV-2026-00023','2026-09-25 00:38:11','2026-09-25 00:38:12',NULL,NULL,NULL),
(217,'G15-00081',15,1,23,'2026-08-30',1,NULL,1,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00023','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(218,'G15-00082',15,1,23,'2026-08-30',1,NULL,1,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00023','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(219,'G15-00083',15,1,23,'2026-08-30',1,NULL,1,'2026-08-30 09:30:00','Recepción REC-PROV-2026-00023','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(220,'G10-00104',10,NULL,NULL,'2026-09-01',5,NULL,0,'2026-09-06 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00105)','2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(221,'G10-00105',10,NULL,NULL,'2026-09-05',5,NULL,0,'2026-09-13 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00110)','2026-09-25 00:38:11','2026-09-25 00:38:12',NULL,NULL,NULL),
(222,'G15-00084',15,NULL,NULL,'2026-09-05',5,NULL,0,'2026-09-06 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00111)','2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(223,'G10-00106',10,1,25,'2026-09-06',1,NULL,1,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00025','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(224,'G10-00107',10,1,25,'2026-09-06',1,NULL,1,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00025','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(225,'G10-00108',10,1,25,'2026-09-06',1,NULL,1,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00025','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(226,'G10-00109',10,1,25,'2026-09-06',1,NULL,1,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00025','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(227,'G10-00110',10,1,25,'2026-09-06',1,NULL,1,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00025','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(228,'G10-00111',10,1,25,'2026-09-06',1,NULL,1,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00025','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(229,'G10-00112',10,1,25,'2026-09-06',1,NULL,1,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00025','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(230,'G10-00113',10,1,25,'2026-09-06',1,NULL,1,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00025','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(231,'G15-00085',15,1,25,'2026-09-06',1,NULL,1,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00025','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(232,'G15-00086',15,1,25,'2026-09-06',1,NULL,1,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00025','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(233,'G15-00087',15,1,25,'2026-09-06',1,NULL,1,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00025','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(234,'G15-00088',15,1,25,'2026-09-06',1,NULL,1,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00025','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(235,'G15-00089',15,1,25,'2026-09-06',1,NULL,1,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00025','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(236,'G15-00090',15,1,25,'2026-09-06',1,NULL,1,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00025','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(237,'G15-00091',15,1,25,'2026-09-06',1,NULL,1,'2026-09-06 09:30:00','Recepción REC-PROV-2026-00025','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(238,'G10-00114',10,NULL,NULL,'2026-09-07',5,NULL,0,'2026-09-13 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00117)','2026-09-25 00:38:11','2026-09-25 00:38:12',NULL,NULL,NULL),
(239,'G45-00034',45,2,26,'2026-09-08',2,NULL,1,'2026-09-14 11:38:00','Recepción REC-PROV-2026-00026','2026-09-25 00:38:11','2026-09-25 00:38:12',NULL,NULL,NULL),
(240,'G45-00035',45,2,26,'2026-09-08',4,NULL,1,'2026-09-17 00:38:12','Recepción REC-PROV-2026-00026','2026-09-25 00:38:11','2026-09-25 00:38:12',NULL,NULL,NULL),
(241,'G45-00036',45,2,26,'2026-09-08',3,21,1,'2026-09-14 11:38:00','Recepción REC-PROV-2026-00026','2026-09-25 00:38:11','2026-09-25 00:38:12',NULL,NULL,NULL),
(242,'G15-00092',15,NULL,NULL,'2026-09-08',5,NULL,0,'2026-09-13 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00118)','2026-09-25 00:38:11','2026-09-25 00:38:12',NULL,NULL,NULL),
(243,'G10-00115',10,1,27,'2026-09-13',1,NULL,1,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00027','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(244,'G10-00116',10,1,27,'2026-09-13',1,NULL,1,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00027','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(245,'G10-00117',10,1,27,'2026-09-13',1,NULL,1,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00027','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(246,'G10-00118',10,1,27,'2026-09-13',1,NULL,1,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00027','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(247,'G10-00119',10,1,27,'2026-09-13',1,NULL,1,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00027','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(248,'G10-00120',10,1,27,'2026-09-13',1,NULL,1,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00027','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(249,'G15-00093',15,1,27,'2026-09-13',1,NULL,1,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00027','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(250,'G15-00094',15,1,27,'2026-09-13',1,NULL,1,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00027','2026-09-25 00:38:11','2026-09-25 03:38:12',NULL,NULL,NULL),
(251,'G15-00095',15,1,27,'2026-09-13',1,NULL,1,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00027','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(252,'G15-00096',15,1,27,'2026-09-13',1,NULL,1,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00027','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(253,'G15-00097',15,1,27,'2026-09-13',1,NULL,1,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00027','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(254,'G15-00098',15,1,27,'2026-09-13',1,NULL,1,'2026-09-13 09:30:00','Recepción REC-PROV-2026-00027','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(255,'G15-00099',15,NULL,NULL,'2026-09-17',5,NULL,0,'2026-09-20 09:30:00','Envase recibido del cliente sin registrar (pedido PED-2026-00131)','2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(256,'G45-00037',45,2,29,'2026-09-18',3,21,1,'2026-09-19 09:05:00','Recepción REC-PROV-2026-00029','2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(257,'G45-00038',45,2,29,'2026-09-18',3,22,1,'2026-09-22 10:19:00','Recepción REC-PROV-2026-00029','2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(258,'G45-00039',45,2,29,'2026-09-18',3,22,1,'2026-09-22 10:19:00','Recepción REC-PROV-2026-00029','2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(259,'G45-00040',45,2,29,'2026-09-18',1,NULL,1,'2026-09-18 09:30:00','Recepción REC-PROV-2026-00029','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(260,'G10-00121',10,1,30,'2026-09-20',1,NULL,1,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00030','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(261,'G10-00122',10,1,30,'2026-09-20',1,NULL,1,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00030','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(262,'G10-00123',10,1,30,'2026-09-20',1,NULL,1,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00030','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(263,'G10-00124',10,1,30,'2026-09-20',1,NULL,1,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00030','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(264,'G10-00125',10,1,30,'2026-09-20',1,NULL,1,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00030','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(265,'G10-00126',10,1,30,'2026-09-20',1,NULL,1,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00030','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(266,'G10-00127',10,1,30,'2026-09-20',1,NULL,1,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00030','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(267,'G10-00128',10,1,30,'2026-09-20',1,NULL,1,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00030','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(268,'G15-00100',15,1,30,'2026-09-20',1,NULL,1,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00030','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(269,'G15-00101',15,1,30,'2026-09-20',1,NULL,1,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00030','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(270,'G15-00102',15,1,30,'2026-09-20',1,NULL,1,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00030','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(271,'G15-00103',15,1,30,'2026-09-20',1,NULL,1,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00030','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(272,'G15-00104',15,1,30,'2026-09-20',1,NULL,1,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00030','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(273,'G15-00105',15,1,30,'2026-09-20',1,NULL,1,'2026-09-20 09:30:00','Recepción REC-PROV-2026-00030','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL);
/*!40000 ALTER TABLE `garrafas` ENABLE KEYS */;
UNLOCK TABLES;
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
DROP TABLE IF EXISTS `medios_contacto_pedido`;
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `medios_contacto_pedido` WRITE;
/*!40000 ALTER TABLE `medios_contacto_pedido` DISABLE KEYS */;
INSERT INTO `medios_contacto_pedido` VALUES
(1,'TELEFONO','Teléfono','Llamada telefónica','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(2,'WHATSAPP','WhatsApp','Mensaje de WhatsApp','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(3,'PRESENCIAL','En el local','El cliente concurre al establecimiento','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(4,'OTRO','Otro','Redes sociales u otro medio','2026-09-25 03:37:54','2026-09-25 03:37:54');
/*!40000 ALTER TABLE `medios_contacto_pedido` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `movimientos_garrafa`;
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
) ENGINE=InnoDB AUTO_INCREMENT=768 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `movimientos_garrafa` WRITE;
/*!40000 ALTER TABLE `movimientos_garrafa` DISABLE KEYS */;
INSERT INTO `movimientos_garrafa` VALUES
(1,1,'2026-06-27 00:00:00',1,NULL,NULL,1,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(2,2,'2026-06-27 00:00:00',1,NULL,NULL,2,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(3,3,'2026-06-27 00:00:00',1,NULL,NULL,3,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(4,4,'2026-06-27 00:00:00',1,NULL,NULL,4,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(5,5,'2026-06-27 00:00:00',1,NULL,NULL,5,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(6,6,'2026-06-27 00:00:00',1,NULL,NULL,6,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(7,7,'2026-06-27 00:00:00',1,NULL,NULL,7,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(8,8,'2026-06-27 00:00:00',1,NULL,NULL,8,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(9,9,'2026-06-27 00:00:00',1,NULL,NULL,9,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(10,10,'2026-06-27 00:00:00',1,NULL,NULL,10,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(11,11,'2026-06-27 00:00:00',1,NULL,NULL,11,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(12,12,'2026-06-27 00:00:00',1,NULL,NULL,12,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(13,13,'2026-06-27 00:00:00',1,NULL,NULL,13,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(14,14,'2026-06-27 00:00:00',1,NULL,NULL,14,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(15,15,'2026-06-27 00:00:00',1,NULL,NULL,15,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(16,16,'2026-06-27 00:00:00',1,NULL,NULL,16,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(17,17,'2026-06-27 00:00:00',1,NULL,NULL,17,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(18,18,'2026-06-27 00:00:00',1,NULL,NULL,18,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(19,19,'2026-06-27 00:00:00',1,NULL,NULL,19,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(20,20,'2026-06-27 00:00:00',1,NULL,NULL,20,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(21,21,'2026-06-27 00:00:00',1,NULL,NULL,21,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(22,22,'2026-06-27 00:00:00',1,NULL,NULL,21,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(23,23,'2026-06-27 00:00:00',1,NULL,NULL,22,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(24,24,'2026-06-27 00:00:00',1,NULL,NULL,22,NULL,3,NULL,'Envase en poder del cliente al iniciar','2026-09-25 00:38:08',NULL),
(25,25,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(26,26,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(27,27,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(28,28,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(29,29,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(30,30,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(31,31,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(32,32,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(33,33,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(34,34,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(35,35,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(36,36,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(37,37,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(38,38,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(39,39,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(40,40,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(41,41,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(42,42,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(43,43,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(44,44,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(45,45,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(46,46,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(47,47,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(48,48,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(49,49,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(50,50,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(51,51,'2026-06-27 00:00:00',1,NULL,NULL,NULL,NULL,1,1,'Parque inicial','2026-09-25 00:38:08',NULL),
(52,52,'2026-06-28 09:30:00',1,NULL,1,NULL,NULL,1,1,'Recepción REC-PROV-2026-00001','2026-09-25 00:38:08',NULL),
(53,53,'2026-06-28 09:30:00',1,NULL,1,NULL,NULL,1,1,'Recepción REC-PROV-2026-00001','2026-09-25 00:38:08',NULL),
(54,54,'2026-06-28 09:30:00',1,NULL,1,NULL,NULL,1,1,'Recepción REC-PROV-2026-00001','2026-09-25 00:38:08',NULL),
(55,55,'2026-06-28 09:30:00',1,NULL,1,NULL,NULL,1,1,'Recepción REC-PROV-2026-00001','2026-09-25 00:38:08',NULL),
(56,56,'2026-06-28 09:30:00',1,NULL,1,NULL,NULL,1,1,'Recepción REC-PROV-2026-00001','2026-09-25 00:38:08',NULL),
(57,57,'2026-06-28 09:30:00',1,NULL,1,NULL,NULL,1,1,'Recepción REC-PROV-2026-00001','2026-09-25 00:38:08',NULL),
(58,58,'2026-06-28 09:30:00',1,NULL,1,NULL,NULL,1,1,'Recepción REC-PROV-2026-00001','2026-09-25 00:38:08',NULL),
(59,59,'2026-06-28 09:30:00',1,NULL,1,NULL,NULL,1,1,'Recepción REC-PROV-2026-00001','2026-09-25 00:38:08',NULL),
(60,60,'2026-06-28 09:30:00',1,NULL,1,NULL,NULL,1,1,'Recepción REC-PROV-2026-00001','2026-09-25 00:38:08',NULL),
(61,61,'2026-06-28 09:30:00',1,NULL,1,NULL,NULL,1,1,'Recepción REC-PROV-2026-00001','2026-09-25 00:38:08',NULL),
(62,62,'2026-06-28 09:30:00',1,NULL,1,NULL,NULL,1,1,'Recepción REC-PROV-2026-00001','2026-09-25 00:38:08',NULL),
(63,63,'2026-06-28 09:30:00',1,NULL,1,NULL,NULL,1,1,'Recepción REC-PROV-2026-00001','2026-09-25 00:38:08',NULL),
(64,64,'2026-06-28 09:30:00',1,NULL,1,NULL,NULL,1,1,'Recepción REC-PROV-2026-00001','2026-09-25 00:38:08',NULL),
(65,65,'2026-06-28 09:30:00',1,NULL,1,NULL,NULL,1,1,'Recepción REC-PROV-2026-00001','2026-09-25 00:38:08',NULL),
(66,66,'2026-06-28 09:30:00',1,NULL,1,NULL,NULL,1,1,'Recepción REC-PROV-2026-00001','2026-09-25 00:38:08',NULL),
(67,25,'2026-06-28 09:39:00',2,1,NULL,12,1,3,2,NULL,'2026-09-25 00:38:08',NULL),
(68,26,'2026-06-28 09:39:00',2,1,NULL,12,1,3,2,NULL,'2026-09-25 00:38:08',NULL),
(69,12,'2026-06-28 09:39:00',3,1,NULL,12,3,2,2,NULL,'2026-09-25 00:38:08',NULL),
(70,67,'2026-06-28 09:39:00',1,1,NULL,12,NULL,2,2,'Envase recibido del cliente sin registrar (pedido PED-2026-00001)','2026-09-25 00:38:08',NULL),
(71,27,'2026-06-28 10:28:00',2,2,NULL,20,1,3,1,NULL,'2026-09-25 00:38:08',NULL),
(72,20,'2026-06-28 10:28:00',3,2,NULL,20,3,2,1,NULL,'2026-09-25 00:38:08',NULL),
(73,39,'2026-06-29 10:02:00',2,3,NULL,3,1,3,3,NULL,'2026-09-25 00:38:08',NULL),
(74,3,'2026-06-29 10:02:00',3,3,NULL,3,3,2,3,NULL,'2026-09-25 00:38:08',NULL),
(75,40,'2026-06-29 09:53:00',2,4,NULL,17,1,3,1,NULL,'2026-09-25 00:38:08',NULL),
(76,41,'2026-06-29 09:53:00',2,4,NULL,17,1,3,1,NULL,'2026-09-25 00:38:08',NULL),
(77,17,'2026-06-29 09:53:00',3,4,NULL,17,3,2,1,NULL,'2026-09-25 00:38:08',NULL),
(78,68,'2026-06-29 09:53:00',1,4,NULL,17,NULL,2,1,'Envase recibido del cliente sin registrar (pedido PED-2026-00004)','2026-09-25 00:38:08',NULL),
(79,69,'2026-06-30 09:30:00',1,NULL,2,NULL,NULL,1,1,'Recepción REC-PROV-2026-00002','2026-09-25 00:38:08',NULL),
(80,70,'2026-06-30 09:30:00',1,NULL,2,NULL,NULL,1,1,'Recepción REC-PROV-2026-00002','2026-09-25 00:38:08',NULL),
(81,71,'2026-06-30 09:30:00',1,NULL,2,NULL,NULL,1,1,'Recepción REC-PROV-2026-00002','2026-09-25 00:38:08',NULL),
(82,28,'2026-06-30 10:44:00',2,5,NULL,4,1,3,2,NULL,'2026-09-25 00:38:08',NULL),
(83,4,'2026-06-30 10:44:00',3,5,NULL,4,3,2,2,NULL,'2026-09-25 00:38:08',NULL),
(84,29,'2026-06-30 09:37:00',2,6,NULL,5,1,3,1,NULL,'2026-09-25 00:38:08',NULL),
(85,5,'2026-06-30 09:37:00',3,6,NULL,5,3,2,1,NULL,'2026-09-25 00:38:08',NULL),
(86,42,'2026-06-30 11:27:00',2,7,NULL,18,1,3,3,NULL,'2026-09-25 00:38:08',NULL),
(87,18,'2026-06-30 11:27:00',3,7,NULL,18,3,2,3,NULL,'2026-09-25 00:38:08',NULL),
(88,43,'2026-07-01 09:58:00',2,8,NULL,9,1,3,2,NULL,'2026-09-25 00:38:08',NULL),
(89,9,'2026-07-01 09:58:00',3,8,NULL,9,3,2,2,NULL,'2026-09-25 00:38:08',NULL),
(90,48,'2026-07-02 09:38:00',2,9,NULL,22,1,3,1,NULL,'2026-09-25 00:38:08',NULL),
(91,49,'2026-07-02 09:38:00',2,9,NULL,22,1,3,1,NULL,'2026-09-25 00:38:08',NULL),
(92,23,'2026-07-02 09:38:00',3,9,NULL,22,3,2,1,NULL,'2026-09-25 00:38:08',NULL),
(93,24,'2026-07-02 09:38:00',3,9,NULL,22,3,2,1,NULL,'2026-09-25 00:38:08',NULL),
(94,50,'2026-07-03 09:41:00',2,10,NULL,21,1,3,1,NULL,'2026-09-25 00:38:08',NULL),
(95,51,'2026-07-03 09:41:00',2,10,NULL,21,1,3,1,NULL,'2026-09-25 00:38:08',NULL),
(96,21,'2026-07-03 09:41:00',3,10,NULL,21,3,2,1,NULL,'2026-09-25 00:38:08',NULL),
(97,22,'2026-07-03 09:41:00',3,10,NULL,21,3,2,1,NULL,'2026-09-25 00:38:08',NULL),
(98,72,'2026-07-05 09:30:00',1,NULL,5,NULL,NULL,1,1,'Recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(99,73,'2026-07-05 09:30:00',1,NULL,5,NULL,NULL,1,1,'Recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(100,74,'2026-07-05 09:30:00',1,NULL,5,NULL,NULL,1,1,'Recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(101,75,'2026-07-05 09:30:00',1,NULL,5,NULL,NULL,1,1,'Recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(102,76,'2026-07-05 09:30:00',1,NULL,5,NULL,NULL,1,1,'Recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(103,77,'2026-07-05 09:30:00',1,NULL,5,NULL,NULL,1,1,'Recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(104,78,'2026-07-05 09:30:00',1,NULL,5,NULL,NULL,1,1,'Recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(105,79,'2026-07-05 09:30:00',1,NULL,5,NULL,NULL,1,1,'Recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(106,80,'2026-07-05 09:30:00',1,NULL,5,NULL,NULL,1,1,'Recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(107,81,'2026-07-05 09:30:00',1,NULL,5,NULL,NULL,1,1,'Recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(108,82,'2026-07-05 09:30:00',1,NULL,5,NULL,NULL,1,1,'Recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(109,12,'2026-07-05 09:30:00',4,NULL,5,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(110,67,'2026-07-05 09:30:00',4,NULL,5,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(111,20,'2026-07-05 09:30:00',4,NULL,5,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(112,5,'2026-07-05 09:30:00',4,NULL,5,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(113,4,'2026-07-05 09:30:00',4,NULL,5,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(114,17,'2026-07-05 09:30:00',4,NULL,5,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(115,68,'2026-07-05 09:30:00',4,NULL,5,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(116,3,'2026-07-05 09:30:00',4,NULL,5,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(117,18,'2026-07-05 09:30:00',4,NULL,5,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(118,9,'2026-07-05 09:30:00',4,NULL,5,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00005','2026-09-25 00:38:08',NULL),
(119,30,'2026-07-05 09:39:00',2,11,NULL,6,1,3,2,NULL,'2026-09-25 00:38:08',NULL),
(120,6,'2026-07-05 09:39:00',3,11,NULL,6,3,2,2,NULL,'2026-09-25 00:38:08',NULL),
(121,44,'2026-07-05 11:06:00',2,12,NULL,19,1,3,3,NULL,'2026-09-25 00:38:08',NULL),
(122,45,'2026-07-05 11:06:00',2,12,NULL,19,1,3,3,NULL,'2026-09-25 00:38:08',NULL),
(123,19,'2026-07-05 11:06:00',3,12,NULL,19,3,2,3,NULL,'2026-09-25 00:38:08',NULL),
(124,83,'2026-07-05 11:06:00',1,12,NULL,19,NULL,2,3,'Envase recibido del cliente sin registrar (pedido PED-2026-00012)','2026-09-25 00:38:08',NULL),
(125,46,'2026-07-06 10:03:00',2,13,NULL,1,1,3,3,NULL,'2026-09-25 00:38:08',NULL),
(126,47,'2026-07-06 10:03:00',2,13,NULL,1,1,3,3,NULL,'2026-09-25 00:38:08',NULL),
(127,1,'2026-07-06 10:03:00',3,13,NULL,1,3,2,3,NULL,'2026-09-25 00:38:08',NULL),
(128,84,'2026-07-06 10:03:00',1,13,NULL,1,NULL,2,3,'Envase recibido del cliente sin registrar (pedido PED-2026-00013)','2026-09-25 00:38:08',NULL),
(129,31,'2026-07-06 10:18:00',2,14,NULL,10,1,3,2,NULL,'2026-09-25 00:38:08',NULL),
(130,10,'2026-07-06 10:18:00',3,14,NULL,10,3,2,2,NULL,'2026-09-25 00:38:08',NULL),
(131,60,'2026-07-06 11:03:00',2,15,NULL,11,1,3,3,NULL,'2026-09-25 00:38:08',NULL),
(132,61,'2026-07-06 11:03:00',2,15,NULL,11,1,3,3,NULL,'2026-09-25 00:38:08',NULL),
(133,11,'2026-07-06 11:03:00',3,15,NULL,11,3,2,3,NULL,'2026-09-25 00:38:08',NULL),
(134,85,'2026-07-06 11:03:00',1,15,NULL,11,NULL,2,3,'Envase recibido del cliente sin registrar (pedido PED-2026-00015)','2026-09-25 00:38:08',NULL),
(135,62,'2026-07-06 12:31:00',2,16,NULL,17,1,3,2,NULL,'2026-09-25 00:38:08',NULL),
(136,40,'2026-07-06 12:31:00',3,16,NULL,17,3,2,2,NULL,'2026-09-25 00:38:08',NULL),
(137,63,'2026-07-07 09:21:00',2,17,NULL,14,1,3,1,NULL,'2026-09-25 00:38:08',NULL),
(138,14,'2026-07-07 09:21:00',3,17,NULL,14,3,2,1,NULL,'2026-09-25 00:38:08',NULL),
(139,32,'2026-07-07 10:36:00',2,18,NULL,15,1,3,1,NULL,'2026-09-25 00:38:08',NULL),
(140,15,'2026-07-07 10:36:00',3,18,NULL,15,3,2,1,NULL,'2026-09-25 00:38:08',NULL),
(141,69,'2026-07-09 09:30:00',2,19,NULL,21,1,3,2,NULL,'2026-09-25 00:38:08',NULL),
(142,70,'2026-07-09 09:30:00',2,19,NULL,21,1,3,2,NULL,'2026-09-25 00:38:08',NULL),
(143,50,'2026-07-09 09:30:00',3,19,NULL,21,3,2,2,NULL,'2026-09-25 00:38:08',NULL),
(144,51,'2026-07-09 09:30:00',3,19,NULL,21,3,2,2,NULL,'2026-09-25 00:38:08',NULL),
(145,86,'2026-07-10 09:30:00',1,NULL,6,NULL,NULL,1,1,'Recepción REC-PROV-2026-00006','2026-09-25 00:38:08',NULL),
(146,87,'2026-07-10 09:30:00',1,NULL,6,NULL,NULL,1,1,'Recepción REC-PROV-2026-00006','2026-09-25 00:38:08',NULL),
(147,88,'2026-07-10 09:30:00',1,NULL,6,NULL,NULL,1,1,'Recepción REC-PROV-2026-00006','2026-09-25 00:38:08',NULL),
(148,23,'2026-07-10 09:30:00',4,NULL,6,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00006','2026-09-25 00:38:08',NULL),
(149,24,'2026-07-10 09:30:00',4,NULL,6,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00006','2026-09-25 00:38:08',NULL),
(150,21,'2026-07-10 09:30:00',4,NULL,6,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00006','2026-09-25 00:38:08',NULL),
(151,33,'2026-07-10 08:56:00',2,20,NULL,13,1,3,2,NULL,'2026-09-25 00:38:08',NULL),
(152,13,'2026-07-10 08:56:00',3,20,NULL,13,3,2,2,NULL,'2026-09-25 00:38:08',NULL),
(153,89,'2026-07-12 09:30:00',1,NULL,7,NULL,NULL,1,1,'Recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(154,90,'2026-07-12 09:30:00',1,NULL,7,NULL,NULL,1,1,'Recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(155,91,'2026-07-12 09:30:00',1,NULL,7,NULL,NULL,1,1,'Recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(156,92,'2026-07-12 09:30:00',1,NULL,7,NULL,NULL,1,1,'Recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(157,93,'2026-07-12 09:30:00',1,NULL,7,NULL,NULL,1,1,'Recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(158,94,'2026-07-12 09:30:00',1,NULL,7,NULL,NULL,1,1,'Recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(159,95,'2026-07-12 09:30:00',1,NULL,7,NULL,NULL,1,1,'Recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(160,96,'2026-07-12 09:30:00',1,NULL,7,NULL,NULL,1,1,'Recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(161,97,'2026-07-12 09:30:00',1,NULL,7,NULL,NULL,1,1,'Recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(162,98,'2026-07-12 09:30:00',1,NULL,7,NULL,NULL,1,1,'Recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(163,99,'2026-07-12 09:30:00',1,NULL,7,NULL,NULL,1,1,'Recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(164,100,'2026-07-12 09:30:00',1,NULL,7,NULL,NULL,1,1,'Recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(165,101,'2026-07-12 09:30:00',1,NULL,7,NULL,NULL,1,1,'Recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(166,6,'2026-07-12 09:30:00',4,NULL,7,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(167,10,'2026-07-12 09:30:00',4,NULL,7,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(168,15,'2026-07-12 09:30:00',4,NULL,7,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(169,13,'2026-07-12 09:30:00',4,NULL,7,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(170,19,'2026-07-12 09:30:00',4,NULL,7,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(171,83,'2026-07-12 09:30:00',4,NULL,7,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(172,1,'2026-07-12 09:30:00',4,NULL,7,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(173,84,'2026-07-12 09:30:00',4,NULL,7,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(174,11,'2026-07-12 09:30:00',4,NULL,7,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(175,85,'2026-07-12 09:30:00',4,NULL,7,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(176,40,'2026-07-12 09:30:00',4,NULL,7,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00007','2026-09-25 00:38:08',NULL),
(177,34,'2026-07-12 08:56:00',2,22,NULL,5,1,3,2,NULL,'2026-09-25 00:38:08',NULL),
(178,29,'2026-07-12 08:56:00',3,22,NULL,5,3,2,2,NULL,'2026-09-25 00:38:08',NULL),
(179,64,'2026-07-12 10:52:00',2,23,NULL,8,1,3,1,NULL,'2026-09-25 00:38:08',NULL),
(180,65,'2026-07-12 10:52:00',2,23,NULL,8,1,3,1,NULL,'2026-09-25 00:38:08',NULL),
(181,8,'2026-07-12 10:52:00',3,23,NULL,8,3,2,1,NULL,'2026-09-25 00:38:08',NULL),
(182,102,'2026-07-12 10:52:00',1,23,NULL,8,NULL,2,1,'Envase recibido del cliente sin registrar (pedido PED-2026-00023)','2026-09-25 00:38:08',NULL),
(183,35,'2026-07-13 10:05:00',2,24,NULL,2,1,3,2,NULL,'2026-09-25 00:38:08',NULL),
(184,36,'2026-07-13 10:05:00',2,24,NULL,2,1,3,2,NULL,'2026-09-25 00:38:08',NULL),
(185,2,'2026-07-13 10:05:00',3,24,NULL,2,3,2,2,NULL,'2026-09-25 00:38:08',NULL),
(186,103,'2026-07-13 10:05:00',1,24,NULL,2,NULL,2,2,'Envase recibido del cliente sin registrar (pedido PED-2026-00024)','2026-09-25 00:38:08',NULL),
(187,37,'2026-07-13 11:33:00',2,25,NULL,7,1,3,2,NULL,'2026-09-25 00:38:08',NULL),
(188,7,'2026-07-13 11:33:00',3,25,NULL,7,3,2,2,NULL,'2026-09-25 00:38:09',NULL),
(189,66,'2026-07-13 12:23:00',2,26,NULL,17,1,3,2,NULL,'2026-09-25 00:38:09',NULL),
(190,41,'2026-07-13 12:23:00',3,26,NULL,17,3,2,2,NULL,'2026-09-25 00:38:09',NULL),
(191,38,'2026-07-15 09:57:00',2,27,NULL,20,1,3,1,NULL,'2026-09-25 00:38:09',NULL),
(192,27,'2026-07-15 09:57:00',3,27,NULL,20,3,2,1,NULL,'2026-09-25 00:38:09',NULL),
(193,52,'2026-07-16 09:51:00',2,28,NULL,10,1,3,1,NULL,'2026-09-25 00:38:09',NULL),
(194,31,'2026-07-16 09:51:00',3,28,NULL,10,3,2,1,NULL,'2026-09-25 00:38:09',NULL),
(195,53,'2026-07-16 09:47:00',2,29,NULL,16,1,3,3,NULL,'2026-09-25 00:38:09',NULL),
(196,54,'2026-07-16 09:47:00',2,29,NULL,16,1,3,3,NULL,'2026-09-25 00:38:09',NULL),
(197,16,'2026-07-16 09:47:00',3,29,NULL,16,3,2,3,NULL,'2026-09-25 00:38:09',NULL),
(198,104,'2026-07-16 09:47:00',1,29,NULL,16,NULL,2,3,'Envase recibido del cliente sin registrar (pedido PED-2026-00029)','2026-09-25 00:38:09',NULL),
(199,71,'2026-07-16 13:10:00',2,31,NULL,21,1,3,2,NULL,'2026-09-25 00:38:09',NULL),
(200,86,'2026-07-16 13:10:00',2,31,NULL,21,1,3,2,NULL,'2026-09-25 00:38:09',NULL),
(201,69,'2026-07-16 13:10:00',3,31,NULL,21,3,2,2,NULL,'2026-09-25 00:38:09',NULL),
(202,70,'2026-07-16 13:10:00',3,31,NULL,21,3,2,2,NULL,'2026-09-25 00:38:09',NULL),
(203,78,'2026-07-17 10:31:00',2,32,NULL,9,1,3,1,NULL,'2026-09-25 00:38:09',NULL),
(204,43,'2026-07-17 10:31:00',3,32,NULL,9,3,2,1,NULL,'2026-09-25 00:38:09',NULL),
(205,55,'2026-07-17 10:08:00',2,33,NULL,12,1,3,1,NULL,'2026-09-25 00:38:09',NULL),
(206,25,'2026-07-17 10:08:00',3,33,NULL,12,3,2,1,NULL,'2026-09-25 00:38:09',NULL),
(207,87,'2026-07-17 12:40:00',2,34,NULL,22,1,3,1,NULL,'2026-09-25 00:38:09',NULL),
(208,48,'2026-07-17 12:40:00',3,34,NULL,22,3,2,1,NULL,'2026-09-25 00:38:09',NULL),
(209,56,'2026-07-18 10:27:00',2,35,NULL,4,1,3,1,NULL,'2026-09-25 00:38:09',NULL),
(210,28,'2026-07-18 10:27:00',3,35,NULL,4,3,2,1,NULL,'2026-09-25 00:38:09',NULL),
(211,79,'2026-07-18 10:26:00',2,36,NULL,18,1,3,1,NULL,'2026-09-25 00:38:09',NULL),
(212,42,'2026-07-18 10:26:00',3,36,NULL,18,3,2,1,NULL,'2026-09-25 00:38:09',NULL),
(213,105,'2026-07-19 09:30:00',1,NULL,8,NULL,NULL,1,1,'Recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(214,106,'2026-07-19 09:30:00',1,NULL,8,NULL,NULL,1,1,'Recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(215,107,'2026-07-19 09:30:00',1,NULL,8,NULL,NULL,1,1,'Recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(216,108,'2026-07-19 09:30:00',1,NULL,8,NULL,NULL,1,1,'Recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(217,109,'2026-07-19 09:30:00',1,NULL,8,NULL,NULL,1,1,'Recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(218,110,'2026-07-19 09:30:00',1,NULL,8,NULL,NULL,1,1,'Recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(219,111,'2026-07-19 09:30:00',1,NULL,8,NULL,NULL,1,1,'Recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(220,112,'2026-07-19 09:30:00',1,NULL,8,NULL,NULL,1,1,'Recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(221,113,'2026-07-19 09:30:00',1,NULL,8,NULL,NULL,1,1,'Recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(222,114,'2026-07-19 09:30:00',1,NULL,8,NULL,NULL,1,1,'Recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(223,115,'2026-07-19 09:30:00',1,NULL,8,NULL,NULL,1,1,'Recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(224,116,'2026-07-19 09:30:00',1,NULL,8,NULL,NULL,1,1,'Recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(225,29,'2026-07-19 09:30:00',4,NULL,8,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(226,2,'2026-07-19 09:30:00',4,NULL,8,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(227,103,'2026-07-19 09:30:00',4,NULL,8,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(228,7,'2026-07-19 09:30:00',4,NULL,8,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(229,27,'2026-07-19 09:30:00',4,NULL,8,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(230,16,'2026-07-19 09:30:00',4,NULL,8,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(231,104,'2026-07-19 09:30:00',4,NULL,8,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(232,14,'2026-07-19 09:30:00',4,NULL,8,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(233,8,'2026-07-19 09:30:00',4,NULL,8,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(234,102,'2026-07-19 09:30:00',4,NULL,8,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(235,41,'2026-07-19 09:30:00',4,NULL,8,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(236,43,'2026-07-19 09:30:00',4,NULL,8,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00008','2026-09-25 00:38:09',NULL),
(237,57,'2026-07-19 09:29:00',2,37,NULL,15,1,3,1,NULL,'2026-09-25 00:38:09',NULL),
(238,58,'2026-07-19 09:29:00',2,37,NULL,15,1,3,1,NULL,'2026-09-25 00:38:09',NULL),
(239,32,'2026-07-19 09:29:00',3,37,NULL,15,3,2,1,NULL,'2026-09-25 00:38:09',NULL),
(240,117,'2026-07-19 09:29:00',1,37,NULL,15,NULL,2,1,'Envase recibido del cliente sin registrar (pedido PED-2026-00037)','2026-09-25 00:38:09',NULL),
(241,118,'2026-07-20 09:30:00',1,NULL,9,NULL,NULL,1,1,'Recepción REC-PROV-2026-00009','2026-09-25 00:38:09',NULL),
(242,119,'2026-07-20 09:30:00',1,NULL,9,NULL,NULL,1,1,'Recepción REC-PROV-2026-00009','2026-09-25 00:38:09',NULL),
(243,120,'2026-07-20 09:30:00',1,NULL,9,NULL,NULL,1,1,'Recepción REC-PROV-2026-00009','2026-09-25 00:38:09',NULL),
(244,121,'2026-07-20 09:30:00',1,NULL,9,NULL,NULL,1,1,'Recepción REC-PROV-2026-00009','2026-09-25 00:38:09',NULL),
(245,22,'2026-07-20 09:30:00',4,NULL,9,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00009','2026-09-25 00:38:09',NULL),
(246,50,'2026-07-20 09:30:00',4,NULL,9,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00009','2026-09-25 00:38:09',NULL),
(247,51,'2026-07-20 09:30:00',4,NULL,9,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00009','2026-09-25 00:38:09',NULL),
(248,69,'2026-07-20 09:30:00',4,NULL,9,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00009','2026-09-25 00:38:09',NULL),
(249,80,'2026-07-20 09:51:00',2,38,NULL,14,1,3,3,NULL,'2026-09-25 00:38:09',NULL),
(250,63,'2026-07-20 09:51:00',3,38,NULL,14,3,2,3,NULL,'2026-09-25 00:38:09',NULL),
(251,81,'2026-07-21 09:48:00',2,39,NULL,1,1,3,1,NULL,'2026-09-25 00:38:09',NULL),
(252,82,'2026-07-21 09:48:00',2,39,NULL,1,1,3,1,NULL,'2026-09-25 00:38:09',NULL),
(253,46,'2026-07-21 09:48:00',3,39,NULL,1,3,2,1,NULL,'2026-09-25 00:38:09',NULL),
(254,47,'2026-07-21 09:48:00',3,39,NULL,1,3,2,1,NULL,'2026-09-25 00:38:09',NULL),
(255,59,'2026-07-24 10:06:00',2,40,NULL,5,1,3,3,NULL,'2026-09-25 00:38:09',NULL),
(256,34,'2026-07-24 10:06:00',3,40,NULL,5,3,2,3,NULL,'2026-09-25 00:38:09',NULL),
(257,72,'2026-07-24 10:42:00',2,41,NULL,13,1,3,3,NULL,'2026-09-25 00:38:09',NULL),
(258,73,'2026-07-24 10:42:00',2,41,NULL,13,1,3,3,NULL,'2026-09-25 00:38:09',NULL),
(259,33,'2026-07-24 10:42:00',3,41,NULL,13,3,2,3,NULL,'2026-09-25 00:38:09',NULL),
(260,122,'2026-07-24 10:42:00',1,41,NULL,13,NULL,2,3,'Envase recibido del cliente sin registrar (pedido PED-2026-00041)','2026-09-25 00:38:09',NULL),
(261,88,'2026-07-24 12:26:00',2,42,NULL,21,1,3,2,NULL,'2026-09-25 00:38:09',NULL),
(262,71,'2026-07-24 12:26:00',3,42,NULL,21,3,2,2,NULL,'2026-09-25 00:38:09',NULL),
(263,95,'2026-07-25 09:52:00',2,43,NULL,11,1,3,3,NULL,'2026-09-25 00:38:09',NULL),
(264,60,'2026-07-25 09:52:00',3,43,NULL,11,3,2,3,NULL,'2026-09-25 00:38:09',NULL),
(265,96,'2026-07-25 10:53:00',2,44,NULL,17,1,3,2,NULL,'2026-09-25 00:38:09',NULL),
(266,62,'2026-07-25 10:53:00',3,44,NULL,17,3,2,2,NULL,'2026-09-25 00:38:09',NULL),
(267,118,'2026-07-25 11:12:00',2,45,NULL,22,1,3,1,NULL,'2026-09-25 00:38:09',NULL),
(268,119,'2026-07-25 11:12:00',2,45,NULL,22,1,3,1,NULL,'2026-09-25 00:38:09',NULL),
(269,49,'2026-07-25 11:12:00',3,45,NULL,22,3,2,1,NULL,'2026-09-25 00:38:09',NULL),
(270,87,'2026-07-25 11:12:00',3,45,NULL,22,3,2,1,NULL,'2026-09-25 00:38:09',NULL),
(271,123,'2026-07-26 09:30:00',1,NULL,11,NULL,NULL,1,1,'Recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(272,124,'2026-07-26 09:30:00',1,NULL,11,NULL,NULL,1,1,'Recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(273,125,'2026-07-26 09:30:00',1,NULL,11,NULL,NULL,1,1,'Recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(274,126,'2026-07-26 09:30:00',1,NULL,11,NULL,NULL,1,1,'Recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(275,127,'2026-07-26 09:30:00',1,NULL,11,NULL,NULL,1,1,'Recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(276,128,'2026-07-26 09:30:00',1,NULL,11,NULL,NULL,1,1,'Recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(277,129,'2026-07-26 09:30:00',1,NULL,11,NULL,NULL,1,1,'Recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(278,130,'2026-07-26 09:30:00',1,NULL,11,NULL,NULL,1,1,'Recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(279,131,'2026-07-26 09:30:00',1,NULL,11,NULL,NULL,1,1,'Recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(280,132,'2026-07-26 09:30:00',1,NULL,11,NULL,NULL,1,1,'Recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(281,133,'2026-07-26 09:30:00',1,NULL,11,NULL,NULL,1,1,'Recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(282,134,'2026-07-26 09:30:00',1,NULL,11,NULL,NULL,1,1,'Recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(283,135,'2026-07-26 09:30:00',1,NULL,11,NULL,NULL,1,1,'Recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(284,136,'2026-07-26 09:30:00',1,NULL,11,NULL,NULL,1,1,'Recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(285,31,'2026-07-26 09:30:00',4,NULL,11,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(286,25,'2026-07-26 09:30:00',4,NULL,11,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(287,28,'2026-07-26 09:30:00',4,NULL,11,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(288,32,'2026-07-26 09:30:00',4,NULL,11,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(289,117,'2026-07-26 09:30:00',4,NULL,11,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(290,34,'2026-07-26 09:30:00',4,NULL,11,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(291,33,'2026-07-26 09:30:00',4,NULL,11,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(292,42,'2026-07-26 09:30:00',4,NULL,11,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(293,63,'2026-07-26 09:30:00',4,NULL,11,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(294,46,'2026-07-26 09:30:00',4,NULL,11,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(295,47,'2026-07-26 09:30:00',4,NULL,11,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(296,60,'2026-07-26 09:30:00',4,NULL,11,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(297,62,'2026-07-26 09:30:00',4,NULL,11,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00011','2026-09-25 00:38:09',NULL),
(298,97,'2026-07-26 09:30:00',2,46,NULL,3,1,3,1,NULL,'2026-09-25 00:38:09',NULL),
(299,39,'2026-07-26 09:30:00',3,46,NULL,3,3,2,1,NULL,'2026-09-25 00:38:09',NULL),
(300,74,'2026-07-26 11:31:00',2,47,NULL,6,1,3,3,NULL,'2026-09-25 00:38:09',NULL),
(301,30,'2026-07-26 11:31:00',3,47,NULL,6,3,2,3,NULL,'2026-09-25 00:38:09',NULL),
(302,75,'2026-07-26 11:10:00',2,48,NULL,10,1,3,1,NULL,'2026-09-25 00:38:09',NULL),
(303,52,'2026-07-26 11:10:00',3,48,NULL,10,3,2,1,NULL,'2026-09-25 00:38:09',NULL),
(304,98,'2026-07-27 10:28:00',2,49,NULL,19,1,3,2,NULL,'2026-09-25 00:38:09',NULL),
(305,44,'2026-07-27 10:28:00',3,49,NULL,19,3,2,2,NULL,'2026-09-25 00:38:09',NULL),
(306,76,'2026-07-29 09:27:00',2,50,NULL,7,1,3,1,NULL,'2026-09-25 00:38:09',NULL),
(307,37,'2026-07-29 09:27:00',3,50,NULL,7,3,2,1,NULL,'2026-09-25 00:38:09',NULL),
(308,99,'2026-07-29 10:06:00',2,51,NULL,9,1,3,2,NULL,'2026-09-25 00:38:09',NULL),
(309,78,'2026-07-29 10:06:00',3,51,NULL,9,3,2,2,NULL,'2026-09-25 00:38:09',NULL),
(310,137,'2026-07-30 09:30:00',1,NULL,13,NULL,NULL,1,1,'Recepción REC-PROV-2026-00013','2026-09-25 00:38:09',NULL),
(311,138,'2026-07-30 09:30:00',1,NULL,13,NULL,NULL,1,1,'Recepción REC-PROV-2026-00013','2026-09-25 00:38:09',NULL),
(312,139,'2026-07-30 09:30:00',1,NULL,13,NULL,NULL,1,1,'Recepción REC-PROV-2026-00013','2026-09-25 00:38:09',NULL),
(313,140,'2026-07-30 09:30:00',1,NULL,13,NULL,NULL,1,1,'Recepción REC-PROV-2026-00013','2026-09-25 00:38:09',NULL),
(314,70,'2026-07-30 09:30:00',4,NULL,13,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00013','2026-09-25 00:38:09',NULL),
(315,48,'2026-07-30 09:30:00',4,NULL,13,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00013','2026-09-25 00:38:09',NULL),
(316,71,'2026-07-30 09:30:00',4,NULL,13,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00013','2026-09-25 00:38:09',NULL),
(317,49,'2026-07-30 09:30:00',4,NULL,13,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00013','2026-09-25 00:38:09',NULL),
(318,120,'2026-07-30 09:55:00',2,52,NULL,21,1,3,3,NULL,'2026-09-25 00:38:09',NULL),
(319,86,'2026-07-30 09:55:00',3,52,NULL,21,3,2,3,NULL,'2026-09-25 00:38:09',NULL),
(320,121,'2026-07-30 10:46:00',2,53,NULL,22,1,3,2,NULL,'2026-09-25 00:38:09',NULL),
(321,118,'2026-07-30 10:46:00',3,53,NULL,22,3,2,2,NULL,'2026-09-25 00:38:09',NULL),
(322,100,'2026-07-31 09:15:00',2,54,NULL,8,1,3,3,NULL,'2026-09-25 00:38:09',NULL),
(323,64,'2026-07-31 09:15:00',3,54,NULL,8,3,2,3,NULL,'2026-09-25 00:38:09',NULL),
(324,77,'2026-07-31 09:50:00',2,55,NULL,15,1,3,2,NULL,'2026-09-25 00:38:09',NULL),
(325,57,'2026-07-31 09:50:00',3,55,NULL,15,3,2,2,NULL,'2026-09-25 00:38:09',NULL),
(326,89,'2026-08-01 09:46:00',2,56,NULL,4,1,3,1,NULL,'2026-09-25 00:38:09',NULL),
(327,56,'2026-08-01 09:46:00',3,56,NULL,4,3,2,1,NULL,'2026-09-25 00:38:09',NULL),
(328,141,'2026-08-02 09:30:00',1,NULL,14,NULL,NULL,1,1,'Recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(329,142,'2026-08-02 09:30:00',1,NULL,14,NULL,NULL,1,1,'Recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(330,143,'2026-08-02 09:30:00',1,NULL,14,NULL,NULL,1,1,'Recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(331,144,'2026-08-02 09:30:00',1,NULL,14,NULL,NULL,1,1,'Recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(332,145,'2026-08-02 09:30:00',1,NULL,14,NULL,NULL,1,1,'Recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(333,146,'2026-08-02 09:30:00',1,NULL,14,NULL,NULL,1,1,'Recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(334,147,'2026-08-02 09:30:00',1,NULL,14,NULL,NULL,1,1,'Recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(335,148,'2026-08-02 09:30:00',1,NULL,14,NULL,NULL,1,1,'Recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(336,149,'2026-08-02 09:30:00',1,NULL,14,NULL,NULL,1,1,'Recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(337,150,'2026-08-02 09:30:00',1,NULL,14,NULL,NULL,1,1,'Recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(338,151,'2026-08-02 09:30:00',1,NULL,14,NULL,NULL,1,1,'Recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(339,152,'2026-08-02 09:30:00',1,NULL,14,NULL,NULL,1,1,'Recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(340,153,'2026-08-02 09:30:00',1,NULL,14,NULL,NULL,1,1,'Recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(341,122,'2026-08-02 09:30:00',4,NULL,14,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(342,52,'2026-08-02 09:30:00',4,NULL,14,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(343,30,'2026-08-02 09:30:00',4,NULL,14,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(344,37,'2026-08-02 09:30:00',4,NULL,14,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(345,57,'2026-08-02 09:30:00',4,NULL,14,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(346,56,'2026-08-02 09:30:00',4,NULL,14,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(347,39,'2026-08-02 09:30:00',4,NULL,14,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(348,44,'2026-08-02 09:30:00',4,NULL,14,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(349,78,'2026-08-02 09:30:00',4,NULL,14,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(350,64,'2026-08-02 09:30:00',4,NULL,14,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00014','2026-09-25 00:38:09',NULL),
(351,101,'2026-08-02 10:20:00',2,57,NULL,1,1,3,2,NULL,'2026-09-25 00:38:09',NULL),
(352,81,'2026-08-02 10:20:00',3,57,NULL,1,3,2,2,NULL,'2026-09-25 00:38:09',NULL),
(353,112,'2026-08-02 10:28:00',2,58,NULL,14,1,3,1,NULL,'2026-09-25 00:38:10',NULL),
(354,80,'2026-08-02 10:28:00',3,58,NULL,14,3,2,1,NULL,'2026-09-25 00:38:10',NULL),
(355,113,'2026-08-02 12:11:00',2,59,NULL,18,1,3,2,NULL,'2026-09-25 00:38:10',NULL),
(356,79,'2026-08-02 12:11:00',3,59,NULL,18,3,2,2,NULL,'2026-09-25 00:38:10',NULL),
(357,90,'2026-08-03 09:40:00',2,60,NULL,2,1,3,3,NULL,'2026-09-25 00:38:10',NULL),
(358,35,'2026-08-03 09:40:00',3,60,NULL,2,3,2,3,NULL,'2026-09-25 00:38:10',NULL),
(359,91,'2026-08-03 11:27:00',2,61,NULL,20,1,3,3,NULL,'2026-09-25 00:38:10',NULL),
(360,38,'2026-08-03 11:27:00',3,61,NULL,20,3,2,3,NULL,'2026-09-25 00:38:10',NULL),
(361,114,'2026-08-04 09:45:00',2,62,NULL,17,1,3,2,NULL,'2026-09-25 00:38:10',NULL),
(362,66,'2026-08-04 09:45:00',3,62,NULL,17,3,2,2,NULL,'2026-09-25 00:38:10',NULL),
(363,92,'2026-08-05 09:36:00',2,63,NULL,5,1,3,2,NULL,'2026-09-25 00:38:10',NULL),
(364,59,'2026-08-05 09:36:00',3,63,NULL,5,3,2,2,NULL,'2026-09-25 00:38:10',NULL),
(365,93,'2026-08-05 11:11:00',2,64,NULL,12,1,3,3,NULL,'2026-09-25 00:38:10',NULL),
(366,26,'2026-08-05 11:11:00',3,64,NULL,12,3,2,3,NULL,'2026-09-25 00:38:10',NULL),
(367,94,'2026-08-06 09:42:00',2,65,NULL,10,1,3,2,NULL,'2026-09-25 00:38:10',NULL),
(368,75,'2026-08-06 09:42:00',3,65,NULL,10,3,2,2,NULL,'2026-09-25 00:38:10',NULL),
(369,137,'2026-08-07 09:27:00',2,67,NULL,22,1,3,1,NULL,'2026-09-25 00:38:10',NULL),
(370,138,'2026-08-07 09:27:00',2,67,NULL,22,1,3,1,NULL,'2026-09-25 00:38:10',NULL),
(371,119,'2026-08-07 09:27:00',3,67,NULL,22,3,2,1,NULL,'2026-09-25 00:38:10',NULL),
(372,121,'2026-08-07 09:27:00',3,67,NULL,22,3,2,1,NULL,'2026-09-25 00:38:10',NULL),
(373,105,'2026-08-08 09:43:00',2,68,NULL,13,1,3,3,NULL,'2026-09-25 00:38:10',NULL),
(374,72,'2026-08-08 09:43:00',3,68,NULL,13,3,2,3,NULL,'2026-09-25 00:38:10',NULL),
(375,154,'2026-08-09 09:30:00',1,NULL,15,NULL,NULL,1,1,'Recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(376,155,'2026-08-09 09:30:00',1,NULL,15,NULL,NULL,1,1,'Recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(377,156,'2026-08-09 09:30:00',1,NULL,15,NULL,NULL,1,1,'Recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(378,157,'2026-08-09 09:30:00',1,NULL,15,NULL,NULL,1,1,'Recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(379,158,'2026-08-09 09:30:00',1,NULL,15,NULL,NULL,1,1,'Recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(380,159,'2026-08-09 09:30:00',1,NULL,15,NULL,NULL,1,1,'Recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(381,160,'2026-08-09 09:30:00',1,NULL,15,NULL,NULL,1,1,'Recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(382,161,'2026-08-09 09:30:00',1,NULL,15,NULL,NULL,1,1,'Recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(383,162,'2026-08-09 09:30:00',1,NULL,15,NULL,NULL,1,1,'Recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(384,163,'2026-08-09 09:30:00',1,NULL,15,NULL,NULL,1,1,'Recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(385,164,'2026-08-09 09:30:00',1,NULL,15,NULL,NULL,1,1,'Recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(386,165,'2026-08-09 09:30:00',1,NULL,15,NULL,NULL,1,1,'Recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(387,166,'2026-08-09 09:30:00',1,NULL,15,NULL,NULL,1,1,'Recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(388,35,'2026-08-09 09:30:00',4,NULL,15,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(389,38,'2026-08-09 09:30:00',4,NULL,15,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(390,59,'2026-08-09 09:30:00',4,NULL,15,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(391,26,'2026-08-09 09:30:00',4,NULL,15,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(392,75,'2026-08-09 09:30:00',4,NULL,15,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(393,72,'2026-08-09 09:30:00',4,NULL,15,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(394,81,'2026-08-09 09:30:00',4,NULL,15,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(395,80,'2026-08-09 09:30:00',4,NULL,15,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(396,79,'2026-08-09 09:30:00',4,NULL,15,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(397,66,'2026-08-09 09:30:00',4,NULL,15,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00015','2026-09-25 00:38:10',NULL),
(398,167,'2026-08-09 09:30:00',1,NULL,16,NULL,NULL,1,1,'Recepción REC-PROV-2026-00016','2026-09-25 00:38:10',NULL),
(399,168,'2026-08-09 09:30:00',1,NULL,16,NULL,NULL,1,1,'Recepción REC-PROV-2026-00016','2026-09-25 00:38:10',NULL),
(400,169,'2026-08-09 09:30:00',1,NULL,16,NULL,NULL,1,1,'Recepción REC-PROV-2026-00016','2026-09-25 00:38:10',NULL),
(401,170,'2026-08-09 09:30:00',1,NULL,16,NULL,NULL,1,1,'Recepción REC-PROV-2026-00016','2026-09-25 00:38:10',NULL),
(402,87,'2026-08-09 09:30:00',4,NULL,16,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00016','2026-09-25 00:38:10',NULL),
(403,86,'2026-08-09 09:30:00',4,NULL,16,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00016','2026-09-25 00:38:10',NULL),
(404,118,'2026-08-09 09:30:00',4,NULL,16,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00016','2026-09-25 00:38:10',NULL),
(405,119,'2026-08-09 09:30:00',4,NULL,16,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00016','2026-09-25 00:38:10',NULL),
(406,115,'2026-08-09 09:43:00',2,69,NULL,11,1,3,3,NULL,'2026-09-25 00:38:10',NULL),
(407,116,'2026-08-09 09:43:00',2,69,NULL,11,1,3,3,NULL,'2026-09-25 00:38:10',NULL),
(408,61,'2026-08-09 09:43:00',3,69,NULL,11,3,2,3,NULL,'2026-09-25 00:38:10',NULL),
(409,95,'2026-08-09 09:43:00',3,69,NULL,11,3,2,3,NULL,'2026-09-25 00:38:10',NULL),
(410,130,'2026-08-10 10:38:00',2,70,NULL,19,1,3,3,NULL,'2026-09-25 00:38:10',NULL),
(411,45,'2026-08-10 10:38:00',3,70,NULL,19,3,2,3,NULL,'2026-09-25 00:38:10',NULL),
(412,131,'2026-08-11 09:10:00',2,71,NULL,9,1,3,2,NULL,'2026-09-25 00:38:10',NULL),
(413,99,'2026-08-11 09:10:00',3,71,NULL,9,3,2,2,NULL,'2026-09-25 00:38:10',NULL),
(414,106,'2026-08-11 11:20:00',2,72,NULL,16,1,3,1,NULL,'2026-09-25 00:38:10',NULL),
(415,53,'2026-08-11 11:20:00',3,72,NULL,16,3,2,1,NULL,'2026-09-25 00:38:10',NULL),
(416,139,'2026-08-14 09:29:00',2,73,NULL,21,1,3,3,NULL,'2026-09-25 00:38:10',NULL),
(417,140,'2026-08-14 09:29:00',2,73,NULL,21,1,3,3,NULL,'2026-09-25 00:38:10',NULL),
(418,88,'2026-08-14 09:29:00',3,73,NULL,21,3,2,3,NULL,'2026-09-25 00:38:10',NULL),
(419,120,'2026-08-14 09:29:00',3,73,NULL,21,3,2,3,NULL,'2026-09-25 00:38:10',NULL),
(420,132,'2026-08-15 09:07:00',2,74,NULL,1,1,3,2,NULL,'2026-09-25 00:38:10',NULL),
(421,82,'2026-08-15 09:07:00',3,74,NULL,1,3,2,2,NULL,'2026-09-25 00:38:10',NULL),
(422,107,'2026-08-15 11:10:00',2,75,NULL,4,1,3,1,NULL,'2026-09-25 00:38:10',NULL),
(423,108,'2026-08-15 11:10:00',2,75,NULL,4,1,3,1,NULL,'2026-09-25 00:38:10',NULL),
(424,89,'2026-08-15 11:10:00',3,75,NULL,4,3,2,1,NULL,'2026-09-25 00:38:10',NULL),
(425,171,'2026-08-15 11:10:00',1,75,NULL,4,NULL,2,1,'Envase recibido del cliente sin registrar (pedido PED-2026-00075)','2026-09-25 00:38:10',NULL),
(426,133,'2026-08-15 11:43:00',2,76,NULL,8,1,3,2,NULL,'2026-09-25 00:38:10',NULL),
(427,65,'2026-08-15 11:43:00',3,76,NULL,8,3,2,2,NULL,'2026-09-25 00:38:10',NULL),
(428,134,'2026-08-15 13:33:00',2,77,NULL,14,1,3,1,NULL,'2026-09-25 00:38:10',NULL),
(429,112,'2026-08-15 13:33:00',3,77,NULL,14,3,2,1,NULL,'2026-09-25 00:38:10',NULL),
(430,109,'2026-08-15 13:29:00',2,78,NULL,15,1,3,2,NULL,'2026-09-25 00:38:10',NULL),
(431,58,'2026-08-15 13:29:00',3,78,NULL,15,3,2,2,NULL,'2026-09-25 00:38:10',NULL),
(432,167,'2026-08-15 14:28:00',2,79,NULL,22,1,3,1,NULL,'2026-09-25 00:38:10',NULL),
(433,168,'2026-08-15 14:28:00',2,79,NULL,22,1,3,1,NULL,'2026-09-25 00:38:10',NULL),
(434,137,'2026-08-15 14:28:00',3,79,NULL,22,3,2,1,NULL,'2026-09-25 00:38:10',NULL),
(435,138,'2026-08-15 14:28:00',3,79,NULL,22,3,2,1,NULL,'2026-09-25 00:38:10',NULL),
(436,172,'2026-08-16 09:30:00',1,NULL,18,NULL,NULL,1,1,'Recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(437,173,'2026-08-16 09:30:00',1,NULL,18,NULL,NULL,1,1,'Recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(438,174,'2026-08-16 09:30:00',1,NULL,18,NULL,NULL,1,1,'Recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(439,175,'2026-08-16 09:30:00',1,NULL,18,NULL,NULL,1,1,'Recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(440,176,'2026-08-16 09:30:00',1,NULL,18,NULL,NULL,1,1,'Recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(441,177,'2026-08-16 09:30:00',1,NULL,18,NULL,NULL,1,1,'Recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(442,178,'2026-08-16 09:30:00',1,NULL,18,NULL,NULL,1,1,'Recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(443,179,'2026-08-16 09:30:00',1,NULL,18,NULL,NULL,1,1,'Recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(444,180,'2026-08-16 09:30:00',1,NULL,18,NULL,NULL,1,1,'Recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(445,181,'2026-08-16 09:30:00',1,NULL,18,NULL,NULL,1,1,'Recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(446,182,'2026-08-16 09:30:00',1,NULL,18,NULL,NULL,1,1,'Recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(447,183,'2026-08-16 09:30:00',1,NULL,18,NULL,NULL,1,1,'Recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(448,184,'2026-08-16 09:30:00',1,NULL,18,NULL,NULL,1,1,'Recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(449,185,'2026-08-16 09:30:00',1,NULL,18,NULL,NULL,1,1,'Recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(450,53,'2026-08-16 09:30:00',4,NULL,18,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(451,89,'2026-08-16 09:30:00',4,NULL,18,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(452,171,'2026-08-16 09:30:00',4,NULL,18,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(453,58,'2026-08-16 09:30:00',4,NULL,18,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(454,61,'2026-08-16 09:30:00',4,NULL,18,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(455,95,'2026-08-16 09:30:00',4,NULL,18,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(456,45,'2026-08-16 09:30:00',4,NULL,18,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(457,99,'2026-08-16 09:30:00',4,NULL,18,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(458,82,'2026-08-16 09:30:00',4,NULL,18,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(459,65,'2026-08-16 09:30:00',4,NULL,18,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00018','2026-09-25 00:38:10',NULL),
(460,110,'2026-08-16 10:29:00',2,80,NULL,6,1,3,3,NULL,'2026-09-25 00:38:10',NULL),
(461,74,'2026-08-16 10:29:00',3,80,NULL,6,3,2,3,NULL,'2026-09-25 00:38:10',NULL),
(462,111,'2026-08-16 11:21:00',2,81,NULL,7,1,3,3,NULL,'2026-09-25 00:38:10',NULL),
(463,76,'2026-08-16 11:21:00',3,81,NULL,7,3,2,3,NULL,'2026-09-25 00:38:10',NULL),
(464,135,'2026-08-16 12:04:00',2,82,NULL,17,1,3,1,NULL,'2026-09-25 00:38:10',NULL),
(465,96,'2026-08-16 12:04:00',3,82,NULL,17,3,2,1,NULL,'2026-09-25 00:38:10',NULL),
(466,136,'2026-08-17 09:32:00',2,83,NULL,18,1,3,3,NULL,'2026-09-25 00:38:10',NULL),
(467,113,'2026-08-17 09:32:00',3,83,NULL,18,3,2,3,NULL,'2026-09-25 00:38:10',NULL),
(468,123,'2026-08-18 09:12:00',2,84,NULL,5,1,3,2,NULL,'2026-09-25 00:38:10',NULL),
(469,92,'2026-08-18 09:12:00',3,84,NULL,5,3,2,2,NULL,'2026-09-25 00:38:10',NULL),
(470,186,'2026-08-19 09:30:00',1,NULL,19,NULL,NULL,1,1,'Recepción REC-PROV-2026-00019','2026-09-25 00:38:10',NULL),
(471,187,'2026-08-19 09:30:00',1,NULL,19,NULL,NULL,1,1,'Recepción REC-PROV-2026-00019','2026-09-25 00:38:10',NULL),
(472,188,'2026-08-19 09:30:00',1,NULL,19,NULL,NULL,1,1,'Recepción REC-PROV-2026-00019','2026-09-25 00:38:10',NULL),
(473,121,'2026-08-19 09:30:00',4,NULL,19,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00019','2026-09-25 00:38:10',NULL),
(474,88,'2026-08-19 09:30:00',4,NULL,19,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00019','2026-09-25 00:38:10',NULL),
(475,120,'2026-08-19 09:30:00',4,NULL,19,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00019','2026-09-25 00:38:10',NULL),
(476,124,'2026-08-20 09:40:00',2,85,NULL,10,1,3,2,NULL,'2026-09-25 00:38:10',NULL),
(477,125,'2026-08-20 09:40:00',2,85,NULL,10,1,3,2,NULL,'2026-09-25 00:38:10',NULL),
(478,94,'2026-08-20 09:40:00',3,85,NULL,10,3,2,2,NULL,'2026-09-25 00:38:10',NULL),
(479,189,'2026-08-20 09:40:00',1,85,NULL,10,NULL,2,2,'Envase recibido del cliente sin registrar (pedido PED-2026-00085)','2026-09-25 00:38:10',NULL),
(480,169,'2026-08-20 11:09:00',2,86,NULL,21,1,3,3,NULL,'2026-09-25 00:38:10',NULL),
(481,139,'2026-08-20 11:09:00',3,86,NULL,21,3,2,3,NULL,'2026-09-25 00:38:10',NULL),
(482,148,'2026-08-21 09:22:00',2,87,NULL,3,1,3,2,NULL,'2026-09-25 00:38:10',NULL),
(483,97,'2026-08-21 09:22:00',3,87,NULL,3,3,2,2,NULL,'2026-09-25 00:38:10',NULL),
(484,126,'2026-08-21 09:35:00',2,88,NULL,13,1,3,1,NULL,'2026-09-25 00:38:10',NULL),
(485,73,'2026-08-21 09:35:00',3,88,NULL,13,3,2,1,NULL,'2026-09-25 00:38:10',NULL),
(486,127,'2026-08-22 09:46:00',2,89,NULL,20,1,3,1,NULL,'2026-09-25 00:38:10',NULL),
(487,91,'2026-08-22 09:46:00',3,89,NULL,20,3,2,1,NULL,'2026-09-25 00:38:10',NULL),
(488,190,'2026-08-23 09:30:00',1,NULL,20,NULL,NULL,1,1,'Recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(489,191,'2026-08-23 09:30:00',1,NULL,20,NULL,NULL,1,1,'Recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(490,192,'2026-08-23 09:30:00',1,NULL,20,NULL,NULL,1,1,'Recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(491,193,'2026-08-23 09:30:00',1,NULL,20,NULL,NULL,1,1,'Recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(492,194,'2026-08-23 09:30:00',1,NULL,20,NULL,NULL,1,1,'Recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(493,195,'2026-08-23 09:30:00',1,NULL,20,NULL,NULL,1,1,'Recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(494,196,'2026-08-23 09:30:00',1,NULL,20,NULL,NULL,1,1,'Recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(495,197,'2026-08-23 09:30:00',1,NULL,20,NULL,NULL,1,1,'Recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(496,198,'2026-08-23 09:30:00',1,NULL,20,NULL,NULL,1,1,'Recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(497,199,'2026-08-23 09:30:00',1,NULL,20,NULL,NULL,1,1,'Recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(498,200,'2026-08-23 09:30:00',1,NULL,20,NULL,NULL,1,1,'Recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(499,74,'2026-08-23 09:30:00',4,NULL,20,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(500,76,'2026-08-23 09:30:00',4,NULL,20,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(501,92,'2026-08-23 09:30:00',4,NULL,20,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(502,94,'2026-08-23 09:30:00',4,NULL,20,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(503,189,'2026-08-23 09:30:00',4,NULL,20,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(504,73,'2026-08-23 09:30:00',4,NULL,20,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(505,112,'2026-08-23 09:30:00',4,NULL,20,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(506,96,'2026-08-23 09:30:00',4,NULL,20,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(507,113,'2026-08-23 09:30:00',4,NULL,20,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(508,97,'2026-08-23 09:30:00',4,NULL,20,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00020','2026-09-25 00:38:10',NULL),
(509,128,'2026-08-23 10:31:00',2,90,NULL,2,1,3,3,NULL,'2026-09-25 00:38:10',NULL),
(510,129,'2026-08-23 10:31:00',2,90,NULL,2,1,3,3,NULL,'2026-09-25 00:38:10',NULL),
(511,36,'2026-08-23 10:31:00',3,90,NULL,2,3,2,3,NULL,'2026-09-25 00:38:10',NULL),
(512,90,'2026-08-23 10:31:00',3,90,NULL,2,3,2,3,NULL,'2026-09-25 00:38:10',NULL),
(513,141,'2026-08-23 10:53:00',2,91,NULL,12,1,3,2,NULL,'2026-09-25 00:38:10',NULL),
(514,55,'2026-08-23 10:53:00',3,91,NULL,12,3,2,2,NULL,'2026-09-25 00:38:10',NULL),
(515,149,'2026-08-24 09:34:00',2,92,NULL,9,1,3,3,NULL,'2026-09-25 00:38:11',NULL),
(516,131,'2026-08-24 09:34:00',3,92,NULL,9,3,2,3,NULL,'2026-09-25 00:38:11',NULL),
(517,150,'2026-08-24 10:30:00',2,93,NULL,19,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(518,98,'2026-08-24 10:30:00',3,93,NULL,19,3,2,2,NULL,'2026-09-25 00:38:11',NULL),
(519,170,'2026-08-24 11:46:00',2,94,NULL,22,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(520,186,'2026-08-24 11:46:00',2,94,NULL,22,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(521,167,'2026-08-24 11:46:00',3,94,NULL,22,3,2,2,NULL,'2026-09-25 00:38:11',NULL),
(522,168,'2026-08-24 11:46:00',3,94,NULL,22,3,2,2,NULL,'2026-09-25 00:38:11',NULL),
(523,151,'2026-08-25 09:36:00',2,95,NULL,11,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(524,115,'2026-08-25 09:36:00',3,95,NULL,11,3,2,2,NULL,'2026-09-25 00:38:11',NULL),
(525,152,'2026-08-25 11:03:00',2,96,NULL,17,1,3,3,NULL,'2026-09-25 00:38:11',NULL),
(526,114,'2026-08-25 11:03:00',3,96,NULL,17,3,2,3,NULL,'2026-09-25 00:38:11',NULL),
(527,153,'2026-08-26 09:22:00',2,97,NULL,14,1,3,3,NULL,'2026-09-25 00:38:11',NULL),
(528,134,'2026-08-26 09:22:00',3,97,NULL,14,3,2,3,NULL,'2026-09-25 00:38:11',NULL),
(529,162,'2026-08-27 09:24:00',2,99,NULL,1,1,3,1,NULL,'2026-09-25 00:38:11',NULL),
(530,101,'2026-08-27 09:24:00',3,99,NULL,1,3,2,1,NULL,'2026-09-25 00:38:11',NULL),
(531,201,'2026-08-29 09:30:00',1,NULL,22,NULL,NULL,1,1,'Recepción REC-PROV-2026-00022','2026-09-25 00:38:11',NULL),
(532,202,'2026-08-29 09:30:00',1,NULL,22,NULL,NULL,1,1,'Recepción REC-PROV-2026-00022','2026-09-25 00:38:11',NULL),
(533,203,'2026-08-29 09:30:00',1,NULL,22,NULL,NULL,1,1,'Recepción REC-PROV-2026-00022','2026-09-25 00:38:11',NULL),
(534,204,'2026-08-29 09:30:00',1,NULL,22,NULL,NULL,1,1,'Recepción REC-PROV-2026-00022','2026-09-25 00:38:11',NULL),
(535,137,'2026-08-29 09:30:00',4,NULL,22,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00022','2026-09-25 00:38:11',NULL),
(536,138,'2026-08-29 09:30:00',4,NULL,22,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00022','2026-09-25 00:38:11',NULL),
(537,139,'2026-08-29 09:30:00',4,NULL,22,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00022','2026-09-25 00:38:11',NULL),
(538,167,'2026-08-29 09:30:00',4,NULL,22,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00022','2026-09-25 00:38:11',NULL),
(539,187,'2026-08-29 09:38:00',2,100,NULL,21,1,3,1,NULL,'2026-09-25 00:38:11',NULL),
(540,140,'2026-08-29 09:38:00',3,100,NULL,21,3,2,1,NULL,'2026-09-25 00:38:11',NULL),
(541,205,'2026-08-30 09:30:00',1,NULL,23,NULL,NULL,1,1,'Recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(542,206,'2026-08-30 09:30:00',1,NULL,23,NULL,NULL,1,1,'Recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(543,207,'2026-08-30 09:30:00',1,NULL,23,NULL,NULL,1,1,'Recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(544,208,'2026-08-30 09:30:00',1,NULL,23,NULL,NULL,1,1,'Recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(545,209,'2026-08-30 09:30:00',1,NULL,23,NULL,NULL,1,1,'Recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(546,210,'2026-08-30 09:30:00',1,NULL,23,NULL,NULL,1,1,'Recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(547,211,'2026-08-30 09:30:00',1,NULL,23,NULL,NULL,1,1,'Recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(548,212,'2026-08-30 09:30:00',1,NULL,23,NULL,NULL,1,1,'Recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(549,213,'2026-08-30 09:30:00',1,NULL,23,NULL,NULL,1,1,'Recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(550,214,'2026-08-30 09:30:00',1,NULL,23,NULL,NULL,1,1,'Recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(551,215,'2026-08-30 09:30:00',1,NULL,23,NULL,NULL,1,1,'Recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(552,216,'2026-08-30 09:30:00',1,NULL,23,NULL,NULL,1,1,'Recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(553,217,'2026-08-30 09:30:00',1,NULL,23,NULL,NULL,1,1,'Recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(554,218,'2026-08-30 09:30:00',1,NULL,23,NULL,NULL,1,1,'Recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(555,219,'2026-08-30 09:30:00',1,NULL,23,NULL,NULL,1,1,'Recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(556,91,'2026-08-30 09:30:00',4,NULL,23,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(557,36,'2026-08-30 09:30:00',4,NULL,23,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(558,90,'2026-08-30 09:30:00',4,NULL,23,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(559,55,'2026-08-30 09:30:00',4,NULL,23,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(560,131,'2026-08-30 09:30:00',4,NULL,23,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(561,98,'2026-08-30 09:30:00',4,NULL,23,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(562,115,'2026-08-30 09:30:00',4,NULL,23,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(563,114,'2026-08-30 09:30:00',4,NULL,23,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(564,134,'2026-08-30 09:30:00',4,NULL,23,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(565,101,'2026-08-30 09:30:00',4,NULL,23,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00023','2026-09-25 00:38:11',NULL),
(566,163,'2026-08-30 10:06:00',2,101,NULL,8,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(567,100,'2026-08-30 10:06:00',3,101,NULL,8,3,2,2,NULL,'2026-09-25 00:38:11',NULL),
(568,142,'2026-08-31 09:01:00',2,102,NULL,4,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(569,143,'2026-08-31 09:01:00',2,102,NULL,4,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(570,107,'2026-08-31 09:01:00',3,102,NULL,4,3,2,2,NULL,'2026-09-25 00:38:11',NULL),
(571,108,'2026-08-31 09:01:00',3,102,NULL,4,3,2,2,NULL,'2026-09-25 00:38:11',NULL),
(572,144,'2026-08-31 09:56:00',2,103,NULL,5,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(573,123,'2026-08-31 09:56:00',3,103,NULL,5,3,2,2,NULL,'2026-09-25 00:38:11',NULL),
(574,188,'2026-08-31 11:27:00',2,104,NULL,22,1,3,1,NULL,'2026-09-25 00:38:11',NULL),
(575,201,'2026-08-31 11:27:00',2,104,NULL,22,1,3,1,NULL,'2026-09-25 00:38:11',NULL),
(576,170,'2026-08-31 11:27:00',3,104,NULL,22,3,2,1,NULL,'2026-09-25 00:38:11',NULL),
(577,186,'2026-08-31 11:27:00',3,104,NULL,22,3,2,1,NULL,'2026-09-25 00:38:11',NULL),
(578,145,'2026-09-01 09:25:00',2,105,NULL,7,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(579,146,'2026-09-01 09:25:00',2,105,NULL,7,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(580,111,'2026-09-01 09:25:00',3,105,NULL,7,3,2,2,NULL,'2026-09-25 00:38:11',NULL),
(581,220,'2026-09-01 09:25:00',1,105,NULL,7,NULL,2,2,'Envase recibido del cliente sin registrar (pedido PED-2026-00105)','2026-09-25 00:38:11',NULL),
(582,147,'2026-09-01 10:16:00',2,106,NULL,10,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(583,124,'2026-09-01 10:16:00',3,106,NULL,10,3,2,2,NULL,'2026-09-25 00:38:11',NULL),
(584,154,'2026-09-02 09:17:00',2,107,NULL,13,1,3,1,NULL,'2026-09-25 00:38:11',NULL),
(585,105,'2026-09-02 09:17:00',3,107,NULL,13,3,2,1,NULL,'2026-09-25 00:38:11',NULL),
(586,164,'2026-09-04 08:46:00',2,108,NULL,19,1,3,3,NULL,'2026-09-25 00:38:11',NULL),
(587,130,'2026-09-04 08:46:00',3,108,NULL,19,3,2,3,NULL,'2026-09-25 00:38:11',NULL),
(588,202,'2026-09-04 11:11:00',2,109,NULL,21,1,3,3,NULL,'2026-09-25 00:38:11',NULL),
(589,169,'2026-09-04 11:11:00',3,109,NULL,21,3,2,3,NULL,'2026-09-25 00:38:11',NULL),
(590,155,'2026-09-05 09:21:00',2,110,NULL,6,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(591,156,'2026-09-05 09:21:00',2,110,NULL,6,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(592,110,'2026-09-05 09:21:00',3,110,NULL,6,3,2,2,NULL,'2026-09-25 00:38:11',NULL),
(593,221,'2026-09-05 09:21:00',1,110,NULL,6,NULL,2,2,'Envase recibido del cliente sin registrar (pedido PED-2026-00110)','2026-09-25 00:38:11',NULL),
(594,165,'2026-09-05 10:12:00',2,111,NULL,14,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(595,166,'2026-09-05 10:12:00',2,111,NULL,14,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(596,153,'2026-09-05 10:12:00',3,111,NULL,14,3,2,2,NULL,'2026-09-25 00:38:11',NULL),
(597,222,'2026-09-05 10:12:00',1,111,NULL,14,NULL,2,2,'Envase recibido del cliente sin registrar (pedido PED-2026-00111)','2026-09-25 00:38:11',NULL),
(598,157,'2026-09-05 11:38:00',2,112,NULL,16,1,3,1,NULL,'2026-09-25 00:38:11',NULL),
(599,54,'2026-09-05 11:38:00',3,112,NULL,16,3,2,1,NULL,'2026-09-25 00:38:11',NULL),
(600,180,'2026-09-05 13:29:00',2,113,NULL,17,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(601,135,'2026-09-05 13:29:00',3,113,NULL,17,3,2,2,NULL,'2026-09-25 00:38:11',NULL),
(602,223,'2026-09-06 09:30:00',1,NULL,25,NULL,NULL,1,1,'Recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(603,224,'2026-09-06 09:30:00',1,NULL,25,NULL,NULL,1,1,'Recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(604,225,'2026-09-06 09:30:00',1,NULL,25,NULL,NULL,1,1,'Recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(605,226,'2026-09-06 09:30:00',1,NULL,25,NULL,NULL,1,1,'Recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(606,227,'2026-09-06 09:30:00',1,NULL,25,NULL,NULL,1,1,'Recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(607,228,'2026-09-06 09:30:00',1,NULL,25,NULL,NULL,1,1,'Recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(608,229,'2026-09-06 09:30:00',1,NULL,25,NULL,NULL,1,1,'Recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(609,230,'2026-09-06 09:30:00',1,NULL,25,NULL,NULL,1,1,'Recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(610,231,'2026-09-06 09:30:00',1,NULL,25,NULL,NULL,1,1,'Recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(611,232,'2026-09-06 09:30:00',1,NULL,25,NULL,NULL,1,1,'Recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(612,233,'2026-09-06 09:30:00',1,NULL,25,NULL,NULL,1,1,'Recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(613,234,'2026-09-06 09:30:00',1,NULL,25,NULL,NULL,1,1,'Recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(614,235,'2026-09-06 09:30:00',1,NULL,25,NULL,NULL,1,1,'Recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(615,236,'2026-09-06 09:30:00',1,NULL,25,NULL,NULL,1,1,'Recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(616,237,'2026-09-06 09:30:00',1,NULL,25,NULL,NULL,1,1,'Recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(617,107,'2026-09-06 09:30:00',4,NULL,25,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(618,108,'2026-09-06 09:30:00',4,NULL,25,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(619,123,'2026-09-06 09:30:00',4,NULL,25,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(620,111,'2026-09-06 09:30:00',4,NULL,25,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(621,220,'2026-09-06 09:30:00',4,NULL,25,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(622,124,'2026-09-06 09:30:00',4,NULL,25,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(623,105,'2026-09-06 09:30:00',4,NULL,25,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(624,110,'2026-09-06 09:30:00',4,NULL,25,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(625,100,'2026-09-06 09:30:00',4,NULL,25,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(626,130,'2026-09-06 09:30:00',4,NULL,25,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(627,153,'2026-09-06 09:30:00',4,NULL,25,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(628,222,'2026-09-06 09:30:00',4,NULL,25,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(629,135,'2026-09-06 09:30:00',4,NULL,25,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00025','2026-09-25 00:38:11',NULL),
(630,181,'2026-09-06 09:52:00',2,114,NULL,18,1,3,3,NULL,'2026-09-25 00:38:11',NULL),
(631,136,'2026-09-06 09:52:00',3,114,NULL,18,3,2,3,NULL,'2026-09-25 00:38:11',NULL),
(632,203,'2026-09-06 10:47:00',2,115,NULL,22,1,3,3,NULL,'2026-09-25 00:38:11',NULL),
(633,204,'2026-09-06 10:47:00',2,115,NULL,22,1,3,3,NULL,'2026-09-25 00:38:11',NULL),
(634,188,'2026-09-06 10:47:00',3,115,NULL,22,3,2,3,NULL,'2026-09-25 00:38:11',NULL),
(635,201,'2026-09-06 10:47:00',3,115,NULL,22,3,2,3,NULL,'2026-09-25 00:38:11',NULL),
(636,158,'2026-09-07 08:46:00',2,116,NULL,15,1,3,3,NULL,'2026-09-25 00:38:11',NULL),
(637,77,'2026-09-07 08:46:00',3,116,NULL,15,3,2,3,NULL,'2026-09-25 00:38:11',NULL),
(638,159,'2026-09-07 10:57:00',2,117,NULL,20,1,3,1,NULL,'2026-09-25 00:38:11',NULL),
(639,160,'2026-09-07 10:57:00',2,117,NULL,20,1,3,1,NULL,'2026-09-25 00:38:11',NULL),
(640,127,'2026-09-07 10:57:00',3,117,NULL,20,3,2,1,NULL,'2026-09-25 00:38:11',NULL),
(641,238,'2026-09-07 10:57:00',1,117,NULL,20,NULL,2,1,'Envase recibido del cliente sin registrar (pedido PED-2026-00117)','2026-09-25 00:38:11',NULL),
(642,239,'2026-09-08 09:30:00',1,NULL,26,NULL,NULL,1,1,'Recepción REC-PROV-2026-00026','2026-09-25 00:38:11',NULL),
(643,240,'2026-09-08 09:30:00',1,NULL,26,NULL,NULL,1,1,'Recepción REC-PROV-2026-00026','2026-09-25 00:38:11',NULL),
(644,241,'2026-09-08 09:30:00',1,NULL,26,NULL,NULL,1,1,'Recepción REC-PROV-2026-00026','2026-09-25 00:38:11',NULL),
(645,168,'2026-09-08 09:30:00',4,NULL,26,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00026','2026-09-25 00:38:11',NULL),
(646,140,'2026-09-08 09:30:00',4,NULL,26,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00026','2026-09-25 00:38:11',NULL),
(647,170,'2026-09-08 09:30:00',4,NULL,26,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00026','2026-09-25 00:38:11',NULL),
(648,182,'2026-09-08 10:42:00',2,118,NULL,9,1,3,3,NULL,'2026-09-25 00:38:11',NULL),
(649,183,'2026-09-08 10:42:00',2,118,NULL,9,1,3,3,NULL,'2026-09-25 00:38:11',NULL),
(650,149,'2026-09-08 10:42:00',3,118,NULL,9,3,2,3,NULL,'2026-09-25 00:38:11',NULL),
(651,242,'2026-09-08 10:42:00',1,118,NULL,9,NULL,2,3,'Envase recibido del cliente sin registrar (pedido PED-2026-00118)','2026-09-25 00:38:11',NULL),
(652,239,'2026-09-08 10:43:00',2,119,NULL,21,1,3,3,NULL,'2026-09-25 00:38:11',NULL),
(653,240,'2026-09-08 10:43:00',2,119,NULL,21,1,3,3,NULL,'2026-09-25 00:38:11',NULL),
(654,187,'2026-09-08 10:43:00',3,119,NULL,21,3,2,3,NULL,'2026-09-25 00:38:11',NULL),
(655,202,'2026-09-08 10:43:00',3,119,NULL,21,3,2,3,NULL,'2026-09-25 00:38:11',NULL),
(656,184,'2026-09-10 10:10:00',2,120,NULL,1,1,3,3,NULL,'2026-09-25 00:38:11',NULL),
(657,132,'2026-09-10 10:10:00',3,120,NULL,1,3,2,3,NULL,'2026-09-25 00:38:11',NULL),
(658,161,'2026-09-10 10:17:00',2,121,NULL,10,1,3,1,NULL,'2026-09-25 00:38:11',NULL),
(659,125,'2026-09-10 10:17:00',3,121,NULL,10,3,2,1,NULL,'2026-09-25 00:38:11',NULL),
(660,172,'2026-09-11 09:46:00',2,122,NULL,2,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(661,128,'2026-09-11 09:46:00',3,122,NULL,2,3,2,2,NULL,'2026-09-25 00:38:11',NULL),
(662,185,'2026-09-11 10:56:00',2,123,NULL,11,1,3,3,NULL,'2026-09-25 00:38:11',NULL),
(663,116,'2026-09-11 10:56:00',3,123,NULL,11,3,2,3,NULL,'2026-09-25 00:38:11',NULL),
(664,173,'2026-09-12 09:54:00',2,124,NULL,12,1,3,2,NULL,'2026-09-25 00:38:11',NULL),
(665,93,'2026-09-12 09:54:00',3,124,NULL,12,3,2,2,NULL,'2026-09-25 00:38:11',NULL),
(666,243,'2026-09-13 09:30:00',1,NULL,27,NULL,NULL,1,1,'Recepción REC-PROV-2026-00027','2026-09-25 00:38:11',NULL),
(667,244,'2026-09-13 09:30:00',1,NULL,27,NULL,NULL,1,1,'Recepción REC-PROV-2026-00027','2026-09-25 00:38:11',NULL),
(668,245,'2026-09-13 09:30:00',1,NULL,27,NULL,NULL,1,1,'Recepción REC-PROV-2026-00027','2026-09-25 00:38:11',NULL),
(669,246,'2026-09-13 09:30:00',1,NULL,27,NULL,NULL,1,1,'Recepción REC-PROV-2026-00027','2026-09-25 00:38:11',NULL),
(670,247,'2026-09-13 09:30:00',1,NULL,27,NULL,NULL,1,1,'Recepción REC-PROV-2026-00027','2026-09-25 00:38:11',NULL),
(671,248,'2026-09-13 09:30:00',1,NULL,27,NULL,NULL,1,1,'Recepción REC-PROV-2026-00027','2026-09-25 00:38:11',NULL),
(672,249,'2026-09-13 09:30:00',1,NULL,27,NULL,NULL,1,1,'Recepción REC-PROV-2026-00027','2026-09-25 00:38:11',NULL),
(673,250,'2026-09-13 09:30:00',1,NULL,27,NULL,NULL,1,1,'Recepción REC-PROV-2026-00027','2026-09-25 00:38:12',NULL),
(674,251,'2026-09-13 09:30:00',1,NULL,27,NULL,NULL,1,1,'Recepción REC-PROV-2026-00027','2026-09-25 00:38:12',NULL),
(675,252,'2026-09-13 09:30:00',1,NULL,27,NULL,NULL,1,1,'Recepción REC-PROV-2026-00027','2026-09-25 00:38:12',NULL),
(676,253,'2026-09-13 09:30:00',1,NULL,27,NULL,NULL,1,1,'Recepción REC-PROV-2026-00027','2026-09-25 00:38:12',NULL),
(677,254,'2026-09-13 09:30:00',1,NULL,27,NULL,NULL,1,1,'Recepción REC-PROV-2026-00027','2026-09-25 00:38:12',NULL),
(678,221,'2026-09-13 09:30:00',4,NULL,27,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00027','2026-09-25 00:38:12',NULL),
(679,54,'2026-09-13 09:30:00',4,NULL,27,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00027','2026-09-25 00:38:12',NULL),
(680,77,'2026-09-13 09:30:00',4,NULL,27,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00027','2026-09-25 00:38:12',NULL),
(681,127,'2026-09-13 09:30:00',4,NULL,27,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00027','2026-09-25 00:38:12',NULL),
(682,238,'2026-09-13 09:30:00',4,NULL,27,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00027','2026-09-25 00:38:12',NULL),
(683,125,'2026-09-13 09:30:00',4,NULL,27,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00027','2026-09-25 00:38:12',NULL),
(684,136,'2026-09-13 09:30:00',4,NULL,27,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00027','2026-09-25 00:38:12',NULL),
(685,149,'2026-09-13 09:30:00',4,NULL,27,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00027','2026-09-25 00:38:12',NULL),
(686,242,'2026-09-13 09:30:00',4,NULL,27,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00027','2026-09-25 00:38:12',NULL),
(687,132,'2026-09-13 09:30:00',4,NULL,27,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00027','2026-09-25 00:38:12',NULL),
(688,116,'2026-09-13 09:30:00',4,NULL,27,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00027','2026-09-25 00:38:12',NULL),
(689,196,'2026-09-13 09:21:00',2,125,NULL,8,1,3,1,NULL,'2026-09-25 00:38:12',NULL),
(690,133,'2026-09-13 09:21:00',3,125,NULL,8,3,2,1,NULL,'2026-09-25 00:38:12',NULL),
(691,174,'2026-09-14 09:47:00',2,126,NULL,5,1,3,2,NULL,'2026-09-25 00:38:12',NULL),
(692,144,'2026-09-14 09:47:00',3,126,NULL,5,3,2,2,NULL,'2026-09-25 00:38:12',NULL),
(693,197,'2026-09-14 10:03:00',2,127,NULL,17,1,3,1,NULL,'2026-09-25 00:38:12',NULL),
(694,152,'2026-09-14 10:03:00',3,127,NULL,17,3,2,1,NULL,'2026-09-25 00:38:12',NULL),
(695,241,'2026-09-14 11:38:00',2,128,NULL,21,1,3,2,NULL,'2026-09-25 00:38:12',NULL),
(696,239,'2026-09-14 11:38:00',3,128,NULL,21,3,2,2,NULL,'2026-09-25 00:38:12',NULL),
(697,175,'2026-09-15 09:33:00',2,130,NULL,4,1,3,2,NULL,'2026-09-25 00:38:12',NULL),
(698,142,'2026-09-15 09:33:00',3,130,NULL,4,3,2,2,NULL,'2026-09-25 00:38:12',NULL),
(699,198,'2026-09-17 09:29:00',2,131,NULL,3,1,3,1,NULL,'2026-09-25 00:38:12',NULL),
(700,199,'2026-09-17 09:29:00',2,131,NULL,3,1,3,1,NULL,'2026-09-25 00:38:12',NULL),
(701,148,'2026-09-17 09:29:00',3,131,NULL,3,3,2,1,NULL,'2026-09-25 00:38:12',NULL),
(702,255,'2026-09-17 09:29:00',1,131,NULL,3,NULL,2,1,'Envase recibido del cliente sin registrar (pedido PED-2026-00131)','2026-09-25 00:38:12',NULL),
(703,176,'2026-09-17 10:35:00',2,132,NULL,13,1,3,2,NULL,'2026-09-25 00:38:12',NULL),
(704,126,'2026-09-17 10:35:00',3,132,NULL,13,3,2,2,NULL,'2026-09-25 00:38:12',NULL),
(705,256,'2026-09-18 09:30:00',1,NULL,29,NULL,NULL,1,1,'Recepción REC-PROV-2026-00029','2026-09-25 00:38:12',NULL),
(706,257,'2026-09-18 09:30:00',1,NULL,29,NULL,NULL,1,1,'Recepción REC-PROV-2026-00029','2026-09-25 00:38:12',NULL),
(707,258,'2026-09-18 09:30:00',1,NULL,29,NULL,NULL,1,1,'Recepción REC-PROV-2026-00029','2026-09-25 00:38:12',NULL),
(708,259,'2026-09-18 09:30:00',1,NULL,29,NULL,NULL,1,1,'Recepción REC-PROV-2026-00029','2026-09-25 00:38:12',NULL),
(709,186,'2026-09-18 09:30:00',4,NULL,29,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00029','2026-09-25 00:38:12',NULL),
(710,169,'2026-09-18 09:30:00',4,NULL,29,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00029','2026-09-25 00:38:12',NULL),
(711,188,'2026-09-18 09:30:00',4,NULL,29,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00029','2026-09-25 00:38:12',NULL),
(712,201,'2026-09-18 09:30:00',4,NULL,29,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00029','2026-09-25 00:38:12',NULL),
(713,177,'2026-09-18 09:45:00',2,133,NULL,7,1,3,3,NULL,'2026-09-25 00:38:12',NULL),
(714,145,'2026-09-18 09:45:00',3,133,NULL,7,3,2,3,NULL,'2026-09-25 00:38:12',NULL),
(715,178,'2026-09-18 11:18:00',2,135,NULL,15,1,3,1,NULL,'2026-09-25 00:38:12',NULL),
(716,179,'2026-09-18 11:18:00',2,135,NULL,15,1,3,1,NULL,'2026-09-25 00:38:12',NULL),
(717,109,'2026-09-18 11:18:00',3,135,NULL,15,3,2,1,NULL,'2026-09-25 00:38:12',NULL),
(718,158,'2026-09-18 11:18:00',3,135,NULL,15,3,2,1,NULL,'2026-09-25 00:38:12',NULL),
(719,200,'2026-09-18 11:39:00',2,136,NULL,19,1,3,1,NULL,'2026-09-25 00:38:12',NULL),
(720,150,'2026-09-18 11:39:00',3,136,NULL,19,3,2,1,NULL,'2026-09-25 00:38:12',NULL),
(721,256,'2026-09-19 09:05:00',2,137,NULL,21,1,3,3,NULL,'2026-09-25 00:38:12',NULL),
(722,240,'2026-09-19 09:05:00',3,137,NULL,21,3,2,3,NULL,'2026-09-25 00:38:12',NULL),
(723,260,'2026-09-20 09:30:00',1,NULL,30,NULL,NULL,1,1,'Recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(724,261,'2026-09-20 09:30:00',1,NULL,30,NULL,NULL,1,1,'Recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(725,262,'2026-09-20 09:30:00',1,NULL,30,NULL,NULL,1,1,'Recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(726,263,'2026-09-20 09:30:00',1,NULL,30,NULL,NULL,1,1,'Recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(727,264,'2026-09-20 09:30:00',1,NULL,30,NULL,NULL,1,1,'Recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(728,265,'2026-09-20 09:30:00',1,NULL,30,NULL,NULL,1,1,'Recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(729,266,'2026-09-20 09:30:00',1,NULL,30,NULL,NULL,1,1,'Recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(730,267,'2026-09-20 09:30:00',1,NULL,30,NULL,NULL,1,1,'Recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(731,268,'2026-09-20 09:30:00',1,NULL,30,NULL,NULL,1,1,'Recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(732,269,'2026-09-20 09:30:00',1,NULL,30,NULL,NULL,1,1,'Recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(733,270,'2026-09-20 09:30:00',1,NULL,30,NULL,NULL,1,1,'Recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(734,271,'2026-09-20 09:30:00',1,NULL,30,NULL,NULL,1,1,'Recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(735,272,'2026-09-20 09:30:00',1,NULL,30,NULL,NULL,1,1,'Recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(736,273,'2026-09-20 09:30:00',1,NULL,30,NULL,NULL,1,1,'Recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(737,128,'2026-09-20 09:30:00',4,NULL,30,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(738,93,'2026-09-20 09:30:00',4,NULL,30,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(739,144,'2026-09-20 09:30:00',4,NULL,30,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(740,142,'2026-09-20 09:30:00',4,NULL,30,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(741,126,'2026-09-20 09:30:00',4,NULL,30,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(742,145,'2026-09-20 09:30:00',4,NULL,30,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(743,109,'2026-09-20 09:30:00',4,NULL,30,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(744,158,'2026-09-20 09:30:00',4,NULL,30,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(745,133,'2026-09-20 09:30:00',4,NULL,30,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(746,152,'2026-09-20 09:30:00',4,NULL,30,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(747,148,'2026-09-20 09:30:00',4,NULL,30,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(748,255,'2026-09-20 09:30:00',4,NULL,30,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(749,150,'2026-09-20 09:30:00',4,NULL,30,NULL,2,5,1,'Entregada al proveedor en recepción REC-PROV-2026-00030','2026-09-25 00:38:12',NULL),
(750,213,'2026-09-20 10:42:00',2,138,NULL,1,1,3,2,NULL,'2026-09-25 00:38:12',NULL),
(751,162,'2026-09-20 10:42:00',3,138,NULL,1,3,2,2,NULL,'2026-09-25 00:38:12',NULL),
(752,190,'2026-09-20 11:01:00',2,139,NULL,10,1,3,1,NULL,'2026-09-25 00:38:12',NULL),
(753,147,'2026-09-20 11:01:00',3,139,NULL,10,3,2,1,NULL,'2026-09-25 00:38:12',NULL),
(754,214,'2026-09-21 09:15:00',2,140,NULL,9,1,3,2,NULL,'2026-09-25 00:38:12',NULL),
(755,182,'2026-09-21 09:15:00',3,140,NULL,9,3,2,2,NULL,'2026-09-25 00:38:12',NULL),
(756,215,'2026-09-21 10:17:00',2,141,NULL,18,1,3,1,NULL,'2026-09-25 00:38:12',NULL),
(757,181,'2026-09-21 10:17:00',3,141,NULL,18,3,2,1,NULL,'2026-09-25 00:38:12',NULL),
(758,257,'2026-09-22 10:19:00',2,143,NULL,22,1,3,1,NULL,'2026-09-25 00:38:12',NULL),
(759,258,'2026-09-22 10:19:00',2,143,NULL,22,1,3,1,NULL,'2026-09-25 00:38:12',NULL),
(760,203,'2026-09-22 10:19:00',3,143,NULL,22,3,2,1,NULL,'2026-09-25 00:38:12',NULL),
(761,204,'2026-09-22 10:19:00',3,143,NULL,22,3,2,1,NULL,'2026-09-25 00:38:12',NULL),
(762,216,'2026-09-24 09:51:00',2,145,NULL,17,1,3,2,NULL,'2026-09-25 00:38:12',NULL),
(763,180,'2026-09-24 09:51:00',3,145,NULL,17,3,2,2,NULL,'2026-09-25 00:38:12',NULL),
(764,182,'2026-09-19 00:38:12',5,NULL,NULL,NULL,2,4,1,'Válvula dañada / prueba hidráulica vencida','2026-09-25 00:38:12',NULL),
(765,202,'2026-09-15 00:38:12',5,NULL,NULL,NULL,2,4,1,'Válvula dañada / prueba hidráulica vencida','2026-09-25 00:38:12',NULL),
(766,181,'2026-09-19 00:38:12',5,NULL,NULL,NULL,2,4,1,'Válvula dañada / prueba hidráulica vencida','2026-09-25 00:38:12',NULL),
(767,240,'2026-09-17 00:38:12',5,NULL,NULL,NULL,2,4,1,'Válvula dañada / prueba hidráulica vencida','2026-09-25 00:38:12',NULL);
/*!40000 ALTER TABLE `movimientos_garrafa` ENABLE KEYS */;
UNLOCK TABLES;
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
DROP TABLE IF EXISTS `pagos`;
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
) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `pagos` WRITE;
/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;
INSERT INTO `pagos` VALUES
(1,'REC-2026-00001','2026-06-28 09:41:00',12,1,1,37200.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(2,'REC-2026-00002','2026-06-28 10:30:00',20,2,1,16500.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(3,'REC-2026-00003','2026-06-29 10:04:00',3,3,1,24000.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(4,'REC-2026-00004','2026-06-29 09:55:00',17,4,2,61000.00,'Op. 85320585',NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(5,'REC-2026-00005','2026-06-30 10:46:00',4,5,1,39500.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(6,'REC-2026-00006','2026-06-30 09:39:00',5,6,1,16500.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(7,'REC-2026-00007','2026-06-30 11:29:00',18,7,2,24000.00,'Op. 12241327',NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(8,'REC-2026-00008','2026-07-01 10:00:00',9,8,1,24000.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(9,'REC-2026-00009','2026-07-02 09:40:00',22,9,1,144000.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(10,'REC-2026-00010','2026-07-03 09:43:00',21,10,2,155500.00,'Op. 61430928',NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(11,'REC-2026-00011','2026-07-05 09:41:00',6,11,1,16500.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(12,'REC-2026-00012','2026-07-05 11:08:00',19,12,2,61000.00,'Op. 47462709',NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(13,'REC-2026-00013','2026-07-06 10:05:00',1,13,1,48000.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(14,'REC-2026-00014','2026-07-06 10:20:00',10,14,1,16500.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(15,'REC-2026-00015','2026-07-06 11:05:00',11,15,2,52200.00,'Op. 38712481',NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(16,'REC-2026-00016','2026-07-06 12:33:00',17,16,1,79000.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(17,'REC-2026-00017','2026-07-07 09:23:00',14,17,1,24000.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(18,'REC-2026-00018','2026-07-07 10:38:00',15,18,2,16500.00,'Op. 29068980',NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(19,'REC-2026-00019','2026-07-09 09:32:00',21,19,2,157000.00,'Op. 92777035',NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(20,'REC-2026-00020','2026-07-10 08:58:00',13,20,2,16500.00,'Op. 24675393',NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(21,'REC-2026-00021','2026-07-12 08:58:00',5,22,1,16500.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(22,'REC-2026-00022','2026-07-12 10:54:00',8,23,2,48000.00,'Op. 80295770',NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(23,'REC-2026-00023','2026-07-13 10:07:00',2,24,1,33000.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(24,'REC-2026-00024','2026-07-13 11:35:00',7,25,2,16500.00,'Op. 55847705',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(25,'REC-2026-00025','2026-07-13 12:25:00',17,26,1,24000.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(26,'REC-2026-00026','2026-07-15 09:59:00',20,27,1,24900.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(27,'REC-2026-00027','2026-07-16 09:53:00',10,28,1,40500.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(28,'REC-2026-00028','2026-07-16 09:49:00',16,29,1,44500.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(29,'REC-2026-00029','2026-07-16 13:12:00',21,31,2,144000.00,'Op. 52548701',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(30,'REC-2026-00030','2026-07-17 10:33:00',9,32,1,24000.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(31,'REC-2026-00031','2026-07-17 10:10:00',12,33,1,16500.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(32,'REC-2026-00032','2026-07-17 12:42:00',22,34,1,96000.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(33,'REC-2026-00033','2026-07-18 10:29:00',4,35,1,16500.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(34,'REC-2026-00034','2026-07-18 10:28:00',18,36,2,47000.00,'Op. 27390254',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(35,'REC-2026-00035','2026-07-19 09:31:00',15,37,2,33000.00,'Op. 68071490',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(36,'REC-2026-00036','2026-07-20 09:53:00',14,38,1,28200.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(37,'REC-2026-00037','2026-07-21 09:50:00',1,39,1,48000.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(38,'REC-2026-00038','2026-07-24 10:08:00',5,40,1,16500.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(39,'REC-2026-00039','2026-07-24 10:44:00',13,41,2,33000.00,'Op. 12650464',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(40,'REC-2026-00040','2026-07-24 12:28:00',21,42,2,95000.00,'Op. 99657028',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(41,'REC-2026-00041','2026-07-25 09:54:00',11,43,2,24000.00,'Op. 17775788',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(42,'REC-2026-00042','2026-07-25 10:55:00',17,44,1,24000.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(43,'REC-2026-00043','2026-07-25 11:14:00',22,45,1,144000.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(44,'REC-2026-00044','2026-07-26 09:32:00',3,46,1,24000.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(45,'REC-2026-00045','2026-07-26 11:33:00',6,47,1,24900.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(46,'REC-2026-00046','2026-07-26 11:12:00',10,48,1,44000.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(47,'REC-2026-00047','2026-07-27 10:30:00',19,49,2,24000.00,'Op. 51263870',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(48,'REC-2026-00048','2026-07-29 09:29:00',7,50,2,44000.00,'Op. 43614204',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(49,'REC-2026-00049','2026-07-29 10:08:00',9,51,1,24000.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(50,'REC-2026-00050','2026-07-30 09:57:00',21,52,2,72000.00,'Op. 45250691',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(51,'REC-2026-00051','2026-07-30 10:48:00',22,53,1,72000.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(52,'REC-2026-00052','2026-07-31 09:17:00',8,54,2,24000.00,'Op. 24127277',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(53,'REC-2026-00053','2026-07-31 09:52:00',15,55,2,39500.00,'Op. 82756012',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(54,'REC-2026-00054','2026-08-01 09:48:00',4,56,1,20700.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(55,'REC-2026-00055','2026-08-02 10:22:00',1,57,1,24000.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(56,'REC-2026-00056','2026-08-02 10:30:00',14,58,1,24000.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(57,'REC-2026-00057','2026-08-02 12:13:00',18,59,2,24000.00,'Op. 37203194',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(58,'REC-2026-00058','2026-08-03 09:42:00',2,60,2,16500.00,'Op. 65745502',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(59,'REC-2026-00059','2026-08-03 11:29:00',20,61,1,16500.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(60,'REC-2026-00060','2026-08-04 09:47:00',17,62,1,30500.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(61,'REC-2026-00061','2026-08-05 09:38:00',5,63,1,28500.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(62,'REC-2026-00062','2026-08-05 11:13:00',12,64,1,28000.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(63,'REC-2026-00063','2026-08-06 09:44:00',10,65,1,29500.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(64,'REC-2026-00064','2026-08-07 09:29:00',22,67,1,148200.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(65,'REC-2026-00065','2026-08-08 09:45:00',13,68,2,16500.00,'Op. 29945022',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(66,'REC-2026-00066','2026-08-09 09:45:00',11,69,2,48000.00,'Op. 86679909',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(67,'REC-2026-00067','2026-08-10 10:40:00',19,70,2,24000.00,'Op. 74731158',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(68,'REC-2026-00068','2026-08-11 09:12:00',9,71,1,24000.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(69,'REC-2026-00069','2026-08-11 11:22:00',16,72,2,16500.00,'Op. 86951963',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(70,'REC-2026-00070','2026-08-14 09:31:00',21,73,2,144000.00,'Op. 12508192',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(71,'REC-2026-00071','2026-08-15 09:09:00',1,74,1,24000.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(72,'REC-2026-00072','2026-08-15 11:12:00',4,75,2,56000.00,'Op. 64919012',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(73,'REC-2026-00073','2026-08-15 11:45:00',8,76,2,48000.00,'Op. 49034372',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(74,'REC-2026-00074','2026-08-15 13:35:00',14,77,1,24000.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(75,'REC-2026-00075','2026-08-15 13:31:00',15,78,2,23000.00,'Op. 72446650',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(76,'REC-2026-00076','2026-08-15 14:30:00',22,79,1,157000.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(77,'REC-2026-00077','2026-08-16 10:31:00',6,80,1,16500.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(78,'REC-2026-00078','2026-08-16 11:23:00',7,81,2,16500.00,'Op. 98561232',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(79,'REC-2026-00079','2026-08-16 12:06:00',17,82,1,24000.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(80,'REC-2026-00080','2026-08-17 09:34:00',18,83,2,24000.00,'Op. 26178244',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(81,'REC-2026-00081','2026-08-18 09:14:00',5,84,1,16500.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(82,'REC-2026-00082','2026-08-20 09:42:00',10,85,1,41400.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(83,'REC-2026-00083','2026-08-20 11:11:00',21,86,2,72000.00,'Op. 44669749',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(84,'REC-2026-00084','2026-08-21 09:24:00',3,87,1,24000.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(85,'REC-2026-00085','2026-08-21 09:37:00',13,88,1,16500.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(86,'REC-2026-00086','2026-08-22 09:48:00',20,89,1,16500.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(87,'REC-2026-00087','2026-08-23 10:33:00',2,90,2,33000.00,'Op. 36431590',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(88,'REC-2026-00088','2026-08-23 10:55:00',12,91,1,16500.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(89,'REC-2026-00089','2026-08-24 09:36:00',9,92,1,24000.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(90,'REC-2026-00090','2026-08-24 10:32:00',19,93,2,24000.00,'Op. 42273063',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(91,'REC-2026-00091','2026-08-24 11:48:00',22,94,1,144000.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(92,'REC-2026-00092','2026-08-25 09:38:00',11,95,2,51500.00,'Op. 88601487',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(93,'REC-2026-00093','2026-08-25 11:05:00',17,96,1,24000.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(94,'REC-2026-00094','2026-08-26 09:24:00',14,97,1,24000.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(95,'REC-2026-00095','2026-08-27 09:26:00',1,99,1,24000.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(96,'REC-2026-00096','2026-08-29 09:40:00',21,100,2,72000.00,'Op. 90638414',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(97,'REC-2026-00097','2026-08-30 10:08:00',8,101,2,24000.00,'Op. 70107201',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(98,'REC-2026-00098','2026-08-31 09:03:00',4,102,1,33000.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(99,'REC-2026-00099','2026-08-31 09:58:00',5,103,1,16500.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(100,'REC-2026-00100','2026-08-31 11:29:00',22,104,1,144000.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(101,'REC-2026-00101','2026-09-01 09:27:00',7,105,2,33000.00,'Op. 10127778',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(102,'REC-2026-00102','2026-09-01 10:18:00',10,106,1,16500.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(103,'REC-2026-00103','2026-09-02 09:19:00',13,107,2,16500.00,'Op. 76406754',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(104,'REC-2026-00104','2026-09-04 08:48:00',19,108,2,24000.00,'Op. 22622292',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(105,'REC-2026-00105','2026-09-04 11:13:00',21,109,2,72000.00,'Op. 56868172',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(106,'REC-2026-00106','2026-09-05 09:23:00',6,110,1,33000.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(107,'REC-2026-00107','2026-09-05 10:14:00',14,111,1,48000.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(108,'REC-2026-00108','2026-09-05 11:40:00',16,112,1,8300.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(109,'REC-2026-00109','2026-09-05 13:31:00',17,113,1,24000.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(110,'REC-2026-00110','2026-09-06 09:54:00',18,114,2,24000.00,'Op. 68744918',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(111,'REC-2026-00111','2026-09-06 10:49:00',22,115,1,144000.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(112,'REC-2026-00112','2026-09-07 08:48:00',15,116,2,8300.00,'Op. 12059909',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(113,'REC-2026-00113','2026-09-08 10:44:00',9,118,1,54500.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(114,'REC-2026-00114','2026-09-08 10:45:00',21,119,2,144000.00,'Op. 22556594',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(115,'REC-2026-00115','2026-09-10 10:12:00',1,120,1,24000.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(116,'REC-2026-00116','2026-09-10 10:19:00',10,121,1,16500.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(117,'REC-2026-00117','2026-09-11 09:48:00',2,122,2,39500.00,'Op. 97172153',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(118,'REC-2026-00118','2026-09-11 10:58:00',11,123,2,24000.00,'Op. 52434402',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(119,'REC-2026-00119','2026-09-12 09:56:00',12,124,1,16500.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(120,'REC-2026-00120','2026-09-14 09:49:00',5,126,1,24900.00,NULL,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(121,'REC-2026-00121','2026-09-14 10:05:00',17,127,1,24000.00,NULL,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(122,'REC-2026-00122','2026-09-14 11:40:00',21,128,2,78500.00,'Op. 84407550',NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(123,'REC-2026-00123','2026-09-15 09:35:00',4,130,1,16500.00,NULL,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(124,'REC-2026-00124','2026-09-17 09:31:00',3,131,1,48000.00,NULL,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(125,'REC-2026-00125','2026-09-17 10:37:00',13,132,2,8300.00,'Op. 60682662',NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(126,'REC-2026-00126','2026-09-18 09:47:00',7,133,2,16500.00,'Op. 64299034',NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(127,'REC-2026-00127','2026-09-18 11:20:00',15,135,2,39500.00,'Op. 59376492',NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(128,'REC-2026-00128','2026-09-18 11:41:00',19,136,2,24000.00,'Op. 55684782',NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(129,'REC-2026-00129','2026-09-19 09:07:00',21,137,2,95000.00,'Op. 78269150',NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(130,'REC-2026-00130','2026-09-20 10:44:00',1,138,1,24000.00,NULL,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(131,'REC-2026-00131','2026-09-20 11:03:00',10,139,1,8300.00,NULL,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(132,'REC-2026-00132','2026-09-21 09:17:00',9,140,1,24000.00,NULL,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(133,'REC-2026-00133','2026-09-21 10:19:00',18,141,2,24000.00,'Op. 87731550',NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(134,'REC-2026-00134','2026-09-22 10:21:00',22,143,1,144000.00,NULL,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(135,'REC-2026-00135','2026-09-24 09:53:00',17,145,1,24000.00,NULL,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL);
/*!40000 ALTER TABLE `pagos` ENABLE KEYS */;
UNLOCK TABLES;
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
DROP TABLE IF EXISTS `pagos_proveedor`;
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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `pagos_proveedor` WRITE;
/*!40000 ALTER TABLE `pagos_proveedor` DISABLE KEYS */;
INSERT INTO `pagos_proveedor` VALUES
(1,'PAG-PROV-2026-00001','2026-07-02 17:00:00',1,1,1,234700.00,'Transf. 5803573',NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(2,'PAG-PROV-2026-00002','2026-07-04 17:00:00',2,2,2,175500.00,'Transf. 7526205',NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(3,'PAG-PROV-2026-00003','2026-07-04 17:00:00',3,3,1,149800.00,'Transf. 3448367',NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(4,'PAG-PROV-2026-00004','2026-07-06 17:00:00',4,4,1,70000.00,'Transf. 5336697',NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(5,'PAG-PROV-2026-00005','2026-07-10 17:00:00',1,5,1,171300.00,'Transf. 3122358',NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(6,'PAG-PROV-2026-00006','2026-07-12 17:00:00',2,6,1,175500.00,'Transf. 8866692',NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(7,'PAG-PROV-2026-00007','2026-07-16 17:00:00',1,7,2,209100.00,'Transf. 9451357',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(8,'PAG-PROV-2026-00008','2026-07-20 17:00:00',1,8,1,184100.00,'Transf. 7619382',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(9,'PAG-PROV-2026-00009','2026-07-23 17:00:00',2,9,1,234000.00,'Transf. 9868588',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(10,'PAG-PROV-2026-00010','2026-07-29 17:00:00',1,11,1,221900.00,'Transf. 1190385',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(11,'PAG-PROV-2026-00011','2026-07-29 17:00:00',3,10,1,149800.00,'Transf. 6413996',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(12,'PAG-PROV-2026-00012','2026-07-31 17:00:00',2,13,2,234000.00,'Transf. 1454453',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(13,'PAG-PROV-2026-00013','2026-08-01 17:00:00',4,12,2,70000.00,'Transf. 9663777',NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(14,'PAG-PROV-2026-00014','2026-08-09 17:00:00',1,14,2,203000.00,'Transf. 8352413',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(15,'PAG-PROV-2026-00015','2026-08-11 17:00:00',2,16,2,234000.00,'Transf. 6877728',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(16,'PAG-PROV-2026-00016','2026-08-14 17:00:00',3,17,1,149800.00,'Transf. 3914200',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(17,'PAG-PROV-2026-00017','2026-08-15 17:00:00',1,15,2,196900.00,'Transf. 4702447',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(18,'PAG-PROV-2026-00018','2026-08-19 17:00:00',1,18,1,215800.00,'Transf. 4742659',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(19,'PAG-PROV-2026-00019','2026-08-22 17:00:00',2,19,2,175500.00,'Transf. 3379479',NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(20,'PAG-PROV-2026-00020','2026-08-26 17:00:00',4,21,2,70000.00,'Transf. 1032648',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(21,'PAG-PROV-2026-00021','2026-08-27 17:00:00',1,20,2,171300.00,'Transf. 5449856',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(22,'PAG-PROV-2026-00022','2026-09-02 17:00:00',2,22,2,234000.00,'Transf. 7657409',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(23,'PAG-PROV-2026-00023','2026-09-04 17:00:00',1,23,2,234700.00,'Transf. 3946962',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(24,'PAG-PROV-2026-00024','2026-09-07 17:00:00',3,24,2,149800.00,'Transf. 7594847',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(25,'PAG-PROV-2026-00025','2026-09-11 17:00:00',1,25,2,234700.00,'Transf. 1394617',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(26,'PAG-PROV-2026-00026','2026-09-11 17:00:00',2,26,1,175500.00,'Transf. 3526040',NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(27,'PAG-PROV-2026-00027','2026-09-16 17:00:00',1,27,1,190200.00,'Transf. 5323292',NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(28,'PAG-PROV-2026-00028','2026-09-19 17:00:00',4,28,2,70000.00,'Transf. 3197586',NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(29,'PAG-PROV-2026-00029','2026-09-21 17:00:00',2,29,2,234000.00,'Transf. 1097418',NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL);
/*!40000 ALTER TABLE `pagos_proveedor` ENABLE KEYS */;
UNLOCK TABLES;
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
DROP TABLE IF EXISTS `pedido_items`;
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
) ENGINE=InnoDB AUTO_INCREMENT=463 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `pedido_items` WRITE;
/*!40000 ALTER TABLE `pedido_items` DISABLE KEYS */;
INSERT INTO `pedido_items` VALUES
(1,1,1,'VENTA',2.00,16500.00,33000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(2,1,4,'VENTA',1.00,4200.00,4200.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(3,1,1,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(4,1,1,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(5,2,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(6,2,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(7,2,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(8,3,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(9,3,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(10,3,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(11,4,2,'VENTA',2.00,24000.00,48000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(12,4,5,'VENTA',2.00,6500.00,13000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(13,4,2,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(14,4,2,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(15,5,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(16,5,8,'VENTA',2.00,11500.00,23000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(17,5,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(18,5,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(19,6,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(20,6,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(21,6,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(22,7,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(23,7,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(24,7,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(25,8,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(26,8,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(27,8,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(28,9,3,'VENTA',2.00,72000.00,144000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(29,9,3,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(30,9,3,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(31,10,3,'VENTA',2.00,72000.00,144000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(32,10,8,'VENTA',1.00,11500.00,11500.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(33,10,3,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(34,10,3,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(35,11,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(36,11,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(37,11,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(38,12,2,'VENTA',2.00,24000.00,48000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(39,12,5,'VENTA',2.00,6500.00,13000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(40,12,2,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(41,12,2,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(42,13,2,'VENTA',2.00,24000.00,48000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(43,13,2,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(44,13,2,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(45,14,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(46,14,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(47,14,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(48,15,2,'VENTA',2.00,24000.00,48000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(49,15,4,'VENTA',1.00,4200.00,4200.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(50,15,2,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(51,15,2,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(52,16,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(53,16,7,'VENTA',2.00,27500.00,55000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(54,16,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(55,16,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(56,17,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(57,17,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(58,17,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(59,18,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(60,18,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(61,18,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(62,19,3,'VENTA',2.00,72000.00,144000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(63,19,5,'VENTA',2.00,6500.00,13000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(64,19,3,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(65,19,3,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(66,20,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(67,20,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(68,20,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(69,21,3,'VENTA',2.00,72000.00,144000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(70,22,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(71,22,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(72,22,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(73,23,2,'VENTA',2.00,24000.00,48000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(74,23,2,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(75,23,2,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(76,24,1,'VENTA',2.00,16500.00,33000.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(77,24,1,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(78,24,1,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:08','2026-09-25 00:38:08'),
(79,25,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(80,25,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(81,25,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(82,26,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(83,26,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(84,26,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(85,27,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(86,27,4,'VENTA',2.00,4200.00,8400.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(87,27,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(88,27,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(89,28,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(90,28,6,'VENTA',2.00,12000.00,24000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(91,28,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(92,28,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(93,29,1,'VENTA',2.00,16500.00,33000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(94,29,8,'VENTA',1.00,11500.00,11500.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(95,29,1,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(96,29,1,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(97,30,2,'VENTA',2.00,24000.00,48000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(98,30,5,'VENTA',2.00,6500.00,13000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(99,31,3,'VENTA',2.00,72000.00,144000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(100,31,3,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(101,31,3,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(102,32,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(103,32,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(104,32,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(105,33,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(106,33,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(107,33,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(108,34,3,'VENTA',1.00,72000.00,72000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(109,34,6,'VENTA',2.00,12000.00,24000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(110,34,3,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(111,34,3,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(112,35,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(113,35,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(114,35,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(115,36,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(116,36,8,'VENTA',2.00,11500.00,23000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(117,36,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(118,36,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(119,37,1,'VENTA',2.00,16500.00,33000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(120,37,1,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(121,37,1,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(122,38,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(123,38,4,'VENTA',1.00,4200.00,4200.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(124,38,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(125,38,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(126,39,2,'VENTA',2.00,24000.00,48000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(127,39,2,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(128,39,2,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(129,40,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(130,40,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(131,40,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(132,41,1,'VENTA',2.00,16500.00,33000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(133,41,1,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(134,41,1,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(135,42,3,'VENTA',1.00,72000.00,72000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(136,42,8,'VENTA',2.00,11500.00,23000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(137,42,3,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(138,42,3,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(139,43,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(140,43,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(141,43,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(142,44,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(143,44,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(144,44,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(145,45,3,'VENTA',2.00,72000.00,144000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(146,45,3,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(147,45,3,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(148,46,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(149,46,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(150,46,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(151,47,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(152,47,4,'VENTA',2.00,4200.00,8400.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(153,47,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(154,47,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(155,48,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(156,48,7,'VENTA',1.00,27500.00,27500.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(157,48,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(158,48,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(159,49,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(160,49,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(161,49,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(162,50,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(163,50,7,'VENTA',1.00,27500.00,27500.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(164,50,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(165,50,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(166,51,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(167,51,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(168,51,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(169,52,3,'VENTA',1.00,72000.00,72000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(170,52,3,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(171,52,3,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(172,53,3,'VENTA',1.00,72000.00,72000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(173,53,3,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(174,53,3,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(175,54,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(176,54,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(177,54,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(178,55,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(179,55,8,'VENTA',2.00,11500.00,23000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(180,55,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(181,55,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(182,56,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(183,56,4,'VENTA',1.00,4200.00,4200.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(184,56,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(185,56,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(186,57,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(187,57,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(188,57,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:09','2026-09-25 00:38:09'),
(189,58,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(190,58,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(191,58,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(192,59,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(193,59,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(194,59,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(195,60,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(196,60,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(197,60,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(198,61,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(199,61,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(200,61,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(201,62,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(202,62,5,'VENTA',1.00,6500.00,6500.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(203,62,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(204,62,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(205,63,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(206,63,6,'VENTA',1.00,12000.00,12000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(207,63,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(208,63,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(209,64,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(210,64,8,'VENTA',1.00,11500.00,11500.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(211,64,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(212,64,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(213,65,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(214,65,5,'VENTA',2.00,6500.00,13000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(215,65,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(216,65,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(217,66,3,'VENTA',2.00,72000.00,144000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(218,67,3,'VENTA',2.00,72000.00,144000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(219,67,4,'VENTA',1.00,4200.00,4200.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(220,67,3,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(221,67,3,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(222,68,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(223,68,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(224,68,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(225,69,2,'VENTA',2.00,24000.00,48000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(226,69,2,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(227,69,2,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(228,70,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(229,70,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(230,70,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(231,71,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(232,71,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(233,71,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(234,72,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(235,72,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(236,72,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(237,73,3,'VENTA',2.00,72000.00,144000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(238,73,3,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(239,73,3,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(240,74,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(241,74,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(242,74,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(243,75,1,'VENTA',2.00,16500.00,33000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(244,75,8,'VENTA',2.00,11500.00,23000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(245,75,1,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(246,75,1,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(247,76,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(248,76,6,'VENTA',2.00,12000.00,24000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(249,76,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(250,76,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(251,77,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(252,77,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(253,77,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(254,78,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(255,78,5,'VENTA',1.00,6500.00,6500.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(256,78,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(257,78,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(258,79,3,'VENTA',2.00,72000.00,144000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(259,79,5,'VENTA',2.00,6500.00,13000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(260,79,3,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(261,79,3,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(262,80,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(263,80,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(264,80,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(265,81,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(266,81,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(267,81,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(268,82,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(269,82,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(270,82,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(271,83,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(272,83,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(273,83,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(274,84,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(275,84,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(276,84,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(277,85,1,'VENTA',2.00,16500.00,33000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(278,85,4,'VENTA',2.00,4200.00,8400.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(279,85,1,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(280,85,1,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(281,86,3,'VENTA',1.00,72000.00,72000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(282,86,3,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(283,86,3,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(284,87,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(285,87,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(286,87,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(287,88,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(288,88,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(289,88,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(290,89,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(291,89,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(292,89,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(293,90,1,'VENTA',2.00,16500.00,33000.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(294,90,1,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(295,90,1,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(296,91,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(297,91,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(298,91,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:10','2026-09-25 00:38:10'),
(299,92,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(300,92,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(301,92,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(302,93,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(303,93,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(304,93,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(305,94,3,'VENTA',2.00,72000.00,144000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(306,94,3,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(307,94,3,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(308,95,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(309,95,7,'VENTA',1.00,27500.00,27500.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(310,95,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(311,95,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(312,96,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(313,96,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(314,96,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(315,97,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(316,97,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(317,97,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(318,98,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(319,99,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(320,99,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(321,99,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(322,100,3,'VENTA',1.00,72000.00,72000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(323,100,3,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(324,100,3,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(325,101,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(326,101,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(327,101,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(328,102,1,'VENTA',2.00,16500.00,33000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(329,102,1,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(330,102,1,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(331,103,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(332,103,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(333,103,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(334,104,3,'VENTA',2.00,72000.00,144000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(335,104,3,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(336,104,3,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(337,105,1,'VENTA',2.00,16500.00,33000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(338,105,1,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(339,105,1,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(340,106,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(341,106,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(342,106,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(343,107,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(344,107,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(345,107,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(346,108,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(347,108,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(348,108,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(349,109,3,'VENTA',1.00,72000.00,72000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(350,109,3,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(351,109,3,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(352,110,1,'VENTA',2.00,16500.00,33000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(353,110,1,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(354,110,1,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(355,111,2,'VENTA',2.00,24000.00,48000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(356,111,2,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(357,111,2,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(358,112,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(359,112,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(360,112,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(361,113,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(362,113,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(363,113,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(364,114,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(365,114,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(366,114,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(367,115,3,'VENTA',2.00,72000.00,144000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(368,115,3,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(369,115,3,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(370,116,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(371,116,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(372,116,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(373,117,1,'VENTA',2.00,16500.00,33000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(374,117,8,'VENTA',2.00,11500.00,23000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(375,117,1,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(376,117,1,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(377,118,2,'VENTA',2.00,24000.00,48000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(378,118,5,'VENTA',1.00,6500.00,6500.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(379,118,2,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(380,118,2,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(381,119,3,'VENTA',2.00,72000.00,144000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(382,119,3,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(383,119,3,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(384,120,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(385,120,6,'VENTA',2.00,12000.00,24000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(386,120,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(387,120,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(388,121,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(389,121,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(390,121,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(391,122,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(392,122,8,'VENTA',2.00,11500.00,23000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(393,122,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(394,122,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(395,123,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(396,123,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(397,123,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(398,124,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(399,124,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(400,124,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:11','2026-09-25 00:38:11'),
(401,125,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(402,125,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(403,125,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(404,126,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(405,126,4,'VENTA',2.00,4200.00,8400.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(406,126,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(407,126,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(408,127,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(409,127,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(410,127,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(411,128,3,'VENTA',1.00,72000.00,72000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(412,128,5,'VENTA',1.00,6500.00,6500.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(413,128,3,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(414,128,3,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(415,129,3,'VENTA',1.00,72000.00,72000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(416,130,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(417,130,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(418,130,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(419,131,2,'VENTA',2.00,24000.00,48000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(420,131,2,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(421,131,2,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(422,132,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(423,132,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(424,132,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(425,133,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(426,133,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(427,133,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(428,134,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(429,135,1,'VENTA',2.00,16500.00,33000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(430,135,5,'VENTA',1.00,6500.00,6500.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(431,135,1,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(432,135,1,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(433,136,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(434,136,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(435,136,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(436,137,3,'VENTA',1.00,72000.00,72000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(437,137,8,'VENTA',2.00,11500.00,23000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(438,137,3,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(439,137,3,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(440,138,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(441,138,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(442,138,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(443,139,1,'VENTA',1.00,16500.00,16500.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(444,139,1,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(445,139,1,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(446,140,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(447,140,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(448,140,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(449,141,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(450,141,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(451,141,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(452,142,1,'VENTA',2.00,16500.00,33000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(453,143,3,'VENTA',2.00,72000.00,144000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(454,143,3,'ENTREGA',2.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(455,143,3,'DEVOLUCION',2.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(456,144,3,'VENTA',2.00,72000.00,144000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(457,145,2,'VENTA',1.00,24000.00,24000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(458,145,2,'ENTREGA',1.00,0.00,0.00,'Envases llenos entregados','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(459,145,2,'DEVOLUCION',1.00,0.00,0.00,'Envases vacíos recibidos','2026-09-25 00:38:12','2026-09-25 00:38:12'),
(460,146,1,'VENTA',2.00,16500.00,33000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(461,147,1,'VENTA',2.00,16500.00,33000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(462,148,3,'VENTA',2.00,72000.00,144000.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12');
/*!40000 ALTER TABLE `pedido_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `pedidos`;
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
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `pedidos` WRITE;
/*!40000 ALTER TABLE `pedidos` DISABLE KEYS */;
INSERT INTO `pedidos` VALUES
(1,'PED-2026-00001','2026-06-28 08:30:00','2026-06-28 09:39:00',1,12,2,4,1,2,37200.00,0.00,37200.00,37200.00,0.00,NULL,'Laprida 2774, Tafí Viejo','2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(2,'PED-2026-00002','2026-06-28 09:45:00','2026-06-28 10:28:00',1,20,1,4,1,1,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Crisóstomo Álvarez 458, Tafí Viejo','2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(3,'PED-2026-00003','2026-06-29 08:15:00','2026-06-29 10:02:00',1,3,3,4,1,1,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Crisóstomo Álvarez 2850 Dto. 3B, San Miguel de Tucumán','2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(4,'PED-2026-00004','2026-06-29 09:15:00','2026-06-29 09:53:00',1,17,1,4,1,2,61000.00,0.00,61000.00,61000.00,0.00,NULL,'Jujuy 416 Dto. 4B, San Miguel de Tucumán','2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(5,'PED-2026-00005','2026-06-30 08:45:00','2026-06-30 10:44:00',1,4,2,4,1,2,39500.00,0.00,39500.00,39500.00,0.00,NULL,'Av. Mate de Luna 1591, San Miguel de Tucumán','2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(6,'PED-2026-00006','2026-06-30 09:00:00','2026-06-30 09:37:00',1,5,1,4,3,3,16500.00,0.00,16500.00,16500.00,0.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(7,'PED-2026-00007','2026-06-30 10:45:00','2026-06-30 11:27:00',1,18,3,4,3,3,24000.00,0.00,24000.00,24000.00,0.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(8,'PED-2026-00008','2026-07-01 08:15:00','2026-07-01 09:58:00',1,9,2,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Crisóstomo Álvarez 2144, Tafí Viejo','2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(9,'PED-2026-00009','2026-07-02 08:15:00','2026-07-02 09:38:00',1,22,1,4,1,1,144000.00,0.00,144000.00,144000.00,0.00,NULL,'Crisóstomo Álvarez 2316, San Miguel de Tucumán','2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(10,'PED-2026-00010','2026-07-03 08:00:00','2026-07-03 09:41:00',1,21,1,4,1,2,155500.00,0.00,155500.00,155500.00,0.00,NULL,'Congreso 1462, San Miguel de Tucumán','2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(11,'PED-2026-00011','2026-07-05 08:15:00','2026-07-05 09:39:00',1,6,2,4,1,2,16500.00,0.00,16500.00,16500.00,0.00,NULL,'San Juan 501, San Miguel de Tucumán','2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(12,'PED-2026-00012','2026-07-05 09:45:00','2026-07-05 11:06:00',1,19,3,4,3,3,61000.00,0.00,61000.00,61000.00,0.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(13,'PED-2026-00013','2026-07-06 08:45:00','2026-07-06 10:03:00',1,1,3,4,1,1,48000.00,0.00,48000.00,48000.00,0.00,NULL,'Santiago del Estero 2868 Dto. 2B, San Miguel de Tucumán','2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(14,'PED-2026-00014','2026-07-06 09:45:00','2026-07-06 10:18:00',1,10,2,4,3,3,16500.00,0.00,16500.00,16500.00,0.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(15,'PED-2026-00015','2026-07-06 10:15:00','2026-07-06 11:03:00',1,11,3,4,3,3,52200.00,0.00,52200.00,52200.00,0.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(16,'PED-2026-00016','2026-07-06 11:00:00','2026-07-06 12:31:00',1,17,2,4,1,1,79000.00,0.00,79000.00,79000.00,0.00,NULL,'Jujuy 416 Dto. 4B, San Miguel de Tucumán','2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(17,'PED-2026-00017','2026-07-07 08:00:00','2026-07-07 09:21:00',1,14,1,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Congreso 160, Tafí Viejo','2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(18,'PED-2026-00018','2026-07-07 09:00:00','2026-07-07 10:36:00',1,15,1,4,3,3,16500.00,0.00,16500.00,16500.00,0.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(19,'PED-2026-00019','2026-07-09 08:00:00','2026-07-09 09:30:00',1,21,2,4,3,3,157000.00,0.00,157000.00,157000.00,0.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(20,'PED-2026-00020','2026-07-10 08:30:00','2026-07-10 08:56:00',1,13,2,4,1,2,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Salta 2313, Tafí Viejo','2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(21,'PED-2026-00021','2026-07-11 08:30:00',NULL,0,22,1,5,1,2,144000.00,0.00,144000.00,0.00,144000.00,NULL,'Crisóstomo Álvarez 2316, San Miguel de Tucumán','2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(22,'PED-2026-00022','2026-07-12 08:15:00','2026-07-12 08:56:00',1,5,2,4,1,1,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Jujuy 2171, San Miguel de Tucumán','2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(23,'PED-2026-00023','2026-07-12 09:45:00','2026-07-12 10:52:00',1,8,1,4,3,3,48000.00,0.00,48000.00,48000.00,0.00,NULL,NULL,'2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(24,'PED-2026-00024','2026-07-13 08:30:00','2026-07-13 10:05:00',1,2,2,4,1,2,33000.00,0.00,33000.00,33000.00,0.00,NULL,'Laprida 965, Yerba Buena','2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(25,'PED-2026-00025','2026-07-13 09:45:00','2026-07-13 11:33:00',1,7,2,4,1,2,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Congreso 2110, Tafí Viejo','2026-09-25 00:38:08','2026-09-25 03:38:09',NULL,NULL,NULL),
(26,'PED-2026-00026','2026-07-13 10:45:00','2026-07-13 12:23:00',1,17,2,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Jujuy 416 Dto. 4B, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(27,'PED-2026-00027','2026-07-15 08:30:00','2026-07-15 09:57:00',1,20,1,4,3,3,24900.00,0.00,24900.00,24900.00,0.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(28,'PED-2026-00028','2026-07-16 08:45:00','2026-07-16 09:51:00',1,10,1,4,1,2,40500.00,0.00,40500.00,40500.00,0.00,NULL,'Salta 1742, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(29,'PED-2026-00029','2026-07-16 09:00:00','2026-07-16 09:47:00',1,16,3,4,1,2,44500.00,0.00,44500.00,44500.00,0.00,NULL,'Córdoba 2623, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(30,'PED-2026-00030','2026-07-16 10:30:00',NULL,0,19,3,5,1,2,61000.00,0.00,61000.00,0.00,61000.00,NULL,'Laprida 317 Dto. 4B, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 00:38:09',NULL,NULL,NULL),
(31,'PED-2026-00031','2026-07-16 11:30:00','2026-07-16 13:10:00',1,21,2,4,1,2,144000.00,0.00,144000.00,144000.00,0.00,NULL,'Congreso 1462, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(32,'PED-2026-00032','2026-07-17 08:45:00','2026-07-17 10:31:00',1,9,1,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Crisóstomo Álvarez 2144, Tafí Viejo','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(33,'PED-2026-00033','2026-07-17 09:45:00','2026-07-17 10:08:00',1,12,1,4,1,2,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Laprida 2774, Tafí Viejo','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(34,'PED-2026-00034','2026-07-17 10:45:00','2026-07-17 12:40:00',1,22,1,4,1,1,96000.00,0.00,96000.00,96000.00,0.00,NULL,'Crisóstomo Álvarez 2316, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(35,'PED-2026-00035','2026-07-18 08:30:00','2026-07-18 10:27:00',1,4,1,4,1,2,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Av. Mate de Luna 1591, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(36,'PED-2026-00036','2026-07-18 09:00:00','2026-07-18 10:26:00',1,18,1,4,1,2,47000.00,0.00,47000.00,47000.00,0.00,NULL,'Av. Aconquija 1693, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(37,'PED-2026-00037','2026-07-19 08:30:00','2026-07-19 09:29:00',1,15,1,4,3,3,33000.00,0.00,33000.00,33000.00,0.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(38,'PED-2026-00038','2026-07-20 08:30:00','2026-07-20 09:51:00',1,14,3,4,1,2,28200.00,0.00,28200.00,28200.00,0.00,NULL,'Congreso 160, Tafí Viejo','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(39,'PED-2026-00039','2026-07-21 08:30:00','2026-07-21 09:48:00',1,1,1,4,3,3,48000.00,0.00,48000.00,48000.00,0.00,NULL,NULL,'2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(40,'PED-2026-00040','2026-07-24 08:30:00','2026-07-24 10:06:00',1,5,3,4,1,2,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Jujuy 2171, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(41,'PED-2026-00041','2026-07-24 09:00:00','2026-07-24 10:42:00',1,13,3,4,1,1,33000.00,0.00,33000.00,33000.00,0.00,NULL,'Salta 2313, Tafí Viejo','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(42,'PED-2026-00042','2026-07-24 10:45:00','2026-07-24 12:26:00',1,21,2,4,1,2,95000.00,0.00,95000.00,95000.00,0.00,NULL,'Congreso 1462, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(43,'PED-2026-00043','2026-07-25 08:45:00','2026-07-25 09:52:00',1,11,3,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Crisóstomo Álvarez 1647, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(44,'PED-2026-00044','2026-07-25 09:30:00','2026-07-25 10:53:00',1,17,2,4,1,1,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Jujuy 416 Dto. 4B, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(45,'PED-2026-00045','2026-07-25 10:15:00','2026-07-25 11:12:00',1,22,1,4,1,2,144000.00,0.00,144000.00,144000.00,0.00,NULL,'Crisóstomo Álvarez 2316, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(46,'PED-2026-00046','2026-07-26 08:30:00','2026-07-26 09:30:00',1,3,1,4,1,1,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Crisóstomo Álvarez 2850 Dto. 3B, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(47,'PED-2026-00047','2026-07-26 09:45:00','2026-07-26 11:31:00',1,6,3,4,1,2,24900.00,0.00,24900.00,24900.00,0.00,NULL,'San Juan 501, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(48,'PED-2026-00048','2026-07-26 10:00:00','2026-07-26 11:10:00',1,10,1,4,1,1,44000.00,0.00,44000.00,44000.00,0.00,NULL,'Salta 1742, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(49,'PED-2026-00049','2026-07-27 08:30:00','2026-07-27 10:28:00',1,19,2,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Laprida 317 Dto. 4B, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(50,'PED-2026-00050','2026-07-29 08:45:00','2026-07-29 09:27:00',1,7,1,4,1,2,44000.00,0.00,44000.00,44000.00,0.00,NULL,'Congreso 2110, Tafí Viejo','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(51,'PED-2026-00051','2026-07-29 09:30:00','2026-07-29 10:06:00',1,9,2,4,1,1,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Crisóstomo Álvarez 2144, Tafí Viejo','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(52,'PED-2026-00052','2026-07-30 08:15:00','2026-07-30 09:55:00',1,21,3,4,1,2,72000.00,0.00,72000.00,72000.00,0.00,NULL,'Congreso 1462, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(53,'PED-2026-00053','2026-07-30 09:00:00','2026-07-30 10:46:00',1,22,2,4,1,2,72000.00,0.00,72000.00,72000.00,0.00,NULL,'Crisóstomo Álvarez 2316, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(54,'PED-2026-00054','2026-07-31 08:15:00','2026-07-31 09:15:00',1,8,3,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Mendoza 1087, Yerba Buena','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(55,'PED-2026-00055','2026-07-31 09:30:00','2026-07-31 09:50:00',1,15,2,4,1,1,39500.00,0.00,39500.00,39500.00,0.00,NULL,'Córdoba 355, Tafí Viejo','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(56,'PED-2026-00056','2026-08-01 08:30:00','2026-08-01 09:46:00',1,4,1,4,1,2,20700.00,0.00,20700.00,20700.00,0.00,NULL,'Av. Mate de Luna 1591, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(57,'PED-2026-00057','2026-08-02 08:45:00','2026-08-02 10:20:00',1,1,2,4,1,1,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Santiago del Estero 2868 Dto. 2B, San Miguel de Tucumán','2026-09-25 00:38:09','2026-09-25 03:38:10',NULL,NULL,NULL),
(58,'PED-2026-00058','2026-08-02 09:30:00','2026-08-02 10:28:00',1,14,1,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Congreso 160, Tafí Viejo','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(59,'PED-2026-00059','2026-08-02 10:45:00','2026-08-02 12:11:00',1,18,2,4,1,1,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Av. Aconquija 1693, San Miguel de Tucumán','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(60,'PED-2026-00060','2026-08-03 08:15:00','2026-08-03 09:40:00',1,2,3,4,1,2,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Laprida 965, Yerba Buena','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(61,'PED-2026-00061','2026-08-03 09:30:00','2026-08-03 11:27:00',1,20,3,4,1,2,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Crisóstomo Álvarez 458, Tafí Viejo','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(62,'PED-2026-00062','2026-08-04 08:15:00','2026-08-04 09:45:00',1,17,2,4,1,2,30500.00,0.00,30500.00,30500.00,0.00,NULL,'Jujuy 416 Dto. 4B, San Miguel de Tucumán','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(63,'PED-2026-00063','2026-08-05 08:15:00','2026-08-05 09:36:00',1,5,2,4,1,2,28500.00,0.00,28500.00,28500.00,0.00,NULL,'Jujuy 2171, San Miguel de Tucumán','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(64,'PED-2026-00064','2026-08-05 09:45:00','2026-08-05 11:11:00',1,12,3,4,1,2,28000.00,0.00,28000.00,28000.00,0.00,NULL,'Laprida 2774, Tafí Viejo','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(65,'PED-2026-00065','2026-08-06 08:00:00','2026-08-06 09:42:00',1,10,2,4,1,1,29500.00,0.00,29500.00,29500.00,0.00,NULL,'Salta 1742, San Miguel de Tucumán','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(66,'PED-2026-00066','2026-08-06 09:30:00',NULL,0,21,3,5,1,2,144000.00,0.00,144000.00,0.00,144000.00,NULL,'Congreso 1462, San Miguel de Tucumán','2026-09-25 00:38:10','2026-09-25 00:38:10',NULL,NULL,NULL),
(67,'PED-2026-00067','2026-08-07 08:15:00','2026-08-07 09:27:00',1,22,1,4,3,3,148200.00,0.00,148200.00,148200.00,0.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(68,'PED-2026-00068','2026-08-08 08:15:00','2026-08-08 09:43:00',1,13,3,4,3,3,16500.00,0.00,16500.00,16500.00,0.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(69,'PED-2026-00069','2026-08-09 08:15:00','2026-08-09 09:43:00',1,11,3,4,3,3,48000.00,0.00,48000.00,48000.00,0.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(70,'PED-2026-00070','2026-08-10 08:45:00','2026-08-10 10:38:00',1,19,3,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Laprida 317 Dto. 4B, San Miguel de Tucumán','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(71,'PED-2026-00071','2026-08-11 08:30:00','2026-08-11 09:10:00',1,9,2,4,3,3,24000.00,0.00,24000.00,24000.00,0.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(72,'PED-2026-00072','2026-08-11 09:30:00','2026-08-11 11:20:00',1,16,1,4,1,2,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Córdoba 2623, San Miguel de Tucumán','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(73,'PED-2026-00073','2026-08-14 08:45:00','2026-08-14 09:29:00',1,21,3,4,1,2,144000.00,0.00,144000.00,144000.00,0.00,NULL,'Congreso 1462, San Miguel de Tucumán','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(74,'PED-2026-00074','2026-08-15 08:30:00','2026-08-15 09:07:00',1,1,2,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Santiago del Estero 2868 Dto. 2B, San Miguel de Tucumán','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(75,'PED-2026-00075','2026-08-15 09:30:00','2026-08-15 11:10:00',1,4,1,4,3,3,56000.00,0.00,56000.00,56000.00,0.00,NULL,NULL,'2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(76,'PED-2026-00076','2026-08-15 10:15:00','2026-08-15 11:43:00',1,8,2,4,1,1,48000.00,0.00,48000.00,48000.00,0.00,NULL,'Mendoza 1087, Yerba Buena','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(77,'PED-2026-00077','2026-08-15 11:45:00','2026-08-15 13:33:00',1,14,1,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Congreso 160, Tafí Viejo','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(78,'PED-2026-00078','2026-08-15 12:00:00','2026-08-15 13:29:00',1,15,2,4,1,2,23000.00,0.00,23000.00,23000.00,0.00,NULL,'Córdoba 355, Tafí Viejo','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(79,'PED-2026-00079','2026-08-15 13:30:00','2026-08-15 14:28:00',1,22,1,4,1,2,157000.00,0.00,157000.00,157000.00,0.00,NULL,'Crisóstomo Álvarez 2316, San Miguel de Tucumán','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(80,'PED-2026-00080','2026-08-16 08:30:00','2026-08-16 10:29:00',1,6,3,4,1,1,16500.00,0.00,16500.00,16500.00,0.00,NULL,'San Juan 501, San Miguel de Tucumán','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(81,'PED-2026-00081','2026-08-16 09:45:00','2026-08-16 11:21:00',1,7,3,4,1,2,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Congreso 2110, Tafí Viejo','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(82,'PED-2026-00082','2026-08-16 10:15:00','2026-08-16 12:04:00',1,17,1,4,1,1,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Jujuy 416 Dto. 4B, San Miguel de Tucumán','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(83,'PED-2026-00083','2026-08-17 08:45:00','2026-08-17 09:32:00',1,18,3,4,1,1,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Av. Aconquija 1693, San Miguel de Tucumán','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(84,'PED-2026-00084','2026-08-18 08:45:00','2026-08-18 09:12:00',1,5,2,4,1,2,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Jujuy 2171, San Miguel de Tucumán','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(85,'PED-2026-00085','2026-08-20 08:15:00','2026-08-20 09:40:00',1,10,2,4,1,1,41400.00,0.00,41400.00,41400.00,0.00,NULL,'Salta 1742, San Miguel de Tucumán','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(86,'PED-2026-00086','2026-08-20 09:30:00','2026-08-20 11:09:00',1,21,3,4,1,2,72000.00,0.00,72000.00,72000.00,0.00,NULL,'Congreso 1462, San Miguel de Tucumán','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(87,'PED-2026-00087','2026-08-21 08:30:00','2026-08-21 09:22:00',1,3,2,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Crisóstomo Álvarez 2850 Dto. 3B, San Miguel de Tucumán','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(88,'PED-2026-00088','2026-08-21 09:00:00','2026-08-21 09:35:00',1,13,1,4,1,1,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Salta 2313, Tafí Viejo','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(89,'PED-2026-00089','2026-08-22 08:00:00','2026-08-22 09:46:00',1,20,1,4,1,2,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Crisóstomo Álvarez 458, Tafí Viejo','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(90,'PED-2026-00090','2026-08-23 08:45:00','2026-08-23 10:31:00',1,2,3,4,1,2,33000.00,0.00,33000.00,33000.00,0.00,NULL,'Laprida 965, Yerba Buena','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(91,'PED-2026-00091','2026-08-23 09:00:00','2026-08-23 10:53:00',1,12,2,4,1,2,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Laprida 2774, Tafí Viejo','2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(92,'PED-2026-00092','2026-08-24 08:45:00','2026-08-24 09:34:00',1,9,3,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Crisóstomo Álvarez 2144, Tafí Viejo','2026-09-25 00:38:10','2026-09-25 03:38:11',NULL,NULL,NULL),
(93,'PED-2026-00093','2026-08-24 09:15:00','2026-08-24 10:30:00',1,19,2,4,1,1,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Laprida 317 Dto. 4B, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(94,'PED-2026-00094','2026-08-24 10:30:00','2026-08-24 11:46:00',1,22,2,4,1,1,144000.00,0.00,144000.00,144000.00,0.00,NULL,'Crisóstomo Álvarez 2316, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(95,'PED-2026-00095','2026-08-25 08:00:00','2026-08-25 09:36:00',1,11,2,4,3,3,51500.00,0.00,51500.00,51500.00,0.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(96,'PED-2026-00096','2026-08-25 09:45:00','2026-08-25 11:03:00',1,17,3,4,1,1,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Jujuy 416 Dto. 4B, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(97,'PED-2026-00097','2026-08-26 08:00:00','2026-08-26 09:22:00',1,14,3,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Congreso 160, Tafí Viejo','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(98,'PED-2026-00098','2026-08-26 09:00:00',NULL,0,15,1,5,1,2,16500.00,0.00,16500.00,0.00,16500.00,NULL,'Córdoba 355, Tafí Viejo','2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(99,'PED-2026-00099','2026-08-27 08:45:00','2026-08-27 09:24:00',1,1,1,4,1,1,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Santiago del Estero 2868 Dto. 2B, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(100,'PED-2026-00100','2026-08-29 08:00:00','2026-08-29 09:38:00',1,21,1,4,1,2,72000.00,0.00,72000.00,72000.00,0.00,NULL,'Congreso 1462, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(101,'PED-2026-00101','2026-08-30 08:30:00','2026-08-30 10:06:00',1,8,2,4,1,1,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Mendoza 1087, Yerba Buena','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(102,'PED-2026-00102','2026-08-31 08:00:00','2026-08-31 09:01:00',1,4,2,4,1,2,33000.00,0.00,33000.00,33000.00,0.00,NULL,'Av. Mate de Luna 1591, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(103,'PED-2026-00103','2026-08-31 09:30:00','2026-08-31 09:56:00',1,5,2,4,1,2,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Jujuy 2171, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(104,'PED-2026-00104','2026-08-31 10:45:00','2026-08-31 11:27:00',1,22,1,4,1,2,144000.00,0.00,144000.00,144000.00,0.00,NULL,'Crisóstomo Álvarez 2316, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(105,'PED-2026-00105','2026-09-01 08:30:00','2026-09-01 09:25:00',1,7,2,4,3,3,33000.00,0.00,33000.00,33000.00,0.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(106,'PED-2026-00106','2026-09-01 09:30:00','2026-09-01 10:16:00',1,10,2,4,3,3,16500.00,0.00,16500.00,16500.00,0.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(107,'PED-2026-00107','2026-09-02 08:30:00','2026-09-02 09:17:00',1,13,1,4,3,3,16500.00,0.00,16500.00,16500.00,0.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(108,'PED-2026-00108','2026-09-04 08:00:00','2026-09-04 08:46:00',1,19,3,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Laprida 317 Dto. 4B, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(109,'PED-2026-00109','2026-09-04 09:30:00','2026-09-04 11:11:00',1,21,3,4,1,2,72000.00,0.00,72000.00,72000.00,0.00,NULL,'Congreso 1462, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(110,'PED-2026-00110','2026-09-05 08:15:00','2026-09-05 09:21:00',1,6,2,4,1,1,33000.00,0.00,33000.00,33000.00,0.00,NULL,'San Juan 501, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(111,'PED-2026-00111','2026-09-05 09:15:00','2026-09-05 10:12:00',1,14,2,4,1,1,48000.00,0.00,48000.00,48000.00,0.00,NULL,'Congreso 160, Tafí Viejo','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(112,'PED-2026-00112','2026-09-05 10:45:00','2026-09-05 11:38:00',1,16,1,4,1,2,16500.00,0.00,16500.00,8300.00,8200.00,NULL,'Córdoba 2623, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(113,'PED-2026-00113','2026-09-05 11:30:00','2026-09-05 13:29:00',1,17,2,4,3,3,24000.00,0.00,24000.00,24000.00,0.00,NULL,NULL,'2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(114,'PED-2026-00114','2026-09-06 08:30:00','2026-09-06 09:52:00',1,18,3,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Av. Aconquija 1693, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(115,'PED-2026-00115','2026-09-06 09:15:00','2026-09-06 10:47:00',1,22,3,4,1,2,144000.00,0.00,144000.00,144000.00,0.00,NULL,'Crisóstomo Álvarez 2316, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(116,'PED-2026-00116','2026-09-07 08:00:00','2026-09-07 08:46:00',1,15,3,4,1,1,16500.00,0.00,16500.00,8300.00,8200.00,NULL,'Córdoba 355, Tafí Viejo','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(117,'PED-2026-00117','2026-09-07 09:45:00','2026-09-07 10:57:00',1,20,1,4,1,2,56000.00,0.00,56000.00,0.00,56000.00,NULL,'Crisóstomo Álvarez 458, Tafí Viejo','2026-09-25 00:38:11','2026-09-25 00:38:11',NULL,NULL,NULL),
(118,'PED-2026-00118','2026-09-08 08:45:00','2026-09-08 10:42:00',1,9,3,4,1,2,54500.00,0.00,54500.00,54500.00,0.00,NULL,'Crisóstomo Álvarez 2144, Tafí Viejo','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(119,'PED-2026-00119','2026-09-08 09:15:00','2026-09-08 10:43:00',1,21,3,4,1,2,144000.00,0.00,144000.00,144000.00,0.00,NULL,'Congreso 1462, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(120,'PED-2026-00120','2026-09-10 08:30:00','2026-09-10 10:10:00',1,1,3,4,1,2,48000.00,0.00,48000.00,24000.00,24000.00,NULL,'Santiago del Estero 2868 Dto. 2B, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(121,'PED-2026-00121','2026-09-10 09:15:00','2026-09-10 10:17:00',1,10,1,4,1,1,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Salta 1742, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(122,'PED-2026-00122','2026-09-11 08:30:00','2026-09-11 09:46:00',1,2,2,4,1,2,39500.00,0.00,39500.00,39500.00,0.00,NULL,'Laprida 965, Yerba Buena','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(123,'PED-2026-00123','2026-09-11 09:00:00','2026-09-11 10:56:00',1,11,3,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Crisóstomo Álvarez 1647, San Miguel de Tucumán','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(124,'PED-2026-00124','2026-09-12 08:00:00','2026-09-12 09:54:00',1,12,2,4,1,2,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Laprida 2774, Tafí Viejo','2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(125,'PED-2026-00125','2026-09-13 08:30:00','2026-09-13 09:21:00',1,8,1,4,1,2,24000.00,0.00,24000.00,0.00,24000.00,NULL,'Mendoza 1087, Yerba Buena','2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(126,'PED-2026-00126','2026-09-14 08:00:00','2026-09-14 09:47:00',1,5,2,4,1,2,24900.00,0.00,24900.00,24900.00,0.00,NULL,'Jujuy 2171, San Miguel de Tucumán','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(127,'PED-2026-00127','2026-09-14 09:15:00','2026-09-14 10:03:00',1,17,1,4,3,3,24000.00,0.00,24000.00,24000.00,0.00,NULL,NULL,'2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(128,'PED-2026-00128','2026-09-14 10:30:00','2026-09-14 11:38:00',1,21,2,4,3,3,78500.00,0.00,78500.00,78500.00,0.00,NULL,NULL,'2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(129,'PED-2026-00129','2026-09-14 11:45:00',NULL,0,22,2,5,3,3,72000.00,0.00,72000.00,0.00,72000.00,NULL,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(130,'PED-2026-00130','2026-09-15 08:15:00','2026-09-15 09:33:00',1,4,2,4,3,3,16500.00,0.00,16500.00,16500.00,0.00,NULL,NULL,'2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(131,'PED-2026-00131','2026-09-17 08:00:00','2026-09-17 09:29:00',1,3,1,4,1,2,48000.00,0.00,48000.00,48000.00,0.00,NULL,'Crisóstomo Álvarez 2850 Dto. 3B, San Miguel de Tucumán','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(132,'PED-2026-00132','2026-09-17 09:45:00','2026-09-17 10:35:00',1,13,2,4,1,2,16500.00,0.00,16500.00,8300.00,8200.00,NULL,'Salta 2313, Tafí Viejo','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(133,'PED-2026-00133','2026-09-18 08:00:00','2026-09-18 09:45:00',1,7,3,4,1,1,16500.00,0.00,16500.00,16500.00,0.00,NULL,'Congreso 2110, Tafí Viejo','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(134,'PED-2026-00134','2026-09-18 09:45:00',NULL,0,14,3,5,1,1,24000.00,0.00,24000.00,0.00,24000.00,NULL,'Congreso 160, Tafí Viejo','2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(135,'PED-2026-00135','2026-09-18 10:00:00','2026-09-18 11:18:00',1,15,1,4,1,2,39500.00,0.00,39500.00,39500.00,0.00,NULL,'Córdoba 355, Tafí Viejo','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(136,'PED-2026-00136','2026-09-18 11:00:00','2026-09-18 11:39:00',1,19,1,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Laprida 317 Dto. 4B, San Miguel de Tucumán','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(137,'PED-2026-00137','2026-09-19 08:45:00','2026-09-19 09:05:00',1,21,3,4,1,1,95000.00,0.00,95000.00,95000.00,0.00,NULL,'Congreso 1462, San Miguel de Tucumán','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(138,'PED-2026-00138','2026-09-20 08:45:00','2026-09-20 10:42:00',1,1,2,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Santiago del Estero 2868 Dto. 2B, San Miguel de Tucumán','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(139,'PED-2026-00139','2026-09-20 09:30:00','2026-09-20 11:01:00',1,10,1,4,3,3,16500.00,0.00,16500.00,8300.00,8200.00,NULL,NULL,'2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(140,'PED-2026-00140','2026-09-21 08:00:00','2026-09-21 09:15:00',1,9,2,4,1,2,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Crisóstomo Álvarez 2144, Tafí Viejo','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(141,'PED-2026-00141','2026-09-21 09:45:00','2026-09-21 10:17:00',1,18,1,4,3,3,24000.00,0.00,24000.00,24000.00,0.00,NULL,NULL,'2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(142,'PED-2026-00142','2026-09-22 08:15:00',NULL,0,6,2,5,1,2,33000.00,0.00,33000.00,0.00,33000.00,NULL,'San Juan 501, San Miguel de Tucumán','2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(143,'PED-2026-00143','2026-09-22 09:15:00','2026-09-22 10:19:00',1,22,1,4,1,1,144000.00,0.00,144000.00,144000.00,0.00,NULL,'Crisóstomo Álvarez 2316, San Miguel de Tucumán','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(144,'PED-2026-00144','2026-09-23 08:15:00',NULL,0,21,2,5,1,1,144000.00,0.00,144000.00,0.00,144000.00,NULL,'Congreso 1462, San Miguel de Tucumán','2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(145,'PED-2026-00145','2026-09-24 08:30:00','2026-09-24 09:51:00',1,17,2,4,1,1,24000.00,0.00,24000.00,24000.00,0.00,NULL,'Jujuy 416 Dto. 4B, San Miguel de Tucumán','2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(146,'PED-2026-00146','2026-09-25 08:15:00',NULL,0,2,1,1,3,3,33000.00,0.00,33000.00,0.00,33000.00,NULL,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(147,'PED-2026-00147','2026-09-25 09:30:00',NULL,0,4,2,1,1,2,33000.00,0.00,33000.00,0.00,33000.00,NULL,'Av. Mate de Luna 1591, San Miguel de Tucumán','2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(148,'PED-2026-00148','2026-09-25 10:45:00',NULL,0,22,2,3,1,1,144000.00,0.00,144000.00,0.00,144000.00,NULL,'Crisóstomo Álvarez 2316, San Miguel de Tucumán','2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL);
/*!40000 ALTER TABLE `pedidos` ENABLE KEYS */;
UNLOCK TABLES;
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
DROP TABLE IF EXISTS `productos`;
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

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES
(1,'GAR10','Garrafa 10 kg',NULL,1,10.00,'GARRAFA',16500.00,12800.00,0.00,15.00,1,1,'2026-09-25 00:37:54','2026-09-25 00:37:54',NULL,NULL,NULL),
(2,'GAR15','Garrafa 15 kg',NULL,1,15.00,'GARRAFA',24000.00,18900.00,0.00,10.00,1,1,'2026-09-25 00:37:54','2026-09-25 00:37:54',NULL,NULL,NULL),
(3,'GAR45','Garrafa 45 kg',NULL,1,45.00,'GARRAFA',72000.00,58500.00,0.00,4.00,1,1,'2026-09-25 00:37:54','2026-09-25 00:37:54',NULL,NULL,NULL),
(4,'CAR03','Carbón 3 kg',NULL,2,3.00,'BOLSA',4200.00,2600.00,57.00,15.00,0,1,'2026-09-25 00:37:54','2026-09-25 00:38:12',NULL,NULL,NULL),
(5,'CAR05','Carbón 5 kg',NULL,2,5.00,'BOLSA',6500.00,4100.00,51.00,15.00,0,1,'2026-09-25 00:37:54','2026-09-25 00:38:12',NULL,NULL,NULL),
(6,'CAR10','Carbón 10 kg',NULL,2,10.00,'BOLSA',12000.00,7800.00,31.00,10.00,0,1,'2026-09-25 00:37:54','2026-09-25 00:38:12',NULL,NULL,NULL),
(7,'CAR25','Carbón 25 kg',NULL,2,25.00,'BOLSA',27500.00,18000.00,10.00,4.00,0,1,'2026-09-25 00:37:54','2026-09-25 00:38:12',NULL,NULL,NULL),
(8,'LEN25','Leña para hogar 25 kg',NULL,3,25.00,'BOLSA',11500.00,7000.00,36.00,10.00,0,1,'2026-09-25 00:37:54','2026-09-25 00:38:12',NULL,NULL,NULL);
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `proveedores`;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES
(1,NULL,'Distribuidora GasNor S.R.L.','GasNor','30-71234567-8','381 430-1122',NULL,'ventas@gasnor.com.ar','Ruta 9 km 1290',NULL,NULL,NULL,'Tafí Viejo',NULL,24,NULL,'Ing. Oscar Paz','381 512-3344',NULL,'Entrega martes y viernes. Alias: GASNOR.VENTAS',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(2,NULL,'Envasadora del Norte S.A.','EnvNorte','30-70987654-3','381 455-9090',NULL,'pedidos@envnorte.com.ar','Parque Industrial Lote 14',NULL,NULL,NULL,'San Miguel de Tucumán',NULL,24,NULL,'Carolina Vega','381 600-7788',NULL,'Garrafas de 45 kg. Pago a 15 días.',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(3,NULL,'Carbonera El Quebracho',NULL,'20-25111222-5','385 411-2020',NULL,NULL,'Ruta 16 km 12',NULL,NULL,NULL,'Monte Quemado',NULL,22,NULL,'Ramón Quiroga','385 411-2020',NULL,'Contado. Alias: QUEBRACHO.CARBON',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL),
(4,NULL,'Leñera Monte Verde',NULL,'20-30444555-1','381 622-4455',NULL,NULL,'Camino a Lules s/n',NULL,NULL,NULL,'Famaillá',NULL,24,NULL,'Julio Sosa','381 622-4455',NULL,'Leña de quebracho y algarrobo.',1,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL);
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `provincias`;
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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `provincias` WRITE;
/*!40000 ALTER TABLE `provincias` DISABLE KEYS */;
INSERT INTO `provincias` VALUES
(1,'CABA','Ciudad Autónoma de Buenos Aires','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(2,'BA','Buenos Aires','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(3,'CAT','Catamarca','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(4,'CHA','Chaco','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(5,'CHU','Chubut','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(6,'COR','Córdoba','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(7,'CRR','Corrientes','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(8,'ER','Entre Ríos','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(9,'FOR','Formosa','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(10,'JUJ','Jujuy','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(11,'LP','La Pampa','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(12,'LR','La Rioja','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(13,'MZA','Mendoza','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(14,'MIS','Misiones','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(15,'NQN','Neuquén','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(16,'RN','Río Negro','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(17,'SAL','Salta','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(18,'SJ','San Juan','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(19,'SL','San Luis','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(20,'SC','Santa Cruz','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(21,'SF','Santa Fe','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(22,'SE','Santiago del Estero','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(23,'TF','Tierra del Fuego','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(24,'TUC','Tucumán','Argentina','2026-09-25 03:37:54','2026-09-25 03:37:54');
/*!40000 ALTER TABLE `provincias` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `recepcion_items`;
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
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `recepcion_items` WRITE;
/*!40000 ALTER TABLE `recepcion_items` DISABLE KEYS */;
INSERT INTO `recepcion_items` VALUES
(1,1,1,8.00,12800.00,102400.00,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(2,1,2,7.00,18900.00,132300.00,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(3,2,3,3.00,58500.00,175500.00,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(4,3,4,10.00,2600.00,26000.00,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(5,3,5,10.00,4100.00,41000.00,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(6,3,6,6.00,7800.00,46800.00,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(7,3,7,2.00,18000.00,36000.00,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(8,4,8,10.00,7000.00,70000.00,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(9,5,1,6.00,12800.00,76800.00,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(10,5,2,5.00,18900.00,94500.00,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(11,6,3,3.00,58500.00,175500.00,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(12,7,1,6.00,12800.00,76800.00,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(13,7,2,7.00,18900.00,132300.00,'2026-09-25 00:38:08','2026-09-25 00:38:08'),
(14,8,1,7.00,12800.00,89600.00,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(15,8,2,5.00,18900.00,94500.00,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(16,9,3,4.00,58500.00,234000.00,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(17,10,4,10.00,2600.00,26000.00,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(18,10,5,10.00,4100.00,41000.00,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(19,10,6,6.00,7800.00,46800.00,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(20,10,7,2.00,18000.00,36000.00,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(21,11,1,7.00,12800.00,89600.00,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(22,11,2,7.00,18900.00,132300.00,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(23,12,8,10.00,7000.00,70000.00,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(24,13,3,4.00,58500.00,234000.00,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(25,14,1,7.00,12800.00,89600.00,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(26,14,2,6.00,18900.00,113400.00,'2026-09-25 00:38:09','2026-09-25 00:38:09'),
(27,15,1,8.00,12800.00,102400.00,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(28,15,2,5.00,18900.00,94500.00,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(29,16,3,4.00,58500.00,234000.00,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(30,17,4,10.00,2600.00,26000.00,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(31,17,5,10.00,4100.00,41000.00,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(32,17,6,6.00,7800.00,46800.00,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(33,17,7,2.00,18000.00,36000.00,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(34,18,1,8.00,12800.00,102400.00,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(35,18,2,6.00,18900.00,113400.00,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(36,19,3,3.00,58500.00,175500.00,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(37,20,1,6.00,12800.00,76800.00,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(38,20,2,5.00,18900.00,94500.00,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(39,21,8,10.00,7000.00,70000.00,'2026-09-25 00:38:10','2026-09-25 00:38:10'),
(40,22,3,4.00,58500.00,234000.00,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(41,23,1,8.00,12800.00,102400.00,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(42,23,2,7.00,18900.00,132300.00,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(43,24,4,10.00,2600.00,26000.00,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(44,24,5,10.00,4100.00,41000.00,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(45,24,6,6.00,7800.00,46800.00,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(46,24,7,2.00,18000.00,36000.00,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(47,25,1,8.00,12800.00,102400.00,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(48,25,2,7.00,18900.00,132300.00,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(49,26,3,3.00,58500.00,175500.00,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(50,27,1,6.00,12800.00,76800.00,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(51,27,2,6.00,18900.00,113400.00,'2026-09-25 00:38:11','2026-09-25 00:38:11'),
(52,28,8,10.00,7000.00,70000.00,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(53,29,3,4.00,58500.00,234000.00,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(54,30,1,8.00,12800.00,102400.00,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(55,30,2,6.00,18900.00,113400.00,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(56,31,4,10.00,2600.00,26000.00,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(57,31,5,10.00,4100.00,41000.00,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(58,31,6,6.00,7800.00,46800.00,'2026-09-25 00:38:12','2026-09-25 00:38:12'),
(59,31,7,2.00,18000.00,36000.00,'2026-09-25 00:38:12','2026-09-25 00:38:12');
/*!40000 ALTER TABLE `recepcion_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `recepciones_proveedor`;
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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `recepciones_proveedor` WRITE;
/*!40000 ALTER TABLE `recepciones_proveedor` DISABLE KEYS */;
INSERT INTO `recepciones_proveedor` VALUES
(1,'REC-PROV-2026-00001','2026-06-28 09:30:00',1,1,'A-0001-64319322',234700.00,0.00,234700.00,234700.00,0.00,NULL,'2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(2,'REC-PROV-2026-00002','2026-06-30 09:30:00',2,1,'A-0001-09130698',175500.00,0.00,175500.00,175500.00,0.00,NULL,'2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(3,'REC-PROV-2026-00003','2026-07-02 09:30:00',3,2,'A-0001-39484896',149800.00,0.00,149800.00,149800.00,0.00,NULL,'2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(4,'REC-PROV-2026-00004','2026-07-04 09:30:00',4,2,'A-0001-99839037',70000.00,0.00,70000.00,70000.00,0.00,NULL,'2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(5,'REC-PROV-2026-00005','2026-07-05 09:30:00',1,1,'A-0001-25192156',171300.00,0.00,171300.00,171300.00,0.00,NULL,'2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(6,'REC-PROV-2026-00006','2026-07-10 09:30:00',2,1,'A-0001-83212298',175500.00,0.00,175500.00,175500.00,0.00,NULL,'2026-09-25 00:38:08','2026-09-25 03:38:08',NULL,NULL,NULL),
(7,'REC-PROV-2026-00007','2026-07-12 09:30:00',1,1,'A-0001-21256727',209100.00,0.00,209100.00,209100.00,0.00,NULL,'2026-09-25 00:38:08','2026-09-25 03:38:09',NULL,NULL,NULL),
(8,'REC-PROV-2026-00008','2026-07-19 09:30:00',1,1,'A-0001-54803389',184100.00,0.00,184100.00,184100.00,0.00,NULL,'2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(9,'REC-PROV-2026-00009','2026-07-20 09:30:00',2,1,'A-0001-36093914',234000.00,0.00,234000.00,234000.00,0.00,NULL,'2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(10,'REC-PROV-2026-00010','2026-07-23 09:30:00',3,2,'A-0001-90744795',149800.00,0.00,149800.00,149800.00,0.00,NULL,'2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(11,'REC-PROV-2026-00011','2026-07-26 09:30:00',1,1,'A-0001-70809939',221900.00,0.00,221900.00,221900.00,0.00,NULL,'2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(12,'REC-PROV-2026-00012','2026-07-29 09:30:00',4,2,'A-0001-70244657',70000.00,0.00,70000.00,70000.00,0.00,NULL,'2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(13,'REC-PROV-2026-00013','2026-07-30 09:30:00',2,1,'A-0001-84876998',234000.00,0.00,234000.00,234000.00,0.00,NULL,'2026-09-25 00:38:09','2026-09-25 03:38:09',NULL,NULL,NULL),
(14,'REC-PROV-2026-00014','2026-08-02 09:30:00',1,1,'A-0001-80202608',203000.00,0.00,203000.00,203000.00,0.00,NULL,'2026-09-25 00:38:09','2026-09-25 03:38:10',NULL,NULL,NULL),
(15,'REC-PROV-2026-00015','2026-08-09 09:30:00',1,1,'A-0001-75985366',196900.00,0.00,196900.00,196900.00,0.00,NULL,'2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(16,'REC-PROV-2026-00016','2026-08-09 09:30:00',2,1,'A-0001-65451926',234000.00,0.00,234000.00,234000.00,0.00,NULL,'2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(17,'REC-PROV-2026-00017','2026-08-13 09:30:00',3,2,'A-0001-25275822',149800.00,0.00,149800.00,149800.00,0.00,NULL,'2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(18,'REC-PROV-2026-00018','2026-08-16 09:30:00',1,1,'A-0001-79188201',215800.00,0.00,215800.00,215800.00,0.00,NULL,'2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(19,'REC-PROV-2026-00019','2026-08-19 09:30:00',2,1,'A-0001-98084850',175500.00,0.00,175500.00,175500.00,0.00,NULL,'2026-09-25 00:38:10','2026-09-25 03:38:10',NULL,NULL,NULL),
(20,'REC-PROV-2026-00020','2026-08-23 09:30:00',1,1,'A-0001-80387728',171300.00,0.00,171300.00,171300.00,0.00,NULL,'2026-09-25 00:38:10','2026-09-25 03:38:11',NULL,NULL,NULL),
(21,'REC-PROV-2026-00021','2026-08-23 09:30:00',4,2,'A-0001-54924660',70000.00,0.00,70000.00,70000.00,0.00,NULL,'2026-09-25 00:38:10','2026-09-25 03:38:11',NULL,NULL,NULL),
(22,'REC-PROV-2026-00022','2026-08-29 09:30:00',2,1,'A-0001-53019767',234000.00,0.00,234000.00,234000.00,0.00,NULL,'2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(23,'REC-PROV-2026-00023','2026-08-30 09:30:00',1,1,'A-0001-36041600',234700.00,0.00,234700.00,234700.00,0.00,NULL,'2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(24,'REC-PROV-2026-00024','2026-09-03 09:30:00',3,2,'A-0001-98336402',149800.00,0.00,149800.00,149800.00,0.00,NULL,'2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(25,'REC-PROV-2026-00025','2026-09-06 09:30:00',1,1,'A-0001-98591032',234700.00,0.00,234700.00,234700.00,0.00,NULL,'2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(26,'REC-PROV-2026-00026','2026-09-08 09:30:00',2,1,'A-0001-02274715',175500.00,0.00,175500.00,175500.00,0.00,NULL,'2026-09-25 00:38:11','2026-09-25 03:38:11',NULL,NULL,NULL),
(27,'REC-PROV-2026-00027','2026-09-13 09:30:00',1,1,'A-0001-44048807',190200.00,0.00,190200.00,190200.00,0.00,NULL,'2026-09-25 00:38:11','2026-09-25 03:38:12',NULL,NULL,NULL),
(28,'REC-PROV-2026-00028','2026-09-17 09:30:00',4,2,'A-0001-45284345',70000.00,0.00,70000.00,70000.00,0.00,NULL,'2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(29,'REC-PROV-2026-00029','2026-09-18 09:30:00',2,1,'A-0001-02731810',234000.00,0.00,234000.00,234000.00,0.00,NULL,'2026-09-25 00:38:12','2026-09-25 03:38:12',NULL,NULL,NULL),
(30,'REC-PROV-2026-00030','2026-09-20 09:30:00',1,1,'A-0001-94847164',215800.00,0.00,215800.00,0.00,215800.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL),
(31,'REC-PROV-2026-00031','2026-09-24 09:30:00',3,2,'A-0001-03269252',149800.00,0.00,149800.00,0.00,149800.00,NULL,'2026-09-25 00:38:12','2026-09-25 00:38:12',NULL,NULL,NULL);
/*!40000 ALTER TABLE `recepciones_proveedor` ENABLE KEYS */;
UNLOCK TABLES;
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
DROP TABLE IF EXISTS `roles`;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES
(1,'ADMIN','Administrador','Dueño: acceso total, precios, usuarios y configuración','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(2,'EMPLEADO','Empleado','Atención de pedidos, cobros, garrafas y recepciones','2026-09-25 03:37:54','2026-09-25 03:37:54');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `secuencias`;
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
) ENGINE=InnoDB AUTO_INCREMENT=344 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `secuencias` WRITE;
/*!40000 ALTER TABLE `secuencias` DISABLE KEYS */;
INSERT INTO `secuencias` VALUES
(1,'recepciones_proveedor','REC-PROV',2026,31,'2026-09-25 03:38:08','2026-09-25 03:38:12'),
(2,'pedidos','PED',2026,148,'2026-09-25 03:38:08','2026-09-25 03:38:12'),
(3,'pagos_cliente','REC',2026,135,'2026-09-25 03:38:08','2026-09-25 03:38:12'),
(22,'pagos_proveedor','PAG-PROV',2026,29,'2026-09-25 03:38:08','2026-09-25 03:38:12');
/*!40000 ALTER TABLE `secuencias` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `tipos_contacto_cliente`;
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `tipos_contacto_cliente` WRITE;
/*!40000 ALTER TABLE `tipos_contacto_cliente` DISABLE KEYS */;
INSERT INTO `tipos_contacto_cliente` VALUES
(1,'CELULAR','Celular','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(2,'TELEFONO_FIJO','Teléfono fijo','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(3,'WHATSAPP','WhatsApp','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(4,'EMAIL','Email','2026-09-25 03:37:54','2026-09-25 03:37:54');
/*!40000 ALTER TABLE `tipos_contacto_cliente` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `tipos_movimiento_garrafa`;
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `tipos_movimiento_garrafa` WRITE;
/*!40000 ALTER TABLE `tipos_movimiento_garrafa` DISABLE KEYS */;
INSERT INTO `tipos_movimiento_garrafa` VALUES
(1,'ALTA','Alta de envase','Ingreso de una garrafa al parque','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(2,'ENTREGA_CLIENTE','Entrega a cliente','Garrafa llena entregada en un pedido','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(3,'DEVOLUCION_CLIENTE','Devolución de cliente','Envase vacío recibido de un cliente','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(4,'ENTREGA_PROVEEDOR','Entrega a proveedor','Envase vacío entregado al proveedor','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(5,'MARCAR_NO_APTA','Marcada no apta','El envase no está en condiciones','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(6,'REPARACION','Reparación','El envase vuelve a estar apto','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(7,'BAJA','Baja','Descarte definitivo','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(8,'AJUSTE','Ajuste','Corrección por inventario','2026-09-25 03:37:54','2026-09-25 03:37:54');
/*!40000 ALTER TABLE `tipos_movimiento_garrafa` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `tipos_producto`;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `tipos_producto` WRITE;
/*!40000 ALTER TABLE `tipos_producto` DISABLE KEYS */;
INSERT INTO `tipos_producto` VALUES
(1,'GAS','Gas envasado','Garrafas de 10, 15 y 45 kg','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(2,'CARBON','Carbón','Bolsas de 3, 5, 10 y 25 kg','2026-09-25 03:37:54','2026-09-25 03:37:54'),
(3,'LENA','Leña','Leña para hogar en bolsa de 25 kg','2026-09-25 03:37:54','2026-09-25 03:37:54');
/*!40000 ALTER TABLE `tipos_producto` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `usuarios`;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES
(1,'admin','$2y$12$NtScdie8i3RYfx3EP94TCuoEQNPMNWbJXTxiEcECBn.AU26Ri8SmK','admin@extragas.com.ar',1,1,NULL,'2026-09-25 00:37:54','2026-09-25 00:37:54',NULL,NULL,NULL),
(2,'lucia','$2y$12$h.vPId11Cj3QO2NJt1BqGO1jiUlx2r5cTa9cSQuPkh0RrNb7pecvi',NULL,2,1,NULL,'2026-09-25 00:38:07','2026-09-25 00:38:07',NULL,NULL,NULL),
(3,'martin','$2y$12$woX6.VpMNYsNFIjVGIAtiuhhY91fbh30HhpbV2ihKyDElUaiJ8PdS',NULL,2,1,NULL,'2026-09-25 00:38:08','2026-09-25 00:38:08',NULL,NULL,NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `v_cuenta_corriente_cliente`;
/*!50001 DROP VIEW IF EXISTS `v_cuenta_corriente_cliente`*/;
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
DROP TABLE IF EXISTS `v_garrafas_en_clientes`;
/*!50001 DROP VIEW IF EXISTS `v_garrafas_en_clientes`*/;
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
DROP TABLE IF EXISTS `v_pagos_por_forma_pago`;
/*!50001 DROP VIEW IF EXISTS `v_pagos_por_forma_pago`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_pagos_por_forma_pago` AS SELECT
 1 AS `fecha`,
  1 AS `forma_pago_codigo`,
  1 AS `forma_pago_nombre`,
  1 AS `cantidad_pagos`,
  1 AS `monto_total` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_pedidos_resumen`;
/*!50001 DROP VIEW IF EXISTS `v_pedidos_resumen`*/;
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
DROP TABLE IF EXISTS `v_productos_mas_vendidos`;
/*!50001 DROP VIEW IF EXISTS `v_productos_mas_vendidos`*/;
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
DROP TABLE IF EXISTS `v_recepciones_resumen`;
/*!50001 DROP VIEW IF EXISTS `v_recepciones_resumen`*/;
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
DROP TABLE IF EXISTS `v_regularidad_clientes`;
/*!50001 DROP VIEW IF EXISTS `v_regularidad_clientes`*/;
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
DROP TABLE IF EXISTS `v_saldo_clientes`;
/*!50001 DROP VIEW IF EXISTS `v_saldo_clientes`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_saldo_clientes` AS SELECT
 1 AS `cliente_id`,
  1 AS `cliente`,
  1 AS `telefono_principal`,
  1 AS `pedidos_pendientes`,
  1 AS `saldo_total` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_saldo_proveedores`;
/*!50001 DROP VIEW IF EXISTS `v_saldo_proveedores`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_saldo_proveedores` AS SELECT
 1 AS `proveedor_id`,
  1 AS `razon_social`,
  1 AS `cuit`,
  1 AS `recepciones_pendientes`,
  1 AS `saldo_total` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_stock_garrafas`;
/*!50001 DROP VIEW IF EXISTS `v_stock_garrafas`*/;
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
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


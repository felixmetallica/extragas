<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Triggers de la base de datos (copiados de extragas.sql):
 * numeración de comprobantes, monto pagado de pedidos / recepciones,
 * estado de la garrafa según su último movimiento y validación de cliente.
 */
return new class extends Migration
{
    private array $triggers = [
        'trg_garrafas_bi_validate' => <<<'SQL'
CREATE TRIGGER `trg_garrafas_bi_validate` BEFORE INSERT ON `garrafas` FOR EACH ROW BEGIN
  DECLARE v_requiere_cliente BOOLEAN;

  SELECT requiere_cliente INTO v_requiere_cliente
  FROM estados_garrafa
  WHERE id = NEW.estado_garrafa_id;

  IF v_requiere_cliente IS TRUE AND NEW.cliente_id IS NULL THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'El estado de garrafa requiere un cliente_id';
  END IF;
END
SQL,
        'trg_mov_garrafa_ai' => <<<'SQL'
CREATE TRIGGER `trg_mov_garrafa_ai` AFTER INSERT ON `movimientos_garrafa` FOR EACH ROW BEGIN
  UPDATE garrafas
  SET fecha_ultimo_movimiento = NEW.fecha,
      estado_garrafa_id = NEW.estado_destino_id
  WHERE id = NEW.garrafa_id;
END
SQL,
        'trg_pagos_ad' => <<<'SQL'
CREATE TRIGGER `trg_pagos_ad` AFTER DELETE ON `pagos` FOR EACH ROW BEGIN
  IF OLD.pedido_id IS NOT NULL THEN
    UPDATE pedidos
    SET monto_pagado = (
      SELECT COALESCE(SUM(monto), 0)
      FROM pagos
      WHERE pedido_id = OLD.pedido_id AND deleted_at IS NULL
    )
    WHERE id = OLD.pedido_id;
  END IF;
END
SQL,
        'trg_pagos_ai' => <<<'SQL'
CREATE TRIGGER `trg_pagos_ai` AFTER INSERT ON `pagos` FOR EACH ROW BEGIN
  IF NEW.pedido_id IS NOT NULL AND NEW.deleted_at IS NULL THEN
    UPDATE pedidos
    SET monto_pagado = (
      SELECT COALESCE(SUM(monto), 0)
      FROM pagos
      WHERE pedido_id = NEW.pedido_id AND deleted_at IS NULL
    )
    WHERE id = NEW.pedido_id;
  END IF;
END
SQL,
        'trg_pagos_au' => <<<'SQL'
CREATE TRIGGER `trg_pagos_au` AFTER UPDATE ON `pagos` FOR EACH ROW BEGIN
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
END
SQL,
        'trg_pagos_bi' => <<<'SQL'
CREATE TRIGGER `trg_pagos_bi` BEFORE INSERT ON `pagos` FOR EACH ROW BEGIN
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
END
SQL,
        'trg_pagos_proveedor_ad' => <<<'SQL'
CREATE TRIGGER `trg_pagos_proveedor_ad` AFTER DELETE ON `pagos_proveedor` FOR EACH ROW BEGIN
  IF OLD.recepcion_id IS NOT NULL THEN
    UPDATE recepciones_proveedor
    SET monto_pagado = (
      SELECT COALESCE(SUM(monto), 0)
      FROM pagos_proveedor
      WHERE recepcion_id = OLD.recepcion_id AND deleted_at IS NULL
    )
    WHERE id = OLD.recepcion_id;
  END IF;
END
SQL,
        'trg_pagos_proveedor_ai' => <<<'SQL'
CREATE TRIGGER `trg_pagos_proveedor_ai` AFTER INSERT ON `pagos_proveedor` FOR EACH ROW BEGIN
  IF NEW.recepcion_id IS NOT NULL AND NEW.deleted_at IS NULL THEN
    UPDATE recepciones_proveedor
    SET monto_pagado = (
      SELECT COALESCE(SUM(monto), 0)
      FROM pagos_proveedor
      WHERE recepcion_id = NEW.recepcion_id AND deleted_at IS NULL
    )
    WHERE id = NEW.recepcion_id;
  END IF;
END
SQL,
        'trg_pagos_proveedor_au' => <<<'SQL'
CREATE TRIGGER `trg_pagos_proveedor_au` AFTER UPDATE ON `pagos_proveedor` FOR EACH ROW BEGIN
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
END
SQL,
        'trg_pagos_proveedor_bi' => <<<'SQL'
CREATE TRIGGER `trg_pagos_proveedor_bi` BEFORE INSERT ON `pagos_proveedor` FOR EACH ROW BEGIN
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
END
SQL,
        'trg_pedidos_bi' => <<<'SQL'
CREATE TRIGGER `trg_pedidos_bi` BEFORE INSERT ON `pedidos` FOR EACH ROW BEGIN
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
END
SQL,
        'trg_recepciones_bi' => <<<'SQL'
CREATE TRIGGER `trg_recepciones_bi` BEFORE INSERT ON `recepciones_proveedor` FOR EACH ROW BEGIN
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
END
SQL,
    ];

    public function up(): void
    {
        foreach ($this->triggers as $nombre => $sql) {
            DB::unprepared("DROP TRIGGER IF EXISTS `{$nombre}`");
            DB::unprepared($sql);
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->triggers) as $nombre) {
            DB::unprepared("DROP TRIGGER IF EXISTS `{$nombre}`");
        }
    }
};

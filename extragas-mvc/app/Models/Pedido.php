<?php

namespace App\Models;

use App\Core\DB;
use App\Core\ErrorNegocio;
use App\Core\Paginador;

class Pedido extends Modelo
{
    protected const TABLA = 'pedidos';

    /** Flujo normal de estados */
    public const FLUJO = ['PENDIENTE', 'EN_PREPARACION', 'EN_REPARTO', 'ENTREGADO'];

    /** Resumen "2× Garrafa 10 kg, 1× Carbón 5 kg" de las líneas de venta */
    private const PRODUCTOS = "(SELECT GROUP_CONCAT(CONCAT(TRIM(TRAILING '.' FROM TRIM(TRAILING '0' FROM pi.cantidad)), '× ', pr.nombre) SEPARATOR ', ')
        FROM pedido_items pi JOIN productos pr ON pr.id = pi.producto_id WHERE pi.pedido_id = p.id AND pi.tipo_linea = 'VENTA') AS productos";

    private const SELECT = 'SELECT p.*, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido, c.telefono_principal AS cliente_telefono,
        ep.codigo AS estado_codigo, ep.nombre AS estado_nombre, ep.es_final, mc.codigo AS medio_codigo, mc.nombre AS medio_nombre,
        cv.codigo AS canal_codigo, cv.nombre AS canal_nombre, e.nombre AS empleado_nombre, e.apellido AS empleado_apellido, '.self::PRODUCTOS.'
        FROM pedidos p JOIN clientes c ON c.id = p.cliente_id JOIN estados_pedido ep ON ep.id = p.estado_pedido_id
        JOIN canales_venta cv ON cv.id = p.canal_venta_id LEFT JOIN medios_contacto_pedido mc ON mc.id = p.medio_contacto_id
        JOIN empleados e ON e.id = p.empleado_id';

    public static function buscar(int $id): ?array
    {
        return DB::uno(self::SELECT.' WHERE p.id = :id AND p.deleted_at IS NULL', ['id' => $id]);
    }

    /** Condiciones de los filtros del listado */
    private static function filtros(array $f, array &$params): string
    {
        $where = ['p.deleted_at IS NULL', 'p.fecha BETWEEN :desde AND :hasta'];
        $params += ['desde' => $f['desde'].' 00:00:00', 'hasta' => $f['hasta'].' 23:59:59'];
        foreach (['estado' => 'p.estado_pedido_id', 'medio' => 'p.medio_contacto_id', 'canal' => 'p.canal_venta_id', 'cliente' => 'p.cliente_id'] as $k => $col) {
            if (! empty($f[$k])) {
                $where[] = "{$col} = :{$k}";
                $params[$k] = $f[$k];
            }
        }
        if (! empty($f['pago'])) {
            $where[] = 'ep.codigo <> \'CANCELADO\' AND '.match ($f['pago']) {
                'Pagado' => 'p.saldo <= 0',
                'Parcial' => 'p.saldo > 0 AND p.monto_pagado > 0',
                default => 'p.saldo > 0 AND p.monto_pagado = 0',
            };
        }
        if (! empty($f['q'])) {
            $where[] = '(p.numero LIKE :num OR '.Cliente::condicionBusqueda($f['q'], $params).')';
            $params['num'] = "%{$f['q']}%";
        }

        return implode(' AND ', $where);
    }

    public static function listar(array $f, ?Paginador &$pag = null, bool $todos = false): array
    {
        $params = [];
        $w = self::filtros($f, $params);
        $limite = '';
        if (! $todos) {
            $pag = new Paginador((int) DB::valor("SELECT COUNT(*) FROM pedidos p JOIN clientes c ON c.id = p.cliente_id JOIN estados_pedido ep ON ep.id = p.estado_pedido_id WHERE {$w}", $params));
            $limite = $pag->sql();
        }

        return DB::todos(self::SELECT." WHERE {$w} ORDER BY p.fecha DESC, p.id DESC{$limite}", $params);
    }

    /** Totales de los pedidos filtrados (sin cancelados) */
    public static function resumen(array $f): array
    {
        $params = [];
        $w = self::filtros($f, $params)." AND ep.codigo <> 'CANCELADO'";
        $base = "FROM pedidos p JOIN clientes c ON c.id = p.cliente_id JOIN estados_pedido ep ON ep.id = p.estado_pedido_id WHERE {$w}";

        return DB::uno("SELECT COUNT(*) n, COALESCE(SUM(p.total), 0) total, COALESCE(SUM(GREATEST(p.saldo, 0)), 0) saldo {$base}", $params)
            + ['por_medio' => DB::pares("SELECT p.medio_contacto_id, COUNT(*) {$base} GROUP BY p.medio_contacto_id", $params)];
    }

    public static function enCurso(): array
    {
        return DB::todos(self::SELECT.' WHERE p.deleted_at IS NULL AND ep.es_final = 0 ORDER BY p.fecha');
    }

    public static function cantidadEnCurso(): int
    {
        return (int) DB::valor('SELECT COUNT(*) FROM pedidos p JOIN estados_pedido ep ON ep.id = p.estado_pedido_id WHERE p.deleted_at IS NULL AND ep.es_final = 0');
    }

    public static function delCliente(int $clienteId, ?Paginador &$pag = null): array
    {
        $pag = new Paginador((int) DB::valor('SELECT COUNT(*) FROM pedidos WHERE cliente_id = :c AND deleted_at IS NULL', ['c' => $clienteId]), 10, 'pagina_pedidos');

        return DB::todos(self::SELECT.' WHERE p.cliente_id = :c AND p.deleted_at IS NULL ORDER BY p.fecha DESC'.$pag->sql(), ['c' => $clienteId]);
    }

    /** Pedidos con saldo pendiente (no cancelados) */
    public static function conSaldo(?int $clienteId = null): array
    {
        $params = [];
        $extra = '';
        if ($clienteId) {
            $extra = ' AND p.cliente_id = :c';
            $params['c'] = $clienteId;
        }

        return DB::todos(self::SELECT." WHERE p.deleted_at IS NULL AND ep.codigo <> 'CANCELADO' AND p.saldo > 0{$extra} ORDER BY p.fecha", $params);
    }

    public static function items(int $id): array
    {
        return DB::todos("SELECT pi.*, pr.nombre AS producto_nombre, pr.capacidad_kg, pr.maneja_garrafa_individual, pr.stock_actual, t.codigo AS tipo_codigo
            FROM pedido_items pi JOIN productos pr ON pr.id = pi.producto_id JOIN tipos_producto t ON t.id = pr.tipo_producto_id
            WHERE pi.pedido_id = :id ORDER BY FIELD(pi.tipo_linea, 'VENTA', 'ENTREGA', 'DEVOLUCION'), pi.id", ['id' => $id]);
    }

    public static function siguienteEstado(string $estado): ?string
    {
        $i = array_search($estado, self::FLUJO, true);

        return $i === false ? null : (self::FLUJO[$i + 1] ?? null);
    }

    public static function crear(array $d, int $empleadoId): int
    {
        return DB::transaccion(function () use ($d, $empleadoId) {
            $id = DB::insertar('pedidos', self::alta([
                'fecha' => $d['fecha'] ?? date('Y-m-d H:i:s'), 'cliente_id' => $d['cliente_id'], 'empleado_id' => $empleadoId,
                'estado_pedido_id' => Catalogo::id('estados_pedido', 'PENDIENTE'), 'canal_venta_id' => $d['canal_venta_id'],
                'medio_contacto_id' => $d['medio_contacto_id'] ?? null, 'direccion_entrega' => $d['direccion_entrega'] ?? null,
                'observaciones' => $d['observaciones'] ?? null,
            ]));
            self::guardarItems($id, $d['items'], (float) ($d['descuento'] ?? 0));

            return $id;
        });
    }

    public static function actualizar(array $pedido, array $d): void
    {
        if ($pedido['es_final']) {
            throw new ErrorNegocio('Sólo se pueden modificar pedidos que no estén entregados ni cancelados.');
        }
        DB::transaccion(function () use ($pedido, $d) {
            DB::actualizar('pedidos', self::modificacion(self::solo($d, ['fecha', 'canal_venta_id', 'medio_contacto_id', 'direccion_entrega', 'observaciones'])), (int) $pedido['id']);
            DB::ejecutar('DELETE FROM pedido_items WHERE pedido_id = :id', ['id' => $pedido['id']]);
            self::guardarItems((int) $pedido['id'], $d['items'], (float) ($d['descuento'] ?? 0));
        });
    }

    private static function guardarItems(int $id, array $items, float $descuento): void
    {
        $subtotal = 0;
        foreach ($items as $it) {
            DB::insertar('pedido_items', ['pedido_id' => $id, 'producto_id' => $it['producto_id'], 'tipo_linea' => 'VENTA',
                'cantidad' => $it['cantidad'], 'precio_unitario' => $it['precio_unitario']]);
            $subtotal += $it['cantidad'] * $it['precio_unitario'];
        }
        $descuento = min($descuento, $subtotal);
        DB::actualizar('pedidos', ['subtotal' => $subtotal, 'descuento' => $descuento, 'total' => $subtotal - $descuento], $id);
    }

    public static function avanzar(array $pedido): void
    {
        $siguiente = self::siguienteEstado($pedido['estado_codigo']);
        if (! $siguiente || $siguiente === 'ENTREGADO') {
            throw new ErrorNegocio('Para marcar el pedido como entregado registrá la entrega de productos y envases.');
        }
        DB::actualizar('pedidos', self::modificacion(['estado_pedido_id' => Catalogo::id('estados_pedido', $siguiente)]), (int) $pedido['id']);
    }

    public static function cancelar(array $pedido): void
    {
        if ($pedido['es_final']) {
            throw new ErrorNegocio('El pedido ya está finalizado.');
        }
        if ((float) $pedido['monto_pagado'] > 0) {
            throw new ErrorNegocio('El pedido tiene pagos registrados. Anulalos antes de cancelarlo.');
        }
        DB::actualizar('pedidos', self::modificacion(['estado_pedido_id' => Catalogo::id('estados_pedido', 'CANCELADO')]), (int) $pedido['id']);
    }

    /**
     * Garrafas necesarias por capacidad para el formulario de entrega:
     * cantidad, llenas disponibles y envases que tiene el cliente.
     */
    public static function necesidadesEntrega(array $pedido): array
    {
        $porCap = [];
        foreach (self::items((int) $pedido['id']) as $it) {
            if ($it['tipo_linea'] === 'VENTA' && $it['maneja_garrafa_individual']) {
                $cap = (int) $it['capacidad_kg'];
                $porCap[$cap] = ($porCap[$cap] ?? 0) + (int) $it['cantidad'];
            }
        }
        $res = [];
        foreach ($porCap as $cap => $cantidad) {
            $res[] = ['capacidad' => $cap, 'cantidad' => $cantidad, 'llenas' => Garrafa::llenas($cap),
                'del_cliente' => Garrafa::enEstado('EN_CLIENTE', $cap, (int) $pedido['cliente_id'])];
        }

        return $res;
    }

    /**
     * Registra la entrega: descuenta stock de carbón / leña, entrega garrafas llenas,
     * recibe envases vacíos y agrega las líneas ENTREGA / DEVOLUCION.
     *
     * @param  int[]  $entregadas  ids de garrafas llenas que se entregan
     * @param  int[]  $devueltas  ids de garrafas del cliente que devuelve
     * @param  int[]  $noAptas  ids (de $devueltas) que vuelven dañadas
     * @param  array<int,int>  $sinRegistrar  capacidad => envases vacíos no registrados que entrega
     */
    public static function entregar(array $pedido, array $entregadas, array $devueltas, array $noAptas, array $sinRegistrar, int $empleadoId): void
    {
        if ($pedido['es_final']) {
            throw new ErrorNegocio('El pedido ya está finalizado.');
        }
        $fecha = date('Y-m-d H:i:s');
        $id = (int) $pedido['id'];

        DB::transaccion(function () use ($pedido, $id, $entregadas, $devueltas, $noAptas, $sinRegistrar, $empleadoId, $fecha) {
            $necesarias = [];
            foreach (self::items($id) as $it) {
                if ($it['tipo_linea'] !== 'VENTA') {
                    continue;
                }
                if ($it['maneja_garrafa_individual']) {
                    $necesarias[(int) $it['capacidad_kg']] = ($necesarias[(int) $it['capacidad_kg']] ?? 0) + (int) $it['cantidad'];
                } else {
                    if ((float) $it['stock_actual'] < (float) $it['cantidad']) {
                        throw new ErrorNegocio("Stock insuficiente de {$it['producto_nombre']} (hay ".num($it['stock_actual']).').');
                    }
                    Producto::sumarStock((int) $it['producto_id'], -(float) $it['cantidad']);
                }
            }

            $params = [];
            $llenas = $entregadas ? DB::todos('SELECT * FROM garrafas WHERE id IN ('.DB::lista($entregadas, $params).') FOR UPDATE', $params) : [];
            foreach ($necesarias as $cap => $cantidad) {
                $n = count(array_filter($llenas, fn ($g) => (int) $g['capacidad_kg'] === $cap));
                if ($n !== $cantidad) {
                    throw new ErrorNegocio("Seleccioná {$cantidad} garrafa(s) llena(s) de {$cap} kg (elegiste {$n}).");
                }
            }
            $idLlena = Catalogo::id('estados_garrafa', 'LLENA');
            foreach ($llenas as $g) {
                if ((int) $g['estado_garrafa_id'] !== $idLlena || ! $g['activo']) {
                    throw new ErrorNegocio("La garrafa {$g['codigo']} no está llena en depósito.");
                }
                Garrafa::mover($g, 'ENTREGA_CLIENTE', 'EN_CLIENTE', ['cliente_id' => $pedido['cliente_id'], 'pedido_id' => $id, 'empleado_id' => $empleadoId], $fecha);
            }

            $params = [];
            $vacias = $devueltas ? DB::todos('SELECT * FROM garrafas WHERE id IN ('.DB::lista($devueltas, $params).') FOR UPDATE', $params) : [];
            $devueltasPorCap = [];
            foreach ($vacias as $g) {
                if ((int) $g['cliente_id'] !== (int) $pedido['cliente_id']) {
                    throw new ErrorNegocio("La garrafa {$g['codigo']} no figura en poder de este cliente.");
                }
                $destino = in_array((string) $g['id'], array_map('strval', $noAptas), true) ? 'NO_APTA' : 'VACIA';
                Garrafa::mover($g, 'DEVOLUCION_CLIENTE', $destino, ['cliente_id' => $pedido['cliente_id'], 'pedido_id' => $id, 'empleado_id' => $empleadoId], $fecha);
                $devueltasPorCap[(int) $g['capacidad_kg']] = ($devueltasPorCap[(int) $g['capacidad_kg']] ?? 0) + 1;
            }
            foreach ($sinRegistrar as $cap => $cantidad) {
                for ($i = 0; $i < (int) $cantidad; $i++) {
                    Garrafa::darDeAlta((int) $cap, 'VACIA', ['cliente_id' => null, 'pedido_id' => $id, 'empleado_id' => $empleadoId,
                        'observaciones' => "Envase recibido del cliente sin registrar (pedido {$pedido['numero']})"], $fecha);
                    $devueltasPorCap[(int) $cap] = ($devueltasPorCap[(int) $cap] ?? 0) + 1;
                }
            }

            $entregadasPorCap = array_count_values(array_map(fn ($g) => (int) $g['capacidad_kg'], $llenas));
            foreach (['ENTREGA' => $entregadasPorCap, 'DEVOLUCION' => $devueltasPorCap] as $tipo => $porCap) {
                foreach ($porCap as $cap => $cantidad) {
                    if ($cantidad > 0 && ($prod = Producto::deGarrafa((int) $cap))) {
                        DB::insertar('pedido_items', ['pedido_id' => $id, 'producto_id' => $prod['id'], 'tipo_linea' => $tipo, 'cantidad' => $cantidad,
                            'precio_unitario' => 0, 'observaciones' => $tipo === 'ENTREGA' ? 'Envases llenos entregados' : 'Envases vacíos recibidos']);
                    }
                }
            }

            DB::actualizar('pedidos', self::modificacion(['estado_pedido_id' => Catalogo::id('estados_pedido', 'ENTREGADO'), 'entregado' => 1, 'fecha_entrega' => $fecha]), $id);
        });
    }

    public static function pagos(int $id): array
    {
        return DB::todos('SELECT pa.*, fp.nombre AS forma_nombre FROM pagos pa JOIN formas_pago fp ON fp.id = pa.forma_pago_id
            WHERE pa.pedido_id = :id AND pa.deleted_at IS NULL ORDER BY pa.fecha', ['id' => $id]);
    }

    public static function movimientosGarrafa(int $id): array
    {
        return DB::todos('SELECT m.*, g.codigo, g.capacidad_kg, t.codigo AS tipo_codigo, t.nombre AS tipo_nombre FROM movimientos_garrafa m
            JOIN garrafas g ON g.id = m.garrafa_id JOIN tipos_movimiento_garrafa t ON t.id = m.tipo_movimiento_id
            WHERE m.pedido_id = :id ORDER BY m.id', ['id' => $id]);
    }
}

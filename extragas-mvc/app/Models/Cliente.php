<?php

namespace App\Models;

use App\Core\DB;

class Cliente extends Modelo
{
    protected const TABLA = 'clientes';

    public const CAMPOS = ['codigo', 'nombre', 'apellido', 'dni', 'cuit_cuil', 'telefono_principal', 'telefono_secundario', 'email', 'calle', 'numero',
        'piso', 'depto', 'ciudad', 'codigo_postal', 'provincia_id', 'forma_pago_habitual_id', 'referencias', 'observaciones'];

    public static function buscar(int $id): ?array
    {
        return DB::uno('SELECT c.*, fp.nombre AS forma_pago_nombre, pr.nombre AS provincia_nombre FROM clientes c
            LEFT JOIN formas_pago fp ON fp.id = c.forma_pago_habitual_id LEFT JOIN provincias pr ON pr.id = c.provincia_id
            WHERE c.id = :id AND c.deleted_at IS NULL', ['id' => $id]);
    }

    /** Condición de búsqueda por nombre, apellido, teléfono, calle, DNI o código */
    public static function condicionBusqueda(?string $texto, array &$params, string $alias = 'c'): string
    {
        if (! $texto) {
            return '1=1';
        }
        $partes = [];
        foreach (preg_split('/\s+/', trim($texto)) as $i => $palabra) {
            $params["b{$i}"] = "%{$palabra}%";
            $partes[] = "({$alias}.nombre LIKE :b{$i} OR {$alias}.apellido LIKE :b{$i} OR {$alias}.telefono_principal LIKE :b{$i} OR {$alias}.calle LIKE :b{$i} OR {$alias}.dni LIKE :b{$i} OR {$alias}.codigo LIKE :b{$i})";
        }

        return implode(' AND ', $partes);
    }

    /** Listado con saldo, garrafas en su poder y regularidad */
    public static function listar(array $f): array
    {
        $params = [];
        $where = ['c.deleted_at IS NULL', ($f['filtro'] ?? '') === 'inactivo' ? 'c.activo = 0' : 'c.activo = 1', self::condicionBusqueda($f['q'] ?? null, $params)];
        if (! empty($f['forma_pago'])) {
            $where[] = 'c.forma_pago_habitual_id = :fp';
            $params['fp'] = $f['forma_pago'];
        }
        $filas = DB::todos('SELECT c.*, fp.nombre AS forma_pago_nombre,
                COALESCE(s.saldo_total, 0) AS saldo,
                (SELECT COUNT(*) FROM garrafas g WHERE g.cliente_id = c.id AND g.activo = 1 AND g.deleted_at IS NULL
                    AND g.estado_garrafa_id = :enCliente) AS garrafas
            FROM clientes c LEFT JOIN formas_pago fp ON fp.id = c.forma_pago_habitual_id
            LEFT JOIN v_saldo_clientes s ON s.cliente_id = c.id
            WHERE '.implode(' AND ', $where).' ORDER BY c.apellido, c.nombre', $params + ['enCliente' => Catalogo::id('estados_garrafa', 'EN_CLIENTE')]);

        $reg = self::regularidad(array_column($filas, 'id'));
        foreach ($filas as &$c) {
            $c['regularidad'] = $reg[$c['id']];
        }
        unset($c);

        return array_values(array_filter($filas, fn ($c) => match ($f['filtro'] ?? '') {
            'deuda' => $c['saldo'] > 0,
            'atrasado' => $c['regularidad']['estado'] === 'Atrasado',
            'envases' => $c['garrafas'] > 0,
            default => true,
        }));
    }

    public static function crear(array $d): int
    {
        return DB::insertar('clientes', self::alta(self::solo($d, self::CAMPOS) + ['fecha_alta' => hoy(), 'activo' => 1]));
    }

    public static function actualizar(int $id, array $d, bool $activo): void
    {
        DB::actualizar('clientes', self::modificacion(self::solo($d, self::CAMPOS) + ['activo' => (int) $activo]), $id);
    }

    public static function saldo(int $id): float
    {
        return (float) DB::valor('SELECT COALESCE(saldo_total, 0) FROM v_saldo_clientes WHERE cliente_id = :id', ['id' => $id]);
    }

    public static function garrafas(int $id): array
    {
        return DB::todos('SELECT * FROM garrafas WHERE cliente_id = :id AND activo = 1 AND deleted_at IS NULL AND estado_garrafa_id = :e ORDER BY capacidad_kg, codigo',
            ['id' => $id, 'e' => Catalogo::id('estados_garrafa', 'EN_CLIENTE')]);
    }

    public static function contactos(int $id): array
    {
        return DB::todos('SELECT cc.*, t.nombre AS tipo_nombre FROM cliente_contactos cc JOIN tipos_contacto_cliente t ON t.id = cc.tipo_contacto_id
            WHERE cc.cliente_id = :id ORDER BY cc.es_principal DESC, cc.id', ['id' => $id]);
    }

    /** Movimientos de la vista v_cuenta_corriente_cliente con saldo acumulado */
    public static function cuentaCorriente(int $id): array
    {
        $saldo = 0;
        $movs = DB::todos('SELECT * FROM v_cuenta_corriente_cliente WHERE cliente_id = :id ORDER BY fecha, debe DESC', ['id' => $id]);
        foreach ($movs as &$m) {
            $saldo += $m['debe'] - $m['haber'];
            $m['saldo'] = $saldo;
        }

        return $movs;
    }

    /**
     * Regularidad de compra: promedio de días entre pedidos, último pedido,
     * próximo estimado y estado (Al día / Por pedir / Atrasado).
     *
     * @return array<int, array>
     */
    public static function regularidad(array $ids): array
    {
        $params = [];
        $porCliente = [];
        if ($ids) {
            $sql = 'SELECT cliente_id, DATE(fecha) AS dia FROM pedidos WHERE deleted_at IS NULL AND estado_pedido_id <> :cancelado
                AND cliente_id IN ('.DB::lista($ids, $params).') GROUP BY cliente_id, dia ORDER BY dia';
            foreach (DB::todos($sql, $params + ['cancelado' => Catalogo::id('estados_pedido', 'CANCELADO')]) as $f) {
                $porCliente[$f['cliente_id']][] = $f['dia'];
            }
        }
        $tolerancia = Configuracion::toleranciaRegularidad();
        $res = [];
        foreach ($ids as $id) {
            $dias = $porCliente[$id] ?? [];
            $r = ['cantidad' => count($dias), 'ultimo' => end($dias) ?: null, 'promedio' => null, 'proximo' => null, 'estado' => 'Sin datos', 'atraso' => null];
            if (count($dias) >= 2) {
                $r['promedio'] = (int) round(dias_entre($dias[0], $r['ultimo']) / (count($dias) - 1));
                $r['proximo'] = date('Y-m-d', strtotime($r['ultimo']." +{$r['promedio']} days"));
                $r['atraso'] = dias_entre($r['proximo'], hoy());
                $r['estado'] = $r['atraso'] > $tolerancia ? 'Atrasado' : ($r['atraso'] >= -1 ? 'Por pedir' : 'Al día');
            }
            $res[$id] = $r;
        }

        return $res;
    }

    /** Clientes activos con los datos que usa el formulario de pedidos */
    public static function paraPedido(): array
    {
        $saldos = DB::pares('SELECT cliente_id, saldo_total FROM v_saldo_clientes');
        $garrafas = [];
        foreach (DB::todos('SELECT cliente_id, capacidad_kg, COUNT(*) n FROM garrafas WHERE activo = 1 AND deleted_at IS NULL AND estado_garrafa_id = :e GROUP BY cliente_id, capacidad_kg',
            ['e' => Catalogo::id('estados_garrafa', 'EN_CLIENTE')]) as $g) {
            $garrafas[$g['cliente_id']][] = "{$g['n']}× {$g['capacidad_kg']} kg";
        }

        return array_map(fn ($c) => [
            'id' => (int) $c['id'], 'label' => nombre($c).' · '.$c['telefono_principal'], 'nombre' => nombre($c),
            'telefono' => $c['telefono_principal'], 'wa' => wa_link($c['telefono_principal']), 'domicilio' => domicilio($c),
            'referencias' => $c['referencias'], 'forma_pago' => $c['forma_pago_nombre'], 'forma_pago_id' => $c['forma_pago_habitual_id'] ? (int) $c['forma_pago_habitual_id'] : null,
            'saldo' => (float) ($saldos[$c['id']] ?? 0), 'url' => url('clientes/'.$c['id']), 'garrafas' => implode(', ', $garrafas[$c['id']] ?? []),
        ], DB::todos('SELECT c.*, fp.nombre AS forma_pago_nombre FROM clientes c LEFT JOIN formas_pago fp ON fp.id = c.forma_pago_habitual_id
            WHERE c.activo = 1 AND c.deleted_at IS NULL ORDER BY c.apellido, c.nombre'));
    }
}

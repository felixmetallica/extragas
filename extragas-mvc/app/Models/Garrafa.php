<?php

namespace App\Models;

use App\Core\Auth;
use App\Core\DB;
use App\Core\ErrorNegocio;
use App\Core\Paginador;

/**
 * Garrafas individuales. El estado lo actualiza el trigger trg_mov_garrafa_ai
 * al registrar un movimiento; aquí se mantienen además cliente_id y activo.
 */
class Garrafa extends Modelo
{
    protected const TABLA = 'garrafas';

    public const CAPACIDADES = [10, 15, 45];

    /** Movimientos manuales permitidos: desde qué estados y hacia cuál */
    public const MOVIMIENTOS_MANUALES = [
        'MARCAR_NO_APTA' => ['desde' => ['VACIA', 'LLENA'], 'hacia' => 'NO_APTA'],
        'REPARACION' => ['desde' => ['NO_APTA'], 'hacia' => 'VACIA'],
        'BAJA' => ['desde' => ['NO_APTA', 'VACIA', 'LLENA'], 'hacia' => 'BAJA'],
    ];

    private const SELECT = 'SELECT g.*, e.codigo AS estado_codigo, e.nombre AS estado_nombre, e.color AS estado_color,
        c.nombre AS cliente_nombre, c.apellido AS cliente_apellido, pv.razon_social AS proveedor_nombre
        FROM garrafas g JOIN estados_garrafa e ON e.id = g.estado_garrafa_id
        LEFT JOIN clientes c ON c.id = g.cliente_id LEFT JOIN proveedores pv ON pv.id = g.proveedor_id';

    public static function buscar(int $id): ?array
    {
        return DB::uno(self::SELECT.' WHERE g.id = :id AND g.deleted_at IS NULL', ['id' => $id]);
    }

    /**
     * Stock por capacidad y estado (vista v_stock_garrafas).
     *
     * @return array<int, array<string, int>>
     */
    public static function stock(): array
    {
        $stock = [];
        foreach (self::CAPACIDADES as $c) {
            $stock[$c] = ['LLENA' => 0, 'VACIA' => 0, 'NO_APTA' => 0, 'EN_CLIENTE' => 0];
        }
        foreach (DB::todos('SELECT capacidad_kg, estado_codigo, cantidad FROM v_stock_garrafas') as $f) {
            $stock[(int) $f['capacidad_kg']][$f['estado_codigo']] = (int) $f['cantidad'];
        }

        return $stock;
    }

    public static function listar(array $f, ?Paginador &$pag = null): array
    {
        $params = [];
        $where = ['g.deleted_at IS NULL'];
        if (! in_array($f['estado'] ?? '', ['BAJA', 'EN_PROVEEDOR'], true)) {
            $where[] = 'g.activo = 1';
        }
        if (! empty($f['capacidad'])) {
            $where[] = 'g.capacidad_kg = :cap';
            $params['cap'] = $f['capacidad'];
        }
        if (! empty($f['estado'])) {
            $where[] = 'e.codigo = :est';
            $params['est'] = $f['estado'];
        }
        if (! empty($f['q'])) {
            $where[] = '(g.codigo LIKE :q OR c.nombre LIKE :q OR c.apellido LIKE :q)';
            $params['q'] = "%{$f['q']}%";
        }
        $w = implode(' AND ', $where);
        $pag = new Paginador((int) DB::valor("SELECT COUNT(*) FROM garrafas g JOIN estados_garrafa e ON e.id = g.estado_garrafa_id LEFT JOIN clientes c ON c.id = g.cliente_id WHERE {$w}", $params), 20);

        return DB::todos(self::SELECT." WHERE {$w} ORDER BY g.capacidad_kg, g.codigo".$pag->sql(), $params);
    }

    /** Movimientos (de una garrafa o de todas) con sus datos relacionados */
    public static function movimientos(?int $garrafaId = null, ?int $tipoId = null, ?Paginador &$pag = null): array
    {
        $params = [];
        $where = ['1=1'];
        if ($garrafaId) {
            $where[] = 'm.garrafa_id = :g';
            $params['g'] = $garrafaId;
        }
        if ($tipoId) {
            $where[] = 'm.tipo_movimiento_id = :t';
            $params['t'] = $tipoId;
        }
        $w = implode(' AND ', $where);
        $limite = '';
        if (! $garrafaId) {
            $pag = new Paginador((int) DB::valor("SELECT COUNT(*) FROM movimientos_garrafa m WHERE {$w}", $params), 15, 'pagina_mov');
            $limite = $pag->sql();
        }

        return DB::todos("SELECT m.*, g.codigo AS garrafa_codigo, g.capacidad_kg, t.codigo AS tipo_codigo, t.nombre AS tipo_nombre,
                eo.nombre AS origen_nombre, ed.nombre AS destino_nombre, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido,
                p.numero AS pedido_numero, r.numero AS recepcion_numero, e.nombre AS empleado_nombre, e.apellido AS empleado_apellido
            FROM movimientos_garrafa m JOIN garrafas g ON g.id = m.garrafa_id JOIN tipos_movimiento_garrafa t ON t.id = m.tipo_movimiento_id
            LEFT JOIN estados_garrafa eo ON eo.id = m.estado_origen_id JOIN estados_garrafa ed ON ed.id = m.estado_destino_id
            LEFT JOIN clientes c ON c.id = m.cliente_id LEFT JOIN pedidos p ON p.id = m.pedido_id
            LEFT JOIN recepciones_proveedor r ON r.id = m.recepcion_id LEFT JOIN empleados e ON e.id = m.empleado_id
            WHERE {$w} ORDER BY m.fecha DESC, m.id DESC{$limite}", $params);
    }

    /** Llenas en depósito de una capacidad (las más antiguas primero) */
    public static function llenas(int $capacidad): array
    {
        return self::enEstado('LLENA', $capacidad);
    }

    public static function enEstado(string $estado, int $capacidad, ?int $clienteId = null, ?int $limite = null): array
    {
        $params = ['e' => Catalogo::id('estados_garrafa', $estado), 'c' => $capacidad];
        $sql = 'SELECT * FROM garrafas WHERE activo = 1 AND deleted_at IS NULL AND estado_garrafa_id = :e AND capacidad_kg = :c';
        if ($clienteId) {
            $sql .= ' AND cliente_id = :cl';
            $params['cl'] = $clienteId;
        }

        return DB::todos($sql.' ORDER BY fecha_ultimo_movimiento, id'.($limite ? ' LIMIT '.(int) $limite : ''), $params);
    }

    /** Registra un movimiento; el trigger actualiza el estado de la garrafa */
    public static function mover(array $garrafa, string $tipo, string $estadoDestino, array $extra = [], ?string $fecha = null): void
    {
        $destino = Catalogo::todos('estados_garrafa')[$estadoDestino];
        if ($destino['requiere_cliente'] && empty($extra['cliente_id'])) {
            throw new ErrorNegocio('Ese estado requiere indicar el cliente.');
        }
        DB::insertar('movimientos_garrafa', [
            'garrafa_id' => $garrafa['id'], 'fecha' => $fecha ?? date('Y-m-d H:i:s'),
            'tipo_movimiento_id' => Catalogo::id('tipos_movimiento_garrafa', $tipo),
            'pedido_id' => $extra['pedido_id'] ?? null, 'recepcion_id' => $extra['recepcion_id'] ?? null, 'cliente_id' => $extra['cliente_id'] ?? null,
            'estado_origen_id' => $garrafa['estado_garrafa_id'], 'estado_destino_id' => $destino['id'],
            'empleado_id' => $extra['empleado_id'] ?? null, 'observaciones' => $extra['observaciones'] ?? null, 'created_by' => Auth::id(),
        ]);
        DB::actualizar('garrafas', [
            'cliente_id' => $destino['requiere_cliente'] ? $extra['cliente_id'] : null,
            'activo' => (int) ! in_array($estadoDestino, ['BAJA', 'EN_PROVEEDOR'], true),
        ], (int) $garrafa['id']);
    }

    /** Alta de una garrafa nueva en el parque */
    public static function darDeAlta(int $capacidad, string $estado, array $datos = [], ?string $fecha = null): int
    {
        $fecha ??= date('Y-m-d H:i:s');
        $estadoId = Catalogo::id('estados_garrafa', $estado);
        $id = DB::insertar('garrafas', self::alta([
            'codigo' => $datos['codigo'] ?? self::generarCodigo($capacidad), 'capacidad_kg' => $capacidad,
            'proveedor_id' => $datos['proveedor_id'] ?? null, 'recepcion_id' => $datos['recepcion_id'] ?? null,
            'fecha_compra' => substr($fecha, 0, 10), 'estado_garrafa_id' => $estadoId, 'cliente_id' => $datos['cliente_id'] ?? null,
            'observaciones' => $datos['observaciones'] ?? null,
        ]));
        DB::insertar('movimientos_garrafa', [
            'garrafa_id' => $id, 'fecha' => $fecha, 'tipo_movimiento_id' => Catalogo::id('tipos_movimiento_garrafa', 'ALTA'),
            'pedido_id' => $datos['pedido_id'] ?? null, 'recepcion_id' => $datos['recepcion_id'] ?? null, 'cliente_id' => $datos['cliente_id'] ?? null,
            'estado_origen_id' => null, 'estado_destino_id' => $estadoId, 'empleado_id' => $datos['empleado_id'] ?? null,
            'observaciones' => $datos['observaciones'] ?? 'Alta de envase', 'created_by' => Auth::id(),
        ]);

        return $id;
    }

    /** Código correlativo por capacidad: G10-00001, G15-00001, G45-00001 */
    public static function generarCodigo(int $capacidad): string
    {
        $prefijo = "G{$capacidad}-";
        $ultimo = (int) DB::valor("SELECT MAX(CAST(SUBSTRING_INDEX(codigo, '-', -1) AS UNSIGNED)) FROM garrafas WHERE codigo LIKE :p", ['p' => $prefijo.'%']);
        do {
            $codigo = $prefijo.str_pad((string) ++$ultimo, 5, '0', STR_PAD_LEFT);
        } while (self::codigoExiste($codigo));

        return $codigo;
    }

    public static function codigoExiste(string $codigo): bool
    {
        return (bool) DB::valor('SELECT COUNT(*) FROM garrafas WHERE codigo = :c', ['c' => $codigo]);
    }

    /** Separa una lista de códigos escrita por el usuario */
    public static function codigosDeTexto(?string $texto): array
    {
        return array_values(array_unique(preg_split('/[\s,;]+/', (string) $texto, -1, PREG_SPLIT_NO_EMPTY)));
    }
}

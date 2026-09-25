<?php

namespace App\Services;

use App\Models\Catalogos\EstadoGarrafa;
use App\Models\Catalogos\TipoMovimientoGarrafa;
use App\Models\Empleado;
use App\Models\Garrafa;
use App\Models\MovimientoGarrafa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Movimientos de garrafas individuales. El estado de la garrafa lo actualiza el
 * trigger trg_mov_garrafa_ai; aquí se mantiene además cliente_id y activo.
 */
class GarrafaService
{
    /** Estados que se pueden elegir en un movimiento manual, por tipo */
    public const MOVIMIENTOS_MANUALES = [
        TipoMovimientoGarrafa::MARCAR_NO_APTA => ['desde' => [EstadoGarrafa::VACIA, EstadoGarrafa::LLENA], 'hacia' => EstadoGarrafa::NO_APTA],
        TipoMovimientoGarrafa::REPARACION => ['desde' => [EstadoGarrafa::NO_APTA], 'hacia' => EstadoGarrafa::VACIA],
        TipoMovimientoGarrafa::BAJA => ['desde' => [EstadoGarrafa::NO_APTA, EstadoGarrafa::VACIA, EstadoGarrafa::LLENA], 'hacia' => EstadoGarrafa::BAJA],
    ];

    public function mover(Garrafa $garrafa, string $tipo, string $estadoDestino, array $extra = [], ?Empleado $empleado = null, ?Carbon $fecha = null): MovimientoGarrafa
    {
        $destino = EstadoGarrafa::porCodigo($estadoDestino);
        if ($destino->requiere_cliente && empty($extra['cliente_id'])) {
            throw new \DomainException('Ese estado requiere indicar el cliente.');
        }

        $mov = MovimientoGarrafa::create([
            'garrafa_id' => $garrafa->id,
            'fecha' => $fecha ?? now(),
            'tipo_movimiento_id' => TipoMovimientoGarrafa::idDe($tipo),
            'pedido_id' => $extra['pedido_id'] ?? null,
            'recepcion_id' => $extra['recepcion_id'] ?? null,
            'cliente_id' => $extra['cliente_id'] ?? null,
            'estado_origen_id' => $garrafa->estado_garrafa_id,
            'estado_destino_id' => $destino->id,
            'empleado_id' => $empleado?->id,
            'observaciones' => $extra['observaciones'] ?? null,
        ]);

        Garrafa::whereKey($garrafa->id)->update([
            'cliente_id' => $destino->requiere_cliente ? $extra['cliente_id'] : null,
            'activo' => ! in_array($estadoDestino, [EstadoGarrafa::BAJA, EstadoGarrafa::EN_PROVEEDOR], true),
        ]);
        $garrafa->refresh();

        return $mov;
    }

    /** Alta de una garrafa nueva en el parque */
    public function alta(int $capacidad, string $estado, array $datos = [], ?Empleado $empleado = null, ?Carbon $fecha = null): Garrafa
    {
        $fecha ??= now();
        $garrafa = Garrafa::create([
            'codigo' => $datos['codigo'] ?? $this->generarCodigo($capacidad),
            'capacidad_kg' => $capacidad,
            'proveedor_id' => $datos['proveedor_id'] ?? null,
            'recepcion_id' => $datos['recepcion_id'] ?? null,
            'fecha_compra' => $fecha->toDateString(),
            'estado_garrafa_id' => EstadoGarrafa::idDe($estado),
            'cliente_id' => $datos['cliente_id'] ?? null,
            'observaciones' => $datos['observaciones'] ?? null,
        ]);

        MovimientoGarrafa::create([
            'garrafa_id' => $garrafa->id, 'fecha' => $fecha,
            'tipo_movimiento_id' => TipoMovimientoGarrafa::idDe(TipoMovimientoGarrafa::ALTA),
            'recepcion_id' => $datos['recepcion_id'] ?? null, 'cliente_id' => $datos['cliente_id'] ?? null,
            'estado_origen_id' => null, 'estado_destino_id' => $garrafa->estado_garrafa_id,
            'empleado_id' => $empleado?->id, 'observaciones' => $datos['observaciones'] ?? 'Alta de envase',
        ]);

        return $garrafa->refresh();
    }

    /** Código correlativo por capacidad: G10-00001, G15-00001, G45-00001 */
    public function generarCodigo(int $capacidad): string
    {
        $prefijo = "G{$capacidad}-";
        $ultimo = (int) Garrafa::withTrashed()->where('codigo', 'like', $prefijo.'%')
            ->max(DB::raw("CAST(SUBSTRING_INDEX(codigo, '-', -1) AS UNSIGNED)"));

        do {
            $codigo = $prefijo.str_pad((string) ++$ultimo, 5, '0', STR_PAD_LEFT);
        } while (Garrafa::withTrashed()->where('codigo', $codigo)->exists());

        return $codigo;
    }

    /**
     * Stock por capacidad y estado según la vista v_stock_garrafas.
     *
     * @return array<int, array<string, int>>
     */
    public function stock(): array
    {
        $stock = [];
        foreach (Garrafa::CAPACIDADES as $cap) {
            $stock[$cap] = array_fill_keys([EstadoGarrafa::LLENA, EstadoGarrafa::VACIA, EstadoGarrafa::NO_APTA, EstadoGarrafa::EN_CLIENTE], 0);
        }
        foreach (DB::table('v_stock_garrafas')->get() as $fila) {
            $stock[(int) $fila->capacidad_kg][$fila->estado_codigo] = (int) $fila->cantidad;
        }

        return $stock;
    }
}

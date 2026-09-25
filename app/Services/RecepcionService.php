<?php

namespace App\Services;

use App\Models\Catalogos\EstadoGarrafa;
use App\Models\Catalogos\TipoMovimientoGarrafa;
use App\Models\Empleado;
use App\Models\Garrafa;
use App\Models\Producto;
use App\Models\RecepcionProveedor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RecepcionService
{
    public function __construct(private GarrafaService $garrafas) {}

    /**
     * Registra una recepción de mercadería.
     *  - garrafas recibidas: se dan de alta llenas (códigos automáticos o los informados)
     *  - vacías entregadas: se toman las vacías aptas más antiguas y salen del parque
     *  - carbón / leña: suma stock y actualiza el costo
     *
     * @param  array{proveedor_id:int, fecha?:mixed, numero_factura_proveedor?:?string, descuento?:float, observaciones?:?string,
     *               items: array<int, array{producto_id:int, cantidad:float, precio_unitario:float, codigos?:?string}>,
     *               vacias?: array<int,int>}  $datos
     */
    public function registrar(array $datos, Empleado $empleado): RecepcionProveedor
    {
        $fecha = Carbon::parse($datos['fecha'] ?? now());

        return DB::transaction(function () use ($datos, $empleado, $fecha) {
            $recepcion = RecepcionProveedor::create([
                'fecha' => $fecha, 'proveedor_id' => $datos['proveedor_id'], 'empleado_id' => $empleado->id,
                'numero_factura_proveedor' => $datos['numero_factura_proveedor'] ?? null, 'observaciones' => $datos['observaciones'] ?? null,
            ]);

            $subtotal = 0;
            foreach ($datos['items'] as $item) {
                $producto = Producto::findOrFail($item['producto_id']);
                $recepcion->items()->create(['producto_id' => $producto->id, 'cantidad' => $item['cantidad'], 'precio_unitario' => $item['precio_unitario']]);
                $subtotal += $item['cantidad'] * $item['precio_unitario'];
                $producto->update(['costo_actual' => $item['precio_unitario']]);

                if ($producto->esGarrafa()) {
                    $codigos = collect(preg_split('/[\s,;]+/', (string) ($item['codigos'] ?? ''), -1, PREG_SPLIT_NO_EMPTY))->unique()->values();
                    if ($codigos->isNotEmpty() && $codigos->count() !== (int) $item['cantidad']) {
                        throw new \DomainException("Informaste {$codigos->count()} códigos para {$item['cantidad']} garrafas de {$producto->capacidad()} kg.");
                    }
                    for ($i = 0; $i < (int) $item['cantidad']; $i++) {
                        if (isset($codigos[$i]) && Garrafa::withTrashed()->where('codigo', $codigos[$i])->exists()) {
                            throw new \DomainException("El código {$codigos[$i]} ya existe.");
                        }
                        $this->garrafas->alta($producto->capacidad(), EstadoGarrafa::LLENA, [
                            'codigo' => $codigos[$i] ?? null, 'proveedor_id' => $datos['proveedor_id'], 'recepcion_id' => $recepcion->id,
                            'observaciones' => 'Recepción '.$recepcion->refresh()->numero,
                        ], $empleado, $fecha);
                    }
                } else {
                    $producto->increment('stock_actual', $item['cantidad']);
                }
            }

            foreach ($datos['vacias'] ?? [] as $cap => $cantidad) {
                $vacias = Garrafa::activas()->enEstado(EstadoGarrafa::VACIA)->where('capacidad_kg', $cap)
                    ->orderBy('fecha_ultimo_movimiento')->orderBy('id')->limit((int) $cantidad)->lockForUpdate()->get();
                if ($vacias->count() < (int) $cantidad) {
                    throw new \DomainException("Hay sólo {$vacias->count()} garrafas vacías de {$cap} kg para entregar al proveedor.");
                }
                foreach ($vacias as $g) {
                    $this->garrafas->mover($g, TipoMovimientoGarrafa::ENTREGA_PROVEEDOR, EstadoGarrafa::EN_PROVEEDOR,
                        ['recepcion_id' => $recepcion->id, 'observaciones' => 'Entregada al proveedor en recepción '.$recepcion->numero], $empleado, $fecha);
                }
            }

            $descuento = min((float) ($datos['descuento'] ?? 0), $subtotal);
            $recepcion->update(['subtotal' => $subtotal, 'descuento' => $descuento, 'total' => $subtotal - $descuento]);

            return $recepcion->refresh();
        });
    }
}

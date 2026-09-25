<?php

namespace App\Services;

use App\Models\Catalogos\EstadoGarrafa;
use App\Models\Catalogos\EstadoPedido;
use App\Models\Catalogos\TipoMovimientoGarrafa;
use App\Models\Empleado;
use App\Models\Garrafa;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PedidoService
{
    public function __construct(private GarrafaService $garrafas) {}

    /**
     * @param  array{cliente_id:int, canal_venta_id:int, medio_contacto_id:?int, fecha?:mixed, direccion_entrega?:?string,
     *               observaciones?:?string, descuento?:float, items: array<int, array{producto_id:int, cantidad:float, precio_unitario:float}>}  $datos
     */
    public function crear(array $datos, Empleado $empleado): Pedido
    {
        return DB::transaction(function () use ($datos, $empleado) {
            $pedido = Pedido::create([
                'fecha' => $datos['fecha'] ?? now(),
                'cliente_id' => $datos['cliente_id'],
                'empleado_id' => $empleado->id,
                'estado_pedido_id' => EstadoPedido::idDe(EstadoPedido::PENDIENTE),
                'canal_venta_id' => $datos['canal_venta_id'],
                'medio_contacto_id' => $datos['medio_contacto_id'] ?? null,
                'direccion_entrega' => $datos['direccion_entrega'] ?? null,
                'observaciones' => $datos['observaciones'] ?? null,
            ]);
            $this->guardarItems($pedido, $datos['items'], (float) ($datos['descuento'] ?? 0));

            return $pedido->refresh();
        });
    }

    public function actualizar(Pedido $pedido, array $datos): Pedido
    {
        if (! $pedido->esEditable()) {
            throw new \DomainException('Sólo se pueden modificar pedidos que no estén entregados ni cancelados.');
        }

        return DB::transaction(function () use ($pedido, $datos) {
            $pedido->update(collect($datos)->only(['fecha', 'canal_venta_id', 'medio_contacto_id', 'direccion_entrega', 'observaciones'])->all());
            $pedido->items()->delete();
            $this->guardarItems($pedido, $datos['items'], (float) ($datos['descuento'] ?? 0));

            return $pedido->refresh();
        });
    }

    private function guardarItems(Pedido $pedido, array $items, float $descuento): void
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $pedido->items()->create([
                'producto_id' => $item['producto_id'], 'tipo_linea' => PedidoItem::VENTA,
                'cantidad' => $item['cantidad'], 'precio_unitario' => $item['precio_unitario'],
            ]);
            $subtotal += $item['cantidad'] * $item['precio_unitario'];
        }
        $pedido->update(['subtotal' => $subtotal, 'descuento' => min($descuento, $subtotal), 'total' => $subtotal - min($descuento, $subtotal)]);
    }

    public function avanzar(Pedido $pedido): Pedido
    {
        $siguiente = $pedido->siguienteEstado();
        if (! $siguiente || $siguiente === EstadoPedido::ENTREGADO) {
            throw new \DomainException('Para marcar el pedido como entregado registrá la entrega de productos y envases.');
        }
        $pedido->update(['estado_pedido_id' => EstadoPedido::idDe($siguiente)]);

        return $pedido;
    }

    public function cancelar(Pedido $pedido): Pedido
    {
        if (! $pedido->esEditable()) {
            throw new \DomainException('El pedido ya está finalizado.');
        }
        if ($pedido->monto_pagado > 0) {
            throw new \DomainException('El pedido tiene pagos registrados. Anulalos antes de cancelarlo.');
        }
        $pedido->update(['estado_pedido_id' => EstadoPedido::idDe(EstadoPedido::CANCELADO)]);

        return $pedido;
    }

    /**
     * Garrafas necesarias por capacidad y sugerencias para el formulario de entrega.
     *
     * @return Collection<int, array{capacidad:int, producto:Producto, cantidad:int, llenas:Collection, del_cliente:Collection}>
     */
    public function necesidadesEntrega(Pedido $pedido): Collection
    {
        return $pedido->itemsVenta()->with('producto')->get()
            ->filter(fn ($i) => $i->producto->esGarrafa())
            ->groupBy(fn ($i) => $i->producto->capacidad())
            ->map(fn ($items, $cap) => [
                'capacidad' => (int) $cap,
                'producto' => $items->first()->producto,
                'cantidad' => (int) $items->sum('cantidad'),
                'llenas' => Garrafa::activas()->enEstado(EstadoGarrafa::LLENA)->where('capacidad_kg', $cap)
                    ->orderBy('fecha_ultimo_movimiento')->orderBy('id')->get(),
                'del_cliente' => Garrafa::activas()->enEstado(EstadoGarrafa::EN_CLIENTE)->where('capacidad_kg', $cap)
                    ->where('cliente_id', $pedido->cliente_id)->orderBy('fecha_ultimo_movimiento')->get(),
            ])->values();
    }

    /**
     * Registra la entrega: descuenta stock, entrega garrafas llenas y recibe las vacías.
     *
     * @param  int[]  $entregadas  ids de garrafas llenas que se entregan
     * @param  int[]  $devueltas  ids de garrafas del cliente que devuelve
     * @param  int[]  $noAptas  ids (de $devueltas) que vuelven dañadas
     * @param  array<int,int>  $sinRegistrar  capacidad => cantidad de envases vacíos que no estaban registrados
     */
    public function entregar(Pedido $pedido, array $entregadas, array $devueltas, array $noAptas, array $sinRegistrar, Empleado $empleado, ?Carbon $fecha = null): Pedido
    {
        if (! $pedido->esEditable()) {
            throw new \DomainException('El pedido ya está finalizado.');
        }
        $fecha ??= now();

        return DB::transaction(function () use ($pedido, $entregadas, $devueltas, $noAptas, $sinRegistrar, $empleado, $fecha) {
            $pedido->load('itemsVenta.producto');
            $necesarias = [];

            foreach ($pedido->itemsVenta as $item) {
                if ($item->producto->esGarrafa()) {
                    $necesarias[$item->producto->capacidad()] = ($necesarias[$item->producto->capacidad()] ?? 0) + (int) $item->cantidad;
                } else {
                    Producto::whereKey($item->producto_id)->decrement('stock_actual', $item->cantidad);
                }
            }

            // Envases llenos entregados
            $llenas = Garrafa::whereIn('id', $entregadas)->lockForUpdate()->get();
            foreach ($necesarias as $cap => $cantidad) {
                $n = $llenas->where('capacidad_kg', $cap)->count();
                if ($n !== $cantidad) {
                    throw new \DomainException("Seleccioná {$cantidad} garrafa(s) llena(s) de {$cap} kg (elegiste {$n}).");
                }
            }
            foreach ($llenas as $g) {
                if ($g->estado_garrafa_id !== EstadoGarrafa::idDe(EstadoGarrafa::LLENA) || ! $g->activo) {
                    throw new \DomainException("La garrafa {$g->codigo} no está llena en depósito.");
                }
                $this->garrafas->mover($g, TipoMovimientoGarrafa::ENTREGA_CLIENTE, EstadoGarrafa::EN_CLIENTE,
                    ['cliente_id' => $pedido->cliente_id, 'pedido_id' => $pedido->id], $empleado, $fecha);
            }

            // Envases vacíos devueltos por el cliente
            $vacias = Garrafa::whereIn('id', $devueltas)->lockForUpdate()->get();
            foreach ($vacias as $g) {
                if ($g->cliente_id !== $pedido->cliente_id) {
                    throw new \DomainException("La garrafa {$g->codigo} no figura en poder de este cliente.");
                }
                $destino = in_array($g->id, $noAptas) ? EstadoGarrafa::NO_APTA : EstadoGarrafa::VACIA;
                $this->garrafas->mover($g, TipoMovimientoGarrafa::DEVOLUCION_CLIENTE, $destino, ['cliente_id' => $pedido->cliente_id, 'pedido_id' => $pedido->id], $empleado, $fecha);
            }
            $devueltasPorCap = $vacias->countBy('capacidad_kg')->all();
            foreach ($sinRegistrar as $cap => $cantidad) {
                for ($i = 0; $i < (int) $cantidad; $i++) {
                    $g = $this->garrafas->alta((int) $cap, EstadoGarrafa::VACIA, ['observaciones' => "Envase recibido del cliente sin registrar (pedido {$pedido->numero})"], $empleado, $fecha);
                    $g->movimientos()->latest('id')->first()->update(['pedido_id' => $pedido->id, 'cliente_id' => $pedido->cliente_id]);
                    $devueltasPorCap[$cap] = ($devueltasPorCap[$cap] ?? 0) + 1;
                }
            }

            // Líneas de entrega / devolución de envases (precio 0)
            foreach ([PedidoItem::ENTREGA => $llenas->countBy('capacidad_kg')->all(), PedidoItem::DEVOLUCION => $devueltasPorCap] as $tipo => $porCap) {
                foreach ($porCap as $cap => $cantidad) {
                    if ($cantidad > 0 && $producto = Producto::deGarrafa((int) $cap)) {
                        $pedido->items()->create(['producto_id' => $producto->id, 'tipo_linea' => $tipo, 'cantidad' => $cantidad, 'precio_unitario' => 0, 'observaciones' => $tipo === PedidoItem::ENTREGA ? 'Envases llenos entregados' : 'Envases vacíos recibidos']);
                    }
                }
            }

            $pedido->update(['estado_pedido_id' => EstadoPedido::idDe(EstadoPedido::ENTREGADO), 'entregado' => true, 'fecha_entrega' => $fecha]);

            return $pedido->refresh();
        });
    }

    /** Entrega automática: toma las llenas más antiguas y recibe tantas vacías del cliente como pueda */
    public function entregarAutomatico(Pedido $pedido, Empleado $empleado, ?Carbon $fecha = null): Pedido
    {
        $entregadas = $devueltas = [];
        $sinRegistrar = [];
        foreach ($this->necesidadesEntrega($pedido) as $n) {
            $entregadas = array_merge($entregadas, $n['llenas']->take($n['cantidad'])->pluck('id')->all());
            $delCliente = $n['del_cliente']->take($n['cantidad'])->pluck('id')->all();
            $devueltas = array_merge($devueltas, $delCliente);
            $sinRegistrar[$n['capacidad']] = $n['cantidad'] - count($delCliente);
        }

        return $this->entregar($pedido, $entregadas, $devueltas, [], $sinRegistrar, $empleado, $fecha);
    }
}

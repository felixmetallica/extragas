<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Línea de pedido. tipo_linea:
 *  VENTA      producto cobrado (gas, carbón, leña)
 *  ENTREGA    envases llenos entregados al cliente (precio 0)
 *  DEVOLUCION envases vacíos que devuelve el cliente (precio 0)
 */
class PedidoItem extends Model
{
    public const VENTA = 'VENTA';

    public const ENTREGA = 'ENTREGA';

    public const DEVOLUCION = 'DEVOLUCION';

    protected $table = 'pedido_items';

    protected $guarded = ['id', 'subtotal'];

    protected function casts(): array
    {
        return ['cantidad' => 'float', 'precio_unitario' => 'float', 'subtotal' => 'float'];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class)->withTrashed();
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }
}

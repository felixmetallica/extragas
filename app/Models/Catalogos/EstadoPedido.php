<?php

namespace App\Models\Catalogos;

class EstadoPedido extends Catalogo
{
    protected $table = 'estados_pedido';

    public const PENDIENTE = 'PENDIENTE';

    public const EN_PREPARACION = 'EN_PREPARACION';

    public const EN_REPARTO = 'EN_REPARTO';

    public const ENTREGADO = 'ENTREGADO';

    public const CANCELADO = 'CANCELADO';

    /** Flujo normal de un pedido */
    public const FLUJO = [self::PENDIENTE, self::EN_PREPARACION, self::EN_REPARTO, self::ENTREGADO];

    protected $casts = ['es_final' => 'boolean'];
}

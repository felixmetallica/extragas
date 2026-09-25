<?php

namespace App\Models\Catalogos;

class TipoMovimientoGarrafa extends Catalogo
{
    protected $table = 'tipos_movimiento_garrafa';

    public const ALTA = 'ALTA';

    public const ENTREGA_CLIENTE = 'ENTREGA_CLIENTE';

    public const DEVOLUCION_CLIENTE = 'DEVOLUCION_CLIENTE';

    public const ENTREGA_PROVEEDOR = 'ENTREGA_PROVEEDOR';

    public const MARCAR_NO_APTA = 'MARCAR_NO_APTA';

    public const REPARACION = 'REPARACION';

    public const BAJA = 'BAJA';

    public const AJUSTE = 'AJUSTE';
}

<?php

namespace App\Models\Catalogos;

class EstadoGarrafa extends Catalogo
{
    protected $table = 'estados_garrafa';

    public const LLENA = 'LLENA';

    public const VACIA = 'VACIA';

    public const EN_CLIENTE = 'EN_CLIENTE';

    public const NO_APTA = 'NO_APTA';

    public const EN_PROVEEDOR = 'EN_PROVEEDOR';

    public const BAJA = 'BAJA';

    protected $casts = ['es_disponible_para_venta' => 'boolean', 'requiere_cliente' => 'boolean'];
}

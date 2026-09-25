<?php

namespace App\Models\Catalogos;

class TipoProducto extends Catalogo
{
    protected $table = 'tipos_producto';

    public const GAS = 'GAS';

    public const CARBON = 'CARBON';

    public const LENA = 'LENA';
}

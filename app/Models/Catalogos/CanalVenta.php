<?php

namespace App\Models\Catalogos;

class CanalVenta extends Catalogo
{
    protected $table = 'canales_venta';

    public const DOMICILIO = 'DOMICILIO';

    public const RETIRO_LOCAL = 'RETIRO_LOCAL';

    public const MOSTRADOR = 'MOSTRADOR';
}

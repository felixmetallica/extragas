<?php

namespace App\Models\Catalogos;

class Rol extends Catalogo
{
    protected $table = 'roles';

    public const ADMIN = 'ADMIN';

    public const EMPLEADO = 'EMPLEADO';
}

<?php

namespace App\Core;

/** Datos inválidos en un formulario */
class ErrorValidacion extends \RuntimeException
{
    public function __construct(public readonly array $errores)
    {
        parent::__construct(reset($errores) ?: 'Datos inválidos');
    }
}

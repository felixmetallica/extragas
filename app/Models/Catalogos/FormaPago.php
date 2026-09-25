<?php

namespace App\Models\Catalogos;

class FormaPago extends Catalogo
{
    protected $table = 'formas_pago';

    public const EFECTIVO = 'EFECTIVO';

    public const TRANSFERENCIA = 'TRANSFERENCIA';

    protected $casts = ['requiere_referencia' => 'boolean', 'activo' => 'boolean'];

    public function icono(): string
    {
        return match ($this->codigo) {
            self::EFECTIVO => 'cash',
            self::TRANSFERENCIA => 'bank',
            default => 'credit-card',
        };
    }
}

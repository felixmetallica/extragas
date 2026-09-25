<?php

namespace App\Models\Catalogos;

class MedioContacto extends Catalogo
{
    protected $table = 'medios_contacto_pedido';

    public const TELEFONO = 'TELEFONO';

    public const WHATSAPP = 'WHATSAPP';

    public const PRESENCIAL = 'PRESENCIAL';

    public const OTRO = 'OTRO';

    public function icono(): string
    {
        return match ($this->codigo) {
            self::TELEFONO => 'telephone',
            self::WHATSAPP => 'whatsapp',
            self::PRESENCIAL => 'shop',
            default => 'chat-dots',
        };
    }
}

<?php

namespace App\Models;

use App\Models\Catalogos\TipoContactoCliente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteContacto extends Model
{
    protected $table = 'cliente_contactos';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['es_principal' => 'boolean'];
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoContactoCliente::class, 'tipo_contacto_id');
    }
}

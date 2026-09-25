<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecepcionItem extends Model
{
    protected $table = 'recepcion_items';

    protected $guarded = ['id', 'subtotal'];

    protected function casts(): array
    {
        return ['cantidad' => 'float', 'precio_unitario' => 'float', 'subtotal' => 'float'];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class)->withTrashed();
    }
}

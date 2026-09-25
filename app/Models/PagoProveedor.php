<?php

namespace App\Models;

use App\Models\Catalogos\FormaPago;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Pago a proveedor. numero lo asigna el trigger trg_pagos_proveedor_bi. */
class PagoProveedor extends Model
{
    use Auditable;

    protected $table = 'pagos_proveedor';

    protected $guarded = ['id', 'numero'];

    protected function casts(): array
    {
        return ['fecha' => 'datetime', 'monto' => 'float'];
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class)->withTrashed();
    }

    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(RecepcionProveedor::class, 'recepcion_id');
    }

    public function formaPago(): BelongsTo
    {
        return $this->belongsTo(FormaPago::class);
    }
}

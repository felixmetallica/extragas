<?php

namespace App\Models;

use App\Models\Catalogos\FormaPago;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Pago de cliente. numero_recibo lo asigna el trigger trg_pagos_bi. */
class Pago extends Model
{
    use Auditable;

    protected $table = 'pagos';

    protected $guarded = ['id', 'numero_recibo'];

    protected function casts(): array
    {
        return ['fecha' => 'datetime', 'monto' => 'float'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class)->withTrashed();
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class)->withTrashed();
    }

    public function formaPago(): BelongsTo
    {
        return $this->belongsTo(FormaPago::class);
    }
}

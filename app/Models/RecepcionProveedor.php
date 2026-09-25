<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecepcionProveedor extends Model
{
    use Auditable;

    protected $table = 'recepciones_proveedor';

    protected $guarded = ['id', 'numero', 'saldo', 'monto_pagado'];

    protected function casts(): array
    {
        return ['fecha' => 'datetime', 'subtotal' => 'float', 'descuento' => 'float', 'total' => 'float', 'monto_pagado' => 'float', 'saldo' => 'float'];
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class)->withTrashed();
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(RecepcionItem::class, 'recepcion_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoProveedor::class, 'recepcion_id');
    }

    public function garrafas(): HasMany
    {
        return $this->hasMany(Garrafa::class, 'recepcion_id');
    }

    public function movimientosGarrafa(): HasMany
    {
        return $this->hasMany(MovimientoGarrafa::class, 'recepcion_id');
    }

    public function estadoPago(): string
    {
        return $this->saldo <= 0 ? 'Pagado' : ($this->monto_pagado > 0 ? 'Parcial' : 'Pendiente');
    }

    public function resumenItems(): string
    {
        return $this->items->map(fn ($i) => (float) $i->cantidad.'× '.$i->producto->nombre)->implode(', ');
    }
}

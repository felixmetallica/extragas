<?php

namespace App\Models;

use App\Models\Catalogos\Provincia;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    use Auditable;

    protected $table = 'proveedores';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class);
    }

    public function recepciones(): HasMany
    {
        return $this->hasMany(RecepcionProveedor::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoProveedor::class);
    }

    public function saldo(): float
    {
        return (float) $this->recepciones()->where('saldo', '>', 0)->sum('saldo');
    }

    public function domicilioCompleto(): string
    {
        return collect([trim("{$this->calle} {$this->numero}"), $this->ciudad, $this->provincia?->nombre])->filter()->implode(', ');
    }
}

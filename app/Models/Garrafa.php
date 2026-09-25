<?php

namespace App\Models;

use App\Models\Catalogos\EstadoGarrafa;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Garrafa individual (envase con código). Su estado lo actualiza el trigger
 * trg_mov_garrafa_ai al registrar un movimiento.
 */
class Garrafa extends Model
{
    use Auditable;

    public const CAPACIDADES = [10, 15, 45];

    protected $table = 'garrafas';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'fecha_compra' => 'date', 'fecha_ultimo_movimiento' => 'datetime'];
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoGarrafa::class, 'estado_garrafa_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class)->withTrashed();
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class)->withTrashed();
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoGarrafa::class)->orderByDesc('fecha')->orderByDesc('id');
    }

    public function scopeActivas(Builder $q): Builder
    {
        return $q->where('activo', true);
    }

    public function scopeEnEstado(Builder $q, string $codigo): Builder
    {
        return $q->where('estado_garrafa_id', EstadoGarrafa::idDe($codigo));
    }
}

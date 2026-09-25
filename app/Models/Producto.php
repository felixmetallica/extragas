<?php

namespace App\Models;

use App\Models\Catalogos\EstadoGarrafa;
use App\Models\Catalogos\TipoProducto;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto extends Model
{
    use Auditable;

    protected $table = 'productos';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean', 'maneja_garrafa_individual' => 'boolean', 'capacidad_kg' => 'float',
            'precio_actual' => 'float', 'costo_actual' => 'float', 'stock_actual' => 'float', 'stock_minimo' => 'float',
        ];
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoProducto::class, 'tipo_producto_id');
    }

    public function esGarrafa(): bool
    {
        return (bool) $this->maneja_garrafa_individual;
    }

    public function capacidad(): int
    {
        return (int) $this->capacidad_kg;
    }

    /** Stock disponible: garrafas llenas para gas, stock_actual para el resto */
    public function stockDisponible(): float
    {
        if ($this->esGarrafa()) {
            return Garrafa::activas()->where('capacidad_kg', $this->capacidad())
                ->where('estado_garrafa_id', EstadoGarrafa::idDe(EstadoGarrafa::LLENA))->count();
        }

        return $this->stock_actual;
    }

    public function icono(): string
    {
        return match ($this->tipo?->codigo) {
            TipoProducto::GAS => 'fuel-pump-fill',
            TipoProducto::CARBON => 'fire',
            default => 'tree-fill',
        };
    }

    public static function deGarrafa(int $capacidad): ?self
    {
        return static::where('maneja_garrafa_individual', true)->where('capacidad_kg', $capacidad)->first();
    }
}

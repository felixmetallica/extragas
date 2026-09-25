<?php

namespace App\Models;

use App\Models\Catalogos\EstadoGarrafa;
use App\Models\Catalogos\TipoMovimientoGarrafa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class MovimientoGarrafa extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'movimientos_garrafa';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['fecha' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function ($m) {
            $m->created_by ??= Auth::id();
        });
    }

    public function garrafa(): BelongsTo
    {
        return $this->belongsTo(Garrafa::class)->withTrashed();
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoMovimientoGarrafa::class, 'tipo_movimiento_id');
    }

    public function estadoOrigen(): BelongsTo
    {
        return $this->belongsTo(EstadoGarrafa::class, 'estado_origen_id');
    }

    public function estadoDestino(): BelongsTo
    {
        return $this->belongsTo(EstadoGarrafa::class, 'estado_destino_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class)->withTrashed();
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class)->withTrashed();
    }

    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(RecepcionProveedor::class, 'recepcion_id')->withTrashed();
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class)->withTrashed();
    }
}

<?php

namespace App\Models;

use App\Models\Catalogos\CanalVenta;
use App\Models\Catalogos\EstadoPedido;
use App\Models\Catalogos\MedioContacto;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    use Auditable;

    protected $table = 'pedidos';

    protected $guarded = ['id', 'numero', 'saldo', 'monto_pagado'];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime', 'fecha_entrega' => 'datetime', 'entregado' => 'boolean',
            'subtotal' => 'float', 'descuento' => 'float', 'total' => 'float', 'monto_pagado' => 'float', 'saldo' => 'float',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class)->withTrashed();
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class)->withTrashed();
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoPedido::class, 'estado_pedido_id');
    }

    public function canal(): BelongsTo
    {
        return $this->belongsTo(CanalVenta::class, 'canal_venta_id');
    }

    public function medioContacto(): BelongsTo
    {
        return $this->belongsTo(MedioContacto::class, 'medio_contacto_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PedidoItem::class);
    }

    /** Sólo las líneas que se cobran (tipo VENTA) */
    public function itemsVenta(): HasMany
    {
        return $this->items()->where('tipo_linea', PedidoItem::VENTA);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function movimientosGarrafa(): HasMany
    {
        return $this->hasMany(MovimientoGarrafa::class);
    }

    public function scopeNoCancelados(Builder $q): Builder
    {
        return $q->where('estado_pedido_id', '!=', EstadoPedido::idDe(EstadoPedido::CANCELADO));
    }

    public function scopeEnCurso(Builder $q): Builder
    {
        return $q->whereIn('estado_pedido_id', EstadoPedido::todos()->where('es_final', false)->pluck('id'));
    }

    public function scopeConSaldo(Builder $q): Builder
    {
        return $q->noCancelados()->where('saldo', '>', 0);
    }

    public function esCancelado(): bool
    {
        return $this->estado->codigo === EstadoPedido::CANCELADO;
    }

    public function esEditable(): bool
    {
        return ! $this->estado->es_final;
    }

    public function siguienteEstado(): ?string
    {
        $i = array_search($this->estado->codigo, EstadoPedido::FLUJO, true);

        return $i === false ? null : (EstadoPedido::FLUJO[$i + 1] ?? null);
    }

    public function estadoPago(): string
    {
        if ($this->esCancelado()) {
            return '—';
        }
        if ($this->saldo <= 0) {
            return 'Pagado';
        }

        return $this->monto_pagado > 0 ? 'Parcial' : 'Pendiente';
    }

    public function resumenItems(): string
    {
        return $this->items->where('tipo_linea', PedidoItem::VENTA)
            ->map(fn ($i) => (float) $i->cantidad.'× '.$i->producto->nombre)->implode(', ');
    }
}

<?php

namespace App\Models;

use App\Models\Catalogos\EstadoGarrafa;
use App\Models\Catalogos\EstadoPedido;
use App\Models\Catalogos\FormaPago;
use App\Models\Catalogos\Provincia;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Cliente extends Model
{
    use Auditable;

    protected $table = 'clientes';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'fecha_alta' => 'date'];
    }

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class);
    }

    public function formaPagoHabitual(): BelongsTo
    {
        return $this->belongsTo(FormaPago::class, 'forma_pago_habitual_id');
    }

    public function contactos(): HasMany
    {
        return $this->hasMany(ClienteContacto::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    /** Garrafas que el cliente tiene en su poder */
    public function garrafas(): HasMany
    {
        return $this->hasMany(Garrafa::class)->where('activo', true)
            ->where('estado_garrafa_id', EstadoGarrafa::idDe(EstadoGarrafa::EN_CLIENTE));
    }

    public function scopeBuscar(Builder $q, ?string $texto): Builder
    {
        if (! $texto) {
            return $q;
        }

        return $q->where(function ($q) use ($texto) {
            foreach (preg_split('/\s+/', trim($texto)) as $palabra) {
                $q->where(fn ($w) => $w->where('nombre', 'like', "%{$palabra}%")->orWhere('apellido', 'like', "%{$palabra}%")
                    ->orWhere('telefono_principal', 'like', "%{$palabra}%")->orWhere('calle', 'like', "%{$palabra}%")
                    ->orWhere('dni', 'like', "%{$palabra}%")->orWhere('codigo', $palabra));
            }
        });
    }

    public function nombreCompleto(): string
    {
        return trim("{$this->nombre} {$this->apellido}");
    }

    public function domicilio(): string
    {
        $d = trim("{$this->calle} {$this->numero}");
        if ($this->piso || $this->depto) {
            $d .= ' '.trim(($this->piso ? "Piso {$this->piso} " : '').($this->depto ? "Dto. {$this->depto}" : ''));
        }

        return $d;
    }

    public function domicilioCompleto(): string
    {
        return collect([$this->domicilio(), $this->ciudad])->filter()->implode(', ');
    }

    /** Saldo adeudado (pedidos no cancelados) */
    public function saldo(): float
    {
        return (float) $this->pedidos()->noCancelados()->where('saldo', '>', 0)->sum('saldo');
    }

    /**
     * Regularidad de compra: promedio de días entre pedidos, último pedido y próximo estimado.
     */
    public function regularidad(): array
    {
        $fechas = $this->pedidos()->noCancelados()->orderBy('fecha')->pluck('fecha')
            ->map(fn ($f) => $f->copy()->startOfDay())->unique(fn ($f) => $f->toDateString())->values();

        return self::calcularRegularidad($fechas);
    }

    /**
     * Regularidad de varios clientes con una sola consulta.
     *
     * @return Collection<int, array>
     */
    public static function regularidadDe(?iterable $ids = null): Collection
    {
        $q = Pedido::noCancelados()->selectRaw('cliente_id, DATE(fecha) AS dia')->groupBy('cliente_id', 'dia')->orderBy('dia');
        if ($ids !== null) {
            $q->whereIn('cliente_id', collect($ids));
        }
        $porCliente = $q->toBase()->get()->groupBy('cliente_id');

        return collect($ids ?? $porCliente->keys())->mapWithKeys(fn ($id) => [
            $id => self::calcularRegularidad(collect($porCliente[$id] ?? [])->map(fn ($f) => Carbon::parse($f->dia))->values()),
        ]);
    }

    public static function calcularRegularidad($fechas): array
    {
        $r = ['cantidad' => $fechas->count(), 'ultimo' => $fechas->last(), 'promedio' => null, 'proximo' => null, 'estado' => 'Sin datos', 'atraso' => null];
        if ($fechas->count() >= 2) {
            $r['promedio'] = (int) round($fechas->first()->diffInDays($fechas->last()) / ($fechas->count() - 1));
            $r['proximo'] = $fechas->last()->copy()->addDays($r['promedio']);
            $r['atraso'] = (int) $r['proximo']->diffInDays(today(), false);
            $tolerancia = ConfiguracionEmpresa::actual()->dias_tolerancia_regularidad;
            $r['estado'] = $r['atraso'] > $tolerancia ? 'Atrasado' : ($r['atraso'] >= -1 ? 'Por pedir' : 'Al día');
        }

        return $r;
    }

    public function pedidosAbiertos(): HasMany
    {
        return $this->pedidos()->whereIn('estado_pedido_id', EstadoPedido::todos()->where('es_final', false)->pluck('id'));
    }
}

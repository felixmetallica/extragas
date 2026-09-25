<?php

namespace App\Models;

use App\Models\Catalogos\Provincia;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empleado extends Model
{
    use Auditable;

    protected $table = 'empleados';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'fecha_ingreso' => 'date'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function nombreCompleto(): string
    {
        return trim("{$this->nombre} {$this->apellido}");
    }
}

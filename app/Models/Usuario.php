<?php

namespace App\Models;

use App\Models\Catalogos\Rol;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Usuario del sistema (tabla usuarios). La contraseña se guarda en password_hash.
 */
class Usuario extends Authenticatable
{
    use SoftDeletes;

    protected $table = 'usuarios';

    protected $guarded = ['id'];

    protected $hidden = ['password_hash'];

    // La tabla no tiene remember_token
    protected $rememberTokenName = '';

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'ultimo_login' => 'datetime', 'password_hash' => 'hashed'];
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class);
    }

    public function empleado(): HasOne
    {
        return $this->hasOne(Empleado::class);
    }

    public function esAdministrador(): bool
    {
        return $this->rol?->codigo === Rol::ADMIN;
    }

    public function nombreVisible(): string
    {
        return $this->empleado?->nombreCompleto() ?? $this->username;
    }

    public function iniciales(): string
    {
        $e = $this->empleado;

        return mb_strtoupper($e ? mb_substr($e->nombre, 0, 1).mb_substr($e->apellido, 0, 1) : mb_substr($this->username, 0, 2));
    }
}

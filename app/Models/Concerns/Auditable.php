<?php

namespace App\Models\Concerns;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * Completa created_by / updated_by con el usuario logueado y habilita
 * el borrado lógico (deleted_at) presente en las tablas principales.
 */
trait Auditable
{
    use SoftDeletes;

    public static function bootAuditable(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by ??= Auth::id();
                $model->updated_by ??= Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'created_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Datos de la empresa (una sola fila) usados en los PDF. */
class ConfiguracionEmpresa extends Model
{
    protected $table = 'configuracion_empresa';

    protected $guarded = ['id'];

    private static ?self $actual = null;

    public static function actual(): self
    {
        return self::$actual ??= static::query()->first() ?? new static(['nombre' => config('app.name'), 'dias_tolerancia_regularidad' => 3]);
    }

    public static function olvidar(): void
    {
        self::$actual = null;
    }
}

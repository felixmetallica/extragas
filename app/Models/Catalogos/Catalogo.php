<?php

namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Base de las tablas de catálogo (codigo + nombre).
 */
abstract class Catalogo extends Model
{
    protected $guarded = ['id'];

    /** @var array<string, array<string, static>> */
    private static array $cache = [];

    public static function porCodigo(string $codigo): static
    {
        return static::todos()[$codigo] ?? throw new \RuntimeException(static::class." sin código {$codigo}. ¿Ejecutaste los seeders?");
    }

    public static function idDe(string $codigo): int
    {
        return static::porCodigo($codigo)->id;
    }

    /** @return Collection<string, static> */
    public static function todos(): Collection
    {
        return collect(self::$cache[static::class] ??= static::query()->orderBy('id')->get()->keyBy('codigo')->all());
    }

    public static function limpiarCache(): void
    {
        self::$cache = [];
    }
}

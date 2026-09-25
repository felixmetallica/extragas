<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tablas de catálogo (valores fijos que usa el sistema).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 100);
            $table->string('descripcion')->nullable();
            $table->fechasRegistro();
        });

        Schema::create('provincias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 4)->unique();
            $table->string('nombre', 100);
            $table->string('pais', 100)->default('Argentina');
            $table->fechasRegistro();
        });

        Schema::create('canales_venta', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 100);
            $table->string('descripcion')->nullable();
            $table->fechasRegistro();
        });

        Schema::create('medios_contacto_pedido', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 100);
            $table->string('descripcion')->nullable();
            $table->fechasRegistro();
        });

        Schema::create('estados_pedido', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 100);
            $table->string('descripcion')->nullable();
            $table->boolean('es_final')->default(false);
            $table->string('color', 7)->nullable();
            $table->fechasRegistro();
        });

        Schema::create('estados_garrafa', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 100);
            $table->string('descripcion')->nullable();
            $table->boolean('es_disponible_para_venta')->default(false);
            $table->boolean('requiere_cliente')->default(false);
            $table->string('color', 7)->nullable();
            $table->fechasRegistro();
        });

        Schema::create('formas_pago', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 100);
            $table->string('descripcion')->nullable();
            $table->boolean('requiere_referencia')->default(false);
            $table->boolean('activo')->default(true);
            $table->fechasRegistro();
        });

        Schema::create('tipos_contacto_cliente', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 100);
            $table->fechasRegistro();
        });

        Schema::create('tipos_movimiento_garrafa', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 100);
            $table->string('descripcion')->nullable();
            $table->fechasRegistro();
        });

        Schema::create('tipos_producto', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 100);
            $table->string('descripcion')->nullable();
            $table->fechasRegistro();
        });

        // Numeración anual de comprobantes (la usan los triggers)
        Schema::create('secuencias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->string('prefijo', 20);
            $table->unsignedSmallInteger('anio');
            $table->unsignedInteger('ultimo_valor')->default(0);
            $table->fechasRegistro();
            $table->unique(['nombre', 'anio'], 'uq_secuencias_nombre_anio');
        });
    }

    public function down(): void
    {
        foreach (['secuencias', 'tipos_producto', 'tipos_movimiento_garrafa', 'tipos_contacto_cliente', 'formas_pago',
            'estados_garrafa', 'estados_pedido', 'medios_contacto_pedido', 'canales_venta', 'provincias', 'roles'] as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};

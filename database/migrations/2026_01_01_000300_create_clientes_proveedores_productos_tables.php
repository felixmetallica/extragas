<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->nullable()->index();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('dni', 15)->nullable()->index();
            $table->string('cuit_cuil', 15)->nullable();
            $table->string('telefono_principal', 25)->index();
            $table->string('telefono_secundario', 25)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('calle', 150)->nullable();
            $table->string('numero', 10)->nullable();
            $table->string('piso', 10)->nullable();
            $table->string('depto', 10)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->foreignId('provincia_id')->nullable()->constrained('provincias');
            $table->text('referencias')->nullable();
            $table->text('observaciones')->nullable();
            $table->date('fecha_alta');
            $table->boolean('activo')->default(true);
            $table->auditoria();
            $table->index(['apellido', 'nombre']);
        });

        Schema::create('cliente_contactos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('tipo_contacto_id')->constrained('tipos_contacto_cliente');
            $table->string('valor', 150);
            $table->boolean('es_principal')->default(false);
            $table->string('observaciones')->nullable();
            $table->fechasRegistro();
        });

        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->nullable()->index();
            $table->string('razon_social', 150)->index();
            $table->string('nombre_fantasia', 150)->nullable();
            $table->string('cuit', 15)->unique();
            $table->string('telefono_principal', 25)->nullable();
            $table->string('telefono_secundario', 25)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('calle', 150)->nullable();
            $table->string('numero', 10)->nullable();
            $table->string('piso', 10)->nullable();
            $table->string('depto', 10)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->foreignId('provincia_id')->nullable()->constrained('provincias');
            $table->text('referencias')->nullable();
            $table->string('contacto_nombre', 150)->nullable();
            $table->string('contacto_telefono', 25)->nullable();
            $table->string('contacto_email', 150)->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->auditoria();
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 150);
            $table->string('descripcion')->nullable();
            $table->foreignId('tipo_producto_id')->constrained('tipos_producto');
            $table->decimal('capacidad_kg', 8, 2)->nullable();
            $table->string('unidad_venta', 20)->default('UNIDAD');
            $table->decimal('precio_actual', 12, 2)->default(0);
            $table->boolean('maneja_garrafa_individual')->default(false);
            $table->boolean('activo')->default(true);
            $table->auditoria();
            $table->index(['codigo', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('cliente_contactos');
        Schema::dropIfExists('clientes');
    }
};

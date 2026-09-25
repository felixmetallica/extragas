<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agregados sobre el esquema original de extragas.sql para cubrir requisitos
 * del relevamiento que no tenían dónde guardarse:
 *  - forma de pago habitual del cliente
 *  - stock, stock mínimo y costo de productos (carbón y leña no se controlan por unidad)
 *  - datos de la empresa para los encabezados de los PDF
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->foreignId('forma_pago_habitual_id')->nullable()->after('provincia_id')->constrained('formas_pago');
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('costo_actual', 12, 2)->default(0)->after('precio_actual');
            $table->decimal('stock_actual', 10, 2)->default(0)->after('costo_actual');
            $table->decimal('stock_minimo', 10, 2)->default(0)->after('stock_actual');
        });

        Schema::create('configuracion_empresa', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('razon_social', 150)->nullable();
            $table->string('cuit', 15)->nullable();
            $table->string('direccion', 150)->nullable();
            $table->string('localidad', 100)->nullable();
            $table->string('telefono', 25)->nullable();
            $table->string('whatsapp', 25)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('horario', 150)->nullable();
            $table->unsignedSmallInteger('dias_tolerancia_regularidad')->default(3);
            $table->fechasRegistro();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_empresa');
        Schema::table('productos', fn (Blueprint $t) => $t->dropColumn(['costo_actual', 'stock_actual', 'stock_minimo']));
        Schema::table('clientes', fn (Blueprint $t) => $t->dropConstrainedForeignId('forma_pago_habitual_id'));
    }
};

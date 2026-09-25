<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('password_hash');
            $table->string('email', 150)->nullable();
            $table->foreignId('rol_id')->constrained('roles');
            $table->boolean('activo')->default(true);
            $table->dateTime('ultimo_login')->nullable();
            $table->fechasRegistro();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('deleted_at')->nullable()->index();
        });

        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('usuarios');
            $table->foreign('updated_by')->references('id')->on('usuarios');
        });

        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('dni', 15)->nullable()->unique();
            $table->string('cuil', 15)->nullable();
            $table->string('telefono', 25)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('calle', 150)->nullable();
            $table->string('numero', 10)->nullable();
            $table->string('piso', 10)->nullable();
            $table->string('depto', 10)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->foreignId('provincia_id')->nullable()->constrained('provincias');
            $table->date('fecha_ingreso')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios');
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->auditoria();
            $table->index(['apellido', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleados');
        Schema::dropIfExists('usuarios');
    }
};

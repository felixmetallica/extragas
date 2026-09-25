<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tablas de movimiento: recepciones, pedidos, pagos y garrafas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recepciones_proveedor', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->nullable()->index();
            $table->dateTime('fecha')->index();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('empleado_id')->constrained('empleados');
            $table->string('numero_factura_proveedor', 50)->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->decimal('saldo', 12, 2)->storedAs('`total` - `monto_pagado`');
            $table->text('observaciones')->nullable();
            $table->auditoria();
            $table->index(['proveedor_id', 'fecha']);
        });

        Schema::create('recepcion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recepcion_id')->constrained('recepciones_proveedor')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2)->storedAs('`cantidad` * `precio_unitario`');
            $table->fechasRegistro();
        });

        Schema::create('pagos_proveedor', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->nullable()->index();
            $table->dateTime('fecha')->index();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('recepcion_id')->nullable()->constrained('recepciones_proveedor');
            $table->foreignId('forma_pago_id')->constrained('formas_pago');
            $table->decimal('monto', 12, 2);
            $table->string('referencia', 100)->nullable();
            $table->string('observaciones')->nullable();
            $table->auditoria();
        });

        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->nullable()->index();
            $table->dateTime('fecha')->index();
            $table->dateTime('fecha_entrega')->nullable();
            $table->boolean('entregado')->default(false);
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('empleado_id')->constrained('empleados');
            $table->foreignId('estado_pedido_id')->constrained('estados_pedido');
            $table->foreignId('canal_venta_id')->constrained('canales_venta');
            $table->foreignId('medio_contacto_id')->nullable()->constrained('medios_contacto_pedido');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->decimal('saldo', 12, 2)->storedAs('`total` - `monto_pagado`');
            $table->text('observaciones')->nullable();
            $table->string('direccion_entrega')->nullable();
            $table->auditoria();
            $table->index(['cliente_id', 'fecha']);
            $table->index(['empleado_id', 'fecha']);
        });

        Schema::create('pedido_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->enum('tipo_linea', ['ENTREGA', 'DEVOLUCION', 'VENTA'])->default('VENTA')->index();
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2)->storedAs('`cantidad` * `precio_unitario`');
            $table->string('observaciones')->nullable();
            $table->fechasRegistro();
        });

        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_recibo', 20)->nullable()->index();
            $table->dateTime('fecha')->index();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos');
            $table->foreignId('forma_pago_id')->constrained('formas_pago');
            $table->decimal('monto', 12, 2);
            $table->string('referencia', 100)->nullable();
            $table->string('observaciones')->nullable();
            $table->auditoria();
            $table->index(['cliente_id', 'fecha']);
        });

        Schema::create('garrafas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->unsignedTinyInteger('capacidad_kg')->index();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores');
            $table->foreignId('recepcion_id')->nullable()->constrained('recepciones_proveedor');
            $table->date('fecha_compra');
            $table->foreignId('estado_garrafa_id')->constrained('estados_garrafa');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->boolean('activo')->default(true);
            $table->dateTime('fecha_ultimo_movimiento')->nullable();
            $table->text('observaciones')->nullable();
            $table->auditoria();
        });

        Schema::create('movimientos_garrafa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garrafa_id')->constrained('garrafas');
            $table->dateTime('fecha')->index();
            $table->foreignId('tipo_movimiento_id')->constrained('tipos_movimiento_garrafa');
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos');
            $table->foreignId('recepcion_id')->nullable()->constrained('recepciones_proveedor');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->foreignId('estado_origen_id')->nullable()->constrained('estados_garrafa');
            $table->foreignId('estado_destino_id')->constrained('estados_garrafa');
            $table->foreignId('empleado_id')->nullable()->constrained('empleados');
            $table->text('observaciones')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->foreignId('created_by')->nullable()->constrained('usuarios');
            $table->index(['garrafa_id', 'fecha']);
        });
    }

    public function down(): void
    {
        foreach (['movimientos_garrafa', 'garrafas', 'pagos', 'pedido_items', 'pedidos', 'pagos_proveedor', 'recepcion_items', 'recepciones_proveedor'] as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};

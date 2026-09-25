<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CobroController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\GarrafaController;
use App\Http\Controllers\InformeController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\PagoProveedorController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\RecepcionController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'formulario'])->name('login');
    Route::post('login', [AuthController::class, 'ingresar'])->middleware('throttle:10,1');
});
Route::post('logout', [AuthController::class, 'salir'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', InicioController::class)->name('inicio');

    // Ventas
    Route::get('pedidos/{pedido}/pdf', [PedidoController::class, 'pdf'])->name('pedidos.pdf');
    Route::post('pedidos/{pedido}/avanzar', [PedidoController::class, 'avanzar'])->name('pedidos.avanzar');
    Route::post('pedidos/{pedido}/cancelar', [PedidoController::class, 'cancelar'])->name('pedidos.cancelar');
    Route::get('pedidos/{pedido}/entrega', [PedidoController::class, 'entrega'])->name('pedidos.entrega');
    Route::post('pedidos/{pedido}/entrega', [PedidoController::class, 'entregar'])->name('pedidos.entregar');
    Route::resource('pedidos', PedidoController::class)->except('destroy');

    Route::get('clientes/{cliente}/estado-cuenta', [ClienteController::class, 'estadoCuenta'])->name('clientes.estado-cuenta');
    Route::post('clientes/{cliente}/contactos', [ClienteController::class, 'agregarContacto'])->name('clientes.contactos.store');
    Route::delete('clientes/{cliente}/contactos/{contacto}', [ClienteController::class, 'quitarContacto'])->name('clientes.contactos.destroy');
    Route::resource('clientes', ClienteController::class);

    Route::get('cobros/recibo', [CobroController::class, 'recibo'])->name('cobros.recibo');
    Route::resource('cobros', CobroController::class)->only(['index', 'create', 'store', 'destroy'])->parameters(['cobros' => 'pago']);

    // Depósito
    Route::get('garrafas/informe', [GarrafaController::class, 'informe'])->name('garrafas.informe');
    Route::post('garrafas/{garrafa}/movimiento', [GarrafaController::class, 'movimiento'])->name('garrafas.movimiento');
    Route::resource('garrafas', GarrafaController::class)->only(['index', 'create', 'store', 'show']);

    Route::post('productos/{producto}/stock', [ProductoController::class, 'ajustarStock'])->name('productos.stock');
    Route::middleware('can:administrar')->group(function () {
        Route::get('productos/precios', [ProductoController::class, 'precios'])->name('productos.precios');
        Route::post('productos/precios', [ProductoController::class, 'actualizarPrecios'])->name('productos.precios.update');
        Route::resource('productos', ProductoController::class)->only(['create', 'store', 'edit', 'update']);
    });
    Route::get('productos', [ProductoController::class, 'index'])->name('productos.index');

    // Compras
    Route::resource('proveedores', ProveedorController::class)->parameters(['proveedores' => 'proveedor']);
    Route::resource('recepciones', RecepcionController::class)->only(['index', 'create', 'store', 'show'])->parameters(['recepciones' => 'recepcion']);
    Route::resource('pagos-proveedores', PagoProveedorController::class)->only(['index', 'create', 'store'])->parameters(['pagos-proveedores' => 'pago']);

    // Informes
    Route::get('informes/{tipo?}', [InformeController::class, 'show'])->name('informes.show')
        ->whereIn('tipo', ['pedidos', 'productos', 'regularidad', 'pagos', 'garrafas']);

    // Sistema (sólo administrador)
    Route::middleware('can:administrar')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except(['show', 'destroy']);
        Route::resource('empleados', EmpleadoController::class)->except(['show', 'destroy']);
        Route::get('configuracion', [ConfiguracionController::class, 'edit'])->name('configuracion.edit');
        Route::put('configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');
    });
});

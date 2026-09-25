<?php
/*
 * Tabla de rutas: [método, patrón, [Controlador, acción], opciones]
 * {id} coincide con números; {tipo} con texto.
 * Opciones: 'publica' (sin login), 'admin' (sólo administrador).
 */

use App\Controllers\AuthController;
use App\Controllers\ClienteController;
use App\Controllers\CobroController;
use App\Controllers\ConfiguracionController;
use App\Controllers\EmpleadoController;
use App\Controllers\GarrafaController;
use App\Controllers\InformeController;
use App\Controllers\InicioController;
use App\Controllers\PagoProveedorController;
use App\Controllers\PedidoController;
use App\Controllers\ProductoController;
use App\Controllers\ProveedorController;
use App\Controllers\RecepcionController;
use App\Controllers\UsuarioController;

return [
    ['GET', 'login', [AuthController::class, 'formulario'], ['publica']],
    ['POST', 'login', [AuthController::class, 'ingresar'], ['publica']],
    ['POST', 'logout', [AuthController::class, 'salir']],

    ['GET', '', [InicioController::class, 'index']],

    // Pedidos
    ['GET', 'pedidos', [PedidoController::class, 'index']],
    ['GET', 'pedidos/nuevo', [PedidoController::class, 'nuevo']],
    ['POST', 'pedidos', [PedidoController::class, 'guardar']],
    ['GET', 'pedidos/{id}', [PedidoController::class, 'ver']],
    ['GET', 'pedidos/{id}/editar', [PedidoController::class, 'editar']],
    ['POST', 'pedidos/{id}/editar', [PedidoController::class, 'actualizar']],
    ['POST', 'pedidos/{id}/avanzar', [PedidoController::class, 'avanzar']],
    ['POST', 'pedidos/{id}/cancelar', [PedidoController::class, 'cancelar']],
    ['GET', 'pedidos/{id}/entrega', [PedidoController::class, 'entrega']],
    ['POST', 'pedidos/{id}/entrega', [PedidoController::class, 'entregar']],
    ['GET', 'pedidos/{id}/pdf', [PedidoController::class, 'pdf']],

    // Clientes
    ['GET', 'clientes', [ClienteController::class, 'index']],
    ['GET', 'clientes/nuevo', [ClienteController::class, 'nuevo']],
    ['POST', 'clientes', [ClienteController::class, 'guardar']],
    ['GET', 'clientes/{id}', [ClienteController::class, 'ver']],
    ['GET', 'clientes/{id}/editar', [ClienteController::class, 'editar']],
    ['POST', 'clientes/{id}/editar', [ClienteController::class, 'actualizar']],
    ['POST', 'clientes/{id}/eliminar', [ClienteController::class, 'eliminar']],
    ['GET', 'clientes/{id}/estado-cuenta', [ClienteController::class, 'estadoCuenta']],
    ['POST', 'clientes/{id}/contactos', [ClienteController::class, 'agregarContacto']],
    ['POST', 'clientes/{id}/contactos/{contacto}/eliminar', [ClienteController::class, 'quitarContacto']],

    // Cobros
    ['GET', 'cobros', [CobroController::class, 'index']],
    ['GET', 'cobros/nuevo', [CobroController::class, 'nuevo']],
    ['POST', 'cobros', [CobroController::class, 'guardar']],
    ['GET', 'cobros/recibo', [CobroController::class, 'recibo']],
    ['POST', 'cobros/{id}/anular', [CobroController::class, 'anular'], ['admin']],

    // Garrafas
    ['GET', 'garrafas', [GarrafaController::class, 'index']],
    ['GET', 'garrafas/nueva', [GarrafaController::class, 'nueva']],
    ['POST', 'garrafas', [GarrafaController::class, 'guardar']],
    ['GET', 'garrafas/informe', [GarrafaController::class, 'informe']],
    ['GET', 'garrafas/{id}', [GarrafaController::class, 'ver']],
    ['POST', 'garrafas/{id}/movimiento', [GarrafaController::class, 'movimiento']],

    // Productos
    ['GET', 'productos', [ProductoController::class, 'index']],
    ['POST', 'productos/{id}/stock', [ProductoController::class, 'ajustarStock']],
    ['GET', 'productos/nuevo', [ProductoController::class, 'nuevo'], ['admin']],
    ['POST', 'productos', [ProductoController::class, 'guardar'], ['admin']],
    ['GET', 'productos/precios', [ProductoController::class, 'precios'], ['admin']],
    ['POST', 'productos/precios', [ProductoController::class, 'actualizarPrecios'], ['admin']],
    ['GET', 'productos/{id}/editar', [ProductoController::class, 'editar'], ['admin']],
    ['POST', 'productos/{id}/editar', [ProductoController::class, 'actualizar'], ['admin']],

    // Proveedores, recepciones y pagos
    ['GET', 'proveedores', [ProveedorController::class, 'index']],
    ['GET', 'proveedores/nuevo', [ProveedorController::class, 'nuevo']],
    ['POST', 'proveedores', [ProveedorController::class, 'guardar']],
    ['GET', 'proveedores/{id}', [ProveedorController::class, 'ver']],
    ['GET', 'proveedores/{id}/editar', [ProveedorController::class, 'editar']],
    ['POST', 'proveedores/{id}/editar', [ProveedorController::class, 'actualizar']],
    ['GET', 'recepciones', [RecepcionController::class, 'index']],
    ['GET', 'recepciones/nueva', [RecepcionController::class, 'nueva']],
    ['POST', 'recepciones', [RecepcionController::class, 'guardar']],
    ['GET', 'recepciones/{id}', [RecepcionController::class, 'ver']],
    ['GET', 'pagos-proveedores', [PagoProveedorController::class, 'index']],
    ['GET', 'pagos-proveedores/nuevo', [PagoProveedorController::class, 'nuevo']],
    ['POST', 'pagos-proveedores', [PagoProveedorController::class, 'guardar']],

    // Informes
    ['GET', 'informes', [InformeController::class, 'ver']],
    ['GET', 'informes/{tipo}', [InformeController::class, 'ver']],

    // Sistema
    ['GET', 'usuarios', [UsuarioController::class, 'index'], ['admin']],
    ['GET', 'usuarios/nuevo', [UsuarioController::class, 'nuevo'], ['admin']],
    ['POST', 'usuarios', [UsuarioController::class, 'guardar'], ['admin']],
    ['GET', 'usuarios/{id}/editar', [UsuarioController::class, 'editar'], ['admin']],
    ['POST', 'usuarios/{id}/editar', [UsuarioController::class, 'actualizar'], ['admin']],
    ['GET', 'empleados', [EmpleadoController::class, 'index'], ['admin']],
    ['GET', 'empleados/nuevo', [EmpleadoController::class, 'nuevo'], ['admin']],
    ['POST', 'empleados', [EmpleadoController::class, 'guardar'], ['admin']],
    ['GET', 'empleados/{id}/editar', [EmpleadoController::class, 'editar'], ['admin']],
    ['POST', 'empleados/{id}/editar', [EmpleadoController::class, 'actualizar'], ['admin']],
    ['GET', 'configuracion', [ConfiguracionController::class, 'editar'], ['admin']],
    ['POST', 'configuracion', [ConfiguracionController::class, 'actualizar'], ['admin']],
];

<?php

namespace Tests\Feature;

use Database\Seeders\DemoSeeder;
use Tests\TestCase;

class AccesoYPantallasTest extends TestCase
{
    public function test_login_con_usuario_y_contrasena(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->post('/login', ['username' => 'admin', 'password' => 'mala'])->assertSessionHasErrors('username');
        $this->post('/login', ['username' => 'admin', 'password' => 'admin123'])->assertRedirect(route('inicio'));
        $this->assertAuthenticated();
        $this->assertNotNull($this->admin()->refresh()->ultimo_login);
    }

    public function test_empleado_no_accede_a_sistema_ni_precios(): void
    {
        $this->actingAs($this->empleadoUsuario());
        $this->get(route('usuarios.index'))->assertForbidden();
        $this->get(route('configuracion.edit'))->assertForbidden();
        $this->get(route('productos.precios'))->assertForbidden();
        $this->get(route('productos.index'))->assertOk()->assertDontSee('Actualizar precios');
    }

    public function test_todas_las_pantallas_y_pdf_responden_con_datos_de_demo(): void
    {
        $this->seed(DemoSeeder::class);
        $this->actingAs($this->admin());

        $pantallas = ['/', '/pedidos', '/pedidos/create', '/pedidos/1', '/clientes', '/clientes/create', '/clientes/1', '/clientes/1/edit', '/cobros', '/cobros/create',
            '/garrafas', '/garrafas/create', '/garrafas/1', '/productos', '/productos/create', '/productos/1/edit', '/productos/precios', '/proveedores', '/proveedores/1',
            '/recepciones', '/recepciones/create', '/recepciones/1', '/pagos-proveedores', '/pagos-proveedores/create', '/usuarios', '/usuarios/create', '/empleados',
            '/configuracion', '/informes/pedidos', '/informes/productos', '/informes/regularidad', '/informes/pagos', '/informes/garrafas'];
        foreach ($pantallas as $url) {
            $this->get($url)->assertOk();
        }

        $pdfs = ['/pedidos/1/pdf', '/pedidos?pdf=1', '/clientes?pdf=1', '/clientes/1/estado-cuenta', '/cobros/recibo?ids=1', '/garrafas/informe',
            '/informes/pedidos?pdf=1', '/informes/productos?pdf=1', '/informes/regularidad?pdf=1', '/informes/pagos?pdf=1'];
        foreach ($pdfs as $url) {
            $this->get($url)->assertOk()->assertHeader('content-type', 'application/pdf');
        }
    }
}

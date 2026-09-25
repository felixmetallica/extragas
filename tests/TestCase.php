<?php

namespace Tests;

use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Rol;
use App\Models\ConfiguracionEmpresa;
use App\Models\Empleado;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Las pruebas usan MySQL (base extragas_test) porque dependen de los
 * triggers y vistas del esquema.
 */
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        Catalogo::limpiarCache();
        ConfiguracionEmpresa::olvidar();
        parent::setUp();
        Catalogo::limpiarCache();
    }

    protected function admin(): Usuario
    {
        return Usuario::where('username', 'admin')->firstOrFail();
    }

    protected function empleadoUsuario(): Usuario
    {
        $u = Usuario::create(['username' => 'lucia', 'password_hash' => 'secreto', 'rol_id' => Rol::idDe(Rol::EMPLEADO), 'activo' => true]);
        Empleado::create(['nombre' => 'Lucía', 'apellido' => 'Fernández', 'usuario_id' => $u->id, 'activo' => true]);

        return $u->refresh();
    }
}

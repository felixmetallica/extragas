<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controlador;
use App\Core\ErrorNegocio;
use App\Models\Catalogo;
use App\Models\Empleado;
use App\Models\Usuario;

class UsuarioController extends Controlador
{
    public function index(): string
    {
        return $this->vista('usuarios/index', ['titulo' => 'Usuarios', 'migas' => ['Sistema' => null, 'Usuarios' => null], 'usuarios' => Usuario::todos()]);
    }

    public function nuevo(): string
    {
        return $this->formulario(['id' => null, 'activo' => 1, 'rol_id' => Catalogo::id('roles', 'EMPLEADO'), 'empleado_id' => null]);
    }

    public function editar(int $id): string
    {
        return $this->formulario($this->noEncontrado(Usuario::buscar($id)));
    }

    private function formulario(array $u): string
    {
        return $this->vista('usuarios/form', [
            'titulo' => $u['id'] ? "Editar usuario {$u['username']}" : 'Nuevo usuario',
            'migas' => ['Sistema' => null, 'Usuarios' => url('usuarios'), ($u['id'] ? 'Editar' : 'Nuevo') => null],
            'usuario' => $u, 'roles' => Catalogo::opciones('roles'), 'empleados' => Empleado::paraVincular($u['id']),
        ]);
    }

    private function datos(?int $id = null): array
    {
        $d = $this->validar([
            'username' => 'requerido|texto:50|unico:usuarios,username'.($id ? ",{$id}" : ''), 'email' => 'email|texto:150', 'rol_id' => 'requerido|existe:roles',
            'password' => ($id ? '' : 'requerido|').'confirmado', 'empleado_id' => 'existe:empleados',
        ], ['username' => 'usuario', 'password' => 'contraseña', 'rol_id' => 'rol']);
        if (! preg_match('/^[a-zA-Z0-9._-]+$/', $d['username'])) {
            throw new ErrorNegocio('El usuario sólo puede tener letras, números, punto, guion y guion bajo.');
        }
        if ($d['password'] && strlen($d['password']) < 6) {
            throw new ErrorNegocio('La contraseña debe tener al menos 6 caracteres.');
        }

        return $d;
    }

    public function guardar(): never
    {
        Usuario::crear($this->datos());
        $this->exito('usuarios', 'Usuario creado.');
    }

    public function actualizar(int $id): never
    {
        $u = $this->noEncontrado(Usuario::buscar($id));
        $d = $this->datos($id);
        $activo = ! empty($_POST['activo']);
        if ($id === Auth::id() && (! $activo || (int) $d['rol_id'] !== (int) $u['rol_id'])) {
            throw new ErrorNegocio('No podés desactivar ni cambiar el rol de tu propio usuario.');
        }
        Usuario::actualizar($id, $d, $activo);
        $this->exito('usuarios', 'Usuario actualizado.');
    }
}

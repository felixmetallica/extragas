<?php

namespace App\Http\Controllers;

use App\Models\Catalogos\Rol;
use App\Models\Empleado;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    public function index()
    {
        return view('usuarios.index', [
            'titulo' => 'Usuarios', 'migas' => ['Sistema' => null, 'Usuarios' => null],
            'usuarios' => Usuario::with(['rol', 'empleado'])->orderByDesc('activo')->orderBy('username')->get(),
        ]);
    }

    public function create()
    {
        return $this->formulario(new Usuario(['activo' => true, 'rol_id' => Rol::idDe(Rol::EMPLEADO)]));
    }

    public function edit(Usuario $usuario)
    {
        return $this->formulario($usuario->load('empleado'));
    }

    private function formulario(Usuario $usuario)
    {
        return view('usuarios.form', [
            'titulo' => $usuario->exists ? "Editar usuario {$usuario->username}" : 'Nuevo usuario',
            'migas' => ['Sistema' => null, 'Usuarios' => route('usuarios.index'), ($usuario->exists ? 'Editar' : 'Nuevo') => null],
            'usuario' => $usuario, 'roles' => Rol::todos(),
            'empleados' => Empleado::where(fn ($q) => $q->whereNull('usuario_id')->orWhere('usuario_id', $usuario->id))->orderBy('apellido')->get(),
        ]);
    }

    private function validar(Request $request, ?Usuario $usuario = null): array
    {
        return $request->validate([
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('usuarios', 'username')->ignore($usuario)],
            'email' => 'nullable|email|max:150',
            'rol_id' => 'required|exists:roles,id',
            'password' => [$usuario ? 'nullable' : 'required', 'confirmed', Password::min(6)],
            'empleado_id' => 'nullable|exists:empleados,id',
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $usuario = Usuario::create(['username' => $datos['username'], 'email' => $datos['email'] ?? null, 'rol_id' => $datos['rol_id'],
            'password_hash' => $datos['password'], 'activo' => true, 'created_by' => auth()->id()]);
        $this->vincular($usuario, $datos['empleado_id'] ?? null);

        return redirect()->route('usuarios.index')->with('ok', 'Usuario creado.');
    }

    public function update(Request $request, Usuario $usuario)
    {
        $datos = $this->validar($request, $usuario);
        $activo = $request->boolean('activo');
        if ($usuario->is(auth()->user()) && (! $activo || $datos['rol_id'] != $usuario->rol_id)) {
            throw new \DomainException('No podés desactivar ni cambiar el rol de tu propio usuario.');
        }
        $usuario->fill(['username' => $datos['username'], 'email' => $datos['email'] ?? null, 'rol_id' => $datos['rol_id'], 'activo' => $activo, 'updated_by' => auth()->id()]);
        if (! empty($datos['password'])) {
            $usuario->password_hash = $datos['password'];
        }
        $usuario->save();
        $this->vincular($usuario, $datos['empleado_id'] ?? null);

        return redirect()->route('usuarios.index')->with('ok', 'Usuario actualizado.');
    }

    private function vincular(Usuario $usuario, ?int $empleadoId): void
    {
        Empleado::where('usuario_id', $usuario->id)->where('id', '!=', $empleadoId)->update(['usuario_id' => null]);
        if ($empleadoId) {
            Empleado::whereKey($empleadoId)->update(['usuario_id' => $usuario->id]);
        }
    }
}

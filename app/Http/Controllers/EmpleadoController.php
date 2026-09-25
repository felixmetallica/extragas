<?php

namespace App\Http\Controllers;

use App\Models\Catalogos\Provincia;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmpleadoController extends Controller
{
    public function index()
    {
        return view('empleados.index', [
            'titulo' => 'Empleados', 'migas' => ['Sistema' => null, 'Empleados' => null],
            'empleados' => Empleado::with('usuario')->withCount('pedidos')->orderByDesc('activo')->orderBy('apellido')->get(),
        ]);
    }

    public function create()
    {
        return $this->formulario(new Empleado(['activo' => true, 'fecha_ingreso' => today()]));
    }

    public function edit(Empleado $empleado)
    {
        return $this->formulario($empleado);
    }

    private function formulario(Empleado $empleado)
    {
        return view('empleados.form', [
            'titulo' => $empleado->exists ? 'Editar empleado' : 'Nuevo empleado',
            'migas' => ['Sistema' => null, 'Empleados' => route('empleados.index'), ($empleado->exists ? $empleado->nombreCompleto() : 'Nuevo') => null],
            'empleado' => $empleado, 'provincias' => Provincia::todos()->sortBy('nombre'),
        ]);
    }

    private function validar(Request $request, ?Empleado $empleado = null): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:100', 'apellido' => 'required|string|max:100',
            'dni' => ['nullable', 'string', 'max:15', Rule::unique('empleados', 'dni')->ignore($empleado)],
            'cuil' => 'nullable|string|max:15', 'telefono' => 'nullable|string|max:25', 'email' => 'nullable|email|max:150',
            'calle' => 'nullable|string|max:150', 'numero' => 'nullable|string|max:10', 'piso' => 'nullable|string|max:10', 'depto' => 'nullable|string|max:10',
            'ciudad' => 'nullable|string|max:100', 'codigo_postal' => 'nullable|string|max:10', 'provincia_id' => 'nullable|exists:provincias,id',
            'fecha_ingreso' => 'nullable|date', 'observaciones' => 'nullable|string|max:2000',
        ]);
    }

    public function store(Request $request)
    {
        Empleado::create($this->validar($request) + ['activo' => true]);

        return redirect()->route('empleados.index')->with('ok', 'Empleado registrado. Podés darle acceso al sistema desde Usuarios.');
    }

    public function update(Request $request, Empleado $empleado)
    {
        $empleado->update($this->validar($request, $empleado) + ['activo' => $request->boolean('activo')]);

        return redirect()->route('empleados.index')->with('ok', 'Empleado actualizado.');
    }
}

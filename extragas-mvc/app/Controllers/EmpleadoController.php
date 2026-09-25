<?php

namespace App\Controllers;

use App\Core\Controlador;
use App\Models\Catalogo;
use App\Models\Empleado;

class EmpleadoController extends Controlador
{
    public function index(): string
    {
        return $this->vista('empleados/index', ['titulo' => 'Empleados', 'migas' => ['Sistema' => null, 'Empleados' => null], 'empleados' => Empleado::todos()]);
    }

    public function nuevo(): string
    {
        return $this->formulario(['id' => null, 'activo' => 1, 'fecha_ingreso' => hoy()]);
    }

    public function editar(int $id): string
    {
        return $this->formulario($this->noEncontrado(Empleado::buscar($id)));
    }

    private function formulario(array $e): string
    {
        return $this->vista('empleados/form', [
            'titulo' => $e['id'] ? 'Editar empleado' : 'Nuevo empleado',
            'migas' => ['Sistema' => null, 'Empleados' => url('empleados'), ($e['id'] ? nombre($e) : 'Nuevo') => null],
            'empleado' => $e, 'provincias' => Catalogo::opciones('provincias'),
        ]);
    }

    private function datos(?int $id = null): array
    {
        return $this->validar([
            'nombre' => 'requerido|texto:100', 'apellido' => 'requerido|texto:100', 'dni' => 'texto:15|unico:empleados,dni'.($id ? ",{$id}" : ''),
            'cuil' => 'texto:15', 'telefono' => 'texto:25', 'email' => 'email|texto:150', 'calle' => 'texto:150', 'numero' => 'texto:10', 'piso' => 'texto:10',
            'depto' => 'texto:10', 'ciudad' => 'texto:100', 'codigo_postal' => 'texto:10', 'provincia_id' => 'existe:provincias', 'fecha_ingreso' => 'fecha',
            'observaciones' => 'texto:2000',
        ], ['dni' => 'DNI']);
    }

    public function guardar(): never
    {
        Empleado::crear($this->datos());
        $this->exito('empleados', 'Empleado registrado. Podés darle acceso al sistema desde Usuarios.');
    }

    public function actualizar(int $id): never
    {
        $this->noEncontrado(Empleado::buscar($id));
        Empleado::actualizar($id, $this->datos($id), ! empty($_POST['activo']));
        $this->exito('empleados', 'Empleado actualizado.');
    }
}

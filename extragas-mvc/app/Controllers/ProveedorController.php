<?php

namespace App\Controllers;

use App\Core\Controlador;
use App\Core\DB;
use App\Models\Catalogo;
use App\Models\PagoProveedor;
use App\Models\Proveedor;
use App\Models\Recepcion;

class ProveedorController extends Controlador
{
    public function index(): string
    {
        return $this->vista('proveedores/index', [
            'titulo' => 'Proveedores', 'migas' => ['Compras' => null, 'Proveedores' => null],
            'proveedores' => Proveedor::listar($_GET['q'] ?? null, $pag), 'pag' => $pag,
        ]);
    }

    public function nuevo(): string
    {
        return $this->formulario(['id' => null, 'activo' => 1, 'provincia_id' => Catalogo::id('provincias', 'TUC')]);
    }

    public function editar(int $id): string
    {
        return $this->formulario($this->noEncontrado(Proveedor::buscar($id)));
    }

    private function formulario(array $p): string
    {
        return $this->vista('proveedores/form', [
            'titulo' => $p['id'] ? 'Editar proveedor' : 'Nuevo proveedor',
            'migas' => ['Compras' => null, 'Proveedores' => url('proveedores'), ($p['id'] ? $p['razon_social'] : 'Nuevo') => null],
            'proveedor' => $p, 'provincias' => Catalogo::opciones('provincias'),
        ]);
    }

    private function datos(?int $id = null): array
    {
        return $this->validar([
            'codigo' => 'texto:20', 'razon_social' => 'requerido|texto:150', 'nombre_fantasia' => 'texto:150',
            'cuit' => 'requerido|texto:15|unico:proveedores,cuit'.($id ? ",{$id}" : ''),
            'telefono_principal' => 'texto:25', 'telefono_secundario' => 'texto:25', 'email' => 'email|texto:150',
            'calle' => 'texto:150', 'numero' => 'texto:10', 'piso' => 'texto:10', 'depto' => 'texto:10', 'ciudad' => 'texto:100', 'codigo_postal' => 'texto:10',
            'provincia_id' => 'existe:provincias', 'referencias' => 'texto:1000', 'contacto_nombre' => 'texto:150', 'contacto_telefono' => 'texto:25',
            'contacto_email' => 'email|texto:150', 'observaciones' => 'texto:2000',
        ], ['razon_social' => 'razón social', 'cuit' => 'CUIT']);
    }

    public function guardar(): never
    {
        $id = Proveedor::crear($this->datos());
        $this->exito("proveedores/{$id}", 'Proveedor registrado.');
    }

    public function actualizar(int $id): never
    {
        $this->noEncontrado(Proveedor::buscar($id));
        Proveedor::actualizar($id, $this->datos($id), ! empty($_POST['activo']));
        $this->exito("proveedores/{$id}", 'Proveedor actualizado.');
    }

    public function ver(int $id): string
    {
        $p = $this->noEncontrado(Proveedor::buscar($id));

        return $this->vista('proveedores/ver', [
            'titulo' => $p['razon_social'], 'migas' => ['Compras' => null, 'Proveedores' => url('proveedores'), 'Ficha' => null],
            'proveedor' => $p, 'saldo' => Proveedor::saldo($id),
            'recepciones' => Recepcion::listar(['proveedor' => $id], $pagRec, false, 'pagina_rec', 10), 'pagRec' => $pagRec,
            'pagos' => PagoProveedor::listar(['proveedor' => $id], $pagPag, false, 'pagina_pag', 10), 'pagPag' => $pagPag,
            'comprado90' => (float) DB::valor('SELECT COALESCE(SUM(total), 0) FROM recepciones_proveedor WHERE proveedor_id = :p AND deleted_at IS NULL AND fecha >= :d',
                ['p' => $id, 'd' => date('Y-m-d', strtotime('-89 days'))]),
            'pagadoTotal' => (float) DB::valor('SELECT COALESCE(SUM(monto), 0) FROM pagos_proveedor WHERE proveedor_id = :p AND deleted_at IS NULL', ['p' => $id]),
        ]);
    }
}

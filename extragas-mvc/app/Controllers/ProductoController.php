<?php

namespace App\Controllers;

use App\Core\Controlador;
use App\Core\DB;
use App\Core\ErrorNegocio;
use App\Models\Catalogo;
use App\Models\Garrafa;
use App\Models\Producto;

class ProductoController extends Controlador
{
    public function index(): string
    {
        $productos = [];
        foreach (Producto::todos() as $p) {
            $productos[$p['tipo_codigo']][] = $p;
        }

        return $this->vista('productos/index', [
            'titulo' => 'Productos y precios', 'migas' => ['Depósito' => null, 'Productos y precios' => null],
            'tipos' => Catalogo::todos('tipos_producto'), 'productos' => $productos, 'stock' => Garrafa::stock(), 'vendidos' => Producto::vendidos30(),
        ]);
    }

    public function nuevo(): string
    {
        return $this->formulario(['id' => null, 'activo' => 1, 'unidad_venta' => 'BOLSA']);
    }

    public function editar(int $id): string
    {
        return $this->formulario($this->noEncontrado(Producto::buscar($id)));
    }

    private function formulario(array $p): string
    {
        return $this->vista('productos/form', [
            'titulo' => $p['id'] ? "Editar {$p['nombre']}" : 'Nuevo producto',
            'migas' => ['Depósito' => null, 'Productos y precios' => url('productos'), ($p['id'] ? 'Editar' : 'Nuevo') => null],
            'producto' => $p, 'tipos' => Catalogo::opciones('tipos_producto'),
        ]);
    }

    private function datos(?int $id = null): array
    {
        return $this->validar([
            'codigo' => 'requerido|texto:30|unico:productos,codigo'.($id ? ",{$id}" : ''), 'nombre' => 'requerido|texto:150', 'descripcion' => 'texto:255',
            'tipo_producto_id' => 'requerido|existe:tipos_producto', 'capacidad_kg' => 'requerido|numero|min:0.1', 'unidad_venta' => 'requerido|en:GARRAFA,BOLSA,UNIDAD',
            'precio_actual' => 'requerido|numero|min:0', 'costo_actual' => 'numero|min:0', 'stock_minimo' => 'numero|min:0',
        ], ['codigo' => 'código', 'tipo_producto_id' => 'tipo', 'capacidad_kg' => 'presentación', 'precio_actual' => 'precio']);
    }

    public function guardar(): never
    {
        $d = $this->datos();
        $d['costo_actual'] ??= 0;
        $d['stock_minimo'] ??= 0;
        Producto::crear($d, max(0, (float) ($_POST['stock_actual'] ?? 0)));
        $this->exito('productos', 'Producto creado.');
    }

    public function actualizar(int $id): never
    {
        $this->noEncontrado(Producto::buscar($id));
        $d = $this->datos($id);
        $d['costo_actual'] ??= 0;
        $d['stock_minimo'] ??= 0;
        Producto::actualizar($id, $d, ! empty($_POST['activo']));
        $this->exito('productos', 'Producto actualizado.');
    }

    public function precios(): string
    {
        return $this->vista('productos/precios', [
            'titulo' => 'Actualización de precios', 'migas' => ['Depósito' => null, 'Productos y precios' => url('productos'), 'Precios' => null],
            'productos' => Producto::todos(true), 'tipos' => Catalogo::opciones('tipos_producto'),
        ]);
    }

    public function actualizarPrecios(): never
    {
        Producto::actualizarPrecios((array) ($_POST['precios'] ?? []));
        $this->exito('productos', 'Precios actualizados. Los pedidos ya registrados conservan su precio.');
    }

    public function ajustarStock(int $id): never
    {
        $p = $this->noEncontrado(Producto::buscar($id));
        if ($p['maneja_garrafa_individual']) {
            throw new ErrorNegocio('El stock de garrafas se ajusta desde el módulo Garrafas.');
        }
        $d = $this->validar(['tipo' => 'requerido|en:fijar,baja', 'cantidad' => 'requerido|numero|min:0', 'motivo' => 'texto:200']);
        $nuevo = $d['tipo'] === 'fijar' ? (float) $d['cantidad'] : max(0, (float) $p['stock_actual'] - (float) $d['cantidad']);
        DB::actualizar('productos', ['stock_actual' => $nuevo], $id);
        $this->exito('productos', "Stock de {$p['nombre']}: ".num($nuevo).'.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Catalogos\TipoProducto;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Services\GarrafaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    public function index(GarrafaService $garrafas)
    {
        $vendidos = PedidoItem::where('tipo_linea', PedidoItem::VENTA)
            ->whereHas('pedido', fn ($q) => $q->noCancelados()->where('fecha', '>=', today()->subDays(29)))
            ->selectRaw('producto_id, SUM(cantidad) n')->groupBy('producto_id')->pluck('n', 'producto_id');

        return view('productos.index', [
            'titulo' => 'Productos y precios', 'migas' => ['Depósito' => null, 'Productos y precios' => null],
            'tipos' => TipoProducto::todos(), 'productos' => Producto::with('tipo')->orderBy('capacidad_kg')->get()->groupBy('tipo_producto_id'),
            'stock' => $garrafas->stock(), 'vendidos' => $vendidos,
        ]);
    }

    public function create()
    {
        return $this->formulario(new Producto(['activo' => true, 'unidad_venta' => 'BOLSA']));
    }

    public function edit(Producto $producto)
    {
        return $this->formulario($producto);
    }

    private function formulario(Producto $producto)
    {
        return view('productos.form', [
            'titulo' => $producto->exists ? "Editar {$producto->nombre}" : 'Nuevo producto',
            'migas' => ['Depósito' => null, 'Productos y precios' => route('productos.index'), ($producto->exists ? 'Editar' : 'Nuevo') => null],
            'producto' => $producto, 'tipos' => TipoProducto::todos(),
        ]);
    }

    private function validar(Request $request, ?Producto $producto = null): array
    {
        return $request->validate([
            'codigo' => ['required', 'string', 'max:30', Rule::unique('productos', 'codigo')->ignore($producto)],
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:255',
            'tipo_producto_id' => 'required|exists:tipos_producto,id',
            'capacidad_kg' => 'required|numeric|min:0.1',
            'unidad_venta' => 'required|string|max:20',
            'precio_actual' => 'required|numeric|min:0',
            'costo_actual' => 'nullable|numeric|min:0',
            'stock_minimo' => 'nullable|numeric|min:0',
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $esGas = TipoProducto::find($datos['tipo_producto_id'])->codigo === TipoProducto::GAS;
        Producto::create($datos + ['maneja_garrafa_individual' => $esGas, 'activo' => true, 'stock_actual' => $esGas ? 0 : (float) $request->input('stock_actual', 0)]);

        return redirect()->route('productos.index')->with('ok', 'Producto creado.');
    }

    public function update(Request $request, Producto $producto)
    {
        $producto->update($this->validar($request, $producto) + ['activo' => $request->boolean('activo')]);

        return redirect()->route('productos.index')->with('ok', 'Producto actualizado.');
    }

    public function precios()
    {
        return view('productos.precios', [
            'titulo' => 'Actualización de precios', 'migas' => ['Depósito' => null, 'Productos y precios' => route('productos.index'), 'Precios' => null],
            'productos' => Producto::with('tipo')->where('activo', true)->orderBy('tipo_producto_id')->orderBy('capacidad_kg')->get(),
            'tipos' => TipoProducto::todos(),
        ]);
    }

    public function actualizarPrecios(Request $request)
    {
        $datos = $request->validate(['precios' => 'required|array', 'precios.*' => 'required|numeric|min:0']);
        DB::transaction(function () use ($datos) {
            foreach ($datos['precios'] as $id => $precio) {
                Producto::whereKey($id)->update(['precio_actual' => $precio, 'updated_by' => auth()->id()]);
            }
        });

        return redirect()->route('productos.index')->with('ok', 'Precios actualizados. Los pedidos ya registrados conservan su precio.');
    }

    public function ajustarStock(Request $request, Producto $producto)
    {
        abort_if($producto->esGarrafa(), 422, 'El stock de garrafas se ajusta desde el módulo Garrafas.');
        $datos = $request->validate(['tipo' => 'required|in:fijar,baja', 'cantidad' => 'required|numeric|min:0', 'motivo' => 'nullable|string|max:200']);
        $nuevo = $datos['tipo'] === 'fijar' ? $datos['cantidad'] : max(0, $producto->stock_actual - $datos['cantidad']);
        $producto->update(['stock_actual' => $nuevo]);

        return back()->with('ok', "Stock de {$producto->nombre}: ".num($nuevo).'.');
    }
}

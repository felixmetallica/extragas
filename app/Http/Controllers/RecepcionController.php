<?php

namespace App\Http\Controllers;

use App\Models\Catalogos\EstadoGarrafa;
use App\Models\Catalogos\FormaPago;
use App\Models\Garrafa;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\RecepcionProveedor;
use App\Services\PagoService;
use App\Services\RecepcionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecepcionController extends Controller
{
    public function index(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);
        $filtro = RecepcionProveedor::whereBetween('fecha', [$desde, $hasta])
            ->when($request->proveedor, fn ($q, $v) => $q->where('proveedor_id', $v))
            ->when($request->pago, fn ($q, $v) => match ($v) {
                'Pagado' => $q->where('saldo', '<=', 0),
                'Parcial' => $q->where('saldo', '>', 0)->where('monto_pagado', '>', 0),
                default => $q->where('saldo', '>', 0)->where('monto_pagado', 0),
            });
        $resumen = (clone $filtro)->selectRaw('COUNT(*) n, COALESCE(SUM(total),0) total')->toBase()->first();
        $garrafasRecibidas = Garrafa::whereIn('recepcion_id', (clone $filtro)->select('id'))->count();

        if ($request->boolean('pdf')) {
            $todas = (clone $filtro)->with(['proveedor', 'items.producto'])->orderBy('fecha')->get();

            return PedidoController::pdfListado('Recepciones de mercadería', 'Del '.fecha($desde).' al '.fecha($hasta), [[
                'head' => ['N°', 'Fecha', 'Proveedor', 'Factura', 'Productos', 'Total', 'Pago'],
                'body' => $todas->map(fn ($r) => [$r->numero, fecha($r->fecha), $r->proveedor->razon_social, $r->numero_factura_proveedor, $r->resumenItems(), pesos($r->total), $r->estadoPago()]),
                'num' => [5],
            ]], 'recepciones', 'landscape');
        }

        return view('recepciones.index', [
            'titulo' => 'Recepciones de mercadería', 'migas' => ['Compras' => null, 'Recepciones' => null],
            'recepciones' => $filtro->with(['proveedor', 'items.producto'])->withCount(['movimientosGarrafa as vacias_entregadas' => fn ($q) => $q->whereHas('tipo', fn ($t) => $t->where('codigo', 'ENTREGA_PROVEEDOR'))])
                ->orderByDesc('fecha')->paginate(15)->withQueryString(),
            'resumen' => $resumen, 'garrafasRecibidas' => $garrafasRecibidas,
            'deuda' => DB::table('v_saldo_proveedores')->sum('saldo_total'),
            'proveedores' => Proveedor::orderBy('razon_social')->pluck('razon_social', 'id'), 'desde' => $desde, 'hasta' => $hasta,
        ]);
    }

    public function create(Request $request)
    {
        $vacias = Garrafa::activas()->enEstado(EstadoGarrafa::VACIA)->selectRaw('capacidad_kg, COUNT(*) n')->groupBy('capacidad_kg')->pluck('n', 'capacidad_kg');

        return view('recepciones.create', [
            'titulo' => 'Nueva recepción', 'migas' => ['Compras' => null, 'Recepciones' => route('recepciones.index'), 'Nueva' => null],
            'proveedores' => Proveedor::where('activo', true)->orderBy('razon_social')->pluck('razon_social', 'id'),
            'proveedorId' => $request->integer('proveedor') ?: null,
            'productos' => Producto::where('activo', true)->orderBy('tipo_producto_id')->orderBy('capacidad_kg')->get()
                ->map(fn ($p) => ['id' => $p->id, 'nombre' => $p->nombre, 'costo' => $p->costo_actual, 'garrafa' => $p->esGarrafa(), 'capacidad' => $p->capacidad(), 'stock' => $p->esGarrafa() ? null : $p->stock_actual]),
            'vacias' => $vacias, 'formasPago' => FormaPago::todos()->where('activo', true),
        ]);
    }

    public function store(Request $request, RecepcionService $servicio, PagoService $pagos)
    {
        $datos = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha' => 'required|date',
            'numero_factura_proveedor' => 'nullable|string|max:50',
            'descuento' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|numeric|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.codigos' => 'nullable|string|max:5000',
            'vacias' => 'array', 'vacias.*' => 'nullable|integer|min:0',
            'pagada' => 'nullable|boolean', 'forma_pago_id' => 'nullable|required_if:pagada,1|exists:formas_pago,id',
        ], ['items.required' => 'Agregá los productos del remito.']);
        $datos['vacias'] = array_filter($datos['vacias'] ?? []);

        $recepcion = DB::transaction(function () use ($datos, $servicio, $pagos, $request) {
            $recepcion = $servicio->registrar($datos, $this->empleadoActual());
            if ($request->boolean('pagada') && $recepcion->total > 0) {
                $pagos->pagarProveedor($recepcion->proveedor, $recepcion, $recepcion->total, (int) $datos['forma_pago_id'], null, $recepcion->fecha);
            }

            return $recepcion;
        });

        return redirect()->route('recepciones.show', $recepcion)->with('ok', "Recepción {$recepcion->numero} registrada. Stock y garrafas actualizados.");
    }

    public function show(RecepcionProveedor $recepcion)
    {
        $recepcion->load(['proveedor', 'empleado', 'items.producto', 'pagos.formaPago', 'garrafas', 'movimientosGarrafa.garrafa', 'movimientosGarrafa.tipo']);

        return view('recepciones.show', [
            'titulo' => "Recepción {$recepcion->numero}", 'migas' => ['Compras' => null, 'Recepciones' => route('recepciones.index'), $recepcion->numero => null],
            'recepcion' => $recepcion,
            'entregadas' => $recepcion->movimientosGarrafa->where('tipo.codigo', 'ENTREGA_PROVEEDOR'),
        ]);
    }
}

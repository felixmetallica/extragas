<?php

namespace App\Http\Controllers;

use App\Models\Catalogos\FormaPago;
use App\Models\PagoProveedor;
use App\Models\Proveedor;
use App\Models\RecepcionProveedor;
use App\Services\PagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoProveedorController extends Controller
{
    public function index(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);
        $filtro = PagoProveedor::whereBetween('fecha', [$desde, $hasta])->when($request->proveedor, fn ($q, $v) => $q->where('proveedor_id', $v));
        $saldos = DB::table('v_saldo_proveedores')->get()->keyBy('proveedor_id');

        if ($request->boolean('pdf')) {
            $todos = (clone $filtro)->with(['proveedor', 'recepcion', 'formaPago'])->orderBy('fecha')->get();

            return PedidoController::pdfListado('Pagos a proveedores', 'Del '.fecha($desde).' al '.fecha($hasta), [
                ['titulo' => 'Saldos pendientes', 'head' => ['Proveedor', 'CUIT', 'Recepciones impagas', 'Saldo'], 'body' => $saldos->map(fn ($s) => [$s->razon_social, $s->cuit, $s->recepciones_pendientes, pesos($s->saldo_total)])->values(), 'num' => [2, 3]],
                ['titulo' => 'Pagos realizados', 'head' => ['N°', 'Fecha', 'Proveedor', 'Recepción', 'Forma', 'Referencia', 'Importe'],
                    'body' => $todos->map(fn ($p) => [$p->numero, fecha($p->fecha), $p->proveedor->razon_social, $p->recepcion?->numero ?? 'A cuenta', $p->formaPago->nombre, $p->referencia, pesos($p->monto)]), 'num' => [6]],
            ], 'pagos-proveedores');
        }

        return view('pagos-proveedores.index', [
            'titulo' => 'Pagos a proveedores', 'migas' => ['Compras' => null, 'Pagos a proveedores' => null],
            'pagos' => $filtro->with(['proveedor', 'recepcion', 'formaPago'])->orderByDesc('fecha')->paginate(15)->withQueryString(),
            'totalPeriodo' => (clone $filtro)->sum('monto'), 'saldos' => $saldos,
            'proveedores' => Proveedor::where('activo', true)->orderBy('razon_social')->get(), 'desde' => $desde, 'hasta' => $hasta,
        ]);
    }

    public function create(Request $request)
    {
        return view('pagos-proveedores.create', [
            'titulo' => 'Registrar pago a proveedor', 'migas' => ['Compras' => null, 'Pagos a proveedores' => route('pagos-proveedores.index'), 'Nuevo' => null],
            'proveedores' => Proveedor::orderBy('razon_social')->get(), 'saldos' => DB::table('v_saldo_proveedores')->pluck('saldo_total', 'proveedor_id'),
            'pendientes' => RecepcionProveedor::where('saldo', '>', 0)->orderBy('fecha')->get()
                ->map(fn ($r) => ['id' => $r->id, 'proveedor_id' => $r->proveedor_id, 'saldo' => $r->saldo, 'texto' => "{$r->numero} · ".fecha($r->fecha).' · saldo '.pesos($r->saldo)]),
            'proveedorId' => $request->integer('proveedor') ?: null, 'recepcionId' => $request->integer('recepcion') ?: null,
            'formasPago' => FormaPago::todos()->where('activo', true),
        ]);
    }

    public function store(Request $request, PagoService $servicio)
    {
        $datos = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id', 'recepcion_id' => 'nullable|exists:recepciones_proveedor,id',
            'monto' => 'required|numeric|min:1', 'forma_pago_id' => 'required|exists:formas_pago,id',
            'referencia' => 'nullable|string|max:100', 'fecha' => 'required|date', 'observaciones' => 'nullable|string|max:255',
        ]);
        $proveedor = Proveedor::findOrFail($datos['proveedor_id']);
        $recepcion = ! empty($datos['recepcion_id']) ? $proveedor->recepciones()->findOrFail($datos['recepcion_id']) : null;
        $pagos = $servicio->pagarProveedor($proveedor, $recepcion, (float) $datos['monto'], (int) $datos['forma_pago_id'], $datos['referencia'] ?? null,
            $datos['fecha'] === today()->toDateString() ? now() : $datos['fecha'], $datos['observaciones'] ?? null);

        return redirect()->route('proveedores.show', $proveedor)->with('ok', 'Pago registrado: '.$pagos->pluck('numero')->implode(', ').'.');
    }
}

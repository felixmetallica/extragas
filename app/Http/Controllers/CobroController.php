<?php

namespace App\Http\Controllers;

use App\Models\Catalogos\FormaPago;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Pedido;
use App\Services\PagoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CobroController extends Controller
{
    public function __construct(private PagoService $servicio) {}

    public function index(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);
        $filtro = Pago::query()->whereBetween('fecha', [$desde, $hasta])
            ->when($request->forma_pago, fn ($q, $v) => $q->where('forma_pago_id', $v))
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w->where('numero_recibo', 'like', "%{$v}%")->orWhere('referencia', 'like', "%{$v}%")
                ->orWhereHas('cliente', fn ($c) => $c->buscar($v))));

        $porForma = (clone $filtro)->join('formas_pago', 'formas_pago.id', '=', 'pagos.forma_pago_id')
            ->selectRaw('formas_pago.nombre, SUM(monto) total')->groupBy('formas_pago.nombre')->pluck('total', 'nombre');

        if ($request->boolean('pdf')) {
            $todos = (clone $filtro)->with(['cliente', 'pedido', 'formaPago'])->orderBy('fecha')->get();

            return PedidoController::pdfListado('Pagos recibidos', 'Del '.fecha($desde).' al '.fecha($hasta), [
                ['resumen' => $porForma->map(fn ($t, $f) => [$f, pesos($t)])->values()->push(['Total', pesos($porForma->sum())])->all()],
                ['head' => ['Recibo', 'Fecha', 'Cliente', 'Pedido', 'Forma', 'Referencia', 'Importe'],
                    'body' => $todos->map(fn ($p) => [$p->numero_recibo, fecha($p->fecha), $p->cliente->nombreCompleto(), $p->pedido?->numero ?? 'A cuenta', $p->formaPago->nombre, $p->referencia, pesos($p->monto)]),
                    'num' => [6]],
            ], 'pagos-recibidos');
        }

        return view('cobros.index', [
            'titulo' => 'Cobros', 'migas' => ['Ventas' => null, 'Cobros' => null],
            'pagos' => $filtro->with(['cliente', 'pedido', 'formaPago', 'creador.empleado'])->orderByDesc('fecha')->orderByDesc('id')->paginate(15)->withQueryString(),
            'totalPeriodo' => $porForma->sum(), 'porForma' => $porForma,
            'cobradoHoy' => Pago::whereDate('fecha', today())->sum('monto'),
            'pendientes' => Pedido::conSaldo()->with(['cliente', 'estado'])->orderBy('fecha')->get(),
            'saldos' => DB::table('v_saldo_clientes')->get(),
            'formasPago' => FormaPago::todos(), 'desde' => $desde, 'hasta' => $hasta,
        ]);
    }

    public function create(Request $request)
    {
        $cliente = $request->integer('cliente') ? Cliente::find($request->integer('cliente')) : null;
        $saldos = DB::table('v_saldo_clientes')->pluck('saldo_total', 'cliente_id');

        return view('cobros.create', [
            'titulo' => 'Registrar pago de cliente', 'migas' => ['Ventas' => null, 'Cobros' => route('cobros.index'), 'Nuevo' => null],
            'cliente' => $cliente, 'pedidoId' => $request->integer('pedido') ?: null,
            'clientes' => Cliente::whereIn('id', $saldos->keys())->orWhere('id', $cliente?->id)->orderBy('apellido')->get(),
            'saldos' => $saldos,
            'pendientes' => Pedido::conSaldo()->orderBy('fecha')->get(['id', 'numero', 'fecha', 'cliente_id', 'saldo'])
                ->map(fn ($p) => ['id' => $p->id, 'cliente_id' => $p->cliente_id, 'texto' => "{$p->numero} · ".fecha($p->fecha).' · saldo '.pesos($p->saldo), 'saldo' => $p->saldo]),
            'formasPago' => FormaPago::todos()->where('activo', true),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'cliente_id' => 'required|exists:clientes,id', 'pedido_id' => 'nullable|exists:pedidos,id',
            'monto' => 'required|numeric|min:1', 'forma_pago_id' => 'required|exists:formas_pago,id',
            'referencia' => 'nullable|string|max:100', 'fecha' => 'required|date', 'observaciones' => 'nullable|string|max:255',
        ]);
        if (FormaPago::find($datos['forma_pago_id'])->requiere_referencia && empty($datos['referencia'])) {
            throw new \DomainException('Esa forma de pago requiere el número de operación o referencia.');
        }
        $cliente = Cliente::findOrFail($datos['cliente_id']);
        $pedido = ! empty($datos['pedido_id']) ? Pedido::where('cliente_id', $cliente->id)->findOrFail($datos['pedido_id']) : null;
        $fecha = $datos['fecha'] === today()->toDateString() ? now() : $datos['fecha'];

        $pagos = $this->servicio->cobrar($cliente, $pedido, (float) $datos['monto'], (int) $datos['forma_pago_id'], $datos['referencia'] ?? null, $fecha, $datos['observaciones'] ?? null);

        return redirect()->route($pedido ? 'pedidos.show' : 'clientes.show', $pedido ?? $cliente)
            ->with('ok', 'Pago registrado: '.$pagos->pluck('numero_recibo')->implode(', ').'.')
            ->with('pdf', route('cobros.recibo', ['ids' => $pagos->pluck('id')->implode(',')]));
    }

    /** Recibo PDF de uno o varios pagos del mismo cliente (?ids=1,2) */
    public function recibo(Request $request)
    {
        $pagos = Pago::with(['cliente', 'pedido.items.producto', 'formaPago', 'creador.empleado'])
            ->whereIn('id', explode(',', (string) $request->query('ids')))->orderBy('id')->get();
        abort_if($pagos->isEmpty() || $pagos->pluck('cliente_id')->unique()->count() > 1, 404);

        return Pdf::loadView('pdf.recibo', ['pagos' => $pagos, 'cliente' => $pagos->first()->cliente])
            ->download('recibo-'.$pagos->first()->numero_recibo.'.pdf');
    }

    public function destroy(Pago $pago)
    {
        Gate::authorize('administrar');
        $this->servicio->anular($pago);

        return back()->with('ok', "Pago {$pago->numero_recibo} anulado.");
    }
}

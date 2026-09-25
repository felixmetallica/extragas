<?php

namespace App\Http\Controllers;

use App\Models\Catalogos\CanalVenta;
use App\Models\Catalogos\EstadoGarrafa;
use App\Models\Catalogos\EstadoPedido;
use App\Models\Catalogos\FormaPago;
use App\Models\Catalogos\MedioContacto;
use App\Models\Cliente;
use App\Models\Garrafa;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\GarrafaService;
use App\Services\PagoService;
use App\Services\PedidoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    public function __construct(private PedidoService $servicio) {}

    public function index(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);
        $filtro = Pedido::query()
            ->whereBetween('fecha', [$desde, $hasta])
            ->when($request->estado, fn ($q, $v) => $q->where('estado_pedido_id', $v))
            ->when($request->medio, fn ($q, $v) => $q->where('medio_contacto_id', $v))
            ->when($request->canal, fn ($q, $v) => $q->where('canal_venta_id', $v))
            ->when($request->pago, fn ($q, $v) => match ($v) {
                'Pagado' => $q->noCancelados()->where('saldo', '<=', 0),
                'Parcial' => $q->noCancelados()->where('saldo', '>', 0)->where('monto_pagado', '>', 0),
                default => $q->noCancelados()->where('saldo', '>', 0)->where('monto_pagado', 0),
            })
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w->where('numero', 'like', "%{$v}%")
                ->orWhereHas('cliente', fn ($c) => $c->buscar($v))));

        $resumen = (clone $filtro)->noCancelados()->selectRaw('COUNT(*) n, COALESCE(SUM(total),0) total, COALESCE(SUM(GREATEST(saldo,0)),0) saldo')->toBase()->first();
        $porMedio = (clone $filtro)->noCancelados()->selectRaw('medio_contacto_id, COUNT(*) n')->groupBy('medio_contacto_id')->pluck('n', 'medio_contacto_id');

        $pedidos = $filtro->with(['cliente', 'estado', 'medioContacto', 'canal', 'items.producto'])
            ->orderByDesc('fecha')->orderByDesc('id')->paginate(15)->withQueryString();

        if ($request->boolean('pdf')) {
            $todos = (clone $filtro)->with(['cliente', 'estado', 'medioContacto', 'items.producto'])->orderBy('fecha')->get();

            return $this->pdfListado('Listado de pedidos', 'Del '.fecha($desde).' al '.fecha($hasta), [[
                'head' => ['N°', 'Fecha', 'Cliente', 'Medio', 'Productos', 'Total', 'Pago', 'Estado'],
                'body' => $todos->map(fn ($p) => [$p->numero, fecha($p->fecha, true), $p->cliente->nombreCompleto(), $p->medioContacto?->nombre, $p->resumenItems(), pesos($p->total), $p->estadoPago(), $p->estado->nombre]),
                'num' => [5],
            ]], 'pedidos', 'landscape');
        }

        return view('pedidos.index', [
            'titulo' => 'Pedidos', 'migas' => ['Ventas' => null, 'Pedidos' => null],
            'pedidos' => $pedidos, 'resumen' => $resumen, 'porMedio' => $porMedio,
            'desde' => $desde, 'hasta' => $hasta,
            'estados' => EstadoPedido::todos(), 'medios' => MedioContacto::todos(), 'canales' => CanalVenta::todos(),
        ]);
    }

    public function create(Request $request)
    {
        return $this->formulario(new Pedido([
            'fecha' => now(), 'cliente_id' => $request->integer('cliente') ?: null,
            'canal_venta_id' => CanalVenta::idDe(CanalVenta::DOMICILIO), 'medio_contacto_id' => MedioContacto::idDe(MedioContacto::WHATSAPP),
        ]));
    }

    public function edit(Pedido $pedido)
    {
        abort_unless($pedido->esEditable(), 403, 'El pedido ya está finalizado.');

        return $this->formulario($pedido->load('itemsVenta'));
    }

    private function formulario(Pedido $pedido)
    {
        $saldos = DB::table('v_saldo_clientes')->pluck('saldo_total', 'cliente_id');
        $garrafas = Garrafa::activas()->enEstado(EstadoGarrafa::EN_CLIENTE)->selectRaw('cliente_id, capacidad_kg, COUNT(*) n')
            ->groupBy('cliente_id', 'capacidad_kg')->toBase()->get()->groupBy('cliente_id');
        $clientes = Cliente::with('formaPagoHabitual')->where('activo', true)->orderBy('apellido')->orderBy('nombre')->get()
            ->map(fn ($c) => [
                'id' => $c->id, 'label' => "{$c->nombreCompleto()} · {$c->telefono_principal}", 'nombre' => $c->nombreCompleto(),
                'telefono' => $c->telefono_principal, 'wa' => wa_link($c->telefono_principal), 'domicilio' => $c->domicilioCompleto(),
                'referencias' => $c->referencias, 'forma_pago' => $c->formaPagoHabitual?->nombre, 'forma_pago_id' => $c->forma_pago_habitual_id,
                'saldo' => (float) ($saldos[$c->id] ?? 0), 'url' => route('clientes.show', $c),
                'garrafas' => collect($garrafas[$c->id] ?? [])->map(fn ($g) => "{$g->n}× {$g->capacidad_kg} kg")->implode(', '),
            ]);

        $productos = Producto::with('tipo')->where('activo', true)->orderBy('tipo_producto_id')->orderBy('capacidad_kg')->get();
        $stock = app(GarrafaService::class)->stock();

        return view('pedidos.form', [
            'titulo' => $pedido->exists ? "Editar pedido {$pedido->numero}" : 'Nuevo pedido',
            'migas' => ['Ventas' => null, 'Pedidos' => route('pedidos.index'), ($pedido->exists ? 'Editar' : 'Nuevo') => null],
            'pedido' => $pedido, 'clientes' => $clientes,
            'productos' => $productos->map(fn ($p) => [
                'id' => $p->id, 'nombre' => $p->nombre, 'precio' => $p->precio_actual, 'garrafa' => $p->esGarrafa(), 'icono' => $p->icono(),
                'tipo' => $p->tipo->nombre, 'stock' => $p->esGarrafa() ? $stock[$p->capacidad()]['LLENA'] ?? 0 : $p->stock_actual,
            ]),
            'medios' => MedioContacto::todos(), 'canales' => CanalVenta::todos(), 'formasPago' => FormaPago::todos()->where('activo', true),
        ]);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'medio_contacto_id' => 'required|exists:medios_contacto_pedido,id',
            'canal_venta_id' => 'required|exists:canales_venta,id',
            'fecha' => 'required|date',
            'direccion_entrega' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:2000',
            'descuento' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ], ['items.required' => 'Agregá al menos un producto.', 'cliente_id.required' => 'Seleccioná un cliente de la lista.']);
    }

    public function store(Request $request, PagoService $pagos)
    {
        $datos = $this->validar($request);
        $pago = $request->validate([
            'registrar_pago' => 'nullable|boolean', 'pago_monto' => 'nullable|required_if:registrar_pago,1|numeric|min:0',
            'forma_pago_id' => 'nullable|required_if:registrar_pago,1|exists:formas_pago,id', 'pago_referencia' => 'nullable|string|max:100',
        ]);
        if (CanalVenta::find($datos['canal_venta_id'])->codigo !== CanalVenta::DOMICILIO) {
            $datos['direccion_entrega'] = null;
        }

        $pedido = DB::transaction(function () use ($datos, $pago, $pagos) {
            $pedido = $this->servicio->crear($datos, $this->empleadoActual());
            if (! empty($pago['registrar_pago']) && $pago['pago_monto'] > 0) {
                $pagos->cobrar($pedido->cliente, $pedido, min((float) $pago['pago_monto'], $pedido->total), (int) $pago['forma_pago_id'], $pago['pago_referencia'] ?? null);
            }

            return $pedido;
        });

        if ($request->boolean('entregar_ahora')) {
            return redirect()->route('pedidos.entrega', $pedido)->with('ok', "Pedido {$pedido->numero} registrado. Confirmá la entrega de productos y envases.");
        }

        return redirect()->route('pedidos.show', $pedido)->with('ok', "Pedido {$pedido->numero} registrado.")
            ->with('pdf', $request->boolean('con_pdf') ? route('pedidos.pdf', $pedido) : null);
    }

    public function update(Request $request, Pedido $pedido)
    {
        $datos = $this->validar($request);
        unset($datos['cliente_id']);
        $this->servicio->actualizar($pedido, $datos);

        return redirect()->route('pedidos.show', $pedido)->with('ok', 'Pedido actualizado.');
    }

    public function show(Pedido $pedido)
    {
        $pedido->load(['cliente.formaPagoHabitual', 'estado', 'canal', 'medioContacto', 'empleado', 'items.producto', 'pagos.formaPago', 'movimientosGarrafa.garrafa', 'movimientosGarrafa.tipo']);

        return view('pedidos.show', [
            'titulo' => "Pedido {$pedido->numero}",
            'migas' => ['Ventas' => null, 'Pedidos' => route('pedidos.index'), $pedido->numero => null],
            'pedido' => $pedido,
        ]);
    }

    public function avanzar(Pedido $pedido)
    {
        if ($pedido->siguienteEstado() === EstadoPedido::ENTREGADO) {
            return redirect()->route('pedidos.entrega', $pedido);
        }
        $this->servicio->avanzar($pedido);

        return back()->with('ok', "Pedido {$pedido->numero}: {$pedido->refresh()->estado->nombre}.");
    }

    public function cancelar(Pedido $pedido)
    {
        $this->servicio->cancelar($pedido);

        return redirect()->route('pedidos.show', $pedido)->with('ok', 'Pedido cancelado.');
    }

    /** Formulario de entrega: qué garrafas llenas salen y qué envases vacíos se reciben */
    public function entrega(Pedido $pedido)
    {
        abort_unless($pedido->esEditable(), 403, 'El pedido ya está finalizado.');
        $pedido->load(['cliente', 'itemsVenta.producto']);

        return view('pedidos.entrega', [
            'titulo' => "Entrega del pedido {$pedido->numero}",
            'migas' => ['Ventas' => null, 'Pedidos' => route('pedidos.index'), $pedido->numero => route('pedidos.show', $pedido), 'Entrega' => null],
            'pedido' => $pedido,
            'necesidades' => $this->servicio->necesidadesEntrega($pedido),
            'otros' => $pedido->itemsVenta->reject(fn ($i) => $i->producto->esGarrafa()),
        ]);
    }

    public function entregar(Request $request, Pedido $pedido)
    {
        $datos = $request->validate([
            'entregadas' => 'array', 'entregadas.*' => 'integer|exists:garrafas,id',
            'devueltas' => 'array', 'devueltas.*' => 'integer|exists:garrafas,id',
            'no_aptas' => 'array', 'no_aptas.*' => 'integer',
            'sin_registrar' => 'array', 'sin_registrar.*' => 'integer|min:0|max:50',
        ]);
        foreach ($pedido->itemsVenta()->with('producto')->get()->reject(fn ($i) => $i->producto->esGarrafa()) as $item) {
            if ($item->producto->stock_actual < $item->cantidad) {
                throw new \DomainException("Stock insuficiente de {$item->producto->nombre} (hay ".num($item->producto->stock_actual).').');
            }
        }

        $this->servicio->entregar($pedido, $datos['entregadas'] ?? [], $datos['devueltas'] ?? [], $datos['no_aptas'] ?? [], $datos['sin_registrar'] ?? [], $this->empleadoActual());

        $redir = redirect()->route('pedidos.show', $pedido)->with('ok', "Pedido {$pedido->numero} entregado. Stock y garrafas actualizados.");

        return $pedido->refresh()->saldo > 0 && $request->boolean('cobrar')
            ? redirect()->route('cobros.create', ['cliente' => $pedido->cliente_id, 'pedido' => $pedido->id])->with('ok', "Pedido {$pedido->numero} entregado. Registrá el cobro.")
            : $redir;
    }

    public function pdf(Pedido $pedido)
    {
        $pedido->load(['cliente', 'estado', 'canal', 'medioContacto', 'empleado', 'items.producto', 'movimientosGarrafa.garrafa', 'movimientosGarrafa.tipo']);

        return Pdf::loadView('pdf.pedido', ['pedido' => $pedido])->download("pedido-{$pedido->numero}.pdf");
    }

    /** PDF genérico de listados e informes */
    public static function pdfListado(string $titulo, string $subtitulo, array $secciones, string $archivo, string $orientacion = 'portrait')
    {
        return Pdf::loadView('pdf.informe', compact('titulo', 'subtitulo', 'secciones'))
            ->setPaper('a4', $orientacion)->download("{$archivo}.pdf");
    }
}

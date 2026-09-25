<?php

namespace App\Http\Controllers;

use App\Models\Catalogos\EstadoGarrafa;
use App\Models\Catalogos\TipoMovimientoGarrafa;
use App\Models\Garrafa;
use App\Models\MovimientoGarrafa;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Services\GarrafaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GarrafaController extends Controller
{
    public function __construct(private GarrafaService $servicio) {}

    public function index(Request $request)
    {
        $garrafas = Garrafa::with(['estado', 'cliente'])
            ->when(! $request->filled('estado') || ! in_array($request->estado, [EstadoGarrafa::BAJA, EstadoGarrafa::EN_PROVEEDOR]), fn ($q) => $q->activas())
            ->when($request->capacidad, fn ($q, $v) => $q->where('capacidad_kg', $v))
            ->when($request->estado, fn ($q, $v) => $q->enEstado($v))
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w->where('codigo', 'like', "%{$v}%")->orWhereHas('cliente', fn ($c) => $c->buscar($v))))
            ->orderBy('capacidad_kg')->orderBy('codigo')->paginate(20, ['*'], 'pagina')->withQueryString();

        $movimientos = MovimientoGarrafa::with(['garrafa', 'tipo', 'estadoOrigen', 'estadoDestino', 'cliente', 'pedido', 'recepcion'])
            ->when($request->tipo, fn ($q, $v) => $q->where('tipo_movimiento_id', $v))
            ->orderByDesc('fecha')->orderByDesc('id')->paginate(15, ['*'], 'pagina_mov')->withQueryString();

        $flujo = MovimientoGarrafa::where('fecha', '>=', today()->subDays(29))
            ->join('garrafas', 'garrafas.id', '=', 'movimientos_garrafa.garrafa_id')
            ->join('tipos_movimiento_garrafa as t', 't.id', '=', 'movimientos_garrafa.tipo_movimiento_id')
            ->selectRaw('garrafas.capacidad_kg cap, t.codigo, COUNT(*) n')->groupBy('cap', 't.codigo')->toBase()->get();
        $cuenta = fn ($cap, $codigo) => (int) ($flujo->first(fn ($f) => $f->cap == $cap && $f->codigo === $codigo)?->n ?? 0);

        return view('garrafas.index', [
            'titulo' => 'Garrafas', 'migas' => ['Depósito' => null, 'Garrafas' => null],
            'stock' => $this->servicio->stock(), 'garrafas' => $garrafas, 'movimientos' => $movimientos,
            'productos' => Producto::where('maneja_garrafa_individual', true)->get()->keyBy(fn ($p) => $p->capacidad()),
            'estados' => EstadoGarrafa::todos(), 'tipos' => TipoMovimientoGarrafa::todos(),
            'grafico' => [
                'type' => 'bar',
                'data' => ['labels' => collect(Garrafa::CAPACIDADES)->map(fn ($c) => "{$c} kg"), 'datasets' => [
                    ['label' => 'Entregadas a clientes', 'data' => collect(Garrafa::CAPACIDADES)->map(fn ($c) => $cuenta($c, 'ENTREGA_CLIENTE')), 'backgroundColor' => '#e8590c', 'borderRadius' => 4],
                    ['label' => 'Recibidas de proveedores', 'data' => collect(Garrafa::CAPACIDADES)->map(fn ($c) => $cuenta($c, 'ALTA')), 'backgroundColor' => '#1971c2', 'borderRadius' => 4],
                    ['label' => 'Vacías devueltas al proveedor', 'data' => collect(Garrafa::CAPACIDADES)->map(fn ($c) => $cuenta($c, 'ENTREGA_PROVEEDOR')), 'backgroundColor' => '#adb5bd', 'borderRadius' => 4],
                ]],
                'options' => ['plugins' => ['legend' => ['position' => 'bottom']], 'scales' => ['x' => ['grid' => ['display' => false]]]],
            ],
        ]);
    }

    public function create()
    {
        return view('garrafas.create', [
            'titulo' => 'Alta de garrafas', 'migas' => ['Depósito' => null, 'Garrafas' => route('garrafas.index'), 'Alta' => null],
            'proveedores' => Proveedor::where('activo', true)->orderBy('razon_social')->pluck('razon_social', 'id'),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'capacidad_kg' => ['required', Rule::in(Garrafa::CAPACIDADES)],
            'cantidad' => 'required|integer|min:1|max:200',
            'estado' => ['required', Rule::in([EstadoGarrafa::LLENA, EstadoGarrafa::VACIA])],
            'codigos' => 'nullable|string|max:5000',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'observaciones' => 'nullable|string|max:500',
        ]);
        $codigos = collect(preg_split('/[\s,;]+/', (string) ($datos['codigos'] ?? ''), -1, PREG_SPLIT_NO_EMPTY))->unique()->values();
        if ($codigos->isNotEmpty() && $codigos->count() !== (int) $datos['cantidad']) {
            throw new \DomainException("Ingresaste {$codigos->count()} códigos para {$datos['cantidad']} garrafas.");
        }
        if ($repetido = Garrafa::withTrashed()->whereIn('codigo', $codigos)->value('codigo')) {
            throw new \DomainException("El código {$repetido} ya existe.");
        }

        DB::transaction(function () use ($datos, $codigos) {
            for ($i = 0; $i < $datos['cantidad']; $i++) {
                $this->servicio->alta((int) $datos['capacidad_kg'], $datos['estado'], [
                    'codigo' => $codigos[$i] ?? null, 'proveedor_id' => $datos['proveedor_id'] ?? null, 'observaciones' => ($datos['observaciones'] ?? null) ?: 'Alta manual',
                ], $this->empleadoActual());
            }
        });

        return redirect()->route('garrafas.index')->with('ok', "Se dieron de alta {$datos['cantidad']} garrafa(s) de {$datos['capacidad_kg']} kg.");
    }

    public function show(Garrafa $garrafa)
    {
        $garrafa->load(['estado', 'cliente', 'proveedor', 'movimientos.tipo', 'movimientos.estadoOrigen', 'movimientos.estadoDestino', 'movimientos.cliente', 'movimientos.pedido', 'movimientos.recepcion', 'movimientos.empleado']);
        $posibles = collect(GarrafaService::MOVIMIENTOS_MANUALES)->filter(fn ($m) => in_array($garrafa->estado->codigo, $m['desde'], true));

        return view('garrafas.show', [
            'titulo' => "Garrafa {$garrafa->codigo}", 'migas' => ['Depósito' => null, 'Garrafas' => route('garrafas.index'), $garrafa->codigo => null],
            'garrafa' => $garrafa, 'posibles' => $posibles, 'tipos' => TipoMovimientoGarrafa::todos(), 'estados' => EstadoGarrafa::todos(),
        ]);
    }

    public function movimiento(Request $request, Garrafa $garrafa)
    {
        $datos = $request->validate([
            'tipo' => ['required', Rule::in(array_merge(array_keys(GarrafaService::MOVIMIENTOS_MANUALES), [TipoMovimientoGarrafa::AJUSTE]))],
            'estado_ajuste' => ['nullable', Rule::in([EstadoGarrafa::LLENA, EstadoGarrafa::VACIA, EstadoGarrafa::NO_APTA])],
            'observaciones' => 'nullable|string|max:500',
        ]);
        if ($datos['tipo'] === TipoMovimientoGarrafa::AJUSTE) {
            abort_unless(auth()->user()->esAdministrador(), 403);
            $destino = $datos['estado_ajuste'] ?? throw new \DomainException('Indicá el estado correcto de la garrafa.');
        } else {
            $regla = GarrafaService::MOVIMIENTOS_MANUALES[$datos['tipo']];
            if (! in_array($garrafa->estado->codigo, $regla['desde'], true)) {
                throw new \DomainException('Ese movimiento no corresponde al estado actual de la garrafa.');
            }
            $destino = $regla['hacia'];
        }

        $this->servicio->mover($garrafa, $datos['tipo'], $destino, ['observaciones' => $datos['observaciones'] ?? null], $this->empleadoActual());

        return redirect()->route('garrafas.show', $garrafa)->with('ok', 'Movimiento registrado.');
    }

    public function informe()
    {
        $stock = $this->servicio->stock();
        $enClientes = DB::table('v_garrafas_en_clientes')->get();

        return PedidoController::pdfListado('Stock de garrafas', 'Al '.fecha(now(), true), [
            ['titulo' => 'Stock por capacidad', 'head' => ['Capacidad', 'Llenas', 'Vacías aptas', 'No aptas', 'En depósito', 'En clientes', 'Parque total'],
                'body' => collect($stock)->map(fn ($e, $cap) => ["{$cap} kg", $e['LLENA'], $e['VACIA'], $e['NO_APTA'], $e['LLENA'] + $e['VACIA'] + $e['NO_APTA'], $e['EN_CLIENTE'], array_sum($e)])->values(),
                'num' => [1, 2, 3, 4, 5, 6]],
            ['titulo' => 'Garrafas en poder de clientes', 'head' => ['Cliente', 'Código', 'Capacidad', 'Desde', 'Días'],
                'body' => $enClientes->map(fn ($g) => [$g->cliente, $g->codigo, "{$g->capacidad_kg} kg", fecha($g->fecha_ultimo_movimiento), $g->dias_en_cliente]), 'num' => [4]],
        ], 'stock-garrafas');
    }
}

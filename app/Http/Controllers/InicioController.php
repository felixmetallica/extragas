<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\GarrafaService;
use Illuminate\Support\Facades\DB;

class InicioController extends Controller
{
    public function __invoke(GarrafaService $garrafas)
    {
        $hoy = today();
        $cobrosHoy = Pago::with('formaPago')->whereDate('fecha', $hoy)->get();
        $stock = $garrafas->stock();
        $productos = Producto::with('tipo')->where('activo', true)->orderBy('id')->get();

        $ventas = Pedido::noCancelados()->where('fecha', '>=', $hoy->copy()->subDays(13))
            ->selectRaw('DATE(fecha) dia, SUM(total) total, COUNT(*) cantidad')->groupBy('dia')->toBase()->get()->keyBy('dia');
        $dias = collect(range(13, 0))->map(fn ($i) => $hoy->copy()->subDays($i));

        $clientes = Cliente::where('activo', true)->get()->keyBy('id');
        $porPedir = Cliente::regularidadDe($clientes->keys())
            ->filter(fn ($r) => in_array($r['estado'], ['Atrasado', 'Por pedir']))
            ->sortByDesc('atraso')->take(6)->map(fn ($r, $id) => ['cliente' => $clientes[$id], 'r' => $r]);

        return view('inicio', [
            'titulo' => 'Inicio',
            'migas' => [ucfirst($hoy->translatedFormat('l j \d\e F')) => null],
            'pedidosHoy' => Pedido::noCancelados()->whereDate('fecha', $hoy)->count(),
            'enCurso' => Pedido::enCurso()->with(['cliente', 'estado', 'medioContacto', 'canal', 'items.producto'])->orderBy('fecha')->get(),
            'cobrosHoy' => $cobrosHoy,
            'porCobrar' => DB::table('v_saldo_clientes')->selectRaw('COALESCE(SUM(saldo_total),0) total, COUNT(*) clientes')->first(),
            'ventasMes' => Pedido::noCancelados()->where('fecha', '>=', $hoy->copy()->startOfMonth())->sum('total'),
            'stock' => $stock,
            'garrafasProducto' => $productos->filter->esGarrafa()->keyBy(fn ($p) => $p->capacidad()),
            'stockBajo' => $productos->filter(fn ($p) => $p->esGarrafa() ? $stock[$p->capacidad()]['LLENA'] <= $p->stock_minimo : $p->stock_actual <= $p->stock_minimo),
            'porPedir' => $porPedir,
            'grafico' => [
                'type' => 'bar', 'pesos' => true,
                'data' => ['labels' => $dias->map->format('d/m'), 'datasets' => [
                    ['label' => 'Ventas', 'data' => $dias->map(fn ($d) => (float) ($ventas[$d->toDateString()]->total ?? 0)), 'backgroundColor' => '#e8590c', 'borderRadius' => 5],
                ]],
                'options' => ['plugins' => ['legend' => ['display' => false]], 'scales' => ['x' => ['grid' => ['display' => false]]]],
            ],
        ]);
    }
}

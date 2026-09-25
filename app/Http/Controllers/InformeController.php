<?php

namespace App\Http\Controllers;

use App\Models\Catalogos\EstadoPedido;
use App\Models\Catalogos\MedioContacto;
use App\Models\Cliente;
use App\Models\ConfiguracionEmpresa;
use App\Models\Pedido;
use App\Services\GarrafaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Informes: pedidos de clientes, productos más vendidos, regularidad de pedidos,
 * gestión de pagos y stock de garrafas. Se basan en las vistas v_* de la base.
 */
class InformeController extends Controller
{
    public const INFORMES = [
        'pedidos' => ['cart3', 'Pedidos de clientes', 'Cantidad, importes, medios y estados'],
        'productos' => ['trophy', 'Productos más vendidos', 'Ranking por unidades e importe'],
        'regularidad' => ['calendar2-week', 'Regularidad de pedidos', 'Frecuencia de compra por cliente'],
        'pagos' => ['cash-coin', 'Gestión de pagos', 'Cobros, deudas y pagos a proveedores'],
        'garrafas' => ['fuel-pump', 'Stock de garrafas', 'Envases en depósito y en clientes'],
    ];

    public function show(Request $request, ?string $tipo = 'pedidos')
    {
        $tipo ??= 'pedidos';
        [$desde, $hasta] = $this->rango($request);
        $dias = collect();
        for ($d = $desde->copy(); $d->lte($hasta) && $dias->count() < 366; $d->addDay()) {
            $dias->push($d->copy());
        }
        $datos = $this->{$tipo}($desde, $hasta, $dias);
        $periodo = $tipo === 'garrafas' ? 'Al '.fecha(now(), true) : 'Del '.fecha($desde).' al '.fecha($hasta);

        if ($request->boolean('pdf')) {
            return PedidoController::pdfListado(self::INFORMES[$tipo][1], $periodo, $datos['pdf'], 'informe-'.$tipo, $datos['orientacion'] ?? 'portrait');
        }

        return view('informes.'.$tipo, $datos + [
            'titulo' => 'Informes', 'migas' => ['Análisis' => null, 'Informes' => route('informes.show'), self::INFORMES[$tipo][1] => null],
            'tipo' => $tipo, 'informes' => self::INFORMES, 'desde' => $desde, 'hasta' => $hasta,
        ]);
    }

    private function serie(Collection $dias, Collection $porDia, string $campo): array
    {
        return $dias->map(fn (Carbon $d) => (float) ($porDia[$d->toDateString()]->{$campo} ?? 0))->all();
    }

    private function pedidos(Carbon $desde, Carbon $hasta, Collection $dias): array
    {
        $base = DB::table('v_pedidos_resumen')->whereBetween('fecha', [$desde, $hasta]);
        $validos = (clone $base)->where('estado_codigo', '!=', EstadoPedido::CANCELADO);
        $resumen = (clone $validos)->selectRaw('COUNT(*) n, COALESCE(SUM(total),0) total, COUNT(DISTINCT cliente_id) clientes, COALESCE(SUM(saldo),0) saldo')->first();
        $porDia = (clone $validos)->selectRaw('DATE(fecha) dia, COUNT(*) n, SUM(total) total')->groupBy('dia')->get()->keyBy('dia');
        $porEstado = (clone $base)->selectRaw('estado_nombre, COUNT(*) n')->groupBy('estado_nombre')->pluck('n', 'estado_nombre');
        $porMedio = Pedido::noCancelados()->whereBetween('fecha', [$desde, $hasta])->selectRaw('medio_contacto_id, COUNT(*) n')->groupBy('medio_contacto_id')->pluck('n', 'medio_contacto_id');
        $medios = MedioContacto::todos();
        $porEmpleado = (clone $validos)->selectRaw('empleado, COUNT(*) n, SUM(total) total')->groupBy('empleado')->orderByDesc('n')->get();
        $detalle = (clone $base)->orderByDesc('fecha')->get();

        return [
            'resumen' => $resumen, 'porEmpleado' => $porEmpleado, 'detalle' => $detalle, 'nDias' => $dias->count(),
            'graficoDias' => ['type' => 'bar', 'data' => ['labels' => $dias->map->format('d/m'), 'datasets' => [
                ['label' => 'Pedidos', 'data' => $this->serie($dias, $porDia, 'n'), 'backgroundColor' => '#e8590c', 'borderRadius' => 4]]],
                'options' => ['plugins' => ['legend' => ['display' => false]], 'scales' => ['x' => ['grid' => ['display' => false]], 'y' => ['ticks' => ['precision' => 0]]]]],
            'graficoMedios' => ['type' => 'doughnut', 'data' => ['labels' => $medios->pluck('nombre')->values(), 'datasets' => [
                ['data' => $medios->map(fn ($m) => $porMedio[$m->id] ?? 0)->values(), 'backgroundColor' => ['#1971c2', '#25d366', '#e8590c', '#adb5bd']]]],
                'options' => ['plugins' => ['legend' => ['position' => 'right']]]],
            'graficoEstados' => ['type' => 'bar', 'data' => ['labels' => $porEstado->keys(), 'datasets' => [['data' => $porEstado->values(), 'backgroundColor' => '#4dabf7', 'borderRadius' => 4]]],
                'options' => ['indexAxis' => 'y', 'plugins' => ['legend' => ['display' => false]], 'scales' => ['x' => ['ticks' => ['precision' => 0]]]]],
            'orientacion' => 'landscape',
            'pdf' => [
                ['resumen' => [['Pedidos (sin cancelados)', $resumen->n], ['Importe total', pesos($resumen->total)], ['Ticket promedio', pesos($resumen->n ? round($resumen->total / $resumen->n) : 0)],
                    ['Clientes atendidos', $resumen->clientes], ['Saldo pendiente', pesos($resumen->saldo)],
                    ...$medios->map(fn ($m) => ["Pedidos por {$m->nombre}", $porMedio[$m->id] ?? 0])->values()->all(),
                    ...$porEstado->map(fn ($n, $e) => ["Estado: {$e}", $n])->values()->all()]],
                ['titulo' => 'Pedidos por empleado', 'head' => ['Empleado', 'Pedidos', 'Importe'], 'body' => $porEmpleado->map(fn ($e) => [$e->empleado, $e->n, pesos($e->total)]), 'num' => [1, 2]],
                ['titulo' => 'Detalle', 'head' => ['N°', 'Fecha', 'Cliente', 'Teléfono', 'Empleado', 'Estado', 'Total', 'Pagado', 'Saldo'],
                    'body' => $detalle->map(fn ($p) => [$p->numero, fecha($p->fecha, true), $p->cliente, $p->cliente_telefono, $p->empleado, $p->estado_nombre, pesos($p->total), pesos($p->monto_pagado), pesos($p->saldo)]), 'num' => [6, 7, 8]],
            ],
        ];
    }

    private function productos(Carbon $desde, Carbon $hasta, Collection $dias): array
    {
        $ranking = DB::table('v_productos_mas_vendidos')->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->selectRaw('producto_id, producto_codigo, producto_nombre, tipo_producto, SUM(cantidad_vendida) vendida, SUM(cantidad_entregada) entregada, SUM(cantidad_devuelta) devuelta, SUM(monto_total) monto')
            ->groupBy('producto_id', 'producto_codigo', 'producto_nombre', 'tipo_producto')->orderByDesc('vendida')->get();
        $total = $ranking->sum('monto');
        $porTipo = $ranking->groupBy('tipo_producto')->map->sum('monto');

        return [
            'ranking' => $ranking, 'total' => $total, 'porTipo' => $porTipo,
            'graficoRanking' => ['type' => 'bar', 'data' => ['labels' => $ranking->pluck('producto_nombre'), 'datasets' => [['label' => 'Unidades', 'data' => $ranking->pluck('vendida')->map(fn ($v) => (float) $v), 'backgroundColor' => '#e8590c', 'borderRadius' => 4]]],
                'options' => ['indexAxis' => 'y', 'plugins' => ['legend' => ['display' => false]], 'scales' => ['y' => ['grid' => ['display' => false]]]]],
            'graficoTipos' => ['type' => 'doughnut', 'pesos' => true, 'data' => ['labels' => $porTipo->keys(), 'datasets' => [['data' => $porTipo->values()->map(fn ($v) => (float) $v), 'backgroundColor' => ['#e8590c', '#495057', '#8d5524']]]],
                'options' => ['plugins' => ['legend' => ['position' => 'bottom']]]],
            'pdf' => [
                ['resumen' => $porTipo->map(fn ($v, $t) => ["Facturación {$t}", pesos($v)])->values()->push(['Total', pesos($total)])->all()],
                ['titulo' => 'Ranking', 'head' => ['#', 'Producto', 'Tipo', 'Unidades', 'Envases entregados', 'Envases recibidos', 'Importe', '%'],
                    'body' => $ranking->values()->map(fn ($r, $i) => [$i + 1, $r->producto_nombre, $r->tipo_producto, num($r->vendida), num($r->entregada), num($r->devuelta), pesos($r->monto), $total ? round($r->monto / $total * 100).'%' : '—']),
                    'num' => [3, 4, 5, 6, 7]],
            ],
        ];
    }

    private function regularidad(Carbon $desde, Carbon $hasta, Collection $dias): array
    {
        $vista = DB::table('v_regularidad_clientes')->where('total_pedidos', '>', 0)->get()->keyBy('cliente_id');
        $clientes = Cliente::whereIn('id', $vista->keys())->get()->keyBy('id');
        $calc = Cliente::regularidadDe($vista->keys());
        $enPeriodo = Pedido::noCancelados()->whereBetween('fecha', [$desde, $hasta])->selectRaw('cliente_id, COUNT(*) n, SUM(total) total')->groupBy('cliente_id')->get()->keyBy('cliente_id');

        $filas = $vista->map(fn ($v) => (object) [
            'cliente' => $clientes[$v->cliente_id], 'total_pedidos' => $v->total_pedidos, 'promedio' => $v->dias_promedio_entre_pedidos ? round($v->dias_promedio_entre_pedidos) : null,
            'ultimo' => $v->ultimo_pedido, 'r' => $calc[$v->cliente_id], 'n_periodo' => $enPeriodo[$v->cliente_id]->n ?? 0, 'total_periodo' => $enPeriodo[$v->cliente_id]->total ?? 0,
            'saldo' => $v->saldo_pendiente,
        ])->sortBy(fn ($f) => $f->promedio ?? 9999)->values();
        $conProm = $filas->whereNotNull('promedio');
        $semana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $porDiaSemana = Pedido::noCancelados()->whereBetween('fecha', [$desde, $hasta])->selectRaw('DAYOFWEEK(fecha) d, COUNT(*) n')->groupBy('d')->pluck('n', 'd');
        $serieSemana = collect(range(1, 7))->map(fn ($d) => (int) ($porDiaSemana[$d] ?? 0));
        $rangos = [['≤ 7 días', 0, 7], ['8–14', 8, 14], ['15–21', 15, 21], ['22–30', 22, 30], ['> 30', 31, PHP_INT_MAX]];

        return [
            'filas' => $filas, 'promedio' => $conProm->count() ? round($conProm->avg('promedio')) : null,
            'atrasados' => $filas->where('r.estado', 'Atrasado')->count(), 'activos' => $filas->where('n_periodo', '>', 0)->count(),
            'diaPico' => $semana[$serieSemana->search($serieSemana->max())] ?? '—', 'picoN' => $serieSemana->max(),
            'tolerancia' => ConfiguracionEmpresa::actual()->dias_tolerancia_regularidad,
            'graficoSemana' => ['type' => 'bar', 'data' => ['labels' => $semana, 'datasets' => [['data' => $serieSemana, 'backgroundColor' => '#e8590c', 'borderRadius' => 4]]],
                'options' => ['plugins' => ['legend' => ['display' => false]], 'scales' => ['x' => ['grid' => ['display' => false]], 'y' => ['ticks' => ['precision' => 0]]]]],
            'graficoFrecuencia' => ['type' => 'bar', 'data' => ['labels' => array_column($rangos, 0), 'datasets' => [['label' => 'Clientes',
                'data' => collect($rangos)->map(fn ($r) => $conProm->filter(fn ($f) => $f->promedio >= $r[1] && $f->promedio <= $r[2])->count()), 'backgroundColor' => '#1971c2', 'borderRadius' => 4]]],
                'options' => ['plugins' => ['legend' => ['display' => false]], 'scales' => ['x' => ['grid' => ['display' => false]], 'y' => ['ticks' => ['precision' => 0]]]]],
            'orientacion' => 'landscape',
            'pdf' => [
                ['resumen' => [['Frecuencia promedio entre pedidos', $conProm->count() ? round($conProm->avg('promedio')).' días' : '—'], ['Clientes con pedidos en el período', $filas->where('n_periodo', '>', 0)->count()],
                    ['Clientes atrasados', $filas->where('r.estado', 'Atrasado')->count()], ...collect($semana)->map(fn ($d, $i) => ["Pedidos los {$d}", $serieSemana[$i]])->all()]],
                ['titulo' => 'Regularidad por cliente', 'head' => ['Cliente', 'Teléfono', 'Pedidos (total)', 'En el período', 'Pide cada', 'Último', 'Próximo estimado', 'Estado', 'Saldo'],
                    'body' => $filas->map(fn ($f) => [$f->cliente->nombreCompleto(), $f->cliente->telefono_principal, $f->total_pedidos, $f->n_periodo, $f->promedio ? "{$f->promedio} días" : '—',
                        fecha($f->ultimo), fecha($f->r['proximo']), $f->r['estado'], pesos($f->saldo)]), 'num' => [2, 3, 8]],
            ],
        ];
    }

    private function pagos(Carbon $desde, Carbon $hasta, Collection $dias): array
    {
        $porForma = DB::table('v_pagos_por_forma_pago')->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->selectRaw('forma_pago_nombre, SUM(cantidad_pagos) cantidad, SUM(monto_total) total')->groupBy('forma_pago_nombre')->orderByDesc('total')->get();
        $cobrosDia = DB::table('v_pagos_por_forma_pago')->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->selectRaw('fecha dia, SUM(monto_total) total')->groupBy('fecha')->get()->keyBy('dia');
        $pagosProv = DB::table('pagos_proveedor')->whereNull('deleted_at')->whereBetween('fecha', [$desde, $hasta]);
        $pagosProvDia = (clone $pagosProv)->selectRaw('DATE(fecha) dia, SUM(monto) total')->groupBy('dia')->get()->keyBy('dia');
        $vendido = Pedido::noCancelados()->whereBetween('fecha', [$desde, $hasta])->selectRaw('COALESCE(SUM(total),0) total, COALESCE(SUM(saldo),0) saldo')->toBase()->first();
        $deudores = DB::table('v_saldo_clientes')->get();
        $proveedores = DB::table('v_saldo_proveedores')->get();
        $habituales = Cliente::where('clientes.activo', true)->join('formas_pago', 'formas_pago.id', '=', 'clientes.forma_pago_habitual_id')
            ->selectRaw('formas_pago.nombre, COUNT(*) n')->groupBy('formas_pago.nombre')->pluck('n', 'nombre');
        $cobrado = $porForma->sum('total');
        $pagadoProv = (clone $pagosProv)->sum('monto');

        return [
            'porForma' => $porForma, 'cobrado' => $cobrado, 'vendido' => $vendido, 'deudores' => $deudores, 'proveedores' => $proveedores, 'pagadoProv' => $pagadoProv,
            'graficoFlujo' => ['type' => 'line', 'pesos' => true, 'data' => ['labels' => $dias->map->format('d/m'), 'datasets' => [
                ['label' => 'Cobros a clientes', 'data' => $this->serie($dias, $cobrosDia, 'total'), 'borderColor' => '#2b8a3e', 'backgroundColor' => 'rgba(43,138,62,.1)', 'fill' => true, 'tension' => .3],
                ['label' => 'Pagos a proveedores', 'data' => $this->serie($dias, $pagosProvDia, 'total'), 'borderColor' => '#c92a2a', 'backgroundColor' => 'rgba(201,42,42,.05)', 'fill' => true, 'tension' => .3],
            ]], 'options' => ['interaction' => ['mode' => 'index', 'intersect' => false], 'plugins' => ['legend' => ['position' => 'bottom']], 'scales' => ['x' => ['grid' => ['display' => false]]]]],
            'graficoFormas' => ['type' => 'doughnut', 'pesos' => true, 'data' => ['labels' => $porForma->pluck('forma_pago_nombre'), 'datasets' => [['data' => $porForma->pluck('total')->map(fn ($v) => (float) $v), 'backgroundColor' => ['#40c057', '#1971c2', '#f59f00', '#7950f2']]]],
                'options' => ['plugins' => ['legend' => ['position' => 'right']]]],
            'graficoHabitual' => ['type' => 'doughnut', 'data' => ['labels' => $habituales->keys(), 'datasets' => [['data' => $habituales->values(), 'backgroundColor' => ['#40c057', '#1971c2', '#f59f00', '#7950f2']]]],
                'options' => ['plugins' => ['legend' => ['position' => 'right']]]],
            'pdf' => [
                ['resumen' => [['Total vendido', pesos($vendido->total)], ['Total cobrado', pesos($cobrado)], ...$porForma->map(fn ($f) => ["Cobrado en {$f->forma_pago_nombre}", pesos($f->total)])->all(),
                    ['Pendiente de cobro (pedidos del período)', pesos($vendido->saldo)], ['Deuda total de clientes', pesos($deudores->sum('saldo_total'))],
                    ['Pagado a proveedores', pesos($pagadoProv)], ['Deuda con proveedores', pesos($proveedores->sum('saldo_total'))], ['Resultado de caja', pesos($cobrado - $pagadoProv)]]],
                ['titulo' => 'Clientes con saldo pendiente', 'head' => ['Cliente', 'Teléfono', 'Pedidos adeudados', 'Saldo'], 'body' => $deudores->map(fn ($d) => [$d->cliente, $d->telefono_principal, $d->pedidos_pendientes, pesos($d->saldo_total)]), 'num' => [2, 3]],
                ['titulo' => 'Saldos con proveedores', 'head' => ['Proveedor', 'CUIT', 'Recepciones impagas', 'Saldo'], 'body' => $proveedores->map(fn ($p) => [$p->razon_social, $p->cuit, $p->recepciones_pendientes, pesos($p->saldo_total)]), 'num' => [2, 3]],
            ],
        ];
    }

    private function garrafas(Carbon $desde, Carbon $hasta, Collection $dias): array
    {
        $stock = app(GarrafaService::class)->stock();
        $enClientes = DB::table('v_garrafas_en_clientes')->get();

        return [
            'stock' => $stock, 'enClientes' => $enClientes,
            'pdf' => [
                ['titulo' => 'Stock por capacidad', 'head' => ['Capacidad', 'Llenas', 'Vacías aptas', 'No aptas', 'En depósito', 'En clientes', 'Parque total'],
                    'body' => collect($stock)->map(fn ($e, $c) => ["{$c} kg", $e['LLENA'], $e['VACIA'], $e['NO_APTA'], $e['LLENA'] + $e['VACIA'] + $e['NO_APTA'], $e['EN_CLIENTE'], array_sum($e)])->values(), 'num' => [1, 2, 3, 4, 5, 6]],
                ['titulo' => 'Garrafas en poder de clientes', 'head' => ['Cliente', 'Código', 'Capacidad', 'Desde', 'Días'],
                    'body' => $enClientes->map(fn ($g) => [$g->cliente, $g->codigo, "{$g->capacidad_kg} kg", fecha($g->fecha_ultimo_movimiento), $g->dias_en_cliente]), 'num' => [4]],
            ],
        ];
    }
}

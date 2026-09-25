<?php

namespace App\Controllers;

use App\Core\Controlador;
use App\Core\ErrorHttp;
use App\Core\Pdf;
use App\Models\Catalogo;
use App\Models\Configuracion;
use App\Models\Garrafa;
use App\Models\Informe;

/**
 * Informes: pedidos de clientes, productos más vendidos, regularidad de pedidos,
 * gestión de pagos y stock de garrafas. En pantalla y en PDF (?pdf=1).
 */
class InformeController extends Controlador
{
    public const INFORMES = [
        'pedidos' => ['cart3', 'Pedidos de clientes', 'Cantidad, importes, medios y estados'],
        'productos' => ['trophy', 'Productos más vendidos', 'Ranking por unidades e importe'],
        'regularidad' => ['calendar2-week', 'Regularidad de pedidos', 'Frecuencia de compra por cliente'],
        'pagos' => ['cash-coin', 'Gestión de pagos', 'Cobros, deudas y pagos a proveedores'],
        'garrafas' => ['fuel-pump', 'Stock de garrafas', 'Envases en depósito y en clientes'],
    ];

    private const SEMANA = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

    public function ver(string $tipo = 'pedidos'): string
    {
        if (! isset(self::INFORMES[$tipo])) {
            throw new ErrorHttp('Informe inexistente.', 404);
        }
        [$desde, $hasta] = $this->rango();
        $dias = [];
        for ($d = $desde; $d <= $hasta && count($dias) < 366; $d = date('Y-m-d', strtotime("{$d} +1 day"))) {
            $dias[] = $d;
        }
        $datos = $this->$tipo($desde, $hasta, $dias);

        if ($this->quierePdf()) {
            $periodo = $tipo === 'garrafas' ? 'Al '.date('d/m/Y H:i') : 'Del '.fecha($desde).' al '.fecha($hasta);
            Pdf::informe(self::INFORMES[$tipo][1], $periodo, $datos['pdf'], "informe-{$tipo}", $datos['orientacion'] ?? 'P');
        }

        return $this->vista("informes/{$tipo}", $datos + [
            'titulo' => 'Informes', 'migas' => ['Análisis' => null, 'Informes' => url('informes'), self::INFORMES[$tipo][1] => null],
            'tipo' => $tipo, 'informes' => self::INFORMES, 'desde' => $desde, 'hasta' => $hasta,
        ]);
    }

    private static function etiquetas(array $dias): array
    {
        return array_map(fn ($d) => date('d/m', strtotime($d)), $dias);
    }

    private function pedidos(string $desde, string $hasta, array $dias): array
    {
        $resumen = Informe::pedidosResumen($desde, $hasta);
        $porDia = Informe::ventasPorDia($desde, $hasta);
        $porEstado = Informe::pedidosPorEstado($desde, $hasta);
        $porMedio = Informe::pedidosPorMedio($desde, $hasta);
        $porEmpleado = Informe::pedidosPorEmpleado($desde, $hasta);
        $detalle = Informe::pedidosDetalle($desde, $hasta);
        $medios = array_column(Catalogo::todos('medios_contacto_pedido'), 'nombre');

        return [
            'resumen' => $resumen, 'porEmpleado' => $porEmpleado, 'detalle' => $detalle, 'nDias' => count($dias),
            'graficoDias' => ['type' => 'bar', 'data' => ['labels' => self::etiquetas($dias), 'datasets' => [
                ['label' => 'Pedidos', 'data' => array_map(fn ($d) => (int) ($porDia[$d]['n'] ?? 0), $dias), 'backgroundColor' => '#e8590c', 'borderRadius' => 4]]],
                'options' => ['plugins' => ['legend' => ['display' => false]], 'scales' => ['x' => ['grid' => ['display' => false]], 'y' => ['ticks' => ['precision' => 0]]]]],
            'graficoMedios' => ['type' => 'doughnut', 'data' => ['labels' => $medios, 'datasets' => [
                ['data' => array_map(fn ($m) => (int) ($porMedio[$m] ?? 0), $medios), 'backgroundColor' => ['#1971c2', '#25d366', '#e8590c', '#adb5bd']]]],
                'options' => ['plugins' => ['legend' => ['position' => 'right']]]],
            'graficoEstados' => ['type' => 'bar', 'data' => ['labels' => array_keys($porEstado), 'datasets' => [['data' => array_map('intval', array_values($porEstado)), 'backgroundColor' => '#4dabf7', 'borderRadius' => 4]]],
                'options' => ['indexAxis' => 'y', 'plugins' => ['legend' => ['display' => false]], 'scales' => ['x' => ['ticks' => ['precision' => 0]]]]],
            'orientacion' => 'L',
            'pdf' => [
                ['resumen' => array_merge([['Pedidos (sin cancelados)', $resumen['n']], ['Importe total', pesos($resumen['total'])],
                    ['Ticket promedio', pesos($resumen['n'] ? round($resumen['total'] / $resumen['n']) : 0)], ['Clientes atendidos', $resumen['clientes']], ['Saldo pendiente', pesos($resumen['saldo'])]],
                    array_map(fn ($m) => ["Pedidos por {$m}", $porMedio[$m] ?? 0], $medios),
                    array_map(fn ($e, $n) => ["Estado: {$e}", $n], array_keys($porEstado), $porEstado))],
                ['titulo' => 'Pedidos por empleado', 'cabecera' => ['Empleado', 'Pedidos', 'Importe'], 'filas' => array_map(fn ($e) => [$e['empleado'], $e['n'], pesos($e['total'])], $porEmpleado), 'derecha' => [1, 2]],
                ['titulo' => 'Detalle', 'cabecera' => ['N°', 'Fecha', 'Cliente', 'Teléfono', 'Empleado', 'Estado', 'Total', 'Pagado', 'Saldo'],
                    'filas' => array_map(fn ($p) => [$p['numero'], fecha($p['fecha'], true), $p['cliente'], $p['cliente_telefono'], $p['empleado'], $p['estado_nombre'], pesos($p['total']), pesos($p['monto_pagado']), pesos($p['saldo'])], $detalle),
                    'derecha' => [6, 7, 8]],
            ],
        ];
    }

    private function productos(string $desde, string $hasta, array $dias): array
    {
        $ranking = Informe::productosRanking($desde, $hasta);
        $total = array_sum(array_column($ranking, 'monto'));
        $porTipo = [];
        foreach ($ranking as $r) {
            $porTipo[$r['tipo_producto']] = ($porTipo[$r['tipo_producto']] ?? 0) + (float) $r['monto'];
        }

        return [
            'ranking' => $ranking, 'total' => $total, 'porTipo' => $porTipo,
            'graficoRanking' => ['type' => 'bar', 'data' => ['labels' => array_column($ranking, 'producto_nombre'), 'datasets' => [
                ['label' => 'Unidades', 'data' => array_map('floatval', array_column($ranking, 'vendida')), 'backgroundColor' => '#e8590c', 'borderRadius' => 4]]],
                'options' => ['indexAxis' => 'y', 'plugins' => ['legend' => ['display' => false]], 'scales' => ['y' => ['grid' => ['display' => false]]]]],
            'graficoTipos' => ['type' => 'doughnut', 'pesos' => true, 'data' => ['labels' => array_keys($porTipo), 'datasets' => [
                ['data' => array_values($porTipo), 'backgroundColor' => ['#e8590c', '#495057', '#8d5524']]]], 'options' => ['plugins' => ['legend' => ['position' => 'bottom']]]],
            'pdf' => [
                ['resumen' => array_merge(array_map(fn ($t, $v) => ["Facturación {$t}", pesos($v)], array_keys($porTipo), $porTipo), [['Total', pesos($total)]])],
                ['titulo' => 'Ranking', 'cabecera' => ['#', 'Producto', 'Tipo', 'Unidades', 'Envases entregados', 'Envases recibidos', 'Importe', '%'],
                    'filas' => array_map(fn ($r, $i) => [$i + 1, $r['producto_nombre'], $r['tipo_producto'], num($r['vendida']), num($r['entregada']), num($r['devuelta']), pesos($r['monto']),
                        $total ? round($r['monto'] / $total * 100).'%' : '—'], $ranking, array_keys($ranking)),
                    'derecha' => [3, 4, 5, 6, 7]],
            ],
        ];
    }

    private function regularidad(string $desde, string $hasta, array $dias): array
    {
        $filas = Informe::regularidad($desde, $hasta);
        $conProm = array_filter($filas, fn ($f) => $f['promedio'] !== null);
        $promedio = $conProm ? (int) round(array_sum(array_column($conProm, 'promedio')) / count($conProm)) : null;
        $semana = Informe::pedidosPorDiaSemana($desde, $hasta);
        $rangos = [['≤ 7 días', 0, 7], ['8–14', 8, 14], ['15–21', 15, 21], ['22–30', 22, 30], ['> 30', 31, PHP_INT_MAX]];
        $atrasados = count(array_filter($filas, fn ($f) => $f['r']['estado'] === 'Atrasado'));
        $activos = count(array_filter($filas, fn ($f) => $f['n_periodo'] > 0));

        return [
            'filas' => $filas, 'promedio' => $promedio, 'atrasados' => $atrasados, 'activos' => $activos,
            'diaPico' => self::SEMANA[array_search(max($semana), $semana, true)], 'picoN' => max($semana), 'tolerancia' => Configuracion::toleranciaRegularidad(),
            'graficoSemana' => ['type' => 'bar', 'data' => ['labels' => self::SEMANA, 'datasets' => [['data' => $semana, 'backgroundColor' => '#e8590c', 'borderRadius' => 4]]],
                'options' => ['plugins' => ['legend' => ['display' => false]], 'scales' => ['x' => ['grid' => ['display' => false]], 'y' => ['ticks' => ['precision' => 0]]]]],
            'graficoFrecuencia' => ['type' => 'bar', 'data' => ['labels' => array_column($rangos, 0), 'datasets' => [['label' => 'Clientes',
                'data' => array_map(fn ($r) => count(array_filter($conProm, fn ($f) => $f['promedio'] >= $r[1] && $f['promedio'] <= $r[2])), $rangos), 'backgroundColor' => '#1971c2', 'borderRadius' => 4]]],
                'options' => ['plugins' => ['legend' => ['display' => false]], 'scales' => ['x' => ['grid' => ['display' => false]], 'y' => ['ticks' => ['precision' => 0]]]]],
            'orientacion' => 'L',
            'pdf' => [
                ['resumen' => array_merge([['Frecuencia promedio entre pedidos', $promedio ? "{$promedio} días" : '—'], ['Clientes con pedidos en el período', $activos], ['Clientes atrasados', $atrasados]],
                    array_map(fn ($d, $n) => ["Pedidos los {$d}", $n], self::SEMANA, $semana))],
                ['titulo' => 'Regularidad por cliente', 'cabecera' => ['Cliente', 'Teléfono', 'Pedidos (total)', 'En el período', 'Pide cada', 'Último', 'Próximo estimado', 'Estado', 'Saldo'],
                    'filas' => array_map(fn ($f) => [nombre($f), $f['telefono_principal'], $f['total_pedidos'], $f['n_periodo'], $f['promedio'] ? "{$f['promedio']} días" : '—',
                        fecha($f['ultimo_pedido']), fecha($f['r']['proximo']), $f['r']['estado'], pesos($f['saldo_pendiente'])], $filas),
                    'derecha' => [2, 3, 8]],
            ],
        ];
    }

    private function pagos(string $desde, string $hasta, array $dias): array
    {
        $porForma = Informe::cobrosPorForma($desde, $hasta);
        $cobrosDia = Informe::cobrosPorDia($desde, $hasta);
        $provDia = Informe::pagosProveedoresPorDia($desde, $hasta);
        $vendido = Informe::vendidoPeriodo($desde, $hasta);
        $deudores = Informe::saldosClientes();
        $proveedores = Informe::saldosProveedores();
        $habituales = Informe::formasHabituales();
        $cobrado = array_sum(array_column($porForma, 'total'));
        $pagadoProv = array_sum($provDia);
        $colores = ['#40c057', '#1971c2', '#f59f00', '#7950f2'];

        return [
            'porForma' => $porForma, 'cobrado' => $cobrado, 'vendido' => $vendido, 'deudores' => $deudores, 'proveedores' => $proveedores, 'pagadoProv' => $pagadoProv,
            'graficoFlujo' => ['type' => 'line', 'pesos' => true, 'data' => ['labels' => self::etiquetas($dias), 'datasets' => [
                ['label' => 'Cobros a clientes', 'data' => array_map(fn ($d) => (float) ($cobrosDia[$d] ?? 0), $dias), 'borderColor' => '#2b8a3e', 'backgroundColor' => 'rgba(43,138,62,.1)', 'fill' => true, 'tension' => .3],
                ['label' => 'Pagos a proveedores', 'data' => array_map(fn ($d) => (float) ($provDia[$d] ?? 0), $dias), 'borderColor' => '#c92a2a', 'backgroundColor' => 'rgba(201,42,42,.05)', 'fill' => true, 'tension' => .3],
            ]], 'options' => ['interaction' => ['mode' => 'index', 'intersect' => false], 'plugins' => ['legend' => ['position' => 'bottom']], 'scales' => ['x' => ['grid' => ['display' => false]]]]],
            'graficoFormas' => ['type' => 'doughnut', 'pesos' => true, 'data' => ['labels' => array_column($porForma, 'forma_pago_nombre'),
                'datasets' => [['data' => array_map('floatval', array_column($porForma, 'total')), 'backgroundColor' => $colores]]], 'options' => ['plugins' => ['legend' => ['position' => 'right']]]],
            'graficoHabitual' => ['type' => 'doughnut', 'data' => ['labels' => array_keys($habituales),
                'datasets' => [['data' => array_map('intval', array_values($habituales)), 'backgroundColor' => $colores]]], 'options' => ['plugins' => ['legend' => ['position' => 'right']]]],
            'pdf' => [
                ['resumen' => array_merge([['Total vendido', pesos($vendido['total'])], ['Total cobrado', pesos($cobrado)]],
                    array_map(fn ($f) => ["Cobrado en {$f['forma_pago_nombre']}", pesos($f['total'])], $porForma),
                    [['Pendiente de cobro (pedidos del período)', pesos($vendido['saldo'])], ['Deuda total de clientes', pesos(array_sum(array_column($deudores, 'saldo_total')))],
                        ['Pagado a proveedores', pesos($pagadoProv)], ['Deuda con proveedores', pesos(array_sum(array_column($proveedores, 'saldo_total')))], ['Resultado de caja', pesos($cobrado - $pagadoProv)]])],
                ['titulo' => 'Clientes con saldo pendiente', 'cabecera' => ['Cliente', 'Teléfono', 'Pedidos adeudados', 'Saldo'],
                    'filas' => array_map(fn ($d) => [$d['cliente'], $d['telefono_principal'], $d['pedidos_pendientes'], pesos($d['saldo_total'])], $deudores), 'derecha' => [2, 3]],
                ['titulo' => 'Saldos con proveedores', 'cabecera' => ['Proveedor', 'CUIT', 'Recepciones impagas', 'Saldo'],
                    'filas' => array_map(fn ($p) => [$p['razon_social'], $p['cuit'], $p['recepciones_pendientes'], pesos($p['saldo_total'])], $proveedores), 'derecha' => [2, 3]],
            ],
        ];
    }

    private function garrafas(string $desde, string $hasta, array $dias): array
    {
        return ['stock' => Garrafa::stock(), 'enClientes' => Informe::garrafasEnClientes(), 'pdf' => GarrafaController::seccionesStock()];
    }
}

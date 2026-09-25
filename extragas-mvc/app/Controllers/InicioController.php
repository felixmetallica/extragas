<?php

namespace App\Controllers;

use App\Core\Controlador;
use App\Models\Cliente;
use App\Models\Garrafa;
use App\Models\Informe;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Producto;

class InicioController extends Controlador
{
    public function index(): string
    {
        $stock = Garrafa::stock();
        $productos = Producto::todos(true);
        $saldos = Informe::saldosClientes();

        $desde = date('Y-m-d', strtotime('-13 days'));
        $ventas = Informe::ventasPorDia($desde, hoy());
        $dias = array_map(fn ($i) => date('Y-m-d', strtotime("{$desde} +{$i} days")), range(0, 13));

        $clientes = array_filter(Cliente::listar([]), fn ($c) => in_array($c['regularidad']['estado'], ['Atrasado', 'Por pedir'], true));
        usort($clientes, fn ($a, $b) => $b['regularidad']['atraso'] <=> $a['regularidad']['atraso']);

        return $this->vista('inicio', [
            'titulo' => 'Inicio', 'migas' => [ucfirst(fecha_larga(hoy())) => null],
            'pedidosHoy' => Informe::pedidosHoy(), 'enCurso' => Pedido::enCurso(),
            'cobrosHoy' => Informe::cobrosHoyPorForma(), 'cobradoHoy' => Pago::cobradoHoy(),
            'porCobrar' => array_sum(array_column($saldos, 'saldo_total')), 'deudores' => count($saldos),
            'ventasMes' => Informe::ventasMes(), 'stock' => $stock,
            'garrafaProducto' => Producto::garrafasPorCapacidad(),
            'stockBajo' => array_filter($productos, fn ($p) => Producto::stockDisponible($p, $stock) <= (float) $p['stock_minimo']),
            'porPedir' => array_slice($clientes, 0, 6),
            'grafico' => ['type' => 'bar', 'pesos' => true, 'data' => [
                'labels' => array_map(fn ($d) => date('d/m', strtotime($d)), $dias),
                'datasets' => [['label' => 'Ventas', 'data' => array_map(fn ($d) => (float) ($ventas[$d]['total'] ?? 0), $dias), 'backgroundColor' => '#e8590c', 'borderRadius' => 5]],
            ], 'options' => ['plugins' => ['legend' => ['display' => false]], 'scales' => ['x' => ['grid' => ['display' => false]]]]],
        ]);
    }
}

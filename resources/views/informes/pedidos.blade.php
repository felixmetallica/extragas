@extends('informes.layout')

@section('informe')
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">@include('partials.kpi', ['icono' => 'receipt', 'label' => 'Pedidos', 'valor' => num($resumen->n), 'sub' => round($resumen->n / max(1, $nDias), 1).' por día'])</div>
    <div class="col-6 col-xl-3">@include('partials.kpi', ['icono' => 'currency-dollar', 'color' => 'i-green', 'label' => 'Importe total', 'valor' => pesos($resumen->total), 'sub' => 'Ticket prom. '.pesos($resumen->n ? round($resumen->total / $resumen->n) : 0)])</div>
    <div class="col-6 col-xl-3">@include('partials.kpi', ['icono' => 'people', 'color' => 'i-blue', 'label' => 'Clientes atendidos', 'valor' => $resumen->clientes])</div>
    <div class="col-6 col-xl-3">@include('partials.kpi', ['icono' => 'hourglass-split', 'color' => 'i-red', 'label' => 'Saldo pendiente', 'valor' => pesos($resumen->saldo)])</div>
</div>
<div class="card mb-3"><div class="card-header"><h2>Pedidos por día</h2></div><div class="card-body"><div class="chart-box"><canvas data-chart='@json($graficoDias)'></canvas></div></div></div>
<div class="row g-3 mb-3">
    <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Por medio de contacto</h2></div><div class="card-body"><div class="chart-box sm"><canvas data-chart='@json($graficoMedios)'></canvas></div></div></div></div>
    <div class="col-md-6"><div class="card h-100"><div class="card-header"><h2>Por estado</h2></div><div class="card-body"><div class="chart-box sm"><canvas data-chart='@json($graficoEstados)'></canvas></div></div></div></div>
</div>
<div class="card mb-3"><div class="card-header"><h2>Por empleado</h2></div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Empleado</th><th class="num">Pedidos</th><th class="num">Importe</th></tr></thead>
        <tbody>@foreach ($porEmpleado as $e)<tr><td>{{ $e->empleado }}</td><td class="num">{{ $e->n }}</td><td class="num">{{ pesos($e->total) }}</td></tr>@endforeach</tbody></table></div></div>
<div class="card"><div class="card-header"><h2>Detalle ({{ $detalle->count() }})</h2></div>
    <div class="table-responsive" style="max-height:520px"><table class="table table-hover align-middle">
        <thead><tr><th>N°</th><th>Fecha</th><th>Cliente</th><th>Empleado</th><th>Estado</th><th class="num">Total</th><th>Pago</th></tr></thead>
        <tbody>@foreach ($detalle as $p)
            <tr><td><a href="{{ route('pedidos.show', $p->id) }}">{{ $p->numero }}</a></td><td>{{ fecha($p->fecha) }}</td><td>{{ $p->cliente }}</td><td class="small">{{ $p->empleado }}</td>
                <td>{{ $p->estado_nombre }}</td><td class="num">{{ pesos($p->total) }}</td><td>{{ badge_pago(['PAGADO' => 'Pagado', 'PARCIAL' => 'Parcial', 'PENDIENTE' => 'Pendiente'][$p->estado_pago]) }}</td></tr>
        @endforeach</tbody>
    </table></div></div>
@endsection

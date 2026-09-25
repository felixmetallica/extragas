@extends('pdf.layout')

@section('titulo')RECIBO {{ $pagos->pluck('numero_recibo')->implode(' / ') }}@endsection
@section('subtitulo')Fecha: {{ fecha($pagos->first()->fecha, true) }}@endsection

@section('cuerpo')
@php $total = $pagos->sum('monto'); @endphp
<p style="font-size:10.5pt;line-height:1.6">Recibimos de <b>{{ $cliente->nombreCompleto() }}</b>{{ $cliente->dni ? ' (DNI '.$cliente->dni.')' : '' }}
    la suma de <b>{{ pesos($total) }}</b> en concepto de pago según el siguiente detalle:</p>
<table>
    <thead><tr><th>Recibo</th><th>Concepto</th><th>Forma de pago</th><th>Referencia</th><th class="num">Importe</th></tr></thead>
    <tbody>@foreach ($pagos as $p)
        <tr><td>{{ $p->numero_recibo }}</td>
            <td>@if ($p->pedido)Pedido {{ $p->pedido->numero }} del {{ fecha($p->pedido->fecha) }}<br><span class="muted">{{ $p->pedido->resumenItems() }}</span>@else Pago a cuenta @endif</td>
            <td>{{ $p->formaPago->nombre }}</td><td>{{ $p->referencia ?: '—' }}</td><td class="num">{{ pesos($p->monto) }}</td></tr>
    @endforeach</tbody>
</table>
<div class="total"><span>TOTAL RECIBIDO</span><b>{{ pesos($total) }}</b></div>
<p style="clear:both;padding-top:4mm" class="muted">Saldo pendiente del cliente luego de este pago: {{ pesos($cliente->saldo()) }}</p>
<div class="firma">Recibió: {{ $pagos->first()->creador?->nombreVisible() ?? $empresa->nombre }}</div>
@endsection

@extends('pdf.layout')

@section('titulo'){{ mb_strtoupper($titulo) }}@endsection
@section('subtitulo'){{ $subtitulo }}@endsection

@section('cuerpo')
@foreach ($secciones as $s)
    @if (! empty($s['titulo']))<h3>{{ $s['titulo'] }}</h3>@endif
    @if (! empty($s['resumen']))
        <table class="resumen" style="width:60%;margin-bottom:4mm">
            @foreach ($s['resumen'] as [$k, $v])<tr><td>{{ $k }}</td><td class="num">{{ $v }}</td></tr>@endforeach
        </table>
    @endif
    @if (! empty($s['head']))
        <table>
            <thead><tr>@foreach ($s['head'] as $i => $h)<th @class(['num' => in_array($i, $s['num'] ?? [])])>{{ $h }}</th>@endforeach</tr></thead>
            <tbody>
            @forelse ($s['body'] as $fila)
                <tr>@foreach (array_values((array) $fila) as $i => $celda)<td @class(['num' => in_array($i, $s['num'] ?? [])])>{{ $celda }}</td>@endforeach</tr>
            @empty
                <tr><td colspan="{{ count($s['head']) }}" class="muted">Sin datos</td></tr>
            @endforelse
            </tbody>
        </table>
    @endif
@endforeach
@endsection

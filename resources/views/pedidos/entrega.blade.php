@extends('layouts.app')

@section('contenido')
<form method="POST" action="{{ route('pedidos.entregar', $pedido) }}">
    @csrf
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="alert alert-light border"><i class="bi bi-info-circle me-1"></i>
                Indicá qué garrafas <b>llenas</b> salen del depósito y qué envases <b>vacíos</b> entrega {{ $pedido->cliente->nombreCompleto() }}.
                Se sugieren las llenas más antiguas y los envases que el cliente tiene registrados.</div>

            @forelse ($necesidades as $n)
                @php $cap = $n['capacidad']; @endphp
                <div class="card mb-3">
                    <div class="card-header"><h2><i class="bi bi-fuel-pump-fill text-brand me-1"></i> Garrafa {{ $cap }} kg · {{ $n['cantidad'] }} unidad(es)</h2></div>
                    <div class="card-body row g-3">
                        <div class="col-md-6">
                            <div class="section-title mt-0">Llenas que se entregan (elegir {{ $n['cantidad'] }})</div>
                            @if ($n['llenas']->count() < $n['cantidad'])
                                <div class="alert alert-danger py-2 small">Sólo hay {{ $n['llenas']->count() }} garrafas llenas de {{ $cap }} kg en depósito.</div>
                            @endif
                            <div class="border rounded p-2" style="max-height:240px;overflow:auto">
                                @forelse ($n['llenas'] as $i => $g)
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="entregadas[]" value="{{ $g->id }}" id="e{{ $g->id }}" @checked(in_array($g->id, old('entregadas', [])) || (! old('entregadas') && $i < $n['cantidad']))>
                                        <label class="form-check-label" for="e{{ $g->id }}">{{ $g->codigo }} <small class="text-body-secondary">· {{ $g->fecha_ultimo_movimiento?->diffForHumans() }}</small></label></div>
                                @empty
                                    <div class="text-body-secondary small">No hay garrafas llenas.</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="section-title mt-0">Envases vacíos que devuelve</div>
                            <div class="border rounded p-2 mb-2" style="max-height:170px;overflow:auto">
                                @forelse ($n['del_cliente'] as $i => $g)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check flex-fill"><input class="form-check-input" type="checkbox" name="devueltas[]" value="{{ $g->id }}" id="d{{ $g->id }}" @checked(in_array($g->id, old('devueltas', [])) || (! old('devueltas') && $i < $n['cantidad']))>
                                            <label class="form-check-label" for="d{{ $g->id }}">{{ $g->codigo }} <small class="text-body-secondary">· en su poder desde {{ fecha($g->fecha_ultimo_movimiento) }}</small></label></div>
                                        <div class="form-check form-check-inline m-0" title="Vuelve dañada o vencida"><input class="form-check-input" type="checkbox" name="no_aptas[]" value="{{ $g->id }}" id="na{{ $g->id }}"><label class="form-check-label small text-danger text-nowrap" for="na{{ $g->id }}">No apta</label></div>
                                    </div>
                                @empty
                                    <div class="text-body-secondary small">El cliente no tiene garrafas de {{ $cap }} kg registradas.</div>
                                @endforelse
                            </div>
                            <label class="form-label small" for="sr{{ $cap }}">Envases vacíos sin registrar que entrega</label>
                            <input type="number" min="0" max="50" class="form-control form-control-sm" name="sin_registrar[{{ $cap }}]" id="sr{{ $cap }}"
                                value="{{ old("sin_registrar.$cap", max(0, $n['cantidad'] - $n['del_cliente']->count())) }}">
                            <div class="form-text">Se dan de alta como vacías aptas con un código nuevo.</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card card-body mb-3 text-body-secondary"><i class="bi bi-info-circle"></i> Este pedido no incluye garrafas.</div>
            @endforelse

            @if ($otros->isNotEmpty())
                <div class="card"><div class="card-header"><h2>Otros productos</h2></div>
                    <ul class="list-group list-group-flush">
                        @foreach ($otros as $it)
                            <li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-{{ $it->producto->icono() }} me-2"></i>{{ num($it->cantidad) }}× {{ $it->producto->nombre }}</span>
                                <span @class(['text-danger fw-semibold' => $it->producto->stock_actual < $it->cantidad, 'text-body-secondary' => true])>stock {{ num($it->producto->stock_actual) }}</span></li>
                        @endforeach
                    </ul></div>
            @endif
        </div>
        <div class="col-xl-4">
            <div class="card summary-card"><div class="card-header"><h2>Confirmar entrega</h2></div><div class="card-body">
                <dl class="info-list mb-3"><dt>Pedido</dt><dd>{{ $pedido->numero }}</dd><dt>Cliente</dt><dd>{{ $pedido->cliente->nombreCompleto() }}</dd>
                    <dt>Total</dt><dd class="fw-bold">{{ pesos($pedido->total) }}</dd><dt>Saldo</dt><dd>{{ pesos($pedido->saldo) }}</dd></dl>
                @if ($pedido->saldo > 0)
                    <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="cobrar" value="1" id="cobrar" checked><label class="form-check-label" for="cobrar">Registrar el cobro a continuación</label></div>
                @endif
                <div class="d-grid gap-2">
                    <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Confirmar entrega</button>
                    <a href="{{ route('pedidos.show', $pedido) }}" class="btn btn-light">Volver</a>
                </div>
            </div></div>
        </div>
    </div>
</form>
@endsection

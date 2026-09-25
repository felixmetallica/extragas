@extends('layouts.app')

@section('contenido')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-body">
            <div class="cyl-head mb-3"><div class="cyl-icon"><i class="bi bi-fuel-pump-fill"></i></div>
                <div><div class="fw-bold fs-4">{{ $garrafa->codigo }}</div><div class="text-muted-sm">Garrafa de {{ $garrafa->capacidad_kg }} kg</div></div></div>
            <dl class="info-list">
                <dt>Estado</dt><dd>{{ badge_estado_garrafa($garrafa->estado) }} @unless ($garrafa->activo) {{ badge('Fuera del parque') }} @endunless</dd>
                <dt>Cliente</dt><dd>@if ($garrafa->cliente)<a href="{{ route('clientes.show', $garrafa->cliente) }}">{{ $garrafa->cliente->nombreCompleto() }}</a>@else — @endif</dd>
                <dt>Proveedor</dt><dd>{{ $garrafa->proveedor?->razon_social ?? '—' }}</dd>
                <dt>Alta</dt><dd>{{ fecha($garrafa->fecha_compra) }}</dd>
                <dt>Últ. movimiento</dt><dd>{{ fecha($garrafa->fecha_ultimo_movimiento, true) }}</dd>
                <dt>Obs.</dt><dd>{{ $garrafa->observaciones ?: '—' }}</dd>
            </dl>
        </div></div>

        @if ($posibles->isNotEmpty() || auth()->user()->esAdministrador())
            <form method="POST" action="{{ route('garrafas.movimiento', $garrafa) }}" class="card">
                @csrf
                <div class="card-header"><h2>Registrar movimiento</h2></div>
                <div class="card-body">
                    <label class="form-label" for="tipo">Movimiento</label>
                    <select class="form-select mb-2" name="tipo" id="tipo">
                        @foreach ($posibles as $codigo => $regla)<option value="{{ $codigo }}">{{ $tipos[$codigo]->nombre }} → {{ $estados[$regla['hacia']]->nombre }}</option>@endforeach
                        @can('administrar')<option value="AJUSTE">Ajuste de estado (administrador)</option>@endcan
                    </select>
                    @can('administrar')
                        <div id="ajusteWrap" class="d-none"><label class="form-label" for="estadoAjuste">Estado correcto</label>
                            <select class="form-select mb-2" name="estado_ajuste" id="estadoAjuste">@foreach (['LLENA', 'VACIA', 'NO_APTA'] as $c)<option value="{{ $c }}">{{ $estados[$c]->nombre }}</option>@endforeach</select></div>
                    @endcan
                    <label class="form-label" for="obs">Motivo / detalle</label>
                    <input class="form-control mb-3" name="observaciones" id="obs" placeholder="Ej.: válvula pierde, prueba hidráulica vencida">
                    <button class="btn btn-primary w-100">Registrar</button>
                </div>
            </form>
        @endif
    </div>
    <div class="col-lg-8">
        <div class="card"><div class="card-header"><h2>Historial de movimientos</h2></div>
            <div class="table-responsive"><table class="table align-middle">
                <thead><tr><th>Fecha</th><th>Movimiento</th><th>Estado</th><th>Detalle</th><th>Empleado</th></tr></thead>
                <tbody>
                @foreach ($garrafa->movimientos as $m)
                    <tr><td class="text-nowrap">{{ fecha($m->fecha, true) }}</td><td>{{ $m->tipo->nombre }}</td>
                        <td class="small text-nowrap">{{ $m->estadoOrigen?->nombre ?? '—' }} → <b>{{ $m->estadoDestino->nombre }}</b></td>
                        <td class="small">@if ($m->pedido)<a href="{{ route('pedidos.show', $m->pedido) }}">{{ $m->pedido->numero }}</a> · @endif
                            @if ($m->recepcion)<a href="{{ route('recepciones.show', $m->recepcion) }}">{{ $m->recepcion->numero }}</a> · @endif
                            {{ $m->cliente?->nombreCompleto() }} {{ $m->observaciones }}</td>
                        <td class="small">{{ $m->empleado?->nombreCompleto() ?? '—' }}</td></tr>
                @endforeach
                </tbody>
            </table></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('tipo')?.addEventListener('change', e => document.getElementById('ajusteWrap')?.classList.toggle('d-none', e.target.value !== 'AJUSTE'));
    if (document.getElementById('tipo')?.value === 'AJUSTE') document.getElementById('ajusteWrap')?.classList.remove('d-none');
</script>
@endpush

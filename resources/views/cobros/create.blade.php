@extends('layouts.app')

@section('contenido')
<form method="POST" action="{{ route('cobros.store') }}" class="card" style="max-width:720px" id="formCobro">
    @csrf
    <div class="card-body row g-3">
        <div class="col-12"><label class="form-label req" for="clienteSel">Cliente</label>
            <select class="form-select" name="cliente_id" id="clienteSel" required>
                <option value="">Seleccioná…</option>
                @foreach ($clientes as $c)<option value="{{ $c->id }}" data-saldo="{{ $saldos[$c->id] ?? 0 }}" @selected(old('cliente_id', $cliente?->id) == $c->id)>{{ $c->nombreCompleto() }} — saldo {{ pesos($saldos[$c->id] ?? 0) }}</option>@endforeach
            </select></div>
        <div class="col-12"><label class="form-label" for="pedidoSel">Pedido</label>
            <select class="form-select" name="pedido_id" id="pedidoSel"></select>
            <div class="form-text">"A cuenta" aplica el importe a los pedidos impagos más antiguos (se emite un recibo por pedido).</div></div>
        <div class="col-12"><div class="alert alert-light border py-2 mb-0 small" id="saldoInfo"></div></div>
        @include('partials.campo', ['name' => 'monto', 'label' => 'Importe', 'type' => 'number', 'req' => true, 'attrs' => 'min="1" step="0.01"'])
        @include('partials.campo', ['name' => 'fecha', 'label' => 'Fecha', 'type' => 'date', 'value' => today()->toDateString(), 'req' => true])
        @include('partials.campo', ['name' => 'forma_pago_id', 'label' => 'Forma de pago', 'type' => 'select', 'opciones' => $formasPago->pluck('nombre', 'id'), 'value' => $cliente?->forma_pago_habitual_id])
        @include('partials.campo', ['name' => 'referencia', 'label' => 'Referencia', 'placeholder' => 'N° de operación / comprobante'])
        @include('partials.campo', ['name' => 'observaciones', 'label' => 'Observaciones', 'col' => 'col-12'])
    </div>
    <div class="card-body border-top d-flex gap-2 justify-content-end">
        <a href="{{ url()->previous() }}" class="btn btn-light">Cancelar</a>
        <button class="btn btn-primary"><i class="bi bi-check2"></i> Registrar pago</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
(() => {
    const pendientes = @json($pendientes);
    const prePedido = {{ (int) old('pedido_id', $pedidoId) }};
    const cli = document.getElementById('clienteSel'), ped = document.getElementById('pedidoSel'), monto = document.getElementById('f_monto'), info = document.getElementById('saldoInfo');
    const pesos = n => '$ ' + Number(n).toLocaleString('es-AR');
    function cargarPedidos() {
        const lista = pendientes.filter(p => p.cliente_id === +cli.value);
        ped.innerHTML = '<option value="">A cuenta (pedidos más antiguos primero)</option>' + lista.map(p => `<option value="${p.id}" data-saldo="${p.saldo}" ${p.id === prePedido ? 'selected' : ''}>${p.texto}</option>`).join('');
        actualizar();
    }
    function actualizar() {
        const op = ped.selectedOptions[0];
        const saldo = op && op.value ? +op.dataset.saldo : +(cli.selectedOptions[0]?.dataset.saldo || 0);
        if (!monto.dataset.tocado) monto.value = saldo || '';
        info.innerHTML = cli.value ? `Saldo ${op && op.value ? 'del pedido' : 'total del cliente'}: <b>${pesos(saldo)}</b>` : 'Seleccioná un cliente.';
    }
    cli.addEventListener('change', cargarPedidos);
    ped.addEventListener('change', actualizar);
    monto.addEventListener('input', () => { monto.dataset.tocado = 1; });
    if (monto.value) monto.dataset.tocado = 1;
    cargarPedidos();
})();
</script>
@endpush

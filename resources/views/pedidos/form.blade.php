@extends('layouts.app')

@section('contenido')
@php
    $editando = $pedido->exists;
    $itemsIniciales = old('items', $pedido->itemsVenta?->map(fn ($i) => ['producto_id' => $i->producto_id, 'cantidad' => (float) $i->cantidad, 'precio_unitario' => (float) $i->precio_unitario])->values()->all() ?? []);
    $domicilio = $canales['DOMICILIO']->id;
@endphp
<form method="POST" action="{{ $editando ? route('pedidos.update', $pedido) : route('pedidos.store') }}" id="formPedido" novalidate>
    @csrf
    @if ($editando) @method('PUT') @endif
    <input type="hidden" name="cliente_id" id="clienteId" value="{{ old('cliente_id', $pedido->cliente_id) }}">
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-3">
                <div class="card-header"><h2>1. Cliente</h2>
                    @unless ($editando)<div class="ms-auto"><a href="{{ route('clientes.create', ['volver' => 'pedido']) }}" class="btn btn-sm btn-light"><i class="bi bi-person-plus"></i> Nuevo cliente</a></div>@endunless
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label req" for="cliente">Buscar cliente</label>
                            <input class="form-control" id="cliente" list="dlClientes" placeholder="Nombre, apellido o teléfono…" autocomplete="off" @disabled($editando)>
                            <datalist id="dlClientes">@foreach ($clientes as $c)<option value="{{ $c['label'] }}"></option>@endforeach</datalist>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">¿Cómo hizo el pedido?</label>
                            <div class="btn-group w-100 canal-options" role="group">
                                @foreach ($medios->except('OTRO') as $m)
                                    <input type="radio" class="btn-check" name="medio_contacto_id" id="medio{{ $m->id }}" value="{{ $m->id }}" data-codigo="{{ $m->codigo }}" @checked(old('medio_contacto_id', $pedido->medio_contacto_id) == $m->id)>
                                    <label class="btn btn-outline-secondary btn-sm" for="medio{{ $m->id }}">@include('partials.medio', ['medio' => $m])</label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div id="clienteInfo" class="mt-3"><div class="text-muted-sm"><i class="bi bi-info-circle"></i> Seleccioná un cliente para ver su domicilio, garrafas en su poder y saldo.</div></div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h2>2. Productos</h2><small class="text-body-secondary ms-2">Tocá un producto para agregarlo</small></div>
                <div class="card-body">
                    @foreach ($productos->groupBy('tipo') as $tipo => $lista)
                        <div class="section-title mt-0">{{ $tipo }}</div>
                        <div class="prod-grid mb-3">
                            @foreach ($lista as $p)
                                <button type="button" class="prod-btn {{ $p['stock'] <= 0 ? 'out' : '' }}" data-add="{{ $p['id'] }}">
                                    <i class="bi bi-{{ $p['icono'] }} pi text-brand"></i><b>{{ str_replace(' para hogar', '', $p['nombre']) }}</b><small>{{ pesos($p['precio']) }} · stock {{ num($p['stock']) }}</small>
                                </button>
                            @endforeach
                        </div>
                    @endforeach
                    <div class="table-responsive border rounded"><table class="table align-middle mb-0">
                        <thead><tr><th>Producto</th><th style="width:120px">Cantidad</th><th class="num" style="width:150px">Precio unit.</th><th class="num">Subtotal</th><th></th></tr></thead>
                        <tbody id="items"></tbody>
                    </table></div>
                    <div class="form-text"><i class="bi bi-info-circle"></i> Los envases (garrafas llenas que salen y vacías que se reciben) se registran al confirmar la entrega.</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h2>3. Entrega</h2></div>
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="canal">Tipo de entrega</label>
                        <select class="form-select" name="canal_venta_id" id="canal">
                            @foreach ($canales as $c)<option value="{{ $c->id }}" @selected(old('canal_venta_id', $pedido->canal_venta_id) == $c->id)>{{ $c->nombre }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label" for="fecha">Fecha y hora del pedido</label>
                        <input type="datetime-local" class="form-control" name="fecha" id="fecha" value="{{ old('fecha', $pedido->fecha?->format('Y-m-d\TH:i')) }}"></div>
                    <div class="col-12" id="dirWrap"><label class="form-label" for="direccion">Dirección de entrega</label>
                        <input class="form-control" name="direccion_entrega" id="direccion" value="{{ old('direccion_entrega', $pedido->direccion_entrega) }}" placeholder="Se completa con el domicilio del cliente"></div>
                    <div class="col-12"><label class="form-label" for="obs">Observaciones</label>
                        <textarea class="form-control" name="observaciones" id="obs" rows="2" placeholder="Ej.: tocar timbre, cambio de $20.000…">{{ old('observaciones', $pedido->observaciones) }}</textarea></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card summary-card">
                <div class="card-header"><h2>Resumen</h2></div>
                <div class="card-body">
                    <div id="resumenItems" class="small mb-3"></div>
                    <div class="d-flex justify-content-between align-items-center mb-2"><span class="text-body-secondary">Subtotal</span><span id="subtotal">$ 0</span></div>
                    <div class="d-flex justify-content-between align-items-center mb-2"><label class="text-body-secondary" for="descuento">Descuento</label>
                        <div class="input-group input-group-sm" style="width:140px"><span class="input-group-text">$</span><input type="number" min="0" step="1" class="form-control text-end" name="descuento" id="descuento" value="{{ old('descuento', (float) $pedido->descuento) }}"></div></div>
                    <div class="d-flex justify-content-between align-items-end border-top pt-3"><span class="text-body-secondary">Total</span><span class="summary-total" id="total">$ 0</span></div>

                    @unless ($editando)
                        <div class="section-title">Pago</div>
                        <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="registrar_pago" value="1" id="registrarPago" @checked(old('registrar_pago'))><label class="form-check-label" for="registrarPago">Registrar pago ahora</label></div>
                        <div id="pagoWrap" class="d-none">
                            <select class="form-select mb-2" name="forma_pago_id" id="formaPago" aria-label="Forma de pago">@foreach ($formasPago as $f)<option value="{{ $f->id }}" @selected(old('forma_pago_id') == $f->id)>{{ $f->nombre }}</option>@endforeach</select>
                            <div class="input-group mb-2"><span class="input-group-text">$</span><input type="number" min="0" step="1" class="form-control" name="pago_monto" id="pagoMonto" value="{{ old('pago_monto') }}" aria-label="Monto"></div>
                            <input class="form-control mb-2" name="pago_referencia" value="{{ old('pago_referencia') }}" placeholder="N° de operación (transferencia)">
                        </div>
                        <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="entregar_ahora" value="1" id="entregarAhora" @checked(old('entregar_ahora'))><label class="form-check-label" for="entregarAhora">Entregar en el momento</label></div>
                    @endunless
                    <div id="alertas"></div>
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> {{ $editando ? 'Guardar cambios' : 'Registrar pedido' }}</button>
                        @unless ($editando)<button class="btn btn-outline-primary" name="con_pdf" value="1"><i class="bi bi-file-earmark-pdf"></i> Registrar y generar PDF</button>@endunless
                        <a class="btn btn-light" href="{{ $editando ? route('pedidos.show', $pedido) : route('pedidos.index') }}">Cancelar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    window.PEDIDO = {
        clientes: @json($clientes),
        productos: @json($productos),
        items: @json($itemsIniciales),
        canalDomicilio: {{ $domicilio }},
        canalLocal: {{ $canales['MOSTRADOR']->id }},
        editando: {{ $editando ? 'true' : 'false' }},
    };
</script>
<script src="{{ asset('js/pedido-form.js') }}"></script>
@endpush

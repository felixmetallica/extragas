@extends('layouts.app')

@section('contenido')
<div class="toolbar">
    <div class="text-body-secondary small">La columna "Vendidos 30 d" muestra las unidades vendidas en los últimos 30 días.</div>
    @can('administrar')
        <div class="ms-auto d-flex gap-2">
            <a class="btn btn-sm btn-light" href="{{ route('productos.precios') }}"><i class="bi bi-percent"></i> Actualizar precios</a>
            <a class="btn btn-sm btn-primary" href="{{ route('productos.create') }}"><i class="bi bi-plus-lg"></i> Nuevo producto</a>
        </div>
    @endcan
</div>
@foreach ($tipos as $tipo)
    <div class="card mb-3">
        <div class="card-header"><h2><i class="bi bi-{{ ['GAS' => 'fuel-pump-fill', 'CARBON' => 'fire', 'LENA' => 'tree-fill'][$tipo->codigo] ?? 'box' }} me-1 text-brand"></i> {{ $tipo->nombre }}</h2>
            @if ($tipo->codigo === 'GAS')<div class="ms-auto"><a href="{{ route('garrafas.index') }}" class="btn btn-sm btn-light">Control de envases</a></div>@endif</div>
        <div class="table-responsive"><table class="table table-hover align-middle">
            <thead><tr><th>Producto</th><th class="num">Precio venta</th><th class="num">Costo</th><th class="num">Margen</th><th class="num">{{ $tipo->codigo === 'GAS' ? 'Llenas' : 'Stock' }}</th><th class="num">Mínimo</th><th class="num">Vendidos 30 d</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            @foreach ($productos[$tipo->id] ?? [] as $p)
                @php $s = $p->esGarrafa() ? ($stock[$p->capacidad()]['LLENA'] ?? 0) : $p->stock_actual; @endphp
                <tr @class(['opacity-50' => ! $p->activo])>
                    <td class="fw-semibold">{{ $p->nombre }}<div class="text-muted-sm">{{ $p->codigo }} · {{ num($p->capacidad_kg) }} kg · {{ strtolower($p->unidad_venta) }}</div></td>
                    <td class="num fw-semibold">{{ pesos($p->precio_actual) }}</td><td class="num">{{ pesos($p->costo_actual) }}</td>
                    <td class="num">{{ $p->precio_actual ? round(($p->precio_actual - $p->costo_actual) / $p->precio_actual * 100).'%' : '—' }}</td>
                    <td @class(['num', 'text-danger fw-bold' => $s <= $p->stock_minimo])>{{ num($s) }}</td><td class="num">{{ num($p->stock_minimo) }}</td><td class="num">{{ num($vendidos[$p->id] ?? 0) }}</td>
                    <td>{!! ! $p->activo ? badge('Inactivo') : ($s <= $p->stock_minimo ? badge('Stock bajo', 'b-impago') : badge('Disponible', 'b-pagado')) !!}</td>
                    <td class="actions">
                        @unless ($p->esGarrafa())
                            <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#stock{{ $p->id }}" title="Ajustar stock"><i class="bi bi-box-seam"></i></button>
                        @endunless
                        @can('administrar')<a class="btn btn-sm btn-light" href="{{ route('productos.edit', $p) }}" title="Editar"><i class="bi bi-pencil"></i></a>@endcan
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
    </div>
    @foreach (($productos[$tipo->id] ?? collect())->reject->esGarrafa() as $p)
                @unless ($p->esGarrafa())
                    <div class="modal fade" id="stock{{ $p->id }}" tabindex="-1"><div class="modal-dialog modal-sm"><form method="POST" action="{{ route('productos.stock', $p) }}" class="modal-content">@csrf
                        <div class="modal-header"><h5 class="modal-title">Ajustar stock</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                        <div class="modal-body"><p class="text-muted-sm">{{ $p->nombre }} · stock actual <b>{{ num($p->stock_actual) }}</b></p>
                            <label class="form-label">Tipo de ajuste</label><select class="form-select mb-2" name="tipo"><option value="fijar">Fijar cantidad contada</option><option value="baja">Baja por rotura / pérdida</option></select>
                            <label class="form-label">Cantidad</label><input type="number" min="0" step="1" class="form-control mb-2" name="cantidad" value="{{ num($p->stock_actual) }}" required>
                            <label class="form-label">Motivo</label><input class="form-control" name="motivo"></div>
                        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary">Guardar</button></div>
                    </form></div></div>
                @endunless
    @endforeach
@endforeach
@endsection

@extends('layouts.app')

@section('contenido')
<div class="row g-3">
    <div class="col-lg-3">
        <div class="card"><div class="card-body p-2"><div class="list-group report-nav">
            @foreach ($informes as $clave => [$icono, $nombre, $desc])
                <a href="{{ route('informes.show', ['tipo' => $clave, 'desde' => request('desde'), 'hasta' => request('hasta')]) }}" @class(['list-group-item list-group-item-action', 'active' => $clave === $tipo])>
                    <i class="bi bi-{{ $icono }} fs-5"></i><span><span class="d-block">{{ $nombre }}</span><small class="text-body-secondary fw-normal">{{ $desc }}</small></span></a>
            @endforeach
        </div></div></div>
    </div>
    <div class="col-lg-9">
        <form class="card mb-3" method="GET" data-auto-submit><div class="card-body d-flex flex-wrap gap-2 align-items-center py-2">
            <div><div class="fw-bold">{{ $informes[$tipo][1] }}</div><div class="text-muted-sm">{{ $informes[$tipo][2] }}</div></div>
            <div class="ms-auto d-flex flex-wrap gap-2 align-items-center">
                @if ($tipo !== 'garrafas') @include('partials.rango') @endif
                <a class="btn btn-sm btn-primary" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}"><i class="bi bi-file-earmark-pdf"></i> Descargar PDF</a>
            </div>
        </div></form>
        @yield('informe')
    </div>
</div>
@endsection

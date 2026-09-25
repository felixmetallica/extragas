<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titulo ?? 'Inicio' }} · {{ $empresa->nombre }}</title>
    <link rel="icon" href="{{ asset('img/logo.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
@php
    $menu = [
        'General' => [['inicio', 'speedometer2', 'Inicio', 'inicio']],
        'Ventas' => [
            ['pedidos.index', 'cart3', 'Pedidos', 'pedidos.*'],
            ['clientes.index', 'people', 'Clientes', 'clientes.*'],
            ['cobros.index', 'cash-coin', 'Cobros', 'cobros.*'],
        ],
        'Depósito' => [
            ['garrafas.index', 'fuel-pump', 'Garrafas', 'garrafas.*'],
            ['productos.index', 'box-seam', 'Productos y precios', 'productos.*'],
        ],
        'Compras' => [
            ['proveedores.index', 'truck', 'Proveedores', 'proveedores.*'],
            ['recepciones.index', 'box-arrow-in-down', 'Recepciones', 'recepciones.*'],
            ['pagos-proveedores.index', 'wallet2', 'Pagos a proveedores', 'pagos-proveedores.*'],
        ],
        'Análisis' => [['informes.show', 'bar-chart-line', 'Informes', 'informes.*']],
    ];
    if (auth()->user()->esAdministrador()) {
        $menu['Sistema'] = [
            ['usuarios.index', 'person-badge', 'Usuarios', 'usuarios.*'],
            ['empleados.index', 'person-vcard', 'Empleados', 'empleados.*'],
            ['configuracion.edit', 'gear', 'Configuración', 'configuracion.*'],
        ];
    }
@endphp
<div class="app">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="{{ route('inicio') }}">
            <img src="{{ asset('img/logo.svg') }}" alt="" width="34" height="34">
            <span><strong>{{ $empresa->nombre }}</strong><small>Gestión de Pedidos</small></span>
        </a>
        <nav class="nav-menu">
            @foreach ($menu as $grupo => $items)
                <div class="nav-group">{{ $grupo }}</div>
                @foreach ($items as [$ruta, $icono, $texto, $patron])
                    <a href="{{ $ruta === 'informes.show' ? route($ruta, 'pedidos') : route($ruta) }}" @class(['active' => request()->routeIs($patron)])>
                        <i class="bi bi-{{ $icono }}"></i> {{ $texto }}
                        @if ($ruta === 'pedidos.index' && $pedidosEnCurso)
                            <span class="badge rounded-pill ms-auto">{{ $pedidosEnCurso }}</span>
                        @endif
                    </a>
                @endforeach
            @endforeach
        </nav>
        <div class="sidebar-foot"><i class="bi bi-info-circle"></i> La facturación se realiza en la web de ARCA.</div>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="main">
        <header class="topbar">
            <button class="btn btn-icon d-lg-none" id="btnMenu" aria-label="Abrir menú"><i class="bi bi-list"></i></button>
            <div class="topbar-title">
                <h1>{{ $titulo ?? 'Inicio' }}</h1>
                <div class="crumb">
                    @foreach ($migas ?? [] as $texto => $url)
                        @if (! $loop->first) <i class="bi bi-chevron-right small"></i> @endif
                        @if ($url) <a href="{{ $url }}">{{ $texto }}</a> @else {{ $texto }} @endif
                    @endforeach
                </div>
            </div>
            <div class="topbar-actions">
                <a href="{{ route('pedidos.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i><span class="d-none d-sm-inline"> Nuevo pedido</span></a>
                <div class="dropdown">
                    <button class="btn user-btn" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar">{{ auth()->user()->iniciales() }}</span>
                        <span class="d-none d-md-inline text-start lh-sm">
                            <span class="d-block fw-semibold">{{ auth()->user()->nombreVisible() }}</span>
                            <small class="text-body-secondary">{{ auth()->user()->rol->nombre }}</small>
                        </span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</button></form></li>
                    </ul>
                </div>
            </div>
        </header>
        <main class="content">
            @if (session('ok'))
                <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>{{ session('ok') }}
                    @if (session('pdf')) <a href="{{ session('pdf') }}" class="alert-link ms-2" target="_blank"><i class="bi bi-file-earmark-pdf"></i> Descargar comprobante</a> @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i>
                    @if ($errors->count() === 1) {{ $errors->first() }} @else
                        Revisá los datos ingresados:<ul class="mb-0 mt-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @yield('contenido')
        </main>
    </div>
</div>

<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>

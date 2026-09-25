<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar · {{ $empresa->nombre }}</title>
    <link rel="icon" href="{{ asset('img/logo.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="login-wrap">
    <section class="login-art">
        <div class="d-flex align-items-center gap-2"><img src="{{ asset('img/logo.svg') }}" width="40" height="40" alt=""><span class="fs-4 fw-bold">{{ $empresa->nombre }}</span></div>
        <div>
            <h2>Pedidos, garrafas y cobros en un solo lugar.</h2>
            <ul class="mt-4">
                <li><i class="bi bi-check-circle-fill"></i>Pedidos por teléfono, WhatsApp o en el local</li>
                <li><i class="bi bi-check-circle-fill"></i>Control de cada garrafa: llena, vacía o en clientes</li>
                <li><i class="bi bi-check-circle-fill"></i>Cobros en efectivo y transferencia con recibo PDF</li>
                <li><i class="bi bi-check-circle-fill"></i>Proveedores, recepciones e informes</li>
            </ul>
        </div>
        <small class="text-white-50">Venta de gas envasado, carbón y leña para hogares</small>
    </section>
    <section class="login-form">
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="d-md-none d-flex align-items-center gap-2 mb-4"><img src="{{ asset('img/logo.svg') }}" width="36" height="36" alt=""><span class="fs-4 fw-bold">{{ $empresa->nombre }}</span></div>
            <h1 class="h3 fw-bold mb-1">Ingresar</h1>
            <p class="text-body-secondary mb-4">Accedé con tu usuario del sistema.</p>
            @error('username') <div class="alert alert-danger py-2 small">{{ $message }}</div> @enderror
            <div class="mb-3"><label class="form-label" for="username">Usuario</label>
                <div class="input-group"><span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input class="form-control" id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"></div></div>
            <div class="mb-4"><label class="form-label" for="password">Contraseña</label>
                <div class="input-group"><span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password"></div></div>
            <button class="btn btn-primary w-100 py-2">Ingresar</button>
        </form>
    </section>
</div>
</body>
</html>

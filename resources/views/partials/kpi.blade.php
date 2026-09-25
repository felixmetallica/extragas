<div class="card kpi">
    <div class="kpi-icon {{ $color ?? 'i-orange' }}"><i class="bi bi-{{ $icono }}"></i></div>
    <div><div class="kpi-label">{{ $label }}</div><div class="kpi-value">{{ $valor }}</div>@isset($sub)<div class="kpi-sub">{{ $sub }}</div>@endisset</div>
</div>

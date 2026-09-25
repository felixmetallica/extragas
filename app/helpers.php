<?php

use App\Models\Catalogos\EstadoPedido;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

/*
 * Funciones de formato usadas en vistas y PDF.
 */

if (! function_exists('pesos')) {
    function pesos(float|int|string|null $monto): string
    {
        $monto = (float) $monto;
        $decimales = fmod($monto, 1.0) == 0.0 ? 0 : 2;

        return '$ '.number_format($monto, $decimales, ',', '.');
    }
}

if (! function_exists('num')) {
    function num(float|int|string|null $n): string
    {
        $n = (float) $n;

        return number_format($n, fmod($n, 1.0) == 0.0 ? 0 : 2, ',', '.');
    }
}

if (! function_exists('fecha')) {
    function fecha($f, bool $hora = false): string
    {
        if (! $f) {
            return '—';
        }
        $f = $f instanceof Carbon ? $f : Carbon::parse($f);

        return $f->format($hora ? 'd/m/Y H:i' : 'd/m/Y');
    }
}

if (! function_exists('wa_link')) {
    function wa_link(?string $telefono): string
    {
        $n = preg_replace('/\D/', '', (string) $telefono);

        return $n ? "https://wa.me/549{$n}" : '#';
    }
}

if (! function_exists('badge')) {
    function badge(string $texto, string $clase = 'b-gray'): HtmlString
    {
        return new HtmlString('<span class="badge-soft '.e($clase).'">'.e($texto).'</span>');
    }
}

if (! function_exists('badge_estado_pedido')) {
    function badge_estado_pedido(?EstadoPedido $estado): HtmlString
    {
        $clase = [
            EstadoPedido::PENDIENTE => 'b-pendiente', EstadoPedido::EN_PREPARACION => 'b-preparacion',
            EstadoPedido::EN_REPARTO => 'b-reparto', EstadoPedido::ENTREGADO => 'b-entregado', EstadoPedido::CANCELADO => 'b-cancelado',
        ][$estado?->codigo] ?? 'b-gray';

        return badge($estado?->nombre ?? '—', $clase);
    }
}

if (! function_exists('badge_pago')) {
    function badge_pago(string $estado): HtmlString
    {
        $clase = ['Pagado' => 'b-pagado', 'Parcial' => 'b-parcial', 'Pendiente' => 'b-impago'][$estado] ?? null;

        return $clase ? badge($estado, $clase) : new HtmlString('<span class="text-body-tertiary">—</span>');
    }
}

if (! function_exists('badge_regularidad')) {
    function badge_regularidad(string $estado): HtmlString
    {
        return badge($estado, ['Al día' => 'b-pagado', 'Por pedir' => 'b-parcial', 'Atrasado' => 'b-impago'][$estado] ?? 'b-gray');
    }
}

if (! function_exists('badge_estado_garrafa')) {
    function badge_estado_garrafa($estado): HtmlString
    {
        $color = $estado?->color ?? '#868e96';

        return new HtmlString('<span class="badge-soft" style="background:'.e($color).'22;color:'.e($color).'">'.e($estado?->nombre ?? '—').'</span>');
    }
}

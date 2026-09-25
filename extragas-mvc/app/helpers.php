<?php
/*
 * Funciones auxiliares usadas por controladores y vistas.
 */

use App\Core\Config;
use App\Core\Sesion;

/** Escapa texto para HTML */
function e(mixed $texto): string
{
    return htmlspecialchars((string) ($texto ?? ''), ENT_QUOTES, 'UTF-8');
}

/** URL interna: url('pedidos/5', ['pdf' => 1]) */
function url(string $ruta = '', array $query = []): string
{
    $base = Config::base().(Config::get('app.url_amigables') ? '' : '/index.php');
    $query = array_filter($query, fn ($v) => $v !== null && $v !== '');

    return $base.'/'.ltrim($ruta, '/').($query ? '?'.http_build_query($query) : '');
}

function asset(string $ruta): string
{
    return Config::base().'/assets/'.ltrim($ruta, '/');
}

/** URL actual cambiando algunos parámetros de la consulta */
function url_actual(array $cambios = []): string
{
    $ruta = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');

    return $ruta.'?'.http_build_query(array_merge($_GET, $cambios));
}

function redirigir(string $ruta, array $query = []): never
{
    header('Location: '.(str_starts_with($ruta, 'http') || str_starts_with($ruta, '/') ? $ruta : url($ruta, $query)));
    exit;
}

/** Vuelve a la página anterior (formularios con error) */
function volver(): never
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    redirigir($ref && parse_url($ref, PHP_URL_HOST) === explode(':', $host)[0] ? $ref : url(''));
}

function csrf_campo(): string
{
    return '<input type="hidden" name="_token" value="'.e(Sesion::token()).'">';
}

/** Valor anterior de un campo (tras un error) o el valor por defecto */
function viejo(string $campo, mixed $defecto = null): mixed
{
    $entrada = Sesion::leer('_entrada', []);

    return array_key_exists($campo, $entrada) ? $entrada[$campo] : $defecto;
}

function hay_entrada_vieja(): bool
{
    return (bool) Sesion::leer('_entrada');
}

function error_de(string $campo): ?string
{
    return Sesion::leer('errores', [])[$campo] ?? null;
}

function sel(mixed $a, mixed $b): string
{
    return (string) $a === (string) $b ? 'selected' : '';
}

function chk(mixed $condicion): string
{
    return $condicion ? 'checked' : '';
}

function hoy(): string
{
    return date('Y-m-d');
}

function dias_entre(string $desde, string $hasta): int
{
    return (int) round((strtotime(substr($hasta, 0, 10)) - strtotime(substr($desde, 0, 10))) / 86400);
}

/* ---------- Formato ---------- */

function pesos(mixed $monto): string
{
    $monto = (float) $monto;

    return '$ '.number_format($monto, fmod($monto, 1.0) == 0.0 ? 0 : 2, ',', '.');
}

function num(mixed $n): string
{
    $n = (float) $n;

    return number_format($n, fmod($n, 1.0) == 0.0 ? 0 : 2, ',', '.');
}

function fecha(?string $f, bool $hora = false): string
{
    if (! $f) {
        return '—';
    }

    return date($hora ? 'd/m/Y H:i' : 'd/m/Y', strtotime($f));
}

function nombre(array $r, string $prefijo = ''): string
{
    return trim(($r[$prefijo.'nombre'] ?? '').' '.($r[$prefijo.'apellido'] ?? ''));
}

function domicilio(array $r): string
{
    $d = trim(($r['calle'] ?? '').' '.($r['numero'] ?? ''));
    if (! empty($r['piso']) || ! empty($r['depto'])) {
        $d .= ' '.trim((! empty($r['piso']) ? "Piso {$r['piso']} " : '').(! empty($r['depto']) ? "Dto. {$r['depto']}" : ''));
    }

    return implode(', ', array_filter([$d, $r['ciudad'] ?? null]));
}

function wa_link(?string $telefono): string
{
    $n = preg_replace('/\D/', '', (string) $telefono);

    return $n ? "https://wa.me/549{$n}" : '#';
}

/* ---------- Etiquetas de estado ---------- */

function badge(string $texto, string $clase = 'b-gray'): string
{
    return '<span class="badge-soft '.e($clase).'">'.e($texto).'</span>';
}

function badge_estado_pedido(string $codigo, string $nombre): string
{
    $clase = ['PENDIENTE' => 'b-pendiente', 'EN_PREPARACION' => 'b-preparacion', 'EN_REPARTO' => 'b-reparto', 'ENTREGADO' => 'b-entregado', 'CANCELADO' => 'b-cancelado'][$codigo] ?? 'b-gray';

    return badge($nombre, $clase);
}

function estado_pago(array $p): string
{
    if (($p['estado_codigo'] ?? '') === 'CANCELADO') {
        return '—';
    }
    if ((float) $p['saldo'] <= 0) {
        return 'Pagado';
    }

    return (float) $p['monto_pagado'] > 0 ? 'Parcial' : 'Pendiente';
}

function badge_pago(string $estado): string
{
    $clase = ['Pagado' => 'b-pagado', 'Parcial' => 'b-parcial', 'Pendiente' => 'b-impago'][$estado] ?? null;

    return $clase ? badge($estado, $clase) : '<span class="text-body-tertiary">—</span>';
}

function badge_regularidad(string $estado): string
{
    return badge($estado, ['Al día' => 'b-pagado', 'Por pedir' => 'b-parcial', 'Atrasado' => 'b-impago'][$estado] ?? 'b-gray');
}

function badge_color(string $texto, ?string $color): string
{
    $color = $color ?: '#868e96';

    return '<span class="badge-soft" style="background:'.e($color).'22;color:'.e($color).'">'.e($texto).'</span>';
}

function icono_medio(?string $codigo): string
{
    return ['TELEFONO' => 'telephone', 'WHATSAPP' => 'whatsapp', 'PRESENCIAL' => 'shop'][$codigo] ?? 'chat-dots';
}

function medio(?string $codigo, ?string $nombre): string
{
    return $codigo ? '<span class="canal"><i class="bi bi-'.icono_medio($codigo).'"></i>'.e($nombre).'</span>' : '<span class="text-body-tertiary">—</span>';
}

function icono_producto(?string $tipo): string
{
    return ['GAS' => 'fuel-pump-fill', 'CARBON' => 'fire'][$tipo] ?? 'tree-fill';
}

/** JSON seguro para atributos HTML y bloques <script> */
function json(mixed $datos): string
{
    return json_encode($datos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
}

/** "jueves 24 de septiembre" */
function fecha_larga(string $f): string
{
    $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $t = strtotime($f);

    return $dias[(int) date('w', $t)].' '.date('j', $t).' de '.$meses[(int) date('n', $t) - 1];
}

function mes_anio(?string $f = null): string
{
    $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $t = strtotime($f ?? 'now');

    return $meses[(int) date('n', $t) - 1].' '.date('Y', $t);
}

/** ¿La ruta actual pertenece a esta sección del menú? */
function seccion_activa(string $prefijo): bool
{
    $ruta = App\Core\App::rutaActual();

    return $prefijo === '' ? $ruta === '' : ($ruta === $prefijo || str_starts_with($ruta, $prefijo.'/'));
}

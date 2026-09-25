<?php

namespace App\Core;

/**
 * Despachador: resuelve la ruta, controla sesión / permisos / CSRF y
 * ejecuta la acción del controlador.
 */
final class App
{
    public static function ejecutar(array $rutas): void
    {
        Sesion::rotar();
        $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $ruta = self::rutaActual();

        try {
            [$destino, $params, $opciones] = self::resolver($rutas, $metodo, $ruta);

            if (! in_array('publica', $opciones, true) && ! Auth::usuario()) {
                redirigir('login');
            }
            if (in_array('admin', $opciones, true) && ! Auth::esAdmin()) {
                throw new ErrorHttp('No tenés permiso para acceder a esta sección.', 403);
            }
            if ($metodo === 'POST' && ! Sesion::tokenValido($_POST['_token'] ?? null)) {
                throw new ErrorNegocio('La sesión expiró. Volvé a intentarlo.');
            }

            [$clase, $accion] = $destino;
            echo (new $clase())->$accion(...$params);
        } catch (ErrorValidacion $e) {
            Sesion::guardarEntrada($_POST);
            Sesion::flash('errores', $e->errores);
            volver();
        } catch (ErrorNegocio $e) {
            Sesion::guardarEntrada($_POST);
            Sesion::flash('errores', ['general' => $e->getMessage()]);
            volver();
        } catch (ErrorHttp $e) {
            http_response_code($e->getCode() ?: 404);
            echo Vista::render('errores/http', ['titulo' => 'Error '.($e->getCode() ?: 404), 'mensaje' => $e->getMessage()], Auth::usuario() ? 'layouts/app' : null);
        }
    }

    public static function rutaActual(): string
    {
        $uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
        $base = Config::base();
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        if (str_starts_with($uri, '/index.php')) {
            $uri = substr($uri, strlen('/index.php'));
        }

        return trim($uri, '/');
    }

    private static function resolver(array $rutas, string $metodo, string $ruta): array
    {
        foreach ($rutas as $r) {
            [$m, $patron, $destino] = $r;
            $regex = '#^'.preg_replace(['#\{id\}#', '#\{contacto\}#', '#\{tipo\}#'], ['(\d+)', '(\d+)', '([a-z-]+)'], $patron).'$#';
            if ($m === $metodo && preg_match($regex, $ruta, $coincidencias)) {
                array_shift($coincidencias);

                return [$destino, array_map(fn ($v) => ctype_digit($v) ? (int) $v : $v, $coincidencias), $r[3] ?? []];
            }
        }

        throw new ErrorHttp('La página solicitada no existe.', 404);
    }
}

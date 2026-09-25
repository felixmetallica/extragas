<?php

namespace App\Core;

/**
 * Motor de vistas: archivos PHP en app/Views. La vista se renderiza dentro
 * de un layout y puede definir secciones (por ejemplo, scripts).
 */
final class Vista
{
    private static array $secciones = [];

    private static array $pila = [];

    public static function render(string $vista, array $datos = [], ?string $layout = 'layouts/app'): string
    {
        $contenido = self::archivo($vista, $datos);

        return $layout ? self::archivo($layout, $datos + ['contenido' => $contenido]) : $contenido;
    }

    public static function archivo(string $vista, array $datos = []): string
    {
        $__ruta = RAIZ.'/app/Views/'.$vista.'.php';
        if (! is_file($__ruta)) {
            throw new \RuntimeException("Vista inexistente: {$vista}");
        }
        extract($datos, EXTR_SKIP);
        ob_start();
        try {
            include $__ruta;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean();
    }

    /** Incluye un parcial dentro de otra vista */
    public static function parcial(string $vista, array $datos = []): void
    {
        echo self::archivo('partials/'.$vista, $datos);
    }

    public static function inicio(string $seccion): void
    {
        self::$pila[] = $seccion;
        ob_start();
    }

    public static function fin(): void
    {
        $seccion = array_pop(self::$pila);
        self::$secciones[$seccion] = (self::$secciones[$seccion] ?? '').ob_get_clean();
    }

    public static function seccion(string $seccion): string
    {
        return self::$secciones[$seccion] ?? '';
    }
}

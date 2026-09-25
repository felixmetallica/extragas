<?php

namespace App\Core;

/** Base de los controladores */
abstract class Controlador
{
    protected function vista(string $vista, array $datos = []): string
    {
        return Vista::render($vista, $datos);
    }

    protected function validar(array $reglas, array $nombres = []): array
    {
        return Validador::validar($_POST, $reglas, $nombres);
    }

    /** Redirige con un mensaje de éxito (y opcionalmente un enlace a un PDF) */
    protected function exito(string $ruta, string $mensaje, ?string $pdf = null): never
    {
        Sesion::flash('ok', $mensaje);
        if ($pdf) {
            Sesion::flash('pdf', $pdf);
        }
        redirigir($ruta);
    }

    protected function noEncontrado(?array $registro): array
    {
        return $registro ?? throw new ErrorHttp('El registro solicitado no existe.', 404);
    }

    /** Rango de fechas de los filtros (por defecto, los últimos $dias días) */
    protected function rango(int $dias = 30): array
    {
        $hasta = ! empty($_GET['hasta']) && strtotime($_GET['hasta']) ? date('Y-m-d', strtotime($_GET['hasta'])) : date('Y-m-d');
        $desde = ! empty($_GET['desde']) && strtotime($_GET['desde']) ? date('Y-m-d', strtotime($_GET['desde'])) : date('Y-m-d', strtotime("-".($dias - 1).' days'));

        return [$desde, $hasta];
    }

    protected function quierePdf(): bool
    {
        return ! empty($_GET['pdf']);
    }
}

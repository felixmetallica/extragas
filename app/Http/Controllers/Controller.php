<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

abstract class Controller
{
    /** Empleado asociado al usuario logueado (pedidos, recepciones y movimientos lo requieren) */
    protected function empleadoActual(): Empleado
    {
        return auth()->user()->empleado
            ?? throw new \DomainException('Tu usuario no tiene un empleado asociado. Pedile al administrador que lo vincule en Sistema › Empleados.');
    }

    /** Rango de fechas de los filtros (por defecto, últimos 30 días) */
    protected function rango(Request $request, int $dias = 30): array
    {
        $hasta = $request->date('hasta') ?? today();
        $desde = $request->date('desde') ?? today()->subDays($dias - 1);

        return [$desde->startOfDay(), Carbon::parse($hasta)->endOfDay()];
    }
}

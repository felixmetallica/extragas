<?php

namespace App\Controllers;

use App\Core\Controlador;
use App\Core\DB;
use App\Models\Catalogo;
use App\Models\Configuracion;

class ConfiguracionController extends Controlador
{
    public function editar(): string
    {
        return $this->vista('configuracion/editar', [
            'titulo' => 'Configuración', 'migas' => ['Sistema' => null, 'Configuración' => null],
            'config' => Configuracion::empresa(), 'formasPago' => Catalogo::todos('formas_pago'),
        ]);
    }

    public function actualizar(): never
    {
        $d = $this->validar([
            'nombre' => 'requerido|texto:150', 'razon_social' => 'texto:150', 'cuit' => 'texto:15', 'direccion' => 'texto:150', 'localidad' => 'texto:100',
            'telefono' => 'texto:25', 'whatsapp' => 'texto:25', 'email' => 'email|texto:150', 'horario' => 'texto:150',
            'dias_tolerancia_regularidad' => 'requerido|entero|min:0|max:60',
        ], ['dias_tolerancia_regularidad' => 'tolerancia']);
        Configuracion::guardar($d);
        $activas = array_map('intval', (array) ($_POST['formas_activas'] ?? []));
        $params = [];
        DB::ejecutar('UPDATE formas_pago SET activo = 0');
        if ($activas) {
            DB::ejecutar('UPDATE formas_pago SET activo = 1 WHERE id IN ('.DB::lista($activas, $params).')', $params);
        }
        Catalogo::limpiar();
        $this->exito('configuracion', 'Configuración guardada.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Catalogos\FormaPago;
use App\Models\ConfiguracionEmpresa;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function edit()
    {
        return view('configuracion.edit', [
            'titulo' => 'Configuración', 'migas' => ['Sistema' => null, 'Configuración' => null],
            'config' => ConfiguracionEmpresa::actual(), 'formasPago' => FormaPago::todos(),
        ]);
    }

    public function update(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:150', 'razon_social' => 'nullable|string|max:150', 'cuit' => 'nullable|string|max:15',
            'direccion' => 'nullable|string|max:150', 'localidad' => 'nullable|string|max:100', 'telefono' => 'nullable|string|max:25',
            'whatsapp' => 'nullable|string|max:25', 'email' => 'nullable|email|max:150', 'horario' => 'nullable|string|max:150',
            'dias_tolerancia_regularidad' => 'required|integer|min:0|max:60',
            'formas_activas' => 'array', 'formas_activas.*' => 'integer',
        ]);
        ConfiguracionEmpresa::query()->updateOrCreate([], collect($datos)->except('formas_activas')->all());
        ConfiguracionEmpresa::olvidar();
        FormaPago::query()->update(['activo' => false]);
        FormaPago::whereIn('id', $datos['formas_activas'] ?? [])->update(['activo' => true]);
        FormaPago::limpiarCache();

        return back()->with('ok', 'Configuración guardada.');
    }
}

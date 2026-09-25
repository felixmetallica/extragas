<?php

namespace App\Http\Controllers;

use App\Models\Catalogos\Provincia;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $proveedores = Proveedor::with('provincia')
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w->where('razon_social', 'like', "%{$v}%")->orWhere('nombre_fantasia', 'like', "%{$v}%")
                ->orWhere('cuit', 'like', "%{$v}%")->orWhere('contacto_nombre', 'like', "%{$v}%")))
            ->withMax('recepciones', 'fecha')->orderByDesc('activo')->orderBy('razon_social')->paginate(15)->withQueryString();

        return view('proveedores.index', [
            'titulo' => 'Proveedores', 'migas' => ['Compras' => null, 'Proveedores' => null],
            'proveedores' => $proveedores, 'saldos' => DB::table('v_saldo_proveedores')->pluck('saldo_total', 'proveedor_id'),
        ]);
    }

    public function create()
    {
        return $this->formulario(new Proveedor(['activo' => true, 'provincia_id' => Provincia::todos()['TUC']->id ?? null]));
    }

    public function edit(Proveedor $proveedor)
    {
        return $this->formulario($proveedor);
    }

    private function formulario(Proveedor $proveedor)
    {
        return view('proveedores.form', [
            'titulo' => $proveedor->exists ? 'Editar proveedor' : 'Nuevo proveedor',
            'migas' => ['Compras' => null, 'Proveedores' => route('proveedores.index'), ($proveedor->exists ? $proveedor->razon_social : 'Nuevo') => null],
            'proveedor' => $proveedor, 'provincias' => Provincia::todos()->sortBy('nombre'),
        ]);
    }

    private function validar(Request $request, ?Proveedor $proveedor = null): array
    {
        return $request->validate([
            'codigo' => 'nullable|string|max:20',
            'razon_social' => 'required|string|max:150',
            'nombre_fantasia' => 'nullable|string|max:150',
            'cuit' => ['required', 'string', 'max:15', Rule::unique('proveedores', 'cuit')->ignore($proveedor)],
            'telefono_principal' => 'nullable|string|max:25',
            'telefono_secundario' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:150',
            'calle' => 'nullable|string|max:150', 'numero' => 'nullable|string|max:10', 'piso' => 'nullable|string|max:10', 'depto' => 'nullable|string|max:10',
            'ciudad' => 'nullable|string|max:100', 'codigo_postal' => 'nullable|string|max:10', 'provincia_id' => 'nullable|exists:provincias,id',
            'referencias' => 'nullable|string|max:1000',
            'contacto_nombre' => 'nullable|string|max:150', 'contacto_telefono' => 'nullable|string|max:25', 'contacto_email' => 'nullable|email|max:150',
            'observaciones' => 'nullable|string|max:2000',
        ], ['cuit.unique' => 'Ya existe un proveedor con ese CUIT.']);
    }

    public function store(Request $request)
    {
        $proveedor = Proveedor::create($this->validar($request) + ['activo' => true]);

        return redirect()->route('proveedores.show', $proveedor)->with('ok', 'Proveedor registrado.');
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $proveedor->update($this->validar($request, $proveedor) + ['activo' => $request->boolean('activo')]);

        return redirect()->route('proveedores.show', $proveedor)->with('ok', 'Proveedor actualizado.');
    }

    public function destroy(Proveedor $proveedor)
    {
        if ($proveedor->recepciones()->exists()) {
            throw new \DomainException('El proveedor tiene recepciones registradas. Podés marcarlo como inactivo.');
        }
        $proveedor->delete();

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor eliminado.');
    }

    public function show(Proveedor $proveedor)
    {
        $proveedor->load('provincia');

        return view('proveedores.show', [
            'titulo' => $proveedor->razon_social, 'migas' => ['Compras' => null, 'Proveedores' => route('proveedores.index'), 'Ficha' => null],
            'proveedor' => $proveedor,
            'recepciones' => $proveedor->recepciones()->with('items.producto')->orderByDesc('fecha')->paginate(10, ['*'], 'pagina_rec'),
            'pagos' => $proveedor->pagos()->with(['recepcion', 'formaPago'])->orderByDesc('fecha')->paginate(10, ['*'], 'pagina_pag'),
            'saldo' => $proveedor->saldo(),
            'comprado90' => $proveedor->recepciones()->where('fecha', '>=', today()->subDays(89))->sum('total'),
            'pagadoTotal' => $proveedor->pagos()->sum('monto'),
        ]);
    }
}

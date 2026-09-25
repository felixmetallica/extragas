<?php

namespace App\Http\Controllers;

use App\Models\Catalogos\FormaPago;
use App\Models\Catalogos\Provincia;
use App\Models\Catalogos\TipoContactoCliente;
use App\Models\Cliente;
use App\Models\ClienteContacto;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $filtro = $request->input('filtro');
        $clientes = Cliente::with('formaPagoHabitual')
            ->when($filtro === 'inactivo', fn ($q) => $q->where('activo', false), fn ($q) => $q->where('activo', true))
            ->when($request->forma_pago, fn ($q, $v) => $q->where('forma_pago_habitual_id', $v))
            ->buscar($request->q)
            ->withCount('garrafas')
            ->orderBy('apellido')->orderBy('nombre')->get();

        $saldos = DB::table('v_saldo_clientes')->pluck('saldo_total', 'cliente_id');
        $regularidad = Cliente::regularidadDe($clientes->pluck('id'));
        $clientes = $clientes->filter(fn ($c) => match ($filtro) {
            'deuda' => ($saldos[$c->id] ?? 0) > 0,
            'atrasado' => $regularidad[$c->id]['estado'] === 'Atrasado',
            'envases' => $c->garrafas_count > 0,
            default => true,
        })->values();

        if ($request->boolean('pdf')) {
            return PedidoController::pdfListado('Listado de clientes', $clientes->count().' clientes', [[
                'head' => ['Cliente', 'Domicilio', 'Teléfono', 'Pago habitual', 'Garrafas', 'Último pedido', 'Frecuencia', 'Saldo'],
                'body' => $clientes->map(fn ($c) => [$c->nombreCompleto(), $c->domicilioCompleto(), $c->telefono_principal, $c->formaPagoHabitual?->nombre, $c->garrafas_count,
                    fecha($regularidad[$c->id]['ultimo']), $regularidad[$c->id]['promedio'] ? $regularidad[$c->id]['promedio'].' días' : '—', pesos($saldos[$c->id] ?? 0)]),
                'num' => [4, 7],
            ]], 'clientes', 'landscape');
        }

        $pagina = LengthAwarePaginator::resolveCurrentPage();
        $paginados = new LengthAwarePaginator($clientes->forPage($pagina, 15), $clientes->count(), 15, $pagina, ['path' => $request->url(), 'query' => $request->query()]);

        return view('clientes.index', [
            'titulo' => 'Clientes', 'migas' => ['Ventas' => null, 'Clientes' => null],
            'clientes' => $paginados, 'saldos' => $saldos, 'regularidad' => $regularidad, 'formasPago' => FormaPago::todos(),
        ]);
    }

    public function create()
    {
        return $this->formulario(new Cliente(['activo' => true, 'provincia_id' => Provincia::todos()['TUC']->id ?? null, 'forma_pago_habitual_id' => FormaPago::idDe(FormaPago::EFECTIVO)]));
    }

    public function edit(Cliente $cliente)
    {
        return $this->formulario($cliente);
    }

    private function formulario(Cliente $cliente)
    {
        return view('clientes.form', [
            'titulo' => $cliente->exists ? 'Editar cliente' : 'Nuevo cliente',
            'migas' => ['Ventas' => null, 'Clientes' => route('clientes.index'), ($cliente->exists ? $cliente->nombreCompleto() : 'Nuevo') => null],
            'cliente' => $cliente, 'provincias' => Provincia::todos()->sortBy('nombre'), 'formasPago' => FormaPago::todos()->where('activo', true),
        ]);
    }

    private function validar(Request $request, ?Cliente $cliente = null): array
    {
        return $request->validate([
            'codigo' => ['nullable', 'string', 'max:20', Rule::unique('clientes', 'codigo')->ignore($cliente)->whereNull('deleted_at')],
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'dni' => 'nullable|string|max:15',
            'cuit_cuil' => 'nullable|string|max:15',
            'telefono_principal' => ['required', 'string', 'max:25', Rule::unique('clientes', 'telefono_principal')->ignore($cliente)->whereNull('deleted_at')],
            'telefono_secundario' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:150',
            'calle' => 'nullable|string|max:150',
            'numero' => 'nullable|string|max:10',
            'piso' => 'nullable|string|max:10',
            'depto' => 'nullable|string|max:10',
            'ciudad' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'provincia_id' => 'nullable|exists:provincias,id',
            'forma_pago_habitual_id' => 'nullable|exists:formas_pago,id',
            'referencias' => 'nullable|string|max:1000',
            'observaciones' => 'nullable|string|max:2000',
        ], ['telefono_principal.unique' => 'Ya existe un cliente con ese teléfono.']);
    }

    public function store(Request $request)
    {
        $cliente = Cliente::create($this->validar($request) + ['fecha_alta' => today(), 'activo' => true]);

        if ($request->input('volver') === 'pedido') {
            return redirect()->route('pedidos.create', ['cliente' => $cliente->id])->with('ok', 'Cliente registrado. Continuá con el pedido.');
        }

        return redirect()->route('clientes.show', $cliente)->with('ok', 'Cliente registrado.');
    }

    public function update(Request $request, Cliente $cliente)
    {
        $cliente->update($this->validar($request, $cliente) + ['activo' => $request->boolean('activo')]);

        return redirect()->route('clientes.show', $cliente)->with('ok', 'Cliente actualizado.');
    }

    public function destroy(Cliente $cliente)
    {
        if ($cliente->saldo() > 0 || $cliente->garrafas()->exists()) {
            throw new \DomainException('No se puede eliminar: el cliente tiene saldo pendiente o garrafas en su poder. Podés marcarlo como inactivo.');
        }
        $cliente->delete();

        return redirect()->route('clientes.index')->with('ok', 'Cliente eliminado.');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['formaPagoHabitual', 'provincia', 'contactos.tipo', 'garrafas']);
        $pedidos = $cliente->pedidos()->with(['estado', 'medioContacto', 'items.producto'])->orderByDesc('fecha')->paginate(10, ['*'], 'pagina_pedidos');
        $movimientos = $this->cuentaCorriente($cliente);
        $validos = $cliente->pedidos()->noCancelados();
        $formasUsadas = $cliente->pagos()->join('formas_pago', 'formas_pago.id', '=', 'pagos.forma_pago_id')
            ->selectRaw('formas_pago.nombre, COUNT(*) n')->groupBy('formas_pago.nombre')->orderByDesc('n')->pluck('n', 'nombre');
        $favoritos = DB::table('pedido_items')->join('pedidos', 'pedidos.id', '=', 'pedido_items.pedido_id')->join('productos', 'productos.id', '=', 'pedido_items.producto_id')
            ->where('pedidos.cliente_id', $cliente->id)->whereNull('pedidos.deleted_at')->where('tipo_linea', 'VENTA')
            ->selectRaw('productos.nombre, SUM(cantidad) cantidad')->groupBy('productos.nombre')->orderByDesc('cantidad')->limit(3)->get();

        return view('clientes.show', [
            'titulo' => $cliente->nombreCompleto(), 'migas' => ['Ventas' => null, 'Clientes' => route('clientes.index'), 'Ficha' => null],
            'cliente' => $cliente, 'pedidos' => $pedidos, 'movimientos' => $movimientos,
            'regularidad' => $cliente->regularidad(), 'saldo' => $cliente->saldo(),
            'cantidadPedidos' => (clone $validos)->count(), 'totalComprado' => (clone $validos)->sum('total'),
            'formasUsadas' => $formasUsadas, 'favoritos' => $favoritos, 'tiposContacto' => TipoContactoCliente::todos(),
        ]);
    }

    /** Movimientos de la vista v_cuenta_corriente_cliente con saldo acumulado */
    private function cuentaCorriente(Cliente $cliente)
    {
        $saldo = 0;

        return DB::table('v_cuenta_corriente_cliente')->where('cliente_id', $cliente->id)
            ->orderBy('fecha')->orderByDesc('debe')->get()
            ->map(function ($m) use (&$saldo) {
                $saldo += $m->debe - $m->haber;
                $m->saldo = $saldo;

                return $m;
            });
    }

    public function estadoCuenta(Cliente $cliente)
    {
        $movimientos = $this->cuentaCorriente($cliente);

        return PedidoController::pdfListado('Estado de cuenta', $cliente->nombreCompleto(), [
            ['resumen' => [['Cliente', $cliente->nombreCompleto()], ['Domicilio', $cliente->domicilioCompleto()], ['Teléfono', $cliente->telefono_principal],
                ['Total comprado', pesos($movimientos->sum('debe'))], ['Total pagado', pesos($movimientos->sum('haber'))], ['Saldo adeudado', pesos($cliente->saldo())]]],
            ['titulo' => 'Movimientos', 'head' => ['Fecha', 'Comprobante', 'Concepto', 'Debe', 'Haber', 'Saldo'],
                'body' => $movimientos->map(fn ($m) => [fecha($m->fecha), $m->comprobante, $m->tipo_movimiento === 'PEDIDO' ? 'Pedido' : 'Pago', $m->debe > 0 ? pesos($m->debe) : '', $m->haber > 0 ? pesos($m->haber) : '', pesos($m->saldo)]),
                'num' => [3, 4, 5]],
        ], "estado-cuenta-{$cliente->id}");
    }

    public function agregarContacto(Request $request, Cliente $cliente)
    {
        $datos = $request->validate([
            'tipo_contacto_id' => 'required|exists:tipos_contacto_cliente,id', 'valor' => 'required|string|max:150',
            'observaciones' => 'nullable|string|max:255', 'es_principal' => 'nullable|boolean',
        ]);
        $cliente->contactos()->create($datos + ['es_principal' => $request->boolean('es_principal')]);

        return back()->with('ok', 'Contacto agregado.');
    }

    public function quitarContacto(Cliente $cliente, ClienteContacto $contacto)
    {
        abort_unless($contacto->cliente_id === $cliente->id, 404);
        $contacto->delete();

        return back()->with('ok', 'Contacto eliminado.');
    }
}

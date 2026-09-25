<?php

namespace App\Controllers;

use App\Core\Controlador;
use App\Core\DB;
use App\Core\ErrorNegocio;
use App\Core\Pdf;
use App\Models\Catalogo;
use App\Models\Cliente;
use App\Models\Pedido;

class ClienteController extends Controlador
{
    private const NOMBRES = ['telefono_principal' => 'teléfono', 'cuit_cuil' => 'CUIT/CUIL', 'forma_pago_habitual_id' => 'forma de pago', 'provincia_id' => 'provincia', 'codigo' => 'código'];

    public function index(): string
    {
        $f = array_intersect_key($_GET, array_flip(['q', 'forma_pago', 'filtro']));
        $clientes = Cliente::listar($f);

        if ($this->quierePdf()) {
            Pdf::informe('Listado de clientes', count($clientes).' clientes', [[
                'cabecera' => ['Cliente', 'Domicilio', 'Teléfono', 'Pago habitual', 'Garrafas', 'Último pedido', 'Frecuencia', 'Saldo'],
                'filas' => array_map(fn ($c) => [nombre($c), domicilio($c), $c['telefono_principal'], $c['forma_pago_nombre'], $c['garrafas'], fecha($c['regularidad']['ultimo']),
                    $c['regularidad']['promedio'] ? $c['regularidad']['promedio'].' días' : '—', pesos($c['saldo'])], $clientes),
                'derecha' => [4, 7],
            ]], 'clientes', 'L');
        }

        $pag = new \App\Core\Paginador(count($clientes));

        return $this->vista('clientes/index', [
            'titulo' => 'Clientes', 'migas' => ['Ventas' => null, 'Clientes' => null],
            'clientes' => array_slice($clientes, $pag->offset(), $pag->porPagina), 'pag' => $pag, 'formasPago' => Catalogo::opciones('formas_pago'),
        ]);
    }

    public function nuevo(): string
    {
        return $this->formulario(['id' => null, 'activo' => 1, 'provincia_id' => Catalogo::id('provincias', 'TUC'), 'forma_pago_habitual_id' => Catalogo::id('formas_pago', 'EFECTIVO')]);
    }

    public function editar(int $id): string
    {
        return $this->formulario($this->noEncontrado(Cliente::buscar($id)));
    }

    private function formulario(array $c): string
    {
        return $this->vista('clientes/form', [
            'titulo' => $c['id'] ? 'Editar cliente' : 'Nuevo cliente',
            'migas' => ['Ventas' => null, 'Clientes' => url('clientes'), ($c['id'] ? nombre($c) : 'Nuevo') => null],
            'cliente' => $c, 'provincias' => Catalogo::opciones('provincias'), 'formasPago' => Catalogo::opciones('formas_pago', true),
        ]);
    }

    private function datos(?int $id = null): array
    {
        return $this->validar([
            'codigo' => 'texto:20|unico:clientes,codigo'.($id ? ",{$id}" : ''),
            'nombre' => 'requerido|texto:100', 'apellido' => 'requerido|texto:100', 'dni' => 'texto:15', 'cuit_cuil' => 'texto:15',
            'telefono_principal' => 'requerido|texto:25|unico:clientes,telefono_principal'.($id ? ",{$id}" : ''),
            'telefono_secundario' => 'texto:25', 'email' => 'email|texto:150', 'calle' => 'texto:150', 'numero' => 'texto:10', 'piso' => 'texto:10', 'depto' => 'texto:10',
            'ciudad' => 'texto:100', 'codigo_postal' => 'texto:10', 'provincia_id' => 'existe:provincias', 'forma_pago_habitual_id' => 'existe:formas_pago',
            'referencias' => 'texto:1000', 'observaciones' => 'texto:2000',
        ], self::NOMBRES);
    }

    public function guardar(): never
    {
        $id = Cliente::crear($this->datos());
        if (($_POST['volver'] ?? '') === 'pedido') {
            $this->exito("pedidos/nuevo?cliente={$id}", 'Cliente registrado. Continuá con el pedido.');
        }
        $this->exito("clientes/{$id}", 'Cliente registrado.');
    }

    public function actualizar(int $id): never
    {
        $this->noEncontrado(Cliente::buscar($id));
        Cliente::actualizar($id, $this->datos($id), ! empty($_POST['activo']));
        $this->exito("clientes/{$id}", 'Cliente actualizado.');
    }

    public function eliminar(int $id): never
    {
        $this->noEncontrado(Cliente::buscar($id));
        if (Cliente::saldo($id) > 0 || Cliente::garrafas($id)) {
            throw new ErrorNegocio('No se puede eliminar: el cliente tiene saldo pendiente o garrafas en su poder. Podés marcarlo como inactivo.');
        }
        Cliente::eliminar($id);
        $this->exito('clientes', 'Cliente eliminado.');
    }

    public function ver(int $id): string
    {
        $c = $this->noEncontrado(Cliente::buscar($id));
        $cancelado = Catalogo::id('estados_pedido', 'CANCELADO');
        $totales = DB::uno('SELECT COUNT(*) n, COALESCE(SUM(total), 0) total FROM pedidos WHERE cliente_id = :c AND deleted_at IS NULL AND estado_pedido_id <> :x', ['c' => $id, 'x' => $cancelado]);

        return $this->vista('clientes/ver', [
            'titulo' => nombre($c), 'migas' => ['Ventas' => null, 'Clientes' => url('clientes'), 'Ficha' => null],
            'cliente' => $c, 'pedidos' => Pedido::delCliente($id, $pag), 'pag' => $pag, 'movimientos' => Cliente::cuentaCorriente($id),
            'regularidad' => Cliente::regularidad([$id])[$id], 'saldo' => Cliente::saldo($id), 'totales' => $totales,
            'garrafas' => Cliente::garrafas($id), 'contactos' => Cliente::contactos($id), 'tiposContacto' => Catalogo::opciones('tipos_contacto_cliente'),
            'formasUsadas' => DB::pares('SELECT fp.nombre, COUNT(*) FROM pagos p JOIN formas_pago fp ON fp.id = p.forma_pago_id WHERE p.cliente_id = :c AND p.deleted_at IS NULL GROUP BY fp.nombre ORDER BY COUNT(*) DESC', ['c' => $id]),
            'favoritos' => DB::pares("SELECT pr.nombre, SUM(pi.cantidad) FROM pedido_items pi JOIN pedidos p ON p.id = pi.pedido_id JOIN productos pr ON pr.id = pi.producto_id
                WHERE p.cliente_id = :c AND p.deleted_at IS NULL AND p.estado_pedido_id <> :x AND pi.tipo_linea = 'VENTA' GROUP BY pr.nombre ORDER BY SUM(pi.cantidad) DESC LIMIT 3", ['c' => $id, 'x' => $cancelado]),
        ]);
    }

    public function estadoCuenta(int $id): never
    {
        $c = $this->noEncontrado(Cliente::buscar($id));
        $movs = Cliente::cuentaCorriente($id);
        Pdf::informe('Estado de cuenta', nombre($c), [
            ['resumen' => [['Cliente', nombre($c)], ['Domicilio', domicilio($c)], ['Teléfono', $c['telefono_principal']],
                ['Total comprado', pesos(array_sum(array_column($movs, 'debe')))], ['Total pagado', pesos(array_sum(array_column($movs, 'haber')))], ['Saldo adeudado', pesos(Cliente::saldo($id))]]],
            ['titulo' => 'Movimientos', 'cabecera' => ['Fecha', 'Comprobante', 'Concepto', 'Debe', 'Haber', 'Saldo'],
                'filas' => array_map(fn ($m) => [fecha($m['fecha']), $m['comprobante'], $m['tipo_movimiento'] === 'PEDIDO' ? 'Pedido' : 'Pago',
                    $m['debe'] > 0 ? pesos($m['debe']) : '', $m['haber'] > 0 ? pesos($m['haber']) : '', pesos($m['saldo'])], $movs),
                'derecha' => [3, 4, 5]],
        ], "estado-cuenta-{$id}");
    }

    public function agregarContacto(int $id): never
    {
        $this->noEncontrado(Cliente::buscar($id));
        $d = $this->validar(['tipo_contacto_id' => 'requerido|existe:tipos_contacto_cliente', 'valor' => 'requerido|texto:150', 'observaciones' => 'texto:255']);
        DB::insertar('cliente_contactos', $d + ['cliente_id' => $id, 'es_principal' => 0]);
        $this->exito("clientes/{$id}", 'Contacto agregado.');
    }

    public function quitarContacto(int $id, int $contacto): never
    {
        DB::ejecutar('DELETE FROM cliente_contactos WHERE id = :c AND cliente_id = :id', ['c' => $contacto, 'id' => $id]);
        $this->exito("clientes/{$id}", 'Contacto eliminado.');
    }
}

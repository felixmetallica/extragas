<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controlador;
use App\Core\DB;
use App\Core\ErrorNegocio;
use App\Core\ErrorValidacion;
use App\Core\Pdf;
use App\Models\Catalogo;
use App\Models\Cliente;
use App\Models\Garrafa;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Producto;

class PedidoController extends Controlador
{
    public function index(): string
    {
        [$desde, $hasta] = $this->rango();
        $f = ['desde' => $desde, 'hasta' => $hasta] + array_intersect_key($_GET, array_flip(['q', 'estado', 'medio', 'canal', 'pago']));

        if ($this->quierePdf()) {
            Pdf::informe('Listado de pedidos', 'Del '.fecha($desde).' al '.fecha($hasta), [[
                'cabecera' => ['N°', 'Fecha', 'Cliente', 'Medio', 'Productos', 'Total', 'Pago', 'Estado'],
                'filas' => array_map(fn ($p) => [$p['numero'], fecha($p['fecha'], true), nombre($p, 'cliente_'), $p['medio_nombre'], $p['productos'], pesos($p['total']), estado_pago($p), $p['estado_nombre']],
                    array_reverse(Pedido::listar($f, todos: true))),
                'derecha' => [5],
            ]], 'pedidos', 'L');
        }

        $pedidos = Pedido::listar($f, $pag);

        return $this->vista('pedidos/index', [
            'titulo' => 'Pedidos', 'migas' => ['Ventas' => null, 'Pedidos' => null],
            'pedidos' => $pedidos, 'pag' => $pag, 'resumen' => Pedido::resumen($f), 'desde' => $desde, 'hasta' => $hasta,
            'estados' => Catalogo::todos('estados_pedido'), 'medios' => Catalogo::todos('medios_contacto_pedido'),
        ]);
    }

    public function nuevo(): string
    {
        return $this->formulario([
            'id' => null, 'fecha' => date('Y-m-d H:i:s'), 'cliente_id' => (int) ($_GET['cliente'] ?? 0) ?: null,
            'canal_venta_id' => Catalogo::id('canales_venta', 'DOMICILIO'), 'medio_contacto_id' => Catalogo::id('medios_contacto_pedido', 'WHATSAPP'),
            'direccion_entrega' => '', 'observaciones' => '', 'descuento' => 0, 'numero' => null,
        ], []);
    }

    public function editar(int $id): string
    {
        $p = $this->noEncontrado(Pedido::buscar($id));
        if ($p['es_final']) {
            throw new ErrorNegocio('El pedido ya está finalizado y no se puede modificar.');
        }
        $items = array_values(array_filter(Pedido::items($id), fn ($i) => $i['tipo_linea'] === 'VENTA'));

        return $this->formulario($p, array_map(fn ($i) => ['producto_id' => (int) $i['producto_id'], 'cantidad' => (float) $i['cantidad'], 'precio_unitario' => (float) $i['precio_unitario']], $items));
    }

    private function formulario(array $pedido, array $items): string
    {
        $stock = Garrafa::stock();

        return $this->vista('pedidos/form', [
            'titulo' => $pedido['id'] ? "Editar pedido {$pedido['numero']}" : 'Nuevo pedido',
            'migas' => ['Ventas' => null, 'Pedidos' => url('pedidos'), ($pedido['id'] ? 'Editar' : 'Nuevo') => null],
            'pedido' => $pedido, 'items' => viejo('items', $items), 'clientes' => Cliente::paraPedido(),
            'productos' => array_map(fn ($p) => ['id' => (int) $p['id'], 'nombre' => $p['nombre'], 'precio' => (float) $p['precio_actual'], 'garrafa' => (bool) $p['maneja_garrafa_individual'],
                'icono' => icono_producto($p['tipo_codigo']), 'tipo' => $p['tipo_nombre'], 'stock' => Producto::stockDisponible($p, $stock)], Producto::todos(true)),
            'medios' => Catalogo::todos('medios_contacto_pedido'), 'canales' => Catalogo::todos('canales_venta'),
            'formasPago' => Catalogo::opciones('formas_pago', true),
        ]);
    }

    /** Valida cabecera e ítems del pedido */
    private function datos(bool $nuevo): array
    {
        $d = $this->validar([
            'cliente_id' => $nuevo ? 'requerido|existe:clientes' : '',
            'medio_contacto_id' => 'requerido|existe:medios_contacto_pedido',
            'canal_venta_id' => 'requerido|existe:canales_venta',
            'fecha' => 'requerido|fecha',
            'direccion_entrega' => 'texto:255',
            'observaciones' => 'texto:2000',
            'descuento' => 'numero|min:0',
        ], ['cliente_id' => 'cliente', 'medio_contacto_id' => 'medio de contacto', 'canal_venta_id' => 'tipo de entrega']);
        $d['fecha'] = date('Y-m-d H:i:s', strtotime($d['fecha']));
        if (Catalogo::porId('canales_venta', (int) $d['canal_venta_id'])['codigo'] !== 'DOMICILIO') {
            $d['direccion_entrega'] = null;
        }

        $d['items'] = [];
        foreach (array_values((array) ($_POST['items'] ?? [])) as $it) {
            $prod = Producto::buscar((int) ($it['producto_id'] ?? 0));
            if (! $prod || ! is_numeric($it['cantidad'] ?? null) || $it['cantidad'] <= 0 || ! is_numeric($it['precio_unitario'] ?? null) || $it['precio_unitario'] < 0) {
                throw new ErrorValidacion(['items' => 'Revisá los productos: cantidades y precios deben ser números positivos.']);
            }
            $d['items'][] = ['producto_id' => (int) $prod['id'], 'cantidad' => (float) $it['cantidad'], 'precio_unitario' => (float) $it['precio_unitario']];
        }
        if (! $d['items']) {
            throw new ErrorValidacion(['items' => 'Agregá al menos un producto.']);
        }

        return $d;
    }

    public function guardar(): never
    {
        $d = $this->datos(true);
        $pago = $this->validar(['registrar_pago' => '', 'pago_monto' => 'numero|min:0', 'forma_pago_id' => 'existe:formas_pago', 'pago_referencia' => 'texto:100']);

        $id = DB::transaccion(function () use ($d, $pago) {
            $id = Pedido::crear($d, Auth::empleadoId());
            if (! empty($pago['registrar_pago']) && (float) $pago['pago_monto'] > 0) {
                if (! $pago['forma_pago_id']) {
                    throw new ErrorValidacion(['forma_pago_id' => 'Elegí la forma de pago.']);
                }
                $pedido = Pedido::buscar($id);
                Pago::cobrar((int) $d['cliente_id'], $pedido, min((float) $pago['pago_monto'], (float) $pedido['total']), (int) $pago['forma_pago_id'], $pago['pago_referencia'], date('Y-m-d H:i:s'));
            }

            return $id;
        });
        $numero = Pedido::buscar($id)['numero'];

        if (! empty($_POST['entregar_ahora'])) {
            $this->exito("pedidos/{$id}/entrega", "Pedido {$numero} registrado. Confirmá la entrega de productos y envases.");
        }
        $this->exito("pedidos/{$id}", "Pedido {$numero} registrado.", ! empty($_POST['con_pdf']) ? url("pedidos/{$id}/pdf") : null);
    }

    public function actualizar(int $id): never
    {
        $p = $this->noEncontrado(Pedido::buscar($id));
        Pedido::actualizar($p, $this->datos(false));
        $this->exito("pedidos/{$id}", 'Pedido actualizado.');
    }

    public function ver(int $id): string
    {
        $p = $this->noEncontrado(Pedido::buscar($id));

        return $this->vista('pedidos/ver', [
            'titulo' => "Pedido {$p['numero']}", 'migas' => ['Ventas' => null, 'Pedidos' => url('pedidos'), $p['numero'] => null],
            'pedido' => $p, 'cliente' => Cliente::buscar((int) $p['cliente_id']) ?? [], 'items' => Pedido::items($id),
            'pagos' => Pedido::pagos($id), 'movimientos' => Pedido::movimientosGarrafa($id), 'estados' => Catalogo::todos('estados_pedido'),
        ]);
    }

    public function avanzar(int $id): never
    {
        $p = $this->noEncontrado(Pedido::buscar($id));
        if (Pedido::siguienteEstado($p['estado_codigo']) === 'ENTREGADO') {
            redirigir("pedidos/{$id}/entrega");
        }
        Pedido::avanzar($p);
        \App\Core\Sesion::flash('ok', "Pedido {$p['numero']}: ".Pedido::buscar($id)['estado_nombre'].'.');
        volver();
    }

    public function cancelar(int $id): never
    {
        Pedido::cancelar($this->noEncontrado(Pedido::buscar($id)));
        $this->exito("pedidos/{$id}", 'Pedido cancelado.');
    }

    public function entrega(int $id): string
    {
        $p = $this->noEncontrado(Pedido::buscar($id));
        if ($p['es_final']) {
            throw new ErrorNegocio('El pedido ya está finalizado.');
        }

        return $this->vista('pedidos/entrega', [
            'titulo' => "Entrega del pedido {$p['numero']}",
            'migas' => ['Ventas' => null, 'Pedidos' => url('pedidos'), $p['numero'] => url("pedidos/{$id}"), 'Entrega' => null],
            'pedido' => $p, 'necesidades' => Pedido::necesidadesEntrega($p),
            'otros' => array_filter(Pedido::items($id), fn ($i) => $i['tipo_linea'] === 'VENTA' && ! $i['maneja_garrafa_individual']),
        ]);
    }

    public function entregar(int $id): never
    {
        $p = $this->noEncontrado(Pedido::buscar($id));
        $enteros = fn ($v) => array_values(array_filter(array_map('intval', (array) $v)));
        $sinRegistrar = array_map(fn ($n) => max(0, min(50, (int) $n)), (array) ($_POST['sin_registrar'] ?? []));

        Pedido::entregar($p, $enteros($_POST['entregadas'] ?? []), $enteros($_POST['devueltas'] ?? []), $enteros($_POST['no_aptas'] ?? []), $sinRegistrar, Auth::empleadoId());

        $p = Pedido::buscar($id);
        if ((float) $p['saldo'] > 0 && ! empty($_POST['cobrar'])) {
            $this->exito('cobros/nuevo?'.http_build_query(['cliente' => $p['cliente_id'], 'pedido' => $id]), "Pedido {$p['numero']} entregado. Registrá el cobro.");
        }
        $this->exito("pedidos/{$id}", "Pedido {$p['numero']} entregado. Stock y garrafas actualizados.");
    }

    public function pdf(int $id): never
    {
        $p = $this->noEncontrado(Pedido::buscar($id));
        $c = Cliente::buscar((int) $p['cliente_id']) ?? [];
        $items = Pedido::items($id);
        $movs = Pedido::movimientosGarrafa($id);

        $pdf = new Pdf("Pedido {$p['numero']}", 'Fecha: '.fecha($p['fecha'], true));
        $y = $pdf->GetY();
        $y1 = $pdf->bloque('Cliente', [nombre($c), 'Tel.: '.($c['telefono_principal'] ?? ''), domicilio($c), ! empty($c['referencias']) ? 'Ref.: '.$c['referencias'] : null], 14, $y);
        $y2 = $pdf->bloque('Pedido', [$p['canal_nombre'], 'Medio: '.($p['medio_nombre'] ?? '—'), $p['direccion_entrega'] ? 'Dirección: '.$p['direccion_entrega'] : null,
            'Estado: '.$p['estado_nombre'], 'Atendió: '.nombre($p, 'empleado_')], 108, $y);
        $pdf->SetY(max($y1, $y2) + 4);
        $pdf->tabla(['Producto', 'Cantidad', 'Precio unit.', 'Subtotal'], array_map(fn ($i) => [$i['producto_nombre'], num($i['cantidad']), pesos($i['precio_unitario']), pesos($i['subtotal'])],
            array_filter($items, fn ($i) => $i['tipo_linea'] === 'VENTA')), [1, 2, 3]);
        if ((float) $p['descuento'] > 0) {
            $pdf->resumen([['Subtotal', pesos($p['subtotal'])], ['Descuento', '- '.pesos($p['descuento'])]], 80);
        }
        $pdf->caja('TOTAL', pesos($p['total']));
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->Cell(0, 5, $pdf->t('Pagado: '.pesos($p['monto_pagado']).' · Saldo: '.pesos($p['estado_codigo'] === 'CANCELADO' ? 0 : $p['saldo'])
            .(! empty($c['forma_pago_nombre']) ? ' · Forma de pago habitual: '.$c['forma_pago_nombre'] : '')), 0, 1);

        $envases = array_filter($items, fn ($i) => $i['tipo_linea'] !== 'VENTA');
        if ($envases) {
            $pdf->titulo('Envases');
            $pdf->tabla(['Movimiento', 'Producto', 'Cantidad', 'Códigos'], array_map(function ($i) use ($movs) {
                $tipo = $i['tipo_linea'] === 'ENTREGA' ? 'ENTREGA_CLIENTE' : 'DEVOLUCION_CLIENTE';
                $codigos = array_column(array_filter($movs, fn ($m) => $m['tipo_codigo'] === $tipo && (int) $m['capacidad_kg'] === (int) $i['capacidad_kg']), 'codigo');

                return [$i['tipo_linea'] === 'ENTREGA' ? 'Llenas entregadas' : 'Vacías recibidas', $i['producto_nombre'], num($i['cantidad']), implode(', ', $codigos)];
            }, $envases), [2]);
        }
        if ($p['observaciones']) {
            $pdf->SetFont('Helvetica', '', 8.5);
            $pdf->MultiCell(0, 5, $pdf->t('Observaciones: '.$p['observaciones']));
        }
        $pdf->firma('Firma y aclaración del cliente');
        $pdf->descargar("pedido-{$p['numero']}");
    }
}

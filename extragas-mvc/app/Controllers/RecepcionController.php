<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controlador;
use App\Core\DB;
use App\Core\ErrorValidacion;
use App\Core\Pdf;
use App\Models\Catalogo;
use App\Models\Garrafa;
use App\Models\Informe;
use App\Models\PagoProveedor;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Recepcion;

class RecepcionController extends Controlador
{
    public function index(): string
    {
        [$desde, $hasta] = $this->rango();
        $f = ['desde' => $desde, 'hasta' => $hasta] + array_intersect_key($_GET, array_flip(['proveedor', 'pago']));

        if ($this->quierePdf()) {
            Pdf::informe('Recepciones de mercadería', 'Del '.fecha($desde).' al '.fecha($hasta), [[
                'cabecera' => ['N°', 'Fecha', 'Proveedor', 'Factura', 'Productos', 'Total', 'Pago'],
                'filas' => array_map(fn ($r) => [$r['numero'], fecha($r['fecha']), $r['proveedor_nombre'], $r['numero_factura_proveedor'], $r['productos'], pesos($r['total']), estado_pago($r)],
                    array_reverse(Recepcion::listar($f, todos: true))),
                'derecha' => [5],
            ]], 'recepciones', 'L');
        }

        return $this->vista('recepciones/index', [
            'titulo' => 'Recepciones de mercadería', 'migas' => ['Compras' => null, 'Recepciones' => null],
            'recepciones' => Recepcion::listar($f, $pag), 'pag' => $pag, 'resumen' => Recepcion::resumen($f),
            'deuda' => array_sum(array_column(Informe::saldosProveedores(), 'saldo_total')),
            'proveedores' => Proveedor::opciones(false), 'desde' => $desde, 'hasta' => $hasta,
        ]);
    }

    public function nueva(): string
    {
        $stock = Garrafa::stock();

        return $this->vista('recepciones/nueva', [
            'titulo' => 'Nueva recepción', 'migas' => ['Compras' => null, 'Recepciones' => url('recepciones'), 'Nueva' => null],
            'proveedores' => Proveedor::opciones(), 'proveedorId' => (int) ($_GET['proveedor'] ?? 0) ?: null,
            'productos' => array_map(fn ($p) => ['id' => (int) $p['id'], 'nombre' => $p['nombre'], 'costo' => (float) $p['costo_actual'], 'garrafa' => (bool) $p['maneja_garrafa_individual'],
                'capacidad' => (int) $p['capacidad_kg'], 'stock' => $p['maneja_garrafa_individual'] ? null : (float) $p['stock_actual']], Producto::todos(true)),
            'vacias' => array_map(fn ($e) => $e['VACIA'], $stock), 'formasPago' => Catalogo::todos('formas_pago'),
        ]);
    }

    public function guardar(): never
    {
        $d = $this->validar([
            'proveedor_id' => 'requerido|existe:proveedores', 'fecha' => 'requerido|fecha', 'numero_factura_proveedor' => 'texto:50',
            'descuento' => 'numero|min:0', 'observaciones' => 'texto:2000', 'forma_pago_id' => 'existe:formas_pago',
        ], ['proveedor_id' => 'proveedor']);
        $d['fecha'] = date('Y-m-d H:i:s', strtotime($d['fecha']));

        $items = [];
        foreach (array_values((array) ($_POST['items'] ?? [])) as $it) {
            if (! is_numeric($it['cantidad'] ?? null) || $it['cantidad'] < 1 || ! is_numeric($it['precio_unitario'] ?? null) || $it['precio_unitario'] < 0) {
                throw new ErrorValidacion(['items' => 'Revisá las cantidades y costos de los productos.']);
            }
            $items[] = ['producto_id' => (int) $it['producto_id'], 'cantidad' => (float) $it['cantidad'], 'precio_unitario' => (float) $it['precio_unitario'], 'codigos' => $it['codigos'] ?? ''];
        }
        if (! $items) {
            throw new ErrorValidacion(['items' => 'Agregá los productos del remito.']);
        }
        $vacias = array_map('intval', array_filter((array) ($_POST['vacias'] ?? [])));

        $id = DB::transaccion(function () use ($d, $items, $vacias) {
            $id = Recepcion::registrar($d, $items, $vacias, Auth::empleadoId());
            $r = Recepcion::buscar($id);
            if (! empty($_POST['pagada']) && (float) $r['total'] > 0) {
                PagoProveedor::pagar((int) $r['proveedor_id'], $r, (float) $r['total'], (int) ($d['forma_pago_id'] ?: Catalogo::id('formas_pago', 'TRANSFERENCIA')), null, $r['fecha']);
            }

            return $id;
        });
        $this->exito("recepciones/{$id}", 'Recepción '.Recepcion::buscar($id)['numero'].' registrada. Stock y garrafas actualizados.');
    }

    public function ver(int $id): string
    {
        $r = $this->noEncontrado(Recepcion::buscar($id));

        return $this->vista('recepciones/ver', [
            'titulo' => "Recepción {$r['numero']}", 'migas' => ['Compras' => null, 'Recepciones' => url('recepciones'), $r['numero'] => null],
            'recepcion' => $r, 'items' => Recepcion::items($id), 'pagos' => Recepcion::pagos($id),
            'garrafas' => Recepcion::garrafasIngresadas($id), 'entregadas' => Recepcion::vaciasEntregadas($id),
        ]);
    }
}

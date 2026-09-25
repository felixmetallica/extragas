<?php

namespace App\Controllers;

use App\Core\Controlador;
use App\Core\ErrorNegocio;
use App\Core\Pdf;
use App\Models\Catalogo;
use App\Models\Informe;
use App\Models\PagoProveedor;
use App\Models\Proveedor;
use App\Models\Recepcion;

class PagoProveedorController extends Controlador
{
    public function index(): string
    {
        [$desde, $hasta] = $this->rango();
        $f = ['desde' => $desde, 'hasta' => $hasta, 'proveedor' => $_GET['proveedor'] ?? null];
        $saldos = array_column(Informe::saldosProveedores(), null, 'proveedor_id');

        if ($this->quierePdf()) {
            Pdf::informe('Pagos a proveedores', 'Del '.fecha($desde).' al '.fecha($hasta), [
                ['titulo' => 'Saldos pendientes', 'cabecera' => ['Proveedor', 'CUIT', 'Recepciones impagas', 'Saldo'],
                    'filas' => array_map(fn ($s) => [$s['razon_social'], $s['cuit'], $s['recepciones_pendientes'], pesos($s['saldo_total'])], array_values($saldos)), 'derecha' => [2, 3]],
                ['titulo' => 'Pagos realizados', 'cabecera' => ['N°', 'Fecha', 'Proveedor', 'Recepción', 'Forma', 'Referencia', 'Importe'],
                    'filas' => array_map(fn ($p) => [$p['numero'], fecha($p['fecha']), $p['proveedor_nombre'], $p['recepcion_numero'] ?? 'A cuenta', $p['forma_nombre'], $p['referencia'], pesos($p['monto'])],
                        array_reverse(PagoProveedor::listar($f, todos: true))), 'derecha' => [6]],
            ], 'pagos-proveedores');
        }

        $pagos = PagoProveedor::listar($f, $pag);

        return $this->vista('pagos-proveedores/index', [
            'titulo' => 'Pagos a proveedores', 'migas' => ['Compras' => null, 'Pagos a proveedores' => null],
            'pagos' => $pagos, 'pag' => $pag, 'totalPeriodo' => array_sum(array_column(PagoProveedor::listar($f, todos: true), 'monto')),
            'saldos' => $saldos, 'proveedores' => Proveedor::opciones(), 'desde' => $desde, 'hasta' => $hasta,
        ]);
    }

    public function nuevo(): string
    {
        return $this->vista('pagos-proveedores/nuevo', [
            'titulo' => 'Registrar pago a proveedor', 'migas' => ['Compras' => null, 'Pagos a proveedores' => url('pagos-proveedores'), 'Nuevo' => null],
            'proveedores' => Proveedor::opciones(false), 'saldos' => array_column(Informe::saldosProveedores(), 'saldo_total', 'proveedor_id'),
            'observaciones' => \App\Core\DB::pares('SELECT id, observaciones FROM proveedores'),
            'pendientes' => array_map(fn ($r) => ['id' => (int) $r['id'], 'proveedor_id' => (int) $r['proveedor_id'], 'saldo' => (float) $r['saldo'],
                'texto' => "{$r['numero']} · ".fecha($r['fecha']).' · saldo '.pesos($r['saldo'])], Recepcion::conSaldo()),
            'proveedorId' => (int) ($_GET['proveedor'] ?? 0) ?: null, 'recepcionId' => (int) ($_GET['recepcion'] ?? 0) ?: null,
            'formasPago' => Catalogo::todos('formas_pago'),
        ]);
    }

    public function guardar(): never
    {
        $d = $this->validar([
            'proveedor_id' => 'requerido|existe:proveedores', 'recepcion_id' => 'existe:recepciones_proveedor', 'monto' => 'requerido|numero|min:1',
            'forma_pago_id' => 'requerido|existe:formas_pago', 'referencia' => 'texto:100', 'fecha' => 'requerido|fecha', 'observaciones' => 'texto:255',
        ], ['proveedor_id' => 'proveedor', 'forma_pago_id' => 'forma de pago']);
        $recepcion = null;
        if ($d['recepcion_id']) {
            $recepcion = Recepcion::buscar((int) $d['recepcion_id']);
            if ((int) $recepcion['proveedor_id'] !== (int) $d['proveedor_id']) {
                throw new ErrorNegocio('La recepción no corresponde al proveedor.');
            }
        }
        $fecha = $d['fecha'] === hoy() ? date('Y-m-d H:i:s') : date('Y-m-d 12:00:00', strtotime($d['fecha']));
        $numeros = PagoProveedor::pagar((int) $d['proveedor_id'], $recepcion, (float) $d['monto'], (int) $d['forma_pago_id'], $d['referencia'], $fecha, $d['observaciones']);
        $this->exito("proveedores/{$d['proveedor_id']}", 'Pago registrado: '.implode(', ', $numeros).'.');
    }
}

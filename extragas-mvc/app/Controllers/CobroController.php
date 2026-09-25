<?php

namespace App\Controllers;

use App\Core\Controlador;
use App\Core\ErrorHttp;
use App\Core\ErrorNegocio;
use App\Core\Pdf;
use App\Models\Catalogo;
use App\Models\Cliente;
use App\Models\Informe;
use App\Models\Pago;
use App\Models\Pedido;

class CobroController extends Controlador
{
    public function index(): string
    {
        [$desde, $hasta] = $this->rango();
        $f = ['desde' => $desde, 'hasta' => $hasta] + array_intersect_key($_GET, array_flip(['q', 'forma_pago']));
        $porForma = Pago::porForma($f);

        if ($this->quierePdf()) {
            Pdf::informe('Pagos recibidos', 'Del '.fecha($desde).' al '.fecha($hasta), [
                ['resumen' => array_merge(array_map(fn ($forma, $t) => [$forma, pesos($t)], array_keys($porForma), $porForma), [['Total', pesos(array_sum($porForma))]])],
                ['cabecera' => ['Recibo', 'Fecha', 'Cliente', 'Pedido', 'Forma', 'Referencia', 'Importe'],
                    'filas' => array_map(fn ($p) => [$p['numero_recibo'], fecha($p['fecha']), nombre($p, 'cliente_'), $p['pedido_numero'] ?? 'A cuenta', $p['forma_nombre'], $p['referencia'], pesos($p['monto'])],
                        array_reverse(Pago::listar($f, todos: true))),
                    'derecha' => [6]],
            ], 'pagos-recibidos');
        }

        return $this->vista('cobros/index', [
            'titulo' => 'Cobros', 'migas' => ['Ventas' => null, 'Cobros' => null],
            'pagos' => Pago::listar($f, $pag), 'pag' => $pag, 'porForma' => $porForma, 'totalPeriodo' => array_sum($porForma),
            'cobradoHoy' => Pago::cobradoHoy(), 'pendientes' => Pedido::conSaldo(), 'saldos' => Informe::saldosClientes(),
            'formasPago' => Catalogo::opciones('formas_pago'), 'desde' => $desde, 'hasta' => $hasta,
        ]);
    }

    public function nuevo(): string
    {
        $clienteId = (int) ($_GET['cliente'] ?? 0) ?: null;
        $cliente = $clienteId ? Cliente::buscar($clienteId) : null;
        $saldos = array_column(Informe::saldosClientes(), null, 'cliente_id');

        return $this->vista('cobros/nuevo', [
            'titulo' => 'Registrar pago de cliente', 'migas' => ['Ventas' => null, 'Cobros' => url('cobros'), 'Nuevo' => null],
            'cliente' => $cliente, 'pedidoId' => (int) ($_GET['pedido'] ?? 0) ?: null, 'saldos' => $saldos,
            'pendientes' => array_map(fn ($p) => ['id' => (int) $p['id'], 'cliente_id' => (int) $p['cliente_id'], 'saldo' => (float) $p['saldo'],
                'texto' => "{$p['numero']} · ".fecha($p['fecha']).' · saldo '.pesos($p['saldo'])], Pedido::conSaldo()),
            'formasPago' => Catalogo::todos('formas_pago'),
        ]);
    }

    public function guardar(): never
    {
        $d = $this->validar([
            'cliente_id' => 'requerido|existe:clientes', 'pedido_id' => 'existe:pedidos', 'monto' => 'requerido|numero|min:1',
            'forma_pago_id' => 'requerido|existe:formas_pago', 'referencia' => 'texto:100', 'fecha' => 'requerido|fecha', 'observaciones' => 'texto:255',
        ], ['cliente_id' => 'cliente', 'forma_pago_id' => 'forma de pago']);
        if (Catalogo::porId('formas_pago', (int) $d['forma_pago_id'])['requiere_referencia'] && ! $d['referencia']) {
            throw new ErrorNegocio('Esa forma de pago requiere el número de operación o referencia.');
        }
        $pedido = null;
        if ($d['pedido_id']) {
            $pedido = Pedido::buscar((int) $d['pedido_id']);
            if (! $pedido || (int) $pedido['cliente_id'] !== (int) $d['cliente_id']) {
                throw new ErrorNegocio('El pedido no corresponde al cliente.');
            }
        }
        $fecha = $d['fecha'] === hoy() ? date('Y-m-d H:i:s') : date('Y-m-d 12:00:00', strtotime($d['fecha']));
        $ids = Pago::cobrar((int) $d['cliente_id'], $pedido, (float) $d['monto'], (int) $d['forma_pago_id'], $d['referencia'], $fecha, $d['observaciones']);
        $recibos = implode(', ', array_column(Pago::porIds($ids), 'numero_recibo'));

        $this->exito($pedido ? "pedidos/{$pedido['id']}" : "clientes/{$d['cliente_id']}", "Pago registrado: {$recibos}.", url('cobros/recibo', ['ids' => implode(',', $ids)]));
    }

    /** Recibo PDF de uno o varios pagos del mismo cliente (?ids=1,2) */
    public function recibo(): never
    {
        $pagos = Pago::porIds(array_filter(array_map('intval', explode(',', (string) ($_GET['ids'] ?? '')))));
        if (! $pagos || count(array_unique(array_column($pagos, 'cliente_id'))) > 1) {
            throw new ErrorHttp('Recibo inexistente.', 404);
        }
        $c = Cliente::buscar((int) $pagos[0]['cliente_id']) ?? $pagos[0];
        $total = array_sum(array_column($pagos, 'monto'));

        $pdf = new Pdf('Recibo '.implode(' / ', array_column($pagos, 'numero_recibo')), 'Fecha: '.fecha($pagos[0]['fecha'], true));
        $pdf->SetFont('Helvetica', '', 10.5);
        $pdf->MultiCell(0, 6, $pdf->t('Recibimos de '.nombre($c).(! empty($c['dni']) ? " (DNI {$c['dni']})" : '').' la suma de '.pesos($total).' en concepto de pago según el siguiente detalle:'));
        $pdf->Ln(3);
        $pdf->tabla(['Recibo', 'Concepto', 'Forma de pago', 'Referencia', 'Importe'], array_map(fn ($p) => [
            $p['numero_recibo'], $p['pedido_numero'] ? "Pedido {$p['pedido_numero']} del ".fecha($p['pedido_fecha']) : 'Pago a cuenta', $p['forma_nombre'], $p['referencia'] ?: '—', pesos($p['monto']),
        ], $pagos), [4]);
        $pdf->caja('TOTAL RECIBIDO', pesos($total));
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(110);
        $pdf->Cell(0, 5, $pdf->t('Saldo pendiente del cliente luego de este pago: '.pesos(Cliente::saldo((int) $c['id']))), 0, 1);
        $pdf->SetTextColor(31, 36, 48);
        $pdf->firma('Recibió: '.(trim(($pagos[0]['empleado_nombre'] ?? '').' '.($pagos[0]['empleado_apellido'] ?? '')) ?: ($pagos[0]['username'] ?? '')));
        $pdf->descargar('recibo-'.$pagos[0]['numero_recibo']);
    }

    public function anular(int $id): never
    {
        $pago = $this->noEncontrado(Pago::buscar($id));
        Pago::anular($id);
        \App\Core\Sesion::flash('ok', "Pago {$pago['numero_recibo']} anulado.");
        volver();
    }
}

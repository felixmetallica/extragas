<?php

namespace Tests\Feature;

use App\Models\Catalogos\CanalVenta;
use App\Models\Catalogos\EstadoGarrafa;
use App\Models\Catalogos\EstadoPedido;
use App\Models\Catalogos\FormaPago;
use App\Models\Catalogos\MedioContacto;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\GarrafaService;
use App\Services\PedidoService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FlujoPedidoTest extends TestCase
{
    private function cliente(): Cliente
    {
        return Cliente::create(['nombre' => 'María', 'apellido' => 'González', 'telefono_principal' => '381 555-0001', 'calle' => 'Lamadrid', 'numero' => '100',
            'fecha_alta' => today(), 'forma_pago_habitual_id' => FormaPago::idDe(FormaPago::EFECTIVO)]);
    }

    public function test_pedido_completo_entrega_intercambio_y_cobro(): void
    {
        $admin = $this->admin();
        $cliente = $this->cliente();
        $garrafas = app(GarrafaService::class);
        $llenas = collect(range(1, 3))->map(fn () => $garrafas->alta(10, EstadoGarrafa::LLENA));
        $delCliente = $garrafas->alta(10, EstadoGarrafa::EN_CLIENTE, ['cliente_id' => $cliente->id]);
        $gar10 = Producto::where('codigo', 'GAR10')->first();
        $carbon = Producto::where('codigo', 'CAR05')->first();
        $carbon->update(['stock_actual' => 10]);

        // Alta del pedido desde el formulario
        $this->actingAs($admin)->post(route('pedidos.store'), [
            'cliente_id' => $cliente->id, 'medio_contacto_id' => MedioContacto::idDe(MedioContacto::WHATSAPP),
            'canal_venta_id' => CanalVenta::idDe(CanalVenta::DOMICILIO), 'fecha' => now()->format('Y-m-d\TH:i'), 'descuento' => 500,
            'items' => [
                ['producto_id' => $gar10->id, 'cantidad' => 1, 'precio_unitario' => 16500],
                ['producto_id' => $carbon->id, 'cantidad' => 2, 'precio_unitario' => 6500],
            ],
        ])->assertRedirect();

        $pedido = Pedido::firstOrFail();
        $this->assertMatchesRegularExpression('/^PED-\d{4}-00001$/', $pedido->numero); // trigger de numeración
        $this->assertEquals(29000, $pedido->total);
        $this->assertEquals(29000, $pedido->saldo); // columna generada

        // Avanzar estados y entregar con intercambio de envases
        $this->post(route('pedidos.avanzar', $pedido))->assertRedirect();
        $this->post(route('pedidos.avanzar', $pedido))->assertRedirect();
        $this->assertEquals(EstadoPedido::EN_REPARTO, $pedido->refresh()->estado->codigo);
        $this->get(route('pedidos.entrega', $pedido))->assertOk()->assertSee($llenas[0]->codigo)->assertSee($delCliente->codigo);

        $this->post(route('pedidos.entregar', $pedido), ['entregadas' => [$llenas[0]->id], 'devueltas' => [$delCliente->id], 'sin_registrar' => [10 => 0]])
            ->assertRedirect(route('pedidos.show', $pedido));

        $pedido->refresh();
        $this->assertTrue($pedido->entregado);
        $this->assertEquals(EstadoPedido::ENTREGADO, $pedido->estado->codigo);
        $this->assertEquals(EstadoGarrafa::EN_CLIENTE, $llenas[0]->refresh()->estado->codigo); // trigger de movimientos
        $this->assertEquals($cliente->id, $llenas[0]->cliente_id);
        $this->assertEquals(EstadoGarrafa::VACIA, $delCliente->refresh()->estado->codigo);
        $this->assertNull($delCliente->cliente_id);
        $this->assertEquals(8, $carbon->refresh()->stock_actual);
        $this->assertEquals(['DEVOLUCION', 'ENTREGA', 'VENTA'], $pedido->items->pluck('tipo_linea')->unique()->sort()->values()->all());

        // Cobro en dos partes: el trigger recalcula monto_pagado
        $this->post(route('cobros.store'), ['cliente_id' => $cliente->id, 'pedido_id' => $pedido->id, 'monto' => 20000, 'forma_pago_id' => FormaPago::idDe(FormaPago::EFECTIVO), 'fecha' => today()->toDateString()])
            ->assertRedirect()->assertSessionHas('pdf');
        $this->assertEquals(9000, $pedido->refresh()->saldo);
        $this->assertEquals('Parcial', $pedido->estadoPago());

        $this->post(route('cobros.store'), ['cliente_id' => $cliente->id, 'monto' => 9000, 'forma_pago_id' => FormaPago::idDe(FormaPago::TRANSFERENCIA), 'fecha' => today()->toDateString()])
            ->assertSessionHasErrors('general'); // transferencia sin referencia
        $this->post(route('cobros.store'), ['cliente_id' => $cliente->id, 'monto' => 9000, 'forma_pago_id' => FormaPago::idDe(FormaPago::TRANSFERENCIA), 'referencia' => 'Op 123', 'fecha' => today()->toDateString()])
            ->assertRedirect();
        $this->assertEquals(0, $pedido->refresh()->saldo);
        $this->assertEquals(0, DB::table('v_saldo_clientes')->count());

        // PDFs
        $this->get(route('pedidos.pdf', $pedido))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('cobros.recibo', ['ids' => $pedido->pagos()->pluck('id')->implode(',')]))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_no_se_puede_cobrar_mas_que_el_saldo_y_cancelado_no_genera_deuda(): void
    {
        $this->actingAs($this->admin());
        $cliente = $this->cliente();
        $gar = Producto::where('codigo', 'GAR15')->first();
        $this->post(route('pedidos.store'), [
            'cliente_id' => $cliente->id, 'medio_contacto_id' => MedioContacto::idDe(MedioContacto::TELEFONO), 'canal_venta_id' => CanalVenta::idDe(CanalVenta::RETIRO_LOCAL),
            'fecha' => now()->format('Y-m-d\TH:i'), 'items' => [['producto_id' => $gar->id, 'cantidad' => 1, 'precio_unitario' => 24000]],
        ]);
        $pedido = Pedido::firstOrFail();

        $this->post(route('cobros.store'), ['cliente_id' => $cliente->id, 'pedido_id' => $pedido->id, 'monto' => 30000, 'forma_pago_id' => FormaPago::idDe(FormaPago::EFECTIVO), 'fecha' => today()->toDateString()])
            ->assertSessionHasErrors('general');
        $this->assertEquals(24000, DB::table('v_saldo_clientes')->value('saldo_total'));

        $this->post(route('pedidos.cancelar', $pedido))->assertRedirect();
        $this->assertEquals(0, DB::table('v_saldo_clientes')->count());
        $this->assertEquals(0, $cliente->saldo());
    }

    public function test_entrega_exige_la_cantidad_correcta_de_garrafas(): void
    {
        $this->actingAs($this->admin());
        $cliente = $this->cliente();
        $g = app(GarrafaService::class)->alta(45, EstadoGarrafa::LLENA);
        $pedido = app(PedidoService::class)->crear([
            'cliente_id' => $cliente->id, 'canal_venta_id' => CanalVenta::idDe(CanalVenta::DOMICILIO),
            'items' => [['producto_id' => Producto::where('codigo', 'GAR45')->value('id'), 'cantidad' => 2, 'precio_unitario' => 72000]],
        ], $this->admin()->empleado);

        $this->post(route('pedidos.entregar', $pedido), ['entregadas' => [$g->id]])->assertSessionHasErrors('general');
        $this->assertFalse($pedido->refresh()->entregado);
        $this->assertEquals(EstadoGarrafa::LLENA, $g->refresh()->estado->codigo);
    }
}

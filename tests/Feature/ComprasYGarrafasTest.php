<?php

namespace Tests\Feature;

use App\Models\Catalogos\EstadoGarrafa;
use App\Models\Catalogos\FormaPago;
use App\Models\Garrafa;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\RecepcionProveedor;
use App\Services\GarrafaService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ComprasYGarrafasTest extends TestCase
{
    public function test_recepcion_da_de_alta_llenas_retira_vacias_y_suma_stock(): void
    {
        $this->actingAs($this->admin());
        $prov = Proveedor::create(['razon_social' => 'GasNor', 'cuit' => '30-1-1', 'activo' => true]);
        $vacias = collect(range(1, 2))->map(fn () => app(GarrafaService::class)->alta(10, EstadoGarrafa::VACIA));
        $gar10 = Producto::where('codigo', 'GAR10')->first();
        $lena = Producto::where('codigo', 'LEN25')->first();

        $this->post(route('recepciones.store'), [
            'proveedor_id' => $prov->id, 'fecha' => now()->format('Y-m-d\TH:i'), 'numero_factura_proveedor' => 'A-1',
            'items' => [
                ['producto_id' => $gar10->id, 'cantidad' => 3, 'precio_unitario' => 12000, 'codigos' => 'ABC1, ABC2 ABC3'],
                ['producto_id' => $lena->id, 'cantidad' => 10, 'precio_unitario' => 7000],
            ],
            'vacias' => [10 => 2],
        ])->assertRedirect();

        $r = RecepcionProveedor::firstOrFail();
        $this->assertStringStartsWith('REC-PROV-', $r->numero);
        $this->assertEquals(106000, $r->total);
        $this->assertEquals(['ABC1', 'ABC2', 'ABC3'], $r->garrafas()->orderBy('codigo')->pluck('codigo')->all());
        $this->assertEquals(3, Garrafa::activas()->enEstado(EstadoGarrafa::LLENA)->count());
        $this->assertEquals(0, Garrafa::activas()->enEstado(EstadoGarrafa::VACIA)->count());
        $this->assertFalse($vacias[0]->refresh()->activo);
        $this->assertEquals(10, $lena->refresh()->stock_actual);

        // Pago a cuenta: se aplica a la recepción y el trigger actualiza el saldo
        $this->post(route('pagos-proveedores.store'), ['proveedor_id' => $prov->id, 'monto' => 100000, 'forma_pago_id' => FormaPago::idDe(FormaPago::TRANSFERENCIA), 'fecha' => today()->toDateString()])
            ->assertRedirect();
        $this->assertEquals(6000, $r->refresh()->saldo);
        $this->assertEquals(6000, DB::table('v_saldo_proveedores')->value('saldo_total'));
        $this->assertStringStartsWith('PAG-PROV-', $r->pagos()->value('numero'));
    }

    public function test_movimientos_manuales_de_garrafa(): void
    {
        $this->actingAs($this->admin());
        $g = app(GarrafaService::class)->alta(15, EstadoGarrafa::VACIA);

        $this->post(route('garrafas.movimiento', $g), ['tipo' => 'MARCAR_NO_APTA', 'observaciones' => 'Válvula'])->assertRedirect();
        $this->assertEquals(EstadoGarrafa::NO_APTA, $g->refresh()->estado->codigo);
        $this->post(route('garrafas.movimiento', $g), ['tipo' => 'MARCAR_NO_APTA'])->assertSessionHasErrors('general');
        $this->post(route('garrafas.movimiento', $g), ['tipo' => 'BAJA'])->assertRedirect();
        $this->assertFalse($g->refresh()->activo);
        $this->assertEquals(3, $g->movimientos()->count());
    }

    public function test_alta_manual_genera_codigos_correlativos(): void
    {
        $this->actingAs($this->admin());
        $this->post(route('garrafas.store'), ['capacidad_kg' => 45, 'cantidad' => 2, 'estado' => 'LLENA'])->assertRedirect(route('garrafas.index'));
        $this->assertEquals(['G45-00001', 'G45-00002'], Garrafa::orderBy('codigo')->pluck('codigo')->all());
    }
}

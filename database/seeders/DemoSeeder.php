<?php

namespace Database\Seeders;

use App\Models\Catalogos\CanalVenta;
use App\Models\Catalogos\EstadoGarrafa;
use App\Models\Catalogos\EstadoPedido;
use App\Models\Catalogos\FormaPago;
use App\Models\Catalogos\MedioContacto;
use App\Models\Catalogos\Provincia;
use App\Models\Catalogos\Rol;
use App\Models\Catalogos\TipoMovimientoGarrafa;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Garrafa;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;
use App\Services\GarrafaService;
use App\Services\PagoService;
use App\Services\PedidoService;
use App\Services\RecepcionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Simula 90 días de operación usando los mismos servicios que la aplicación,
 * de modo que numeración, triggers, stock y movimientos de garrafas sean coherentes.
 */
class DemoSeeder extends Seeder
{
    public function __construct(
        private PedidoService $pedidos,
        private PagoService $pagos,
        private RecepcionService $recepciones,
        private GarrafaService $garrafas,
    ) {}

    public function run(): void
    {
        $this->call(DatabaseSeeder::class);
        mt_srand(20260924);

        $empleados = $this->empleados();
        $tuc = Provincia::idDe('TUC');
        $formas = [FormaPago::idDe(FormaPago::EFECTIVO), FormaPago::idDe(FormaPago::TRANSFERENCIA)];
        $inicio = today()->subDays(90);

        // Stock inicial de carbón y leña
        foreach (['CAR03' => 20, 'CAR05' => 16, 'CAR10' => 10, 'CAR25' => 5, 'LEN25' => 15] as $codigo => $stock) {
            Producto::where('codigo', $codigo)->update(['stock_actual' => $stock]);
        }

        $proveedores = $this->proveedores($tuc);
        $clientes = $this->clientes($tuc, $formas, $inicio);

        // Parque inicial de garrafas llenas en depósito
        foreach ([10 => 14, 15 => 9, 45 => 4] as $cap => $cantidad) {
            for ($i = 0; $i < $cantidad; $i++) {
                $this->garrafas->alta($cap, EstadoGarrafa::LLENA, ['proveedor_id' => $proveedores[$cap === 45 ? 1 : 0]->id, 'observaciones' => 'Parque inicial'], $empleados[0], $inicio->copy());
            }
        }

        $productos = Producto::all()->keyBy('codigo');
        $agenda = []; // fecha => [cliente, ...]
        foreach ($clientes as [$cliente, $prefiere, $frecuencia]) {
            $d = $inicio->copy()->addDays(mt_rand(1, $frecuencia));
            while ($d->lte(today())) {
                $agenda[$d->toDateString()][] = [$cliente, $prefiere];
                $d->addDays(max(2, $frecuencia + mt_rand(-2, 3)));
            }
        }

        for ($dia = $inicio->copy()->addDay(); $dia->lte(today()); $dia->addDay()) {
            $n = $inicio->diffInDays($dia);
            $esHoy = $dia->isToday();

            // Recepciones de proveedores
            if ($n % 7 === 1) {
                $this->recibir($proveedores[0], $dia, [['GAR10', mt_rand(6, 8)], ['GAR15', mt_rand(5, 7)]], $empleados[0], $productos);
            }
            if ($n % 10 === 3) {
                $this->recibir($proveedores[1], $dia, [['GAR45', mt_rand(3, 4)]], $empleados[0], $productos);
            }
            if ($n % 21 === 5) {
                $this->recibir($proveedores[2], $dia, [['CAR03', 10], ['CAR05', 10], ['CAR10', 6], ['CAR25', 2]], $empleados[1], $productos);
            }
            if ($n % 25 === 7) {
                $this->recibir($proveedores[3], $dia, [['LEN25', 10]], $empleados[1], $productos);
            }

            $delDia = $agenda[$dia->toDateString()] ?? [];
            if ($esHoy) {
                // Siempre algunos pedidos en curso para el día de hoy
                foreach (array_rand($clientes, 3) as $k) {
                    $delDia[] = [$clientes[$k][0], $clientes[$k][1]];
                }
            }
            foreach ($delDia as $i => [$cliente, $prefiere]) {
                $empleado = $empleados[mt_rand(0, count($empleados) - 1)];
                $items = [['producto_id' => $productos[$prefiere]->id, 'cantidad' => $prefiere === 'GAR45' ? mt_rand(1, 2) : (mt_rand(1, 10) > 8 ? 2 : 1), 'precio_unitario' => $productos[$prefiere]->precio_actual]];
                if (mt_rand(1, 10) <= 3) {
                    $extra = $productos[['CAR03', 'CAR05', 'CAR05', 'CAR10', 'CAR25', 'LEN25', 'LEN25'][mt_rand(0, 6)]];
                    $items[] = ['producto_id' => $extra->id, 'cantidad' => mt_rand(1, 2), 'precio_unitario' => $extra->precio_actual];
                }
                $medio = [MedioContacto::TELEFONO, MedioContacto::WHATSAPP, MedioContacto::WHATSAPP, MedioContacto::WHATSAPP, MedioContacto::PRESENCIAL][mt_rand(0, 4)];
                $canal = $medio === MedioContacto::PRESENCIAL ? CanalVenta::MOSTRADOR : CanalVenta::DOMICILIO;
                $fecha = $dia->copy()->setTime(8 + $i % 11, [0, 15, 30, 45][mt_rand(0, 3)]);

                $pedido = $this->pedidos->crear([
                    'fecha' => $fecha, 'cliente_id' => $cliente->id, 'canal_venta_id' => CanalVenta::idDe($canal),
                    'medio_contacto_id' => MedioContacto::idDe($medio), 'items' => $items,
                    'direccion_entrega' => $canal === CanalVenta::DOMICILIO ? $cliente->domicilioCompleto() : null,
                ], $empleado);

                if ($esHoy && $i >= count($delDia) - 3) {
                    $estado = [EstadoPedido::PENDIENTE, EstadoPedido::PENDIENTE, EstadoPedido::EN_PREPARACION, EstadoPedido::EN_REPARTO][mt_rand(0, 3)];
                    $pedido->update(['estado_pedido_id' => EstadoPedido::idDe($estado)]);

                    continue;
                }
                if (mt_rand(1, 100) <= 3) {
                    $this->pedidos->cancelar($pedido);

                    continue;
                }
                if (! $this->hayStock($pedido)) {
                    $this->pedidos->cancelar($pedido);

                    continue;
                }
                $entrega = $fecha->copy()->addMinutes(mt_rand(20, 120));
                $this->pedidos->entregarAutomatico($pedido, $empleado, $entrega);

                // Cobro: la mayoría paga al recibir; algunos quedan debiendo
                $antiguedad = $dia->diffInDays(today());
                $r = mt_rand(1, 100);
                if ($antiguedad < 20 && $r <= 12) {
                    continue;
                }
                $pedido->refresh();
                $monto = ($antiguedad < 30 && $r <= 20) ? round($pedido->total / 2, -2) : $pedido->total;
                $forma = mt_rand(1, 100) <= 85 ? $cliente->forma_pago_habitual_id : $formas[mt_rand(0, 1)];
                $this->pagos->cobrar($cliente, $pedido, $monto, $forma, $forma === $formas[1] ? 'Op. '.mt_rand(10000000, 99999999) : null, $entrega->copy()->addMinutes(2));
            }

            // Pagos a proveedores de recepciones con más de una semana
            foreach (Proveedor::all() as $prov) {
                foreach ($prov->recepciones()->where('saldo', '>', 0)->where('fecha', '<=', $dia->copy()->subDays(mt_rand(0, 7)))->get() as $rec) {
                    if ($rec->fecha->diffInDays(today()) > 6 || mt_rand(1, 10) <= 3) {
                        $this->pagos->pagarProveedor($prov, $rec, $rec->saldo, $formas[mt_rand(1, 10) <= 6 ? 1 : 0], 'Transf. '.mt_rand(1000000, 9999999), $dia->copy()->setTime(17, 0));
                    }
                }
            }
        }

        // Algunos envases dañados
        foreach (Garrafa::activas()->enEstado(EstadoGarrafa::VACIA)->inRandomOrder()->limit(4)->get() as $g) {
            $this->garrafas->mover($g, TipoMovimientoGarrafa::MARCAR_NO_APTA, EstadoGarrafa::NO_APTA, ['observaciones' => 'Válvula dañada / prueba hidráulica vencida'], $empleados[0], now()->subDays(mt_rand(1, 10)));
        }
    }

    private function hayStock(Pedido $pedido): bool
    {
        foreach ($pedido->itemsVenta()->with('producto')->get() as $item) {
            if ($item->producto->stockDisponible() < $item->cantidad) {
                return false;
            }
        }

        return true;
    }

    private function recibir(Proveedor $prov, Carbon $dia, array $lineas, Empleado $empleado, $productos): void
    {
        $items = [];
        $vacias = [];
        foreach ($lineas as [$codigo, $cantidad]) {
            $p = $productos[$codigo];
            $items[] = ['producto_id' => $p->id, 'cantidad' => $cantidad, 'precio_unitario' => $p->costo_actual];
            if ($p->esGarrafa()) {
                $vacias[$p->capacidad()] = min($cantidad, Garrafa::activas()->enEstado(EstadoGarrafa::VACIA)->where('capacidad_kg', $p->capacidad())->count());
            }
        }
        $this->recepciones->registrar([
            'proveedor_id' => $prov->id, 'fecha' => $dia->copy()->setTime(9, 30),
            'numero_factura_proveedor' => 'A-0001-'.str_pad((string) mt_rand(1000, 99999999), 8, '0', STR_PAD_LEFT),
            'items' => $items, 'vacias' => $vacias,
        ], $empleado);
    }

    /** @return Empleado[] */
    private function empleados(): array
    {
        $admin = Usuario::where('username', 'admin')->first()->empleado;
        $lista = [$admin];
        foreach ([['lucia', 'Lucía', 'Fernández', '30111222'], ['martin', 'Martín', 'Ríos', '33444555']] as [$user, $nombre, $apellido, $dni]) {
            $u = Usuario::firstOrCreate(['username' => $user], ['password_hash' => 'extragas', 'rol_id' => Rol::idDe(Rol::EMPLEADO), 'activo' => true]);
            $lista[] = Empleado::firstOrCreate(['usuario_id' => $u->id], ['nombre' => $nombre, 'apellido' => $apellido, 'dni' => $dni, 'telefono' => '381 5'.mt_rand(10, 99).'-'.mt_rand(1000, 9999), 'fecha_ingreso' => '2021-06-01', 'activo' => true]);
        }

        return $lista;
    }

    /** @return Proveedor[] */
    private function proveedores(int $provincia): array
    {
        $datos = [
            ['Distribuidora GasNor S.R.L.', 'GasNor', '30-71234567-8', '381 430-1122', 'ventas@gasnor.com.ar', 'Ruta 9 km 1290', 'Tafí Viejo', 'Ing. Oscar Paz', '381 512-3344', 'Entrega martes y viernes. Alias: GASNOR.VENTAS'],
            ['Envasadora del Norte S.A.', 'EnvNorte', '30-70987654-3', '381 455-9090', 'pedidos@envnorte.com.ar', 'Parque Industrial Lote 14', 'San Miguel de Tucumán', 'Carolina Vega', '381 600-7788', 'Garrafas de 45 kg. Pago a 15 días.'],
            ['Carbonera El Quebracho', null, '20-25111222-5', '385 411-2020', null, 'Ruta 16 km 12', 'Monte Quemado', 'Ramón Quiroga', '385 411-2020', 'Contado. Alias: QUEBRACHO.CARBON'],
            ['Leñera Monte Verde', null, '20-30444555-1', '381 622-4455', null, 'Camino a Lules s/n', 'Famaillá', 'Julio Sosa', '381 622-4455', 'Leña de quebracho y algarrobo.'],
        ];

        return array_map(fn ($d) => Proveedor::create([
            'razon_social' => $d[0], 'nombre_fantasia' => $d[1], 'cuit' => $d[2], 'telefono_principal' => $d[3], 'email' => $d[4],
            'calle' => $d[5], 'ciudad' => $d[6], 'provincia_id' => $d[6] === 'Monte Quemado' ? Provincia::idDe('SE') : $provincia,
            'contacto_nombre' => $d[7], 'contacto_telefono' => $d[8], 'observaciones' => $d[9], 'activo' => true,
        ]), $datos);
    }

    /** @return array<int, array{0: Cliente, 1: string, 2: int}> */
    private function clientes(int $provincia, array $formas, Carbon $inicio): array
    {
        $nombres = [['María', 'González'], ['José', 'Rodríguez'], ['Ana', 'López'], ['Carlos', 'Martínez'], ['Laura', 'Pérez'], ['Jorge', 'Sánchez'],
            ['Silvia', 'Romero'], ['Miguel', 'Díaz'], ['Patricia', 'Álvarez'], ['Ricardo', 'Torres'], ['Graciela', 'Ruiz'], ['Diego', 'Ramírez'],
            ['Sofía', 'Flores'], ['Hugo', 'Acosta'], ['Norma', 'Benítez'], ['Pablo', 'Medina'], ['Mónica', 'Herrera'], ['Daniel', 'Suárez'],
            ['Claudia', 'Aguirre'], ['Rubén', 'Giménez'], ['Parrilla', 'Don Tito'], ['Rotisería', 'La Esquina']];
        $calles = ['Lamadrid', 'Crisóstomo Álvarez', 'San Juan', 'Mendoza', 'Córdoba', 'Santiago del Estero', 'Av. Mate de Luna', 'Av. Aconquija', 'Jujuy', 'Salta', 'Laprida', 'Congreso'];
        $ciudades = ['San Miguel de Tucumán', 'San Miguel de Tucumán', 'San Miguel de Tucumán', 'Yerba Buena', 'Tafí Viejo'];
        $clientes = [];

        foreach ($nombres as $i => [$nombre, $apellido]) {
            $comercio = $i >= 20;
            $prefiere = $comercio ? 'GAR45' : ['GAR10', 'GAR10', 'GAR10', 'GAR15', 'GAR15'][mt_rand(0, 4)];
            $c = Cliente::create([
                'codigo' => 'C'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT), 'nombre' => $nombre, 'apellido' => $apellido,
                'dni' => $comercio ? null : (string) mt_rand(18000000, 44000000), 'cuit_cuil' => $comercio ? '30-'.mt_rand(50000000, 79999999).'-'.mt_rand(0, 9) : null,
                'telefono_principal' => '381 '.mt_rand(400, 699).'-'.str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT),
                'calle' => $calles[mt_rand(0, count($calles) - 1)], 'numero' => (string) mt_rand(100, 2900), 'depto' => mt_rand(1, 10) > 8 ? mt_rand(1, 6).'B' : null,
                'ciudad' => $ciudades[mt_rand(0, 4)], 'codigo_postal' => '4000', 'provincia_id' => $provincia,
                'referencias' => ['', '', 'Portón verde', 'Casa esquina', 'Timbre 2', 'Frente a la plaza'][mt_rand(0, 5)] ?: null,
                'observaciones' => $comercio ? 'Cliente comercial, entregar por la mañana.' : null,
                'forma_pago_habitual_id' => $formas[mt_rand(1, 100) <= 55 ? 0 : 1],
                'fecha_alta' => $inicio->copy()->subDays(mt_rand(30, 800)), 'activo' => true,
            ]);
            // Envases que el cliente ya tenía antes de empezar a usar el sistema
            $cap = (int) substr($prefiere, 3);
            for ($k = 0; $k < ($comercio ? 2 : 1); $k++) {
                $this->garrafas->alta($cap, EstadoGarrafa::EN_CLIENTE, ['cliente_id' => $c->id, 'observaciones' => 'Envase en poder del cliente al iniciar'], null, $inicio->copy());
            }
            $clientes[] = [$c, $prefiere, $comercio ? mt_rand(4, 7) : mt_rand(8, 24)];
        }

        return $clientes;
    }
}

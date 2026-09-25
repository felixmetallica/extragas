<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controlador;
use App\Core\DB;
use App\Core\ErrorNegocio;
use App\Core\Pdf;
use App\Models\Catalogo;
use App\Models\Garrafa;
use App\Models\Informe;
use App\Models\Producto;
use App\Models\Proveedor;

class GarrafaController extends Controlador
{
    public function index(): string
    {
        $f = array_intersect_key($_GET, array_flip(['q', 'capacidad', 'estado']));
        $flujo = Informe::flujoGarrafas();
        $serie = fn ($codigo) => array_map(fn ($c) => $flujo[$codigo][$c] ?? 0, Garrafa::CAPACIDADES);

        return $this->vista('garrafas/index', [
            'titulo' => 'Garrafas', 'migas' => ['Depósito' => null, 'Garrafas' => null],
            'stock' => Garrafa::stock(), 'garrafas' => Garrafa::listar($f, $pag), 'pag' => $pag,
            'movimientos' => Garrafa::movimientos(null, (int) ($_GET['tipo'] ?? 0) ?: null, $pagMov), 'pagMov' => $pagMov,
            'productos' => Producto::garrafasPorCapacidad(),
            'estados' => Catalogo::todos('estados_garrafa'), 'tipos' => Catalogo::opciones('tipos_movimiento_garrafa'),
            'grafico' => ['type' => 'bar', 'data' => ['labels' => array_map(fn ($c) => "{$c} kg", Garrafa::CAPACIDADES), 'datasets' => [
                ['label' => 'Entregadas a clientes', 'data' => $serie('ENTREGA_CLIENTE'), 'backgroundColor' => '#e8590c', 'borderRadius' => 4],
                ['label' => 'Altas (recepciones)', 'data' => $serie('ALTA'), 'backgroundColor' => '#1971c2', 'borderRadius' => 4],
                ['label' => 'Vacías devueltas al proveedor', 'data' => $serie('ENTREGA_PROVEEDOR'), 'backgroundColor' => '#adb5bd', 'borderRadius' => 4],
            ]], 'options' => ['plugins' => ['legend' => ['position' => 'bottom']], 'scales' => ['x' => ['grid' => ['display' => false]]]]],
        ]);
    }

    public function nueva(): string
    {
        return $this->vista('garrafas/nueva', [
            'titulo' => 'Alta de garrafas', 'migas' => ['Depósito' => null, 'Garrafas' => url('garrafas'), 'Alta' => null],
            'proveedores' => Proveedor::opciones(),
        ]);
    }

    public function guardar(): never
    {
        $d = $this->validar(['capacidad_kg' => 'requerido|en:10,15,45', 'cantidad' => 'requerido|entero|min:1|max:200', 'estado' => 'requerido|en:LLENA,VACIA',
            'codigos' => 'texto:5000', 'proveedor_id' => 'existe:proveedores', 'observaciones' => 'texto:500'], ['capacidad_kg' => 'capacidad']);
        $codigos = Garrafa::codigosDeTexto($d['codigos']);
        if ($codigos && count($codigos) !== (int) $d['cantidad']) {
            throw new ErrorNegocio('Ingresaste '.count($codigos)." códigos para {$d['cantidad']} garrafas.");
        }
        foreach ($codigos as $c) {
            if (Garrafa::codigoExiste($c)) {
                throw new ErrorNegocio("El código {$c} ya existe.");
            }
        }
        $empleado = Auth::empleadoId();
        DB::transaccion(function () use ($d, $codigos, $empleado) {
            for ($i = 0; $i < (int) $d['cantidad']; $i++) {
                Garrafa::darDeAlta((int) $d['capacidad_kg'], $d['estado'], ['codigo' => $codigos[$i] ?? null, 'proveedor_id' => $d['proveedor_id'],
                    'empleado_id' => $empleado, 'observaciones' => $d['observaciones'] ?: 'Alta manual']);
            }
        });
        $this->exito('garrafas', "Se dieron de alta {$d['cantidad']} garrafa(s) de {$d['capacidad_kg']} kg.");
    }

    public function ver(int $id): string
    {
        $g = $this->noEncontrado(Garrafa::buscar($id));

        return $this->vista('garrafas/ver', [
            'titulo' => "Garrafa {$g['codigo']}", 'migas' => ['Depósito' => null, 'Garrafas' => url('garrafas'), $g['codigo'] => null],
            'garrafa' => $g, 'movimientos' => Garrafa::movimientos($id),
            'posibles' => array_filter(Garrafa::MOVIMIENTOS_MANUALES, fn ($m) => in_array($g['estado_codigo'], $m['desde'], true)),
            'tipos' => Catalogo::todos('tipos_movimiento_garrafa'), 'estados' => Catalogo::todos('estados_garrafa'),
        ]);
    }

    public function movimiento(int $id): never
    {
        $g = $this->noEncontrado(Garrafa::buscar($id));
        $d = $this->validar(['tipo' => 'requerido|en:MARCAR_NO_APTA,REPARACION,BAJA,AJUSTE', 'estado_ajuste' => 'en:LLENA,VACIA,NO_APTA', 'observaciones' => 'texto:500']);
        if ($d['tipo'] === 'AJUSTE') {
            if (! Auth::esAdmin()) {
                throw new ErrorNegocio('Sólo el administrador puede ajustar el estado.');
            }
            $destino = $d['estado_ajuste'] ?? throw new ErrorNegocio('Indicá el estado correcto de la garrafa.');
        } else {
            $regla = Garrafa::MOVIMIENTOS_MANUALES[$d['tipo']];
            if (! in_array($g['estado_codigo'], $regla['desde'], true)) {
                throw new ErrorNegocio('Ese movimiento no corresponde al estado actual de la garrafa.');
            }
            $destino = $regla['hacia'];
        }
        Garrafa::mover($g, $d['tipo'], $destino, ['observaciones' => $d['observaciones'], 'empleado_id' => Auth::empleadoId()]);
        $this->exito("garrafas/{$id}", 'Movimiento registrado.');
    }

    public function informe(): never
    {
        Pdf::informe('Stock de garrafas', 'Al '.fecha(date('Y-m-d H:i'), true), self::seccionesStock(), 'stock-garrafas');
    }

    /** Secciones del informe de stock (también lo usa Informes) */
    public static function seccionesStock(): array
    {
        $filas = [];
        foreach (Garrafa::stock() as $cap => $e) {
            $filas[] = ["{$cap} kg", $e['LLENA'], $e['VACIA'], $e['NO_APTA'], $e['LLENA'] + $e['VACIA'] + $e['NO_APTA'], $e['EN_CLIENTE'], array_sum($e)];
        }

        return [
            ['titulo' => 'Stock por capacidad', 'cabecera' => ['Capacidad', 'Llenas', 'Vacías aptas', 'No aptas', 'En depósito', 'En clientes', 'Parque total'], 'filas' => $filas, 'derecha' => [1, 2, 3, 4, 5, 6]],
            ['titulo' => 'Garrafas en poder de clientes', 'cabecera' => ['Cliente', 'Código', 'Capacidad', 'Desde', 'Días'],
                'filas' => array_map(fn ($g) => [$g['cliente'], $g['codigo'], "{$g['capacidad_kg']} kg", fecha($g['fecha_ultimo_movimiento']), $g['dias_en_cliente']], Informe::garrafasEnClientes()), 'derecha' => [4]],
        ];
    }
}

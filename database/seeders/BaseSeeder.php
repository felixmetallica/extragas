<?php

namespace Database\Seeders;

use App\Models\Catalogos\Rol;
use App\Models\Catalogos\TipoProducto;
use App\Models\ConfiguracionEmpresa;
use App\Models\Empleado;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

/**
 * Datos iniciales mínimos: productos, empresa y el usuario administrador.
 */
class BaseSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            ['GAR10', 'Garrafa 10 kg', 'GAS', 10, 16500, 12800, 15, true],
            ['GAR15', 'Garrafa 15 kg', 'GAS', 15, 24000, 18900, 10, true],
            ['GAR45', 'Garrafa 45 kg', 'GAS', 45, 72000, 58500, 4, true],
            ['CAR03', 'Carbón 3 kg', 'CARBON', 3, 4200, 2600, 15, false],
            ['CAR05', 'Carbón 5 kg', 'CARBON', 5, 6500, 4100, 15, false],
            ['CAR10', 'Carbón 10 kg', 'CARBON', 10, 12000, 7800, 10, false],
            ['CAR25', 'Carbón 25 kg', 'CARBON', 25, 27500, 18000, 4, false],
            ['LEN25', 'Leña para hogar 25 kg', 'LENA', 25, 11500, 7000, 10, false],
        ];
        foreach ($productos as [$codigo, $nombre, $tipo, $kg, $precio, $costo, $min, $garrafa]) {
            Producto::updateOrCreate(['codigo' => $codigo], [
                'nombre' => $nombre, 'tipo_producto_id' => TipoProducto::idDe($tipo), 'capacidad_kg' => $kg,
                'unidad_venta' => $garrafa ? 'GARRAFA' : 'BOLSA', 'precio_actual' => $precio, 'costo_actual' => $costo,
                'stock_minimo' => $min, 'maneja_garrafa_individual' => $garrafa, 'activo' => true,
            ]);
        }

        if (! ConfiguracionEmpresa::query()->exists()) {
            ConfiguracionEmpresa::create([
                'nombre' => 'ExtraGas', 'razon_social' => 'ExtraGas — Venta de gas envasado, carbón y leña', 'cuit' => '20-28456123-7',
                'direccion' => 'Av. Belgrano 1450', 'localidad' => 'San Miguel de Tucumán', 'telefono' => '381 421-5566',
                'whatsapp' => '381 555-1020', 'email' => 'contacto@extragas.com.ar', 'horario' => 'Lun a Sáb de 8 a 20 hs',
                'dias_tolerancia_regularidad' => 3,
            ]);
        }

        $admin = Usuario::firstOrCreate(['username' => 'admin'], [
            'password_hash' => 'admin123', 'email' => 'admin@extragas.com.ar', 'rol_id' => Rol::idDe(Rol::ADMIN), 'activo' => true,
        ]);
        Empleado::firstOrCreate(['usuario_id' => $admin->id], ['nombre' => 'Roberto', 'apellido' => 'Medina', 'fecha_ingreso' => '2015-03-01', 'activo' => true]);
    }
}

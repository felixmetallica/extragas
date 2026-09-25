<?php

namespace Database\Seeders;

use App\Models\Catalogos\Catalogo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Valores de las tablas de catálogo. Son los códigos que usa la aplicación,
 * por eso se insertan con upsert (se puede ejecutar más de una vez).
 */
class CatalogosSeeder extends Seeder
{
    public function run(): void
    {
        $this->cargar('roles', [
            ['codigo' => 'ADMIN', 'nombre' => 'Administrador', 'descripcion' => 'Dueño: acceso total, precios, usuarios y configuración'],
            ['codigo' => 'EMPLEADO', 'nombre' => 'Empleado', 'descripcion' => 'Atención de pedidos, cobros, garrafas y recepciones'],
        ]);

        $this->cargar('canales_venta', [
            ['codigo' => 'DOMICILIO', 'nombre' => 'Envío a domicilio', 'descripcion' => 'Se entrega en el domicilio del cliente'],
            ['codigo' => 'RETIRO_LOCAL', 'nombre' => 'Retira en el local', 'descripcion' => 'El cliente pasa a retirar el pedido'],
            ['codigo' => 'MOSTRADOR', 'nombre' => 'Venta en mostrador', 'descripcion' => 'Venta y entrega inmediata en el local'],
        ]);

        $this->cargar('medios_contacto_pedido', [
            ['codigo' => 'TELEFONO', 'nombre' => 'Teléfono', 'descripcion' => 'Llamada telefónica'],
            ['codigo' => 'WHATSAPP', 'nombre' => 'WhatsApp', 'descripcion' => 'Mensaje de WhatsApp'],
            ['codigo' => 'PRESENCIAL', 'nombre' => 'En el local', 'descripcion' => 'El cliente concurre al establecimiento'],
            ['codigo' => 'OTRO', 'nombre' => 'Otro', 'descripcion' => 'Redes sociales u otro medio'],
        ]);

        $this->cargar('estados_pedido', [
            ['codigo' => 'PENDIENTE', 'nombre' => 'Pendiente', 'es_final' => false, 'color' => '#fab005'],
            ['codigo' => 'EN_PREPARACION', 'nombre' => 'En preparación', 'es_final' => false, 'color' => '#228be6'],
            ['codigo' => 'EN_REPARTO', 'nombre' => 'En reparto', 'es_final' => false, 'color' => '#7950f2'],
            ['codigo' => 'ENTREGADO', 'nombre' => 'Entregado', 'es_final' => true, 'color' => '#40c057'],
            ['codigo' => 'CANCELADO', 'nombre' => 'Cancelado', 'es_final' => true, 'color' => '#868e96'],
        ]);

        $this->cargar('estados_garrafa', [
            ['codigo' => 'LLENA', 'nombre' => 'Llena', 'descripcion' => 'Llena en depósito, lista para la venta', 'es_disponible_para_venta' => true, 'requiere_cliente' => false, 'color' => '#40c057'],
            ['codigo' => 'VACIA', 'nombre' => 'Vacía apta', 'descripcion' => 'Vacía en depósito, apta para intercambio', 'es_disponible_para_venta' => false, 'requiere_cliente' => false, 'color' => '#4dabf7'],
            ['codigo' => 'EN_CLIENTE', 'nombre' => 'En cliente', 'descripcion' => 'En poder de un cliente', 'es_disponible_para_venta' => false, 'requiere_cliente' => true, 'color' => '#9775fa'],
            ['codigo' => 'NO_APTA', 'nombre' => 'No apta', 'descripcion' => 'Dañada o con prueba hidráulica vencida', 'es_disponible_para_venta' => false, 'requiere_cliente' => false, 'color' => '#fa5252'],
            ['codigo' => 'EN_PROVEEDOR', 'nombre' => 'Entregada al proveedor', 'descripcion' => 'Entregada vacía al proveedor en un intercambio', 'es_disponible_para_venta' => false, 'requiere_cliente' => false, 'color' => '#adb5bd'],
            ['codigo' => 'BAJA', 'nombre' => 'Baja', 'descripcion' => 'Descartada definitivamente', 'es_disponible_para_venta' => false, 'requiere_cliente' => false, 'color' => '#495057'],
        ]);

        $this->cargar('formas_pago', [
            ['codigo' => 'EFECTIVO', 'nombre' => 'Efectivo', 'requiere_referencia' => false],
            ['codigo' => 'TRANSFERENCIA', 'nombre' => 'Transferencia', 'requiere_referencia' => true],
            ['codigo' => 'MERCADO_PAGO', 'nombre' => 'Mercado Pago / QR', 'requiere_referencia' => true],
            ['codigo' => 'DEBITO', 'nombre' => 'Tarjeta de débito', 'requiere_referencia' => false],
        ]);

        $this->cargar('tipos_contacto_cliente', [
            ['codigo' => 'CELULAR', 'nombre' => 'Celular'],
            ['codigo' => 'TELEFONO_FIJO', 'nombre' => 'Teléfono fijo'],
            ['codigo' => 'WHATSAPP', 'nombre' => 'WhatsApp'],
            ['codigo' => 'EMAIL', 'nombre' => 'Email'],
        ]);

        $this->cargar('tipos_movimiento_garrafa', [
            ['codigo' => 'ALTA', 'nombre' => 'Alta de envase', 'descripcion' => 'Ingreso de una garrafa al parque'],
            ['codigo' => 'ENTREGA_CLIENTE', 'nombre' => 'Entrega a cliente', 'descripcion' => 'Garrafa llena entregada en un pedido'],
            ['codigo' => 'DEVOLUCION_CLIENTE', 'nombre' => 'Devolución de cliente', 'descripcion' => 'Envase vacío recibido de un cliente'],
            ['codigo' => 'ENTREGA_PROVEEDOR', 'nombre' => 'Entrega a proveedor', 'descripcion' => 'Envase vacío entregado al proveedor'],
            ['codigo' => 'MARCAR_NO_APTA', 'nombre' => 'Marcada no apta', 'descripcion' => 'El envase no está en condiciones'],
            ['codigo' => 'REPARACION', 'nombre' => 'Reparación', 'descripcion' => 'El envase vuelve a estar apto'],
            ['codigo' => 'BAJA', 'nombre' => 'Baja', 'descripcion' => 'Descarte definitivo'],
            ['codigo' => 'AJUSTE', 'nombre' => 'Ajuste', 'descripcion' => 'Corrección por inventario'],
        ]);

        $this->cargar('tipos_producto', [
            ['codigo' => 'GAS', 'nombre' => 'Gas envasado', 'descripcion' => 'Garrafas de 10, 15 y 45 kg'],
            ['codigo' => 'CARBON', 'nombre' => 'Carbón', 'descripcion' => 'Bolsas de 3, 5, 10 y 25 kg'],
            ['codigo' => 'LENA', 'nombre' => 'Leña', 'descripcion' => 'Leña para hogar en bolsa de 25 kg'],
        ]);

        $provincias = ['CABA' => 'Ciudad Autónoma de Buenos Aires', 'BA' => 'Buenos Aires', 'CAT' => 'Catamarca', 'CHA' => 'Chaco', 'CHU' => 'Chubut',
            'COR' => 'Córdoba', 'CRR' => 'Corrientes', 'ER' => 'Entre Ríos', 'FOR' => 'Formosa', 'JUJ' => 'Jujuy', 'LP' => 'La Pampa', 'LR' => 'La Rioja',
            'MZA' => 'Mendoza', 'MIS' => 'Misiones', 'NQN' => 'Neuquén', 'RN' => 'Río Negro', 'SAL' => 'Salta', 'SJ' => 'San Juan', 'SL' => 'San Luis',
            'SC' => 'Santa Cruz', 'SF' => 'Santa Fe', 'SE' => 'Santiago del Estero', 'TF' => 'Tierra del Fuego', 'TUC' => 'Tucumán'];
        $this->cargar('provincias', collect($provincias)->map(fn ($n, $c) => ['codigo' => $c, 'nombre' => $n])->values()->all());

        Catalogo::limpiarCache();
    }

    private function cargar(string $tabla, array $filas): void
    {
        $columnas = array_keys(array_merge(...array_map(fn ($f) => $f, $filas)));
        $filas = array_map(fn ($f) => array_merge(array_fill_keys($columnas, null), $f), $filas);
        DB::table($tabla)->upsert($filas, ['codigo'], array_diff($columnas, ['codigo']));
    }
}

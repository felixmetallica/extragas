# ExtraGas · Sistema de Gestión de Pedidos

Sistema web para una empresa familiar de venta de **gas envasado** (garrafas de 10, 15 y 45 kg), **carbón** (bolsas de 3, 5, 10 y 25 kg) y **leña** (bolsa de 25 kg).

- **PHP 8.3+ · Laravel 13 · MySQL 8 / MariaDB 10.6+**
- Interfaz Blade + Bootstrap 5 (incluido en `public/vendor`, funciona sin internet)
- PDF con `barryvdh/laravel-dompdf` · gráficos con Chart.js

La facturación **no** forma parte del sistema: se emite en la web de ARCA (los PDF lo aclaran).

## Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Crear la base de datos (MySQL) y completar `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en `.env`:

```sql
CREATE DATABASE extragas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan migrate --seed          # estructura + catálogos + productos + usuario admin
php artisan db:seed --class=DemoSeeder   # (opcional) 90 días de datos de ejemplo
php artisan serve
```

Ingresar en http://localhost:8000 con **admin / admin123** (cambiar la contraseña en Sistema › Usuarios).
Con los datos de ejemplo también existen los empleados **lucia** y **martin** (contraseña `extragas`).

> Las migraciones crean **triggers** y **vistas**. Si MySQL tiene el binlog activo y el usuario no es SUPER,
> habilitar `log_bin_trust_function_creators = 1` antes de migrar.

## Base de datos

Las migraciones reproducen exactamente `extragas.sql` (mismas tablas, columnas, columnas generadas `saldo` / `subtotal`,
12 triggers y 10 vistas `v_*`). Sobre ese esquema se agregó:

| Cambio | Motivo |
|---|---|
| `clientes.forma_pago_habitual_id` | El relevamiento pide conocer la forma de pago habitual de cada cliente |
| `productos.costo_actual`, `stock_actual`, `stock_minimo` | Carbón y leña no se controlan por unidad individual; alertas de stock y márgenes |
| tabla `configuracion_empresa` | Datos de la empresa para los encabezados de los PDF |
| Vistas `v_saldo_clientes`, `v_regularidad_clientes`, `v_cuenta_corriente_cliente`, `v_productos_mas_vendidos` excluyen pedidos **cancelados** | En el esquema original un pedido cancelado seguía figurando como deuda y como venta |

Lo que hace la base (y la aplicación aprovecha):

- Numeración automática: `PED-2026-00001`, `REC-2026-00001` (recibos), `REC-PROV-…`, `PAG-PROV-…` (tabla `secuencias`).
- `monto_pagado` de pedidos y recepciones se recalcula al registrar o anular pagos.
- El estado de cada garrafa se actualiza al registrar un movimiento (`movimientos_garrafa`).

## Cómo funciona el control de garrafas

Cada garrafa tiene un código (`G10-00001`, o el que se ingrese) y un estado: **Llena**, **Vacía apta**, **En cliente**, **No apta**,
**Entregada al proveedor** o **Baja**.

1. **Recepción de proveedor**: las garrafas llenas que llegan se dan de alta; las vacías que se lleva el proveedor salen del parque.
2. **Pedido**: se cargan los productos (líneas `VENTA`). Al **confirmar la entrega** se eligen las garrafas llenas que salen
   (línea `ENTREGA`) y las vacías que devuelve el cliente (línea `DEVOLUCION`), marcando si alguna vuelve no apta.
   Si el cliente entrega un envase que no estaba registrado, se da de alta como vacía.
3. Desde **Garrafas** se ve el stock por capacidad y estado, el historial de cada envase y se registran movimientos manuales
   (marcar no apta, reparación, baja, ajuste).

## Módulos

| Módulo | Contenido |
|---|---|
| Inicio | Pedidos en curso, cobrado hoy, deuda, ventas del mes, garrafas, clientes que deberían pedir, stock bajo |
| Pedidos | Alta (teléfono / WhatsApp / en el local), estados, entrega con intercambio de envases, PDF |
| Clientes | Datos, contactos adicionales, forma de pago habitual, garrafas en su poder, regularidad, cuenta corriente, estado de cuenta PDF |
| Cobros | Pagos (efectivo, transferencia, etc.), recibo PDF, pendientes de cobro, saldos por cliente, anulación (admin) |
| Garrafas | Stock por estado, listado y ficha de cada garrafa, altas y movimientos, informe PDF |
| Productos y precios | Precios, costos, márgenes, stock de carbón y leña, aumento masivo por porcentaje (admin) |
| Proveedores / Recepciones / Pagos a proveedores | Datos, recepción de mercadería, pagos y saldos |
| Informes | Pedidos, productos más vendidos, regularidad de pedidos, gestión de pagos, stock de garrafas (pantalla y PDF) |
| Sistema (admin) | Usuarios y roles, empleados, datos de la empresa, formas de pago habilitadas |

## Estructura

```
app/Models/                 modelos Eloquent (Catalogos/ para las tablas de códigos)
app/Services/               lógica de negocio: PedidoService, GarrafaService, PagoService, RecepcionService
app/Http/Controllers/       un controlador por módulo
database/migrations/        esquema, triggers, vistas y agregados
database/seeders/           catálogos, datos iniciales y DemoSeeder
resources/views/            vistas Blade (pdf/ para los comprobantes)
public/css, public/js       estilos y scripts propios
prototipo/                  maqueta HTML original (referencia de diseño)
```

## Pruebas

Las pruebas usan MySQL porque dependen de los triggers y las vistas. Crear la base `extragas_test` y ejecutar:

```bash
php artisan test
```

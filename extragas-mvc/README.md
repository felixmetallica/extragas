# ExtraGas · Sistema de Gestión de Pedidos (PHP MVC)

Sistema web para una empresa familiar de venta de **gas envasado** (garrafas de 10, 15 y 45 kg), **carbón**
(bolsas de 3, 5, 10 y 25 kg) y **leña** (bolsa de 25 kg).

- **PHP 8.1 o superior, sin frameworks**, con arquitectura **Modelo – Vista – Controlador**
- **MySQL 8** o **MariaDB 10.6+** (XAMPP, WAMP, Laragon o un hosting con PHP y MySQL)
- Interfaz con Bootstrap 5 y gráficos con Chart.js, incluidos en `assets/vendor`, así que funciona sin internet
- PDF con **FPDF**, incluida en `lib/fpdf` (es una librería de un solo archivo, no un framework)
- No requiere Composer ni ninguna instalación adicional

La facturación **no** forma parte del sistema: se emite en la web de ARCA, y los PDF lo aclaran.

## Instalación (XAMPP)

1. Copiar la carpeta `extragas-mvc` dentro de `C:\xampp\htdocs\` (puede renombrarse, por ejemplo a `extragas`).
2. Iniciar **Apache** y **MySQL** desde el panel de XAMPP.
3. Importar la base de datos en phpMyAdmin (http://localhost/phpmyadmin → pestaña **Importar**) eligiendo uno de estos archivos:
   - `database/extragas.sql`: base lista para usar, **sin registros de ejemplo**.
   - `database/extragas_datos_ejemplo.sql`: la misma base con **90 días de datos de ejemplo**.

   Los dos archivos crean la base `extragas` automáticamente.
4. Si MySQL tiene contraseña o la base tiene otro nombre, editar `config/config.php`.
5. Abrir http://localhost/extragas-mvc/ e ingresar con **admin / admin123**.
   Con los datos de ejemplo también existen los empleados **lucia** y **martin** (contraseña `extragas`).

> Si Apache no tiene `mod_rewrite` activo, poner `'url_amigables' => false` en `config/config.php`.
> Las direcciones pasan a ser del tipo `/index.php/pedidos`.

Sin XAMPP, desde la carpeta del proyecto se puede usar el servidor integrado de PHP:

```bash
php -S localhost:8000 index.php
```

> Si una importación anterior falló a mitad de camino, borrar la base `extragas` en phpMyAdmin
> (pestaña Operaciones → Eliminar la base de datos) antes de volver a importar.
>
> Si al importar aparece un error al crear los triggers, ejecutar antes
> `SET GLOBAL log_bin_trust_function_creators = 1;` con el usuario root.

## Qué incluye cada archivo SQL

| | `extragas.sql` | `extragas_datos_ejemplo.sql` |
|---|---|---|
| 26 tablas, 12 triggers, 10 vistas | ✔ | ✔ |
| Catálogos (estados, formas de pago, canales, medios de contacto, tipos, 24 provincias) | ✔ | ✔ |
| 8 productos con precios, datos de la empresa, usuario **admin** | ✔ | ✔ |
| Clientes, pedidos, garrafas, cobros, proveedores, recepciones | — | ✔ (22 clientes, ~150 pedidos, ~270 garrafas) |

La estructura respeta el `extragas.sql` original (mismas tablas, columnas, columnas generadas, triggers y vistas).
Se quitaron las tablas propias de Laravel (`users`, `sessions`, `cache`, `jobs`, `migrations`) y se agregó:

- `clientes.forma_pago_habitual_id`: la forma de pago habitual de cada cliente.
- `productos.costo_actual`, `stock_actual` y `stock_minimo`: stock de carbón y leña, alertas y márgenes.
- La tabla `configuracion_empresa`: datos de la empresa para los PDF.
- En las vistas de saldos, regularidad, cuenta corriente y productos vendidos, los pedidos **cancelados** quedan excluidos.

## Arquitectura MVC

```
index.php                  Controlador frontal: toda petición entra por aquí
.htaccess                  Redirige las URLs a index.php (mod_rewrite)
config/config.php          Conexión a la base y opciones
app/
  bootstrap.php            Autoload de clases, sesión, zona horaria
  rutas.php                Tabla de rutas: método + URL → Controlador@acción
  helpers.php              Formato ($, fechas), URLs, escape HTML, etiquetas
  Core/                    Núcleo del MVC (hecho a mano)
    App.php                  Despachador: resuelve la ruta, controla login, permisos y CSRF
    Controlador.php          Clase base de los controladores
    DB.php                   Acceso a MySQL con PDO y consultas preparadas
    Vista.php                Motor de vistas (layout + secciones)
    Auth.php, Sesion.php     Login, mensajes flash, token CSRF
    Validador.php            Validación de formularios
    Paginador.php, Pdf.php   Paginación y documentos PDF (FPDF)
  Models/                  MODELO: acceso a datos y reglas de negocio
    Pedido.php               Alta, estados, entrega con intercambio de envases
    Garrafa.php              Stock por estado, altas, movimientos, códigos
    Pago.php, PagoProveedor.php, Recepcion.php, Cliente.php, Proveedor.php, Producto.php, ...
  Controllers/             CONTROLADOR: recibe la petición, valida y elige la vista
  Views/                   VISTA: plantillas PHP (layouts/, partials/, un directorio por módulo)
assets/                    CSS, JS, imágenes y librerías del navegador
lib/fpdf/                  Librería FPDF
database/                  Archivos SQL
docs/modelo-dominio.md     Modelo de dominio (entidades, relaciones, estados y reglas)
```

Flujo de una petición: `index.php` → `App::ejecutar()` busca la ruta en `rutas.php` → comprueba la sesión y el rol →
ejecuta el método del **controlador** → el controlador usa los **modelos** → devuelve una **vista** que se muestra dentro de `Views/layouts/app.php`.

## Módulos

| Módulo | Contenido |
|---|---|
| Inicio | Pedidos en curso, cobrado hoy, deuda, ventas del mes, garrafas, clientes que deberían pedir, stock bajo |
| Pedidos | Alta (teléfono / WhatsApp / en el local), estados, entrega con intercambio de envases, PDF |
| Clientes | Datos, contactos, forma de pago habitual, garrafas en su poder, regularidad, cuenta corriente, estado de cuenta en PDF |
| Cobros | Pagos (efectivo, transferencia, etc.), recibo PDF, pendientes de cobro, saldos, anulación (admin) |
| Garrafas | Stock por estado, ficha e historial de cada garrafa, altas y movimientos, informe PDF |
| Productos y precios | Precios, costos, márgenes, stock de carbón y leña, aumento por porcentaje (admin) |
| Proveedores / Recepciones / Pagos a proveedores | Datos, recepción de mercadería con intercambio de envases, pagos y saldos |
| Informes | Pedidos, productos más vendidos, regularidad de pedidos, gestión de pagos y stock de garrafas (en pantalla y PDF) |
| Sistema (admin) | Usuarios y roles, empleados, datos de la empresa, formas de pago habilitadas |

## Control de garrafas

Cada garrafa tiene un código (`G10-00001` o el que se cargue) y un estado: **Llena**, **Vacía apta**, **En cliente**,
**No apta**, **Entregada al proveedor** o **Baja**.

1. **Recepción de proveedor**: las garrafas llenas que llegan se dan de alta, y las vacías que se lleva el proveedor salen del parque.
2. **Pedido**: se cargan los productos (líneas `VENTA`). Al **confirmar la entrega** se eligen las garrafas llenas que salen
   (línea `ENTREGA`) y las vacías que devuelve el cliente (línea `DEVOLUCION`).
3. El estado de cada garrafa lo actualiza el trigger `trg_mov_garrafa_ai` al registrar cada movimiento.

## Seguridad

- Contraseñas con `password_hash` (bcrypt).
- Todas las consultas usan parámetros preparados (PDO), para evitar inyección SQL.
- Token CSRF en todos los formularios.
- Todo texto que se muestra pasa por `htmlspecialchars`.
- Las secciones de administración verifican el rol en el servidor.
- Las carpetas `app`, `config`, `database` y `lib` están bloqueadas para el navegador mediante `.htaccess`.

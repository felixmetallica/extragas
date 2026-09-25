# ExtraGas · Gestión de Pedidos

Interfaz visual (prototipo funcional) del sistema de gestión de pedidos para una empresa familiar de venta de **gas envasado** (garrafas de 10, 15 y 45 kg), **carbón** (bolsas de 3, 5, 10 y 25 kg) y **leña** (bolsa de 25 kg).

## Cómo abrirlo

No requiere instalación ni internet (las librerías están en `assets/vendor`).

```bash
python3 -m http.server 8000
# abrir http://localhost:8000/login.html  (usuario: admin, lucia o martin; cualquier contraseña)
```

También se puede abrir `index.html` directamente en el navegador.

## Módulos

| Módulo | Ruta | Qué incluye |
|---|---|---|
| Ingreso | `login.html` | Acceso por usuario; los empleados no ven Usuarios ni Configuración |
| Inicio | `#/inicio` | Pedidos en curso, cobrado hoy, deuda, ventas del mes, garrafas en depósito, clientes que suelen pedir, stock bajo |
| Pedidos | `#/pedidos` | Listado con filtros (fechas, estado, canal, pago), alta rápida (teléfono / WhatsApp / local), intercambio de envases, estados Pendiente → En preparación → En reparto → Entregado, PDF del pedido |
| Clientes | `#/clientes` | Domicilio, celular (link a WhatsApp), forma de pago habitual, garrafas en su poder, regularidad, cuenta corriente, estado de cuenta en PDF |
| Cobros | `#/cobros` | Pagos en efectivo / transferencia, recibo PDF, pedidos pendientes de cobro, saldos por cliente |
| Garrafas | `#/garrafas` | Llenas, vacías aptas, no aptas y en clientes por tipo; movimientos, ajuste por inventario |
| Productos y precios | `#/productos` | Catálogo, precios, costos y márgenes, stock de carbón y leña, aumento masivo de precios |
| Proveedores | `#/proveedores` | Datos, CBU/alias, condición de pago, recepciones y pagos |
| Recepciones | `#/recepciones` | Ingreso de garrafas llenas (y vacías entregadas), carbón y leña, con impacto en el stock |
| Pagos a proveedores | `#/pagos-proveedores` | Registro de pagos y saldos adeudados |
| Informes | `#/informes` | Pedidos de clientes, productos más vendidos, regularidad de pedidos, gestión de pagos, stock de garrafas; todos en PDF |
| Usuarios / Configuración | `#/usuarios`, `#/configuracion` | Dueño y empleados, datos de la empresa para los PDF, parámetros, copia de seguridad |

La facturación **no** forma parte del sistema (se hace en la web de ARCA); los PDF lo aclaran.

## Estructura

```
index.html, login.html
assets/css/app.css          estilos
assets/js/data.js           modelo de datos, datos de demostración, consultas (Q) y operaciones de stock (Ops)
assets/js/ui.js             tablas, modales, avisos, formatos
assets/js/pdf.js            PDF de pedidos, recibos e informes (jsPDF)
assets/js/app.js            enrutador
assets/js/modules/*.js      un archivo por módulo
```

Por ahora los datos se guardan en el navegador (localStorage) con datos de ejemplo. Para conectarlo a la base de datos alcanza con reemplazar `Store`/`Q`/`Ops` en `data.js` por llamadas al backend: las colecciones (`clientes`, `productos`, `envases`, `pedidos` + ítems, `pagos`, `proveedores`, `recepciones` + ítems, `pagosProveedores`, `movEnvases`, `usuarios`) equivalen a las tablas.

/*
 * Capa de datos del prototipo.
 * Mientras no exista backend, los datos viven en localStorage y se generan
 * datos de demostración. Cada colección se corresponde con una tabla de la BD;
 * al conectar el backend alcanza con reemplazar Store por llamadas a la API.
 */

function localISO(d) {
  const z = n => String(n).padStart(2, '0');
  return `${d.getFullYear()}-${z(d.getMonth() + 1)}-${z(d.getDate())}`;
}
function addDays(iso, n) {
  const [y, m, d] = iso.split('-').map(Number);
  return localISO(new Date(y, m - 1, d + n));
}
function daysBetween(a, b) {
  const [y1, m1, d1] = a.split('-').map(Number);
  const [y2, m2, d2] = b.split('-').map(Number);
  return Math.round((new Date(y2, m2 - 1, d2) - new Date(y1, m1 - 1, d1)) / 86400000);
}

const CATEGORIAS = { gas: 'Gas envasado', carbon: 'Carbón', lena: 'Leña' };
const ESTADOS_PEDIDO = ['Pendiente', 'En preparación', 'En reparto', 'Entregado', 'Cancelado'];
const CANALES = ['Teléfono', 'WhatsApp', 'Local'];
const FORMAS_PAGO = ['Efectivo', 'Transferencia'];

function seedDB() {
  let s = 20260924;
  const rnd = () => { s |= 0; s = s + 0x6D2B79F5 | 0; let t = Math.imul(s ^ s >>> 15, 1 | s); t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t; return ((t ^ t >>> 14) >>> 0) / 4294967296; };
  const pick = a => a[Math.floor(rnd() * a.length)];
  const int = (a, b) => a + Math.floor(rnd() * (b - a + 1));
  const hoy = localISO(new Date());

  const empresa = {
    nombre: 'ExtraGas', razonSocial: 'ExtraGas — Venta de gas envasado, carbón y leña',
    cuit: '20-28456123-7', direccion: 'Av. Belgrano 1450', localidad: 'San Miguel de Tucumán',
    telefono: '381 421-5566', whatsapp: '381 555-1020', email: 'contacto@extragas.com.ar',
    horario: 'Lun a Sáb de 8 a 20 hs',
  };
  const parametros = { toleranciaDias: 3, pedidoMinimoDelivery: 0 };

  const productos = [
    { id: 1, categoria: 'gas', nombre: 'Garrafa 10 kg', pesoKg: 10, precio: 16500, costo: 12800, envase: true, stockMin: 15, activo: true },
    { id: 2, categoria: 'gas', nombre: 'Garrafa 15 kg', pesoKg: 15, precio: 24000, costo: 18900, envase: true, stockMin: 10, activo: true },
    { id: 3, categoria: 'gas', nombre: 'Garrafa 45 kg', pesoKg: 45, precio: 72000, costo: 58500, envase: true, stockMin: 4, activo: true },
    { id: 4, categoria: 'carbon', nombre: 'Carbón 3 kg', pesoKg: 3, precio: 4200, costo: 2600, envase: false, stock: 38, stockMin: 15, activo: true },
    { id: 5, categoria: 'carbon', nombre: 'Carbón 5 kg', pesoKg: 5, precio: 6500, costo: 4100, envase: false, stock: 27, stockMin: 15, activo: true },
    { id: 6, categoria: 'carbon', nombre: 'Carbón 10 kg', pesoKg: 10, precio: 12000, costo: 7800, envase: false, stock: 9, stockMin: 10, activo: true },
    { id: 7, categoria: 'carbon', nombre: 'Carbón 25 kg', pesoKg: 25, precio: 27500, costo: 18000, envase: false, stock: 6, stockMin: 4, activo: true },
    { id: 8, categoria: 'lena', nombre: 'Leña para hogar 25 kg', pesoKg: 25, precio: 11500, costo: 7000, envase: false, stock: 22, stockMin: 10, activo: true },
  ];
  // Stock de envases en depósito por tipo de garrafa
  const envases = {
    1: { llenas: 34, vacias: 18, noAptas: 3 },
    2: { llenas: 21, vacias: 9, noAptas: 2 },
    3: { llenas: 3, vacias: 5, noAptas: 1 },
  };

  const usuarios = [
    { id: 1, nombre: 'Roberto', apellido: 'Medina', usuario: 'admin', email: 'roberto@extragas.com.ar', rol: 'Administrador', activo: true, ultimoAcceso: hoy },
    { id: 2, nombre: 'Lucía', apellido: 'Fernández', usuario: 'lucia', email: '', rol: 'Empleado', activo: true, ultimoAcceso: hoy },
    { id: 3, nombre: 'Martín', apellido: 'Ríos', usuario: 'martin', email: '', rol: 'Empleado', activo: true, ultimoAcceso: addDays(hoy, -1) },
  ];

  const nombres = [
    ['María', 'González'], ['José', 'Rodríguez'], ['Ana', 'López'], ['Carlos', 'Martínez'], ['Laura', 'Pérez'],
    ['Jorge', 'Sánchez'], ['Silvia', 'Romero'], ['Miguel', 'Díaz'], ['Patricia', 'Álvarez'], ['Ricardo', 'Torres'],
    ['Graciela', 'Ruiz'], ['Diego', 'Ramírez'], ['Sofía', 'Flores'], ['Hugo', 'Acosta'], ['Norma', 'Benítez'],
    ['Pablo', 'Medina'], ['Mónica', 'Herrera'], ['Daniel', 'Suárez'], ['Claudia', 'Aguirre'], ['Rubén', 'Giménez'],
    ['Parrilla', 'Don Tito'], ['Rotisería', 'La Esquina'],
  ];
  const calles = ['Lamadrid', 'Crisóstomo Álvarez', 'San Juan', 'Mendoza', 'Córdoba', 'Santiago del Estero', 'Av. Mate de Luna', 'Av. Aconquija', 'Jujuy', 'Salta', 'Laprida', 'Congreso', 'Maipú', 'Las Heras'];
  const barrios = ['Centro', 'Barrio Norte', 'Villa Luján', 'Barrio Sur', 'Ciudadela', 'Yerba Buena', 'Villa 9 de Julio', 'Parque Guillermina'];

  const clientes = nombres.map(([n, a], i) => {
    const comercio = i >= 20;
    const prefiere = comercio ? 3 : pick([1, 1, 1, 2, 2]);
    return {
      id: i + 1,
      nombre: comercio ? `${n} ${a}` : n,
      apellido: comercio ? '' : a,
      tipo: comercio ? 'Comercio' : 'Particular',
      dni: comercio ? `30-${int(50000000, 79999999)}-${int(0, 9)}` : String(int(18000000, 44000000)),
      celular: `381 ${int(400, 699)}-${String(int(0, 9999)).padStart(4, '0')}`,
      telefono: rnd() < .25 ? `381 4${int(10, 99)}-${String(int(0, 9999)).padStart(4, '0')}` : '',
      domicilio: `${pick(calles)} ${int(100, 2900)}`,
      barrio: pick(barrios),
      referencia: pick(['', '', 'Portón verde', 'Casa esquina', 'Timbre 2', 'Frente a la plaza', 'Dpto 3B']),
      formaPago: rnd() < .55 ? 'Efectivo' : 'Transferencia',
      observaciones: comercio ? 'Cliente comercial, entrega por la mañana.' : '',
      activo: true,
      alta: addDays(hoy, -int(120, 900)),
      envases: { 1: 0, 2: 0, 3: 0 },
      _prefiere: prefiere,
      _frecuencia: comercio ? int(4, 7) : int(8, 26),
    };
  });

  // ---- Pedidos históricos (últimos 90 días) ----
  let pedidos = [];
  clientes.forEach(c => {
    let d = addDays(hoy, -90 + int(0, c._frecuencia));
    while (daysBetween(d, hoy) >= 0) {
      const items = [];
      const cantGas = c._prefiere === 3 ? int(1, 2) : (rnd() < .8 ? 1 : 2);
      items.push({ productoId: c._prefiere, cantidad: cantGas, devueltos: cantGas });
      if (rnd() < .3) {
        const pid = pick([4, 5, 5, 6, 7, 8, 8]);
        items.push({ productoId: pid, cantidad: int(1, 2), devueltos: 0 });
      }
      pedidos.push({ fecha: d, clienteId: c.id, items });
      d = addDays(d, c._frecuencia + int(-2, 3));
    }
    c.envases[c._prefiere] = c._prefiere === 3 ? 2 : int(1, 2);
    delete c._prefiere; delete c._frecuencia;
  });
  // Algunos clientes que sólo compran carbón / leña
  for (let k = 0; k < 18; k++) {
    const d = addDays(hoy, -int(0, 89));
    pedidos.push({ fecha: d, clienteId: int(1, clientes.length), items: [{ productoId: pick([4, 5, 6, 7, 8]), cantidad: int(1, 3), devueltos: 0 }] });
  }
  pedidos.sort((a, b) => a.fecha.localeCompare(b.fecha));

  const pagos = [];
  let nroRecibo = 1;
  pedidos = pedidos.map((p, i) => {
    const c = clientes[p.clienteId - 1];
    const dias = daysBetween(p.fecha, hoy);
    let estado = 'Entregado';
    if (dias === 0) estado = pick(['Pendiente', 'Pendiente', 'En preparación', 'En reparto', 'Entregado']);
    else if (rnd() < .03) estado = 'Cancelado';
    const items = p.items.map(it => ({ ...it, precio: productos[it.productoId - 1].precio }));
    const total = items.reduce((s, it) => s + it.cantidad * it.precio, 0);
    const canal = pick(['Teléfono', 'WhatsApp', 'WhatsApp', 'WhatsApp', 'Local']);
    const ped = {
      id: i + 1, numero: i + 1, fecha: p.fecha, hora: `${String(int(8, 19)).padStart(2, '0')}:${pick(['00', '15', '30', '45'])}`,
      clienteId: p.clienteId, canal, entrega: canal === 'Local' ? 'Retira en local' : 'A domicilio',
      direccion: canal === 'Local' ? '' : `${c.domicilio}, ${c.barrio}`,
      estado, items, total, formaPago: c.formaPago, observaciones: '', usuarioId: int(1, 3),
    };
    if (estado === 'Entregado') {
      const r = rnd();
      const deuda = dias < 20 && r < .12;
      const parcial = !deuda && dias < 30 && r < .2;
      if (!deuda) {
        const monto = parcial ? Math.round(total / 2 / 100) * 100 : total;
        pagos.push({ id: pagos.length + 1, recibo: nroRecibo++, fecha: p.fecha, clienteId: p.clienteId, pedidoId: ped.id, monto, formaPago: rnd() < .85 ? c.formaPago : pick(FORMAS_PAGO), referencia: '', usuarioId: ped.usuarioId });
      }
    }
    return ped;
  });
  pagos.forEach(pg => { if (pg.formaPago === 'Transferencia') pg.referencia = `Op. ${int(10000000, 99999999)}`; });

  // ---- Proveedores, recepciones y pagos ----
  const proveedores = [
    { id: 1, razonSocial: 'Distribuidora GasNor S.R.L.', cuit: '30-71234567-8', contacto: 'Ing. Oscar Paz', telefono: '381 430-1122', celular: '381 512-3344', email: 'ventas@gasnor.com.ar', direccion: 'Ruta 9 km 1290, Tafí Viejo', rubro: 'gas', condicionPago: 'Contado / 7 días', alias: 'GASNOR.VENTAS', observaciones: 'Entrega los martes y viernes.', activo: true },
    { id: 2, razonSocial: 'Envasadora del Norte S.A.', cuit: '30-70987654-3', contacto: 'Carolina Vega', telefono: '381 455-9090', celular: '381 600-7788', email: 'pedidos@envnorte.com.ar', direccion: 'Parque Industrial, Lote 14', rubro: 'gas', condicionPago: '15 días', alias: 'ENVNORTE.SA', observaciones: 'Proveedor de garrafas de 45 kg.', activo: true },
    { id: 3, razonSocial: 'Carbonera El Quebracho', cuit: '20-25111222-5', contacto: 'Ramón Quiroga', telefono: '', celular: '385 411-2020', email: '', direccion: 'Monte Quemado, Santiago del Estero', rubro: 'carbon', condicionPago: 'Contado', alias: 'QUEBRACHO.CARBON', observaciones: '', activo: true },
    { id: 4, razonSocial: 'Leñera Monte Verde', cuit: '20-30444555-1', contacto: 'Julio Sosa', telefono: '', celular: '381 622-4455', email: '', direccion: 'Famaillá, Tucumán', rubro: 'lena', condicionPago: 'Contado', alias: 'MONTEVERDE.LENA', observaciones: 'Leña de quebracho y algarrobo.', activo: true },
  ];
  const recepciones = [];
  const pagosProveedores = [];
  const addRecepcion = (fecha, proveedorId, items) => {
    const total = items.reduce((s, it) => s + it.cantidad * it.costo, 0);
    const r = { id: recepciones.length + 1, fecha, proveedorId, remito: `R-0001-${String(int(1000, 99999)).padStart(8, '0')}`, items, total, observaciones: '', usuarioId: 1 };
    recepciones.push(r);
    const dias = daysBetween(fecha, hoy);
    if (dias > 8 || rnd() < .4) {
      pagosProveedores.push({ id: pagosProveedores.length + 1, fecha: addDays(fecha, Math.min(dias, int(0, 6))), proveedorId, recepcionId: r.id, monto: total, formaPago: rnd() < .6 ? 'Transferencia' : 'Efectivo', comprobante: '', observaciones: '' });
    }
  };
  for (let d = -88; d <= 0; d += int(3, 5)) {
    addRecepcion(addDays(hoy, d), 1, [
      { productoId: 1, cantidad: int(25, 40), costo: 12800, vaciasEntregadas: 0 },
      { productoId: 2, cantidad: int(12, 22), costo: 18900, vaciasEntregadas: 0 },
    ]);
  }
  for (let d = -85; d <= 0; d += int(9, 14)) addRecepcion(addDays(hoy, d), 2, [{ productoId: 3, cantidad: int(4, 8), costo: 58500, vaciasEntregadas: 0 }]);
  for (let d = -80; d <= 0; d += int(12, 18)) addRecepcion(addDays(hoy, d), 3, [{ productoId: 4, cantidad: 30, costo: 2600 }, { productoId: 5, cantidad: 25, costo: 4100 }, { productoId: 6, cantidad: 15, costo: 7800 }, { productoId: 7, cantidad: 6, costo: 18000 }]);
  for (let d = -75; d <= 0; d += int(15, 22)) addRecepcion(addDays(hoy, d), 4, [{ productoId: 8, cantidad: 30, costo: 7000 }]);
  recepciones.sort((a, b) => a.fecha.localeCompare(b.fecha)).forEach((r, i) => {
    const nuevo = i + 1;
    pagosProveedores.filter(p => p.recepcionId === r.id && !p._ok).forEach(p => { p.recepcionId = nuevo; p._ok = true; });
    r.id = nuevo;
    r.items.forEach(it => { if (productos[it.productoId - 1].envase) it.vaciasEntregadas = it.cantidad; });
  });
  pagosProveedores.forEach(p => delete p._ok);
  pagosProveedores.sort((a, b) => a.fecha.localeCompare(b.fecha)).forEach((p, i) => { p.id = i + 1; if (p.formaPago === 'Transferencia') p.comprobante = `Transf. ${int(1000000, 9999999)}`; });

  // ---- Movimientos de envases (últimos 30 días) ----
  const movEnvases = [];
  const desde = addDays(hoy, -30);
  pedidos.filter(p => p.estado === 'Entregado' && p.fecha >= desde).forEach(p => p.items.filter(it => it.productoId <= 3).forEach(it => {
    movEnvases.push({ fecha: p.fecha, productoId: it.productoId, tipo: 'Entrega a cliente', llenas: -it.cantidad, vacias: it.devueltos, noAptas: 0, detalle: `Pedido #${p.numero} · ${clientes[p.clienteId - 1].nombre} ${clientes[p.clienteId - 1].apellido}`.trim() });
  }));
  recepciones.filter(r => r.fecha >= desde).forEach(r => r.items.filter(it => it.productoId <= 3).forEach(it => {
    movEnvases.push({ fecha: r.fecha, productoId: it.productoId, tipo: 'Recepción de proveedor', llenas: it.cantidad, vacias: -it.vaciasEntregadas, noAptas: 0, detalle: `${proveedores[r.proveedorId - 1].razonSocial} · Remito ${r.remito}` });
  }));
  movEnvases.push({ fecha: addDays(hoy, -12), productoId: 1, tipo: 'Marcada no apta', llenas: 0, vacias: -2, noAptas: 2, detalle: 'Válvula dañada / prueba hidráulica vencida' });
  movEnvases.push({ fecha: addDays(hoy, -6), productoId: 2, tipo: 'Marcada no apta', llenas: 0, vacias: -1, noAptas: 1, detalle: 'Abollada' });
  movEnvases.sort((a, b) => a.fecha.localeCompare(b.fecha)).forEach((m, i) => { m.id = i + 1; });

  return {
    version: 1, empresa, parametros, productos, envases, usuarios, clientes, pedidos, pagos,
    proveedores, recepciones, pagosProveedores, movEnvases,
  };
}

const Store = (() => {
  const KEY = 'extragas_db_v1';
  let db = null;
  try { db = JSON.parse(localStorage.getItem(KEY)); } catch (e) { db = null; }
  if (!db || db.version !== 1) db = seedDB();
  const save = () => { try { localStorage.setItem(KEY, JSON.stringify(db)); } catch (e) { /* sin almacenamiento */ } };
  save();
  return {
    get db() { return db; },
    save,
    nextId: col => db[col].reduce((m, x) => Math.max(m, x.id), 0) + 1,
    reset() { db = seedDB(); save(); },
    replace(data) { db = data; save(); },
  };
})();

/* ---------- Consultas ---------- */
const Q = {
  hoy: () => localISO(new Date()),
  producto: id => Store.db.productos.find(p => p.id === +id),
  cliente: id => Store.db.clientes.find(c => c.id === +id),
  proveedor: id => Store.db.proveedores.find(p => p.id === +id),
  pedido: id => Store.db.pedidos.find(p => p.id === +id),
  usuario: id => Store.db.usuarios.find(u => u.id === +id),
  recepcion: id => Store.db.recepciones.find(r => r.id === +id),
  garrafas: () => Store.db.productos.filter(p => p.envase),
  nombreCliente: c => (c ? `${c.nombre} ${c.apellido || ''}`.trim() : '—'),

  pagadoPedido: id => Store.db.pagos.filter(p => p.pedidoId === +id).reduce((s, p) => s + p.monto, 0),
  saldoPedido(p) { return p.estado === 'Cancelado' ? 0 : Math.max(0, p.total - Q.pagadoPedido(p.id)); },
  estadoPago(p) {
    if (p.estado === 'Cancelado') return '—';
    const pagado = Q.pagadoPedido(p.id);
    if (pagado >= p.total) return 'Pagado';
    return pagado > 0 ? 'Parcial' : 'Impago';
  },
  pedidosCliente: id => Store.db.pedidos.filter(p => p.clienteId === +id),
  pagosCliente: id => Store.db.pagos.filter(p => p.clienteId === +id),
  // Deuda: pedidos entregados (o en curso) no cancelados, menos pagos
  saldoCliente(id) {
    return Q.pedidosCliente(id).filter(p => p.estado !== 'Cancelado').reduce((s, p) => s + Q.saldoPedido(p), 0);
  },
  envasesEnClientes(pid) { return Store.db.clientes.reduce((s, c) => s + (c.envases[pid] || 0), 0); },
  totalEnvasesCliente: c => Object.values(c.envases).reduce((s, n) => s + n, 0),

  regularidad(clienteId) {
    const fechas = [...new Set(Q.pedidosCliente(clienteId).filter(p => p.estado !== 'Cancelado').map(p => p.fecha))].sort();
    const r = { cantidad: fechas.length, ultimo: fechas[fechas.length - 1] || null, promedio: null, proximo: null, estado: 'Sin datos', diasSinPedir: null };
    if (r.ultimo) r.diasSinPedir = daysBetween(r.ultimo, Q.hoy());
    if (fechas.length >= 2) {
      r.promedio = Math.round(daysBetween(fechas[0], r.ultimo) / (fechas.length - 1));
      r.proximo = addDays(r.ultimo, r.promedio);
      const atraso = daysBetween(r.proximo, Q.hoy());
      const tol = Store.db.parametros.toleranciaDias;
      r.estado = atraso > tol ? 'Atrasado' : (atraso >= -1 ? 'Por pedir' : 'Al día');
      r.atraso = atraso;
    }
    return r;
  },
  deudaProveedor(id) {
    const db = Store.db;
    const total = db.recepciones.filter(r => r.proveedorId === +id).reduce((s, r) => s + r.total, 0);
    const pagado = db.pagosProveedores.filter(p => p.proveedorId === +id).reduce((s, p) => s + p.monto, 0);
    return Math.max(0, total - pagado);
  },
  pagadoRecepcion: id => Store.db.pagosProveedores.filter(p => p.recepcionId === +id).reduce((s, p) => s + p.monto, 0),
};

/* ---------- Operaciones que afectan stock ---------- */
const Ops = {
  movEnvase(productoId, tipo, llenas, vacias, noAptas, detalle, fecha) {
    const db = Store.db;
    const e = db.envases[productoId];
    e.llenas += llenas; e.vacias += vacias; e.noAptas += noAptas;
    db.movEnvases.push({ id: Store.nextId('movEnvases'), fecha: fecha || Q.hoy(), productoId: +productoId, tipo, llenas, vacias, noAptas, detalle });
  },
  // Al entregar un pedido se descuenta stock y se registra el intercambio de envases
  entregarPedido(p) {
    const c = Q.cliente(p.clienteId);
    p.items.forEach(it => {
      const prod = Q.producto(it.productoId);
      if (prod.envase) {
        const dev = +it.devueltos || 0;
        Ops.movEnvase(prod.id, 'Entrega a cliente', -it.cantidad, dev, 0, `Pedido #${p.numero} · ${Q.nombreCliente(c)}`);
        c.envases[prod.id] = Math.max(0, (c.envases[prod.id] || 0) + it.cantidad - dev);
      } else {
        prod.stock -= it.cantidad;
      }
    });
    p.estado = 'Entregado';
  },
  recibirMercaderia(r) {
    const prov = Q.proveedor(r.proveedorId);
    r.items.forEach(it => {
      const prod = Q.producto(it.productoId);
      if (prod.envase) Ops.movEnvase(prod.id, 'Recepción de proveedor', it.cantidad, -(+it.vaciasEntregadas || 0), 0, `${prov.razonSocial} · Remito ${r.remito}`, r.fecha);
      else prod.stock += it.cantidad;
    });
  },
};

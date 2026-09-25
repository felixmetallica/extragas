/* Generación de PDF: comprobantes de pedido, recibos de pago e informes */

const PDF = (() => {
  const BRAND = [232, 89, 12];
  const INK = [31, 36, 48];

  function nuevo(orientation = 'p') {
    const { jsPDF } = window.jspdf;
    return new jsPDF({ orientation, unit: 'mm', format: 'a4' });
  }

  function encabezado(doc, titulo, subtitulo) {
    const e = Store.db.empresa;
    const w = doc.internal.pageSize.getWidth();
    doc.setFillColor(...BRAND); doc.rect(0, 0, w, 4, 'F');
    doc.setFillColor(...BRAND); doc.roundedRect(14, 10, 12, 12, 2, 2, 'F');
    doc.setTextColor(255); doc.setFont('helvetica', 'bold'); doc.setFontSize(11); doc.text('EG', 20, 18, { align: 'center' });
    doc.setTextColor(...INK); doc.setFontSize(15); doc.text(e.nombre, 30, 15.5);
    doc.setFont('helvetica', 'normal'); doc.setFontSize(8); doc.setTextColor(110);
    doc.text(`${e.direccion} · ${e.localidad}`, 30, 20);
    doc.text(`Tel. ${e.telefono} · WhatsApp ${e.whatsapp} · CUIT ${e.cuit}`, 30, 24);
    doc.setTextColor(...INK); doc.setFont('helvetica', 'bold'); doc.setFontSize(13);
    doc.text(titulo, w - 14, 15.5, { align: 'right' });
    doc.setFont('helvetica', 'normal'); doc.setFontSize(8.5); doc.setTextColor(110);
    if (subtitulo) doc.text(subtitulo, w - 14, 20.5, { align: 'right' });
    doc.text(`Emitido: ${fmt.date(Q.hoy())} ${new Date().toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' })}`, w - 14, 24.5, { align: 'right' });
    doc.setDrawColor(230); doc.line(14, 29, w - 14, 29);
    doc.setTextColor(...INK);
    return 36;
  }

  function pie(doc) {
    const n = doc.internal.getNumberOfPages();
    const w = doc.internal.pageSize.getWidth(), h = doc.internal.pageSize.getHeight();
    for (let i = 1; i <= n; i++) {
      doc.setPage(i); doc.setFontSize(7.5); doc.setTextColor(140);
      doc.text('Documento no válido como factura. La facturación se emite a través de ARCA.', 14, h - 8);
      doc.text(`Página ${i} de ${n}`, w - 14, h - 8, { align: 'right' });
    }
  }

  function tabla(doc, y, head, body, opts = {}) {
    doc.autoTable({
      startY: y, head: [head], body, theme: 'striped', margin: { left: 14, right: 14 },
      styles: { fontSize: 8.5, cellPadding: 2, textColor: INK },
      headStyles: { fillColor: BRAND, textColor: 255, fontStyle: 'bold' },
      alternateRowStyles: { fillColor: [250, 247, 244] },
      ...opts,
    });
    return doc.lastAutoTable.finalY + 6;
  }

  function bloque(doc, y, titulo, lineas, x = 14) {
    doc.setFont('helvetica', 'bold'); doc.setFontSize(9); doc.setTextColor(...BRAND);
    doc.text(titulo.toUpperCase(), x, y);
    doc.setFont('helvetica', 'normal'); doc.setTextColor(...INK); doc.setFontSize(9.5);
    lineas.forEach((l, i) => doc.text(String(l), x, y + 5.5 + i * 5));
    return y + 5.5 + lineas.length * 5;
  }

  function totalBox(doc, y, label, value) {
    const w = doc.internal.pageSize.getWidth();
    doc.setFillColor(255, 244, 230); doc.roundedRect(w - 84, y, 70, 12, 2, 2, 'F');
    doc.setFontSize(9); doc.setTextColor(110); doc.text(label, w - 80, y + 7.5);
    doc.setFont('helvetica', 'bold'); doc.setFontSize(12); doc.setTextColor(...INK);
    doc.text(value, w - 17, y + 7.8, { align: 'right' });
    doc.setFont('helvetica', 'normal');
    return y + 18;
  }

  const money = n => fmt.money(n).replace(/ /g, ' ');

  return {
    pedido(p) {
      const doc = nuevo();
      const c = Q.cliente(p.clienteId);
      let y = encabezado(doc, `PEDIDO N° ${fmt.nro(p.numero)}`, `Fecha: ${fmt.date(p.fecha)} ${p.hora || ''}`);
      const y1 = bloque(doc, y, 'Cliente', [Q.nombreCliente(c), `Cel.: ${c.celular}`, `${c.domicilio} · ${c.barrio}`, c.referencia ? `Ref.: ${c.referencia}` : '']);
      bloque(doc, y, 'Pedido', [`Canal: ${p.canal}`, `Entrega: ${p.entrega}`, p.direccion ? `Dirección: ${p.direccion}` : '', `Estado: ${p.estado}`], 115);
      y = tabla(doc, y1 + 2,
        ['Producto', 'Cant.', 'Envases recibidos', 'Precio unit.', 'Subtotal'],
        p.items.map(it => { const pr = Q.producto(it.productoId); return [pr.nombre, it.cantidad, pr.envase ? it.devueltos : '—', money(it.precio), money(it.cantidad * it.precio)]; }),
        { columnStyles: { 1: { halign: 'center' }, 2: { halign: 'center' }, 3: { halign: 'right' }, 4: { halign: 'right' } } });
      y = totalBox(doc, y, 'TOTAL', money(p.total));
      const pagado = Q.pagadoPedido(p.id), saldo = Q.saldoPedido(p);
      doc.setFontSize(9.5);
      doc.text(`Forma de pago: ${p.formaPago}`, 14, y - 12);
      doc.text(`Pagado: ${money(pagado)}   ·   Saldo: ${money(saldo)}`, 14, y - 6.5);
      if (p.observaciones) { doc.setTextColor(110); doc.text(`Observaciones: ${p.observaciones}`, 14, y + 2); }
      doc.setTextColor(...INK);
      const h = doc.internal.pageSize.getHeight();
      doc.setDrawColor(180); doc.line(120, h - 35, 190, h - 35); doc.setFontSize(8); doc.text('Firma y aclaración del cliente', 155, h - 31, { align: 'center' });
      pie(doc);
      doc.save(`pedido-${fmt.nro(p.numero)}.pdf`);
    },

    recibo(pg) {
      const doc = nuevo();
      const c = Q.cliente(pg.clienteId);
      // Un recibo puede cancelar varios pedidos
      const lineas = Store.db.pagos.filter(x => x.recibo === pg.recibo);
      const total = lineas.reduce((s, x) => s + x.monto, 0);
      let y = encabezado(doc, `RECIBO N° ${fmt.nro(pg.recibo)}`, `Fecha: ${fmt.date(pg.fecha)}`);
      doc.setFontSize(11);
      doc.text(`Recibimos de ${Q.nombreCliente(c)} (DNI/CUIT ${c.dni || '—'})`, 14, y + 2);
      doc.text(`la suma de ${money(total)} en concepto de pago según el siguiente detalle:`, 14, y + 9);
      y = tabla(doc, y + 16, ['Concepto', 'Forma de pago', 'Referencia', 'Importe'], lineas.map(x => [
        x.pedidoId ? `Pedido N° ${fmt.nro(Q.pedido(x.pedidoId).numero)} del ${fmt.date(Q.pedido(x.pedidoId).fecha)} — ${UI.itemsResumen(Q.pedido(x.pedidoId).items)}` : 'Pago a cuenta',
        x.formaPago, x.referencia || '—', money(x.monto)]), { columnStyles: { 3: { halign: 'right' } } });
      y = totalBox(doc, y, 'TOTAL RECIBIDO', money(total));
      doc.setFontSize(9.5); doc.setTextColor(110);
      doc.text(`Saldo pendiente del cliente luego de este pago: ${money(Q.saldoCliente(c.id))}`, 14, y);
      doc.setTextColor(...INK);
      doc.setDrawColor(180); doc.line(120, y + 30, 190, y + 30); doc.setFontSize(8); doc.text(`Recibió: ${Q.nombreCliente(Q.usuario(pg.usuarioId))}`, 155, y + 34, { align: 'center' });
      pie(doc);
      doc.save(`recibo-${fmt.nro(pg.recibo)}.pdf`);
    },

    /* Informe genérico: secciones [{titulo, head, body, resumen:[[k,v]], columnStyles}] */
    informe(titulo, subtitulo, secciones, archivo, orientation = 'p') {
      const doc = nuevo(orientation);
      let y = encabezado(doc, titulo.toUpperCase(), subtitulo);
      secciones.forEach(s => {
        if (y > doc.internal.pageSize.getHeight() - 40) { doc.addPage(); y = 20; }
        if (s.titulo) { doc.setFont('helvetica', 'bold'); doc.setFontSize(10.5); doc.text(s.titulo, 14, y); doc.setFont('helvetica', 'normal'); y += 3; }
        if (s.resumen) {
          y = tabla(doc, y, ['Indicador', 'Valor'], s.resumen, { theme: 'plain', headStyles: { fillColor: [245, 246, 248], textColor: INK }, columnStyles: { 1: { halign: 'right', fontStyle: 'bold' } }, tableWidth: 110 });
        }
        if (s.head) y = tabla(doc, y, s.head, s.body, { columnStyles: s.columnStyles || {} });
      });
      pie(doc);
      doc.save(`${archivo}.pdf`);
    },
    money,
  };
})();

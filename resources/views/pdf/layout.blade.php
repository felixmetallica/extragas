<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 26mm 14mm 18mm 14mm; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 9pt; color: #1f2430; }
    header { position: fixed; top: -20mm; left: 0; right: 0; height: 18mm; border-bottom: 1px solid #e5e7eb; }
    footer { position: fixed; bottom: -12mm; left: 0; right: 0; font-size: 7pt; color: #888; }
    .franja { position: fixed; top: -26mm; left: -14mm; right: -14mm; height: 3mm; background: #e8590c; }
    .logo { display: inline-block; width: 11mm; height: 8mm; padding-top: 3mm; background: #e8590c; color: #fff; text-align: center; line-height: 1; font-weight: bold; border-radius: 2mm; font-size: 11pt; }
    .empresa { display: inline-block; vertical-align: top; margin-left: 3mm; }
    .empresa b { font-size: 13pt; }
    .empresa div { font-size: 7.5pt; color: #666; }
    .titulo { position: absolute; right: 0; top: 0; text-align: right; }
    .titulo b { font-size: 12pt; }
    .titulo div { font-size: 7.5pt; color: #666; }
    h3 { font-size: 10pt; margin: 5mm 0 2mm; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #e8590c; color: #fff; text-align: left; padding: 1.6mm 2mm; font-size: 8pt; }
    td { padding: 1.5mm 2mm; border-bottom: 1px solid #eee; vertical-align: top; }
    tr:nth-child(even) td { background: #faf7f4; }
    .num { text-align: right; white-space: nowrap; }
    .resumen td { background: #fff !important; border-bottom: 1px solid #f1f1f1; }
    .resumen td.num { font-weight: bold; }
    .bloques { width: 100%; margin-bottom: 5mm; }
    .bloques td { width: 50%; border: 0; padding: 0 3mm 0 0; background: #fff !important; }
    .et { color: #e8590c; font-weight: bold; font-size: 7.5pt; text-transform: uppercase; margin-bottom: 1mm; }
    .total { float: right; background: #fff4e6; padding: 2.5mm 4mm; border-radius: 2mm; margin-top: 3mm; font-size: 11pt; }
    .total span { color: #666; font-size: 8.5pt; margin-right: 8mm; }
    .firma { margin-top: 22mm; width: 70mm; margin-left: auto; border-top: 1px solid #aaa; text-align: center; font-size: 7.5pt; padding-top: 1.5mm; }
    .muted { color: #777; }
    .pagina { float: right; }
    .pagina:after { content: "Página " counter(page); }
</style>
</head>
<body>
<div class="franja"></div>
<header>
    <span class="logo">EG</span>
    <span class="empresa"><b>{{ $empresa->nombre }}</b>
        <div>{{ collect([$empresa->direccion, $empresa->localidad])->filter()->implode(' · ') }}</div>
        <div>{{ collect([$empresa->telefono ? 'Tel. '.$empresa->telefono : null, $empresa->whatsapp ? 'WhatsApp '.$empresa->whatsapp : null, $empresa->cuit ? 'CUIT '.$empresa->cuit : null])->filter()->implode(' · ') }}</div></span>
    <div class="titulo"><b>@yield('titulo')</b><div>@yield('subtitulo')</div><div>Emitido: {{ now()->format('d/m/Y H:i') }}</div></div>
</header>
<footer>Documento no válido como factura. La facturación se emite a través de ARCA.<span class="pagina"></span></footer>
<main>@yield('cuerpo')</main>
</body>
</html>

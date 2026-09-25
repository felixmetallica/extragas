<?php

namespace App\Core;

use App\Models\Configuracion;

define('FPDF_FONTPATH', RAIZ.'/lib/fpdf/font/');
require_once RAIZ.'/lib/fpdf/fpdf.php';

/**
 * Documentos PDF (FPDF): encabezado con datos de la empresa, pie con leyenda
 * de ARCA y numeración, tablas con ajuste de texto.
 */
class Pdf extends \FPDF
{
    private const NARANJA = [232, 89, 12];

    private array $cabeceraTabla = [];

    public function __construct(private string $titulo, private string $subtitulo = '', string $orientacion = 'P')
    {
        parent::__construct($orientacion, 'mm', 'A4');
        $this->SetMargins(14, 34, 14);
        $this->SetAutoPageBreak(true, 18);
        $this->AliasNbPages();
        $this->SetTitle($this->t($titulo));
        $this->AddPage();
    }

    /** Convierte UTF-8 a la codificación de las fuentes estándar */
    public function t(mixed $texto): string
    {
        return (string) iconv('UTF-8', 'windows-1252//TRANSLIT', (string) $texto);
    }

    public function Header(): void
    {
        $e = Configuracion::empresa();
        $w = $this->GetPageWidth();
        $this->SetFillColor(...self::NARANJA);
        $this->Rect(0, 0, $w, 3, 'F');
        $this->RoundedBox(14, 9, 11, 11);
        $this->SetTextColor(255);
        $this->SetFont('Helvetica', 'B', 11);
        $this->SetXY(14, 12);
        $this->Cell(11, 5, 'EG', 0, 0, 'C');

        $this->SetTextColor(31, 36, 48);
        $this->SetFont('Helvetica', 'B', 14);
        $this->SetXY(28, 9);
        $this->Cell(90, 6, $this->t($e['nombre']));
        $this->SetFont('Helvetica', '', 7.5);
        $this->SetTextColor(110);
        $this->SetXY(28, 15);
        $this->Cell(100, 4, $this->t(implode(' · ', array_filter([$e['direccion'] ?? '', $e['localidad'] ?? '']))));
        $this->SetXY(28, 19);
        $this->Cell(100, 4, $this->t(implode(' · ', array_filter([
            $e['telefono'] ? 'Tel. '.$e['telefono'] : '', $e['whatsapp'] ? 'WhatsApp '.$e['whatsapp'] : '', $e['cuit'] ? 'CUIT '.$e['cuit'] : '']))));

        $this->SetTextColor(31, 36, 48);
        $this->SetFont('Helvetica', 'B', 12);
        $this->SetXY($w - 114, 9);
        $this->Cell(100, 6, $this->t(mb_strtoupper($this->titulo)), 0, 0, 'R');
        $this->SetFont('Helvetica', '', 7.5);
        $this->SetTextColor(110);
        $this->SetXY($w - 114, 15);
        $this->Cell(100, 4, $this->t($this->subtitulo), 0, 0, 'R');
        $this->SetXY($w - 114, 19);
        $this->Cell(100, 4, $this->t('Emitido: '.date('d/m/Y H:i')), 0, 0, 'R');
        $this->SetDrawColor(225);
        $this->Line(14, 26, $w - 14, 26);
        $this->SetY(32);
        $this->SetTextColor(31, 36, 48);

        if ($this->cabeceraTabla && $this->PageNo() > 1) {
            $this->filaCabecera(...$this->cabeceraTabla);
        }
    }

    public function Footer(): void
    {
        $this->SetY(-12);
        $this->SetFont('Helvetica', '', 7);
        $this->SetTextColor(140);
        $this->Cell(0, 4, $this->t('Documento no válido como factura. La facturación se emite a través de ARCA.'));
        $this->Cell(0, 4, $this->t('Página '.$this->PageNo().' de {nb}'), 0, 0, 'R');
    }

    private function RoundedBox(float $x, float $y, float $w, float $h): void
    {
        $this->SetFillColor(...self::NARANJA);
        $this->Rect($x, $y, $w, $h, 'F');
    }

    public function titulo(string $texto): void
    {
        $this->Ln(2);
        $this->SetFont('Helvetica', 'B', 10.5);
        $this->SetTextColor(31, 36, 48);
        $this->Cell(0, 7, $this->t($texto), 0, 1);
    }

    public function etiqueta(string $texto, float $x, float $y): void
    {
        $this->SetXY($x, $y);
        $this->SetFont('Helvetica', 'B', 7.5);
        $this->SetTextColor(...self::NARANJA);
        $this->Cell(80, 4, $this->t(mb_strtoupper($texto)));
        $this->SetTextColor(31, 36, 48);
    }

    /** Bloque de líneas de texto en una columna */
    public function bloque(string $etiqueta, array $lineas, float $x, float $y, float $ancho = 88): float
    {
        $this->etiqueta($etiqueta, $x, $y);
        $this->SetFont('Helvetica', '', 9);
        $yy = $y + 5;
        foreach (array_filter($lineas, fn ($l) => $l !== null && $l !== '') as $i => $l) {
            $this->SetXY($x, $yy);
            $this->SetFont('Helvetica', $i === 0 ? 'B' : '', 9);
            $this->MultiCell($ancho, 4.6, $this->t($l));
            $yy = $this->GetY();
        }

        return $yy;
    }

    /** Pares indicador => valor */
    public function resumen(array $pares, float $ancho = 110): void
    {
        $this->SetFont('Helvetica', '', 9);
        foreach ($pares as [$k, $v]) {
            $this->SetDrawColor(235);
            $this->Cell($ancho * .65, 6.5, $this->t($k), 'B');
            $this->SetFont('Helvetica', 'B', 9);
            $this->Cell($ancho * .35, 6.5, $this->t($v), 'B', 1, 'R');
            $this->SetFont('Helvetica', '', 9);
        }
        $this->Ln(4);
    }

    public function caja(string $etiqueta, string $valor): void
    {
        $w = $this->GetPageWidth();
        $this->Ln(3);
        $y = $this->GetY();
        $this->SetFillColor(255, 244, 230);
        $this->Rect($w - 84, $y, 70, 11, 'F');
        $this->SetXY($w - 82, $y + 3);
        $this->SetFont('Helvetica', '', 8.5);
        $this->SetTextColor(110);
        $this->Cell(30, 5, $this->t($etiqueta));
        $this->SetFont('Helvetica', 'B', 12);
        $this->SetTextColor(31, 36, 48);
        $this->Cell(36, 5, $this->t($valor), 0, 1, 'R');
        $this->SetY($y + 14);
    }

    /**
     * Tabla con ancho de columnas automático y filas de varias líneas.
     *
     * @param  int[]  $derecha  índices de columnas alineadas a la derecha
     */
    public function tabla(array $cabecera, array $filas, array $derecha = []): void
    {
        $disponible = $this->GetPageWidth() - 28;
        $this->SetFont('Helvetica', '', 8);
        $anchos = [];
        foreach ($cabecera as $i => $h) {
            $max = $this->GetStringWidth($this->t($h)) + 5;
            foreach (array_slice($filas, 0, 200) as $f) {
                $max = max($max, min(70, $this->GetStringWidth($this->t(array_values($f)[$i] ?? '')) + 4));
            }
            $anchos[$i] = $max;
        }
        $factor = $disponible / array_sum($anchos);
        $anchos = array_map(fn ($a) => $a * $factor, $anchos);

        $this->cabeceraTabla = [$cabecera, $anchos, $derecha];
        $this->filaCabecera($cabecera, $anchos, $derecha);
        $this->SetFont('Helvetica', '', 8);
        if (! $filas) {
            $this->SetTextColor(130);
            $this->Cell($disponible, 7, $this->t('Sin datos'), 'B', 1);
            $this->SetTextColor(31, 36, 48);
        }
        foreach ($filas as $n => $fila) {
            $fila = array_map(fn ($v) => $this->t($v), array_values($fila));
            $alto = 5 * max(array_map(fn ($i) => $this->lineas($anchos[$i], $fila[$i] ?? ''), array_keys($anchos)));
            if ($this->GetY() + $alto > $this->PageBreakTrigger) {
                $this->AddPage($this->CurOrientation);
            }
            $x = $this->GetX();
            $y = $this->GetY();
            if ($n % 2) {
                $this->SetFillColor(250, 247, 244);
                $this->Rect($x, $y, $disponible, $alto, 'F');
            }
            foreach ($anchos as $i => $a) {
                $this->SetXY($x, $y);
                $this->MultiCell($a, 5, $fila[$i] ?? '', 0, in_array($i, $derecha, true) ? 'R' : 'L');
                $x += $a;
            }
            $this->SetDrawColor(238);
            $this->Line(14, $y + $alto, 14 + $disponible, $y + $alto);
            $this->SetXY(14, $y + $alto);
        }
        $this->cabeceraTabla = [];
        $this->Ln(5);
    }

    private function filaCabecera(array $cabecera, array $anchos, array $derecha): void
    {
        $this->SetFont('Helvetica', 'B', 8);
        $this->SetFillColor(...self::NARANJA);
        $this->SetTextColor(255);
        foreach ($cabecera as $i => $h) {
            $this->Cell($anchos[$i], 7, $this->t($h), 0, 0, in_array($i, $derecha, true) ? 'R' : 'L', true);
        }
        $this->Ln();
        $this->SetTextColor(31, 36, 48);
        $this->SetFont('Helvetica', '', 8);
    }

    /** Cantidad de líneas que ocupa un texto en una celda de ancho $w */
    private function lineas(float $w, string $txt): int
    {
        $cw = $this->CurrentFont['cw'];
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] === "\n") {
            $nb--;
        }
        $sep = -1;
        $i = $j = $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c === "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;

                continue;
            }
            if ($c === ' ') {
                $sep = $i;
            }
            $l += $cw[$c] ?? 500;
            if ($l > $wmax) {
                if ($sep === -1) {
                    if ($i === $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }

        return $nl;
    }

    public function firma(string $texto): void
    {
        $w = $this->GetPageWidth();
        $y = max($this->GetY() + 22, $this->GetPageHeight() - 45);
        $this->SetDrawColor(170);
        $this->Line($w - 90, $y, $w - 14, $y);
        $this->SetXY($w - 90, $y + 1.5);
        $this->SetFont('Helvetica', '', 7.5);
        $this->Cell(76, 4, $this->t($texto), 0, 0, 'C');
    }

    /** Envía el PDF al navegador como descarga */
    public function descargar(string $archivo): never
    {
        $this->Output('D', $archivo.'.pdf');
        exit;
    }

    /**
     * Informe genérico por secciones:
     * ['titulo' => ?, 'resumen' => [[k, v]], 'cabecera' => [...], 'filas' => [[...]], 'derecha' => [índices]]
     */
    public static function informe(string $titulo, string $subtitulo, array $secciones, string $archivo, string $orientacion = 'P'): never
    {
        $pdf = new self($titulo, $subtitulo, $orientacion);
        foreach ($secciones as $s) {
            if (! empty($s['titulo'])) {
                $pdf->titulo($s['titulo']);
            }
            if (! empty($s['resumen'])) {
                $pdf->resumen($s['resumen']);
            }
            if (! empty($s['cabecera'])) {
                $pdf->tabla($s['cabecera'], $s['filas'] ?? [], $s['derecha'] ?? []);
            }
        }
        $pdf->descargar($archivo);
    }
}

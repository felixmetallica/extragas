<?php

namespace App\Core;

/** Paginación de listados (LIMIT / OFFSET) con enlaces Bootstrap */
final class Paginador
{
    public readonly int $pagina;

    public readonly int $paginas;

    public function __construct(public readonly int $total, public readonly int $porPagina = 15, public readonly string $parametro = 'pagina')
    {
        $this->paginas = max(1, (int) ceil($total / $porPagina));
        $this->pagina = min($this->paginas, max(1, (int) ($_GET[$this->parametro] ?? 1)));
    }

    public function offset(): int
    {
        return ($this->pagina - 1) * $this->porPagina;
    }

    public function sql(): string
    {
        return ' LIMIT '.$this->porPagina.' OFFSET '.$this->offset();
    }

    public function enlaces(): string
    {
        if (! $this->total) {
            return '';
        }
        $desde = $this->offset() + 1;
        $hasta = min($this->total, $this->offset() + $this->porPagina);
        $html = '<div class="tbl-footer"><small class="text-body-secondary">Mostrando '.$desde.'–'.$hasta.' de '.$this->total.'</small>';
        if ($this->paginas > 1) {
            $html .= '<ul class="pagination pagination-sm mb-0">';
            $html .= $this->item($this->pagina - 1, '‹', $this->pagina === 1);
            $desdeP = max(1, min($this->pagina - 2, $this->paginas - 4));
            for ($p = $desdeP; $p <= min($this->paginas, $desdeP + 4); $p++) {
                $html .= $this->item($p, (string) $p, false, $p === $this->pagina);
            }
            $html .= $this->item($this->pagina + 1, '›', $this->pagina === $this->paginas).'</ul>';
        }

        return $html.'</div>';
    }

    private function item(int $p, string $texto, bool $deshabilitado, bool $activo = false): string
    {
        $url = e(url_actual([$this->parametro => $p]));

        return '<li class="page-item'.($deshabilitado ? ' disabled' : '').($activo ? ' active' : '').'"><a class="page-link" href="'.$url.'">'.$texto.'</a></li>';
    }
}

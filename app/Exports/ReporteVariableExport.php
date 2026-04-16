<?php

namespace App\Exports;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReporteVariableExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    private array $row;
    private ?string $scopeTipo;

    public function __construct(array $row, ?string $scopeTipo)
    {
        $this->row = $row;
        $this->scopeTipo = $scopeTipo;
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->row['subvariables'] as $subData) {
            $subName = $subData['subvariable']->descripcion;

            foreach ($subData['establecimientos'] as $detalle) {
                $row = [$subName];

                if (in_array($this->scopeTipo, ['red', 'sedes'], true)) {
                    $row[] = $detalle['establecimiento']->red_nombre;
                }

                if (in_array($this->scopeTipo, ['municipio', 'red', 'sedes'], true)) {
                    $row[] = $detalle['establecimiento']->municipio_nombre;
                }

                $row[] = $detalle['establecimiento']->descripcion;

                foreach ($this->row['cabeceras'] as $cabecera) {
                    $row[] = $detalle['totales'][$cabecera->id] ?? 0;
                }

                $row[] = $detalle['total_general'];
                $rows[] = $row;
            }

            foreach ($subData['consolidado_municipio'] as $detalle) {
                $row = [$subName];

                if (in_array($this->scopeTipo, ['red', 'sedes'], true)) {
                    $row[] = $detalle['red_nombre'];
                }

                if (in_array($this->scopeTipo, ['municipio', 'red', 'sedes'], true)) {
                    $row[] = $detalle['municipio_nombre'];
                }

                $row[] = 'Consolidado municipal';

                foreach ($this->row['cabeceras'] as $cabecera) {
                    $row[] = $detalle['totales'][$cabecera->id] ?? 0;
                }

                $row[] = $detalle['total_general'];
                $rows[] = $row;
            }

            foreach ($subData['consolidado_red'] as $detalle) {
                $row = [$subName];

                if (in_array($this->scopeTipo, ['red', 'sedes'], true)) {
                    $row[] = $detalle['red_nombre'];
                }

                if (in_array($this->scopeTipo, ['municipio', 'red', 'sedes'], true)) {
                    $row[] = ''; // Leave municipio blank for red-level consolidation
                }

                $row[] = 'Consolidado de Red';

                foreach ($this->row['cabeceras'] as $cabecera) {
                    $row[] = $detalle['totales'][$cabecera->id] ?? 0;
                }

                $row[] = $detalle['total_general'];
                $rows[] = $row;
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        $headings = ['Subvariable'];

        if (in_array($this->scopeTipo, ['red', 'sedes'], true)) {
            $headings[] = 'Red';
        }

        if (in_array($this->scopeTipo, ['municipio', 'red', 'sedes'], true)) {
            $headings[] = 'Municipio';
        }

        $headings[] = 'Establecimiento';

        foreach ($this->row['cabeceras'] as $cabecera) {
            $headings[] = $cabecera->descripcion;
        }

        $headings[] = 'Total';

        return $headings;
    }

    public function title(): string
    {
        return Str::limit($this->row['variable']->descripcion ?? 'Variable', 31);
    }
}

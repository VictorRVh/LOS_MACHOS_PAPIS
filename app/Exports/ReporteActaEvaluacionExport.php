<?php

namespace App\Exports;

use App\Models\Grupo;
use App\Models\Matricula;
use App\Models\CapacidadTerminal;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ReporteActaEvaluacionExport
{
    protected string $idGrupo;

    public function __construct(string $idGrupo)
    {
        $this->idGrupo = $idGrupo;
    }

    public function build(): Spreadsheet
    {
        $spreadsheet = IOFactory::load(
            storage_path('app/templates/ACTA-EVALUACION.xlsx')
        );

        $grupo = Grupo::with([
            'programaEstudio',
            'especialidad',
            'modulo',
            'periodo',
        ])->findOrFail($this->idGrupo);

        $capacidades = CapacidadTerminal::where('id_grupo', $this->idGrupo)
            ->orderBy('numero_capacidad')
            ->limit(10)
            ->get();

        $matriculas = Matricula::with([
            'estudiante',
            'notasCapacidades' => function ($q) {
                $q->where('id_grupo', $this->idGrupo);
            }
        ])
            ->where('id_grupo', $this->idGrupo)
            ->where('reserva', 0)
            ->orderBy('id_estudiante')
            ->get();


        $this->llenarDatosGenerales($spreadsheet, $grupo);
        $this->llenarNomina($spreadsheet, $matriculas);
        $this->llenarNotas($spreadsheet, $matriculas, $capacidades);

        return $spreadsheet;
    }

    private function llenarDatosGenerales(Spreadsheet $spreadsheet, $grupo): void
    {
        $sheet = $spreadsheet->getSheetByName('DATOS');

        $sheet->setCellValue('R10', $grupo->especialidad->especialidadMadre->nombre_especialidad);
        // $sheet->setCellValue('C6', $grupo->programaEstudio->numero_rd);
        $sheet->setCellValue('E11', $grupo->modulo->descripcion);
        // $sheet->setCellValue('C8', $grupo->periodo->nombre_periodo);
        $sheet->setCellValue('H12', $grupo->fecha_inicio);
        $sheet->setCellValue('R12', $grupo->fecha_fin);
        $sheet->setCellValue('AB12', $grupo->turno);
        $sheet->setCellValue('AK12', $grupo->seccion);
    }

    private function llenarNomina(Spreadsheet $spreadsheet, $matriculas): void
    {
        $sheet = $spreadsheet->getSheetByName('NOMINA');

        $filaInicio = 15;

        foreach ($matriculas as $i => $matricula) {

            $fila = $filaInicio + $i;
            $est = $matricula->estudiante;

            // $sheet->setCellValue("B{$fila}", $i + 1);
            // $sheet->setCellValue("B{$fila}", $est->nro_documento);
            $this->escribirDocumentoPorDigitos(
                $sheet,
                $est->nro_documento,
                'B',
                $fila
            );

            $sheet->setCellValue(
                "O{$fila}",
                "{$est->apellido_paterno} {$est->apellido_materno}, {$est->nombre}"
            );
        }
    }

    private function llenarNotas(
        Spreadsheet $spreadsheet,
        $matriculas,
        $capacidades
    ): void {

        foreach ($matriculas as $index => $matricula) {

            // ==================================
            // DETERMINAR HOJA, FILA Y COLUMNA BASE
            // ==================================
            if ($index <= 19) {
                // -------- CARA1 --------
                $sheet = $spreadsheet->getSheetByName('CARA1');
                $fila = 19 + $index;
                $colNotasInicio = 'R';
            } elseif ($index <= 29) {
                // -------- REVES1 --------
                $sheet = $spreadsheet->getSheetByName('REVES1');
                $fila = 18 + ($index - 20);
                $colNotasInicio = 'Y';
            } elseif ($index <= 49) {
                // -------- CARA2 --------
                $sheet = $spreadsheet->getSheetByName('CARA2');
                $fila = 19 + ($index - 30);
                $colNotasInicio = 'R';
            } elseif ($index <= 59) {
                // -------- REVES2 --------
                $sheet = $spreadsheet->getSheetByName('REVES2');
                $fila = 18 + ($index - 50);
                $colNotasInicio = 'Y';
            } else {
                // Excel no soporta más de 60 alumnos
                break;
            }

            $est = $matricula->estudiante;

            // =============================
            // DNI
            // =============================
            // $sheet->setCellValue(
            //     "D{$fila}",
            //     $est->nro_documento
            // );
            $this->escribirDocumentoPorDigitos(
                $sheet,
                $est->nro_documento,
                'D',
                $fila
            );

            // =============================
            // APELLIDOS Y NOMBRES
            // =============================
            $sheet->setCellValue(
                "Q{$fila}",
                "{$est->apellido_paterno} {$est->apellido_materno}, {$est->nombre}"
            );

            // =============================
            // NOTAS POR CAPACIDAD
            // =============================
            foreach ($capacidades as $cIndex => $capacidad) {

                $nota = $matricula->notasCapacidades
                    ->firstWhere('id_capacidad', $capacidad->id);

                $col = Coordinate::stringFromColumnIndex(
                    Coordinate::columnIndexFromString($colNotasInicio) + $cIndex
                );

                $sheet->setCellValue(
                    "{$col}{$fila}",
                    $nota->nota_capacidad ?? ''
                );
            }
        }
    }

    private function escribirDocumentoPorDigitos(
        $sheet,
        string $dni,
        string $colInicio,
        int $fila
    ): void {
        $dni = str_pad($dni, 8, '0', STR_PAD_LEFT); // asegura 8 dígitos
        $colIndex = Coordinate::columnIndexFromString($colInicio);

        foreach (str_split($dni) as $i => $digito) {
            $col = Coordinate::stringFromColumnIndex($colIndex + $i);
            $sheet->setCellValue("{$col}{$fila}", $digito);
        }
    }
}

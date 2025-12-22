<?php

namespace App\Exports;

use App\Models\Grupo;
use App\Models\Matricula;
use App\Models\CapacidadTerminal;
use Carbon\Carbon;
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
            storage_path('app/templates/ACTA-EVALUACION-ULTIMA.xlsx')
        );

        $grupo = Grupo::with([
            'programaEstudio',
            'especialidad',
            'modulo',
            'periodo',
            'docente',
        ])->findOrFail($this->idGrupo);

        $capacidades = CapacidadTerminal::where('id_grupo', $this->idGrupo)
            ->orderBy('numero_capacidad')
            ->limit(10)
            ->get();

        $matriculas = Matricula::with([
            'estudiante',
            'pago',
            'notasCapacidades' => function ($q) {
                $q->where('id_grupo', $this->idGrupo);
            }
        ])
            ->where('id_grupo', $this->idGrupo)
            ->where('reserva', 0)
            ->orderBy('id_estudiante')
            ->get();


        // $this->llenarDatosGenerales($spreadsheet, $grupo);
        $this->llenarDatosGeneralesActa($spreadsheet, $grupo);
        $this->llenarTitulosCapacidadesCaras($spreadsheet, $capacidades);
        $this->llenarTitulosCapacidadesReves($spreadsheet, $capacidades);
        $this->llenarModuloDocenteReves($spreadsheet, $grupo);
        // $this->llenarNomina($spreadsheet, $matriculas);
        $this->llenarNotas($spreadsheet, $matriculas, $capacidades);

        return $spreadsheet;
    }

    // private function llenarDatosGenerales(Spreadsheet $spreadsheet, $grupo): void
    // {
    //     $sheet = $spreadsheet->getSheetByName('DATOS');

    //     $sheet->setCellValue('R10', $grupo->especialidad->especialidadMadre->nombre_especialidad);
    //     // $sheet->setCellValue('C6', $grupo->programaEstudio->numero_rd);
    //     $sheet->setCellValue('E11', $grupo->modulo->descripcion);
    //     // $sheet->setCellValue('C8', $grupo->periodo->nombre_periodo);
    //     $sheet->setCellValue('H12', $grupo->fecha_inicio);
    //     $sheet->setCellValue('R12', $grupo->fecha_fin);
    //     $sheet->setCellValue('AB12', $grupo->turno);
    //     $sheet->setCellValue('AK12', $grupo->seccion);
    // }

    private function llenarDatosGeneralesActa(
        Spreadsheet $spreadsheet,
        $grupo
    ): void {

        $hojas = ['CARA1', 'CARA2'];

        foreach ($hojas as $nombreHoja) {

            $sheet = $spreadsheet->getSheetByName($nombreHoja);

            if (!$sheet) {
                continue;
            }

            // =============================
            // FORMATEO DE FECHAS
            // =============================
            $fechaInicio = $grupo->fecha_inicio
                ? Carbon::parse($grupo->fecha_inicio)   ->format('d/m/Y')
                : '';

            $fechaFin = $grupo->fecha_fin
                ? Carbon::parse($grupo->fecha_fin)->format('d/m/Y')
                : '';

            // =============================
            // DATOS GENERALES
            // =============================

            // ESPECIALIDAD
            $sheet->setCellValue(
                'AD6',
                $grupo->especialidad?->especialidadMadre?->nombre_especialidad ?? ''
            );

            // CICLO
            $sheet->setCellValue(
                'AF8',
                $grupo->programaEstudio?->ciclo?->nombre_ciclo ?? ''
            );

            // MÓDULO
            $sheet->setCellValue(
                'AD10',
                $grupo->modulo?->descripcion ?? ''
            );

            // NÚMERO RD
            $sheet->setCellValue(
                'AD13',
                $grupo->programaEstudio?->numero_rd ?? ''
            );

            // TURNO
            $sheet->setCellValue(
                'AF14',
                $grupo->turno ?? ''
            );

            // SECCIÓN
            $sheet->setCellValue(
                'AF15',
                $grupo->seccion ?? ''
            );

            // HORAS
            $sheet->setCellValue(
                'AG16',
                $grupo->modulo?->horas ?? ''
            );

            // FECHAS
            $sheet->setCellValue('AF17', $fechaInicio);
            $sheet->setCellValue('AL17', $fechaFin);
        }
    }

    // private function llenarNomina(Spreadsheet $spreadsheet, $matriculas): void
    // {
    //     $sheet = $spreadsheet->getSheetByName('NOMINA');

    //     $filaInicio = 15;

    //     foreach ($matriculas as $i => $matricula) {

    //         $fila = $filaInicio + $i;
    //         $est = $matricula->estudiante;

    //         // $sheet->setCellValue("B{$fila}", $i + 1);
    //         // $sheet->setCellValue("B{$fila}", $est->nro_documento);
    //         $this->escribirDocumentoPorDigitos(
    //             $sheet,
    //             $est->nro_documento,
    //             'B',
    //             $fila
    //         );

    //         $sheet->setCellValue(
    //             "O{$fila}",
    //             "{$est->apellido_paterno} {$est->apellido_materno}, {$est->nombre}"
    //         );
    //     }
    // }

    private function llenarModuloDocenteReves(Spreadsheet $spreadsheet, $grupo): void
    {
        $hojasReves = ['REVES1', 'REVES2'];

        foreach ($hojasReves as $nombreHoja) {
            $sheet = $spreadsheet->getSheetByName($nombreHoja);
            if (!$sheet) continue;

            // Módulo
            $sheet->setCellValue('B32', $grupo->modulo?->descripcion ?? '');

            // Docente
            $docenteNombre = $grupo->docente?->user->name ?? '';
            $docenteApellidoPaterno = $grupo->docente?->user->apellido_paterno ?? '';
            $docenteApellidoMaterno = $grupo->docente?->user->apellido_materno ?? '';
            $sheet->setCellValue('X32', trim("$docenteNombre $docenteApellidoPaterno $docenteApellidoMaterno"));
            $sheet->setCellValue('U42', trim("Prof. $docenteNombre $docenteApellidoPaterno $docenteApellidoMaterno"));

            // Fecha actual
            $sheet->setCellValue('C41', Carbon::now()->format('d/m/Y'));
        }
    }

    private function llenarTitulosCapacidadesCaras(
        Spreadsheet $spreadsheet,
        $capacidades
    ): void {

        $hojas = ['CARA1', 'CARA2'];
        $filaTitulo = 7;
        $colInicio = 'R';

        foreach ($hojas as $nombreHoja) {

            $sheet = $spreadsheet->getSheetByName($nombreHoja);
            $colIndex = Coordinate::columnIndexFromString($colInicio);

            foreach ($capacidades as $i => $capacidad) {

                if ($i >= 10) break; // máximo 10 capacidades

                $col = Coordinate::stringFromColumnIndex($colIndex + $i);

                $sheet->setCellValue(
                    "{$col}{$filaTitulo}",
                    $capacidad->nombre_capacidad
                );
            }
        }
    }

    private function llenarTitulosCapacidadesReves(
        Spreadsheet $spreadsheet,
        $capacidades
    ): void {

        $hojas = ['REVES1', 'REVES2'];
        $filaTitulo = 6;
        $colInicio = 'Y';

        foreach ($hojas as $nombreHoja) {

            $sheet = $spreadsheet->getSheetByName($nombreHoja);
            $colIndex = Coordinate::columnIndexFromString($colInicio);

            foreach ($capacidades as $i => $capacidad) {

                if ($i >= 10) break; // máximo 10 capacidades

                $col = Coordinate::stringFromColumnIndex($colIndex + $i);

                $sheet->setCellValue(
                    "{$col}{$filaTitulo}",
                    $capacidad->nombre_capacidad
                );
            }
        }
    }

    private function llenarNotas(
        Spreadsheet $spreadsheet,
        $matriculas,
        $capacidades
    ): void {

        $totalMatriculados = 0;
        $totalAprobados = 0;
        $totalDesaprobados = 0;

        foreach ($matriculas as $index => $matricula) {

            // ==================================
            // DETERMINAR HOJA, FILA Y COLUMNAS
            // ==================================
            if ($index <= 19) {
                // -------- CARA1 --------
                $sheet = $spreadsheet->getSheetByName('CARA1');
                $fila = 19 + $index;
                $colNotasInicio = 'R';
                $colSuma = 'AB';
                $colPromedio = 'AC';
            } elseif ($index <= 29) {
                // -------- REVES1 --------
                $sheet = $spreadsheet->getSheetByName('REVES1');
                $fila = 18 + ($index - 20);
                $colNotasInicio = 'Y';
                $colSuma = 'AI';
                $colPromedio = 'AJ';
            } elseif ($index <= 49) {
                // -------- CARA2 --------
                $sheet = $spreadsheet->getSheetByName('CARA2');
                $fila = 19 + ($index - 30);
                $colNotasInicio = 'R';
                $colSuma = 'AB';
                $colPromedio = 'AC';
            } elseif ($index <= 59) {
                // -------- REVES2 --------
                $sheet = $spreadsheet->getSheetByName('REVES2');
                $fila = 18 + ($index - 50);
                $colNotasInicio = 'Y';
                $colSuma = 'AI';
                $colPromedio = 'AJ';
            } else {
                break;
            }

            $est = $matricula->estudiante;

            $sheet->setCellValue(
                "C{$fila}",
                $this->obtenerCondicion($matricula->pago)
            );

            // =============================
            // DNI POR DÍGITOS
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
            // NOTAS + SUMA + PROMEDIO
            // =============================
            $sumaNotas = 0;
            $contadorNotas = 0;

            foreach ($capacidades as $cIndex => $capacidad) {

                $notaModel = $matricula->notasCapacidades
                    ->firstWhere('id_capacidad', $capacidad->id);

                $nota = is_numeric($notaModel?->nota_capacidad)
                    ? (float) $notaModel->nota_capacidad
                    : null;

                $col = Coordinate::stringFromColumnIndex(
                    Coordinate::columnIndexFromString($colNotasInicio) + $cIndex
                );

                // Nota individual
                $sheet->setCellValue("{$col}{$fila}", $nota ?? '');

                if ($nota !== null) {
                    $sumaNotas += $nota;
                    $contadorNotas++;
                }
            }

            // =============================
            // ESCRIBIR SUMA Y PROMEDIO
            // =============================
            $sheet->setCellValue(
                "{$colSuma}{$fila}",
                $contadorNotas > 0 ? $sumaNotas : ''
            );

            $sheet->setCellValue(
                "{$colPromedio}{$fila}",
                $contadorNotas > 0 ? round($sumaNotas / $contadorNotas, 2) : ''
            );

            if ($contadorNotas > 0) {
                $totalMatriculados++;

                $promedioEstudiante = $sumaNotas / $contadorNotas;
                if ($promedioEstudiante >= 11) {
                    $totalAprobados++;
                } else {
                    $totalDesaprobados++;
                }
            }

            $spreadsheet->getSheetByName('REVES1')->setCellValue('AP7', $totalMatriculados);
            // $spreadsheet->getSheetByName('CARA1')->setCellValue('AP8', $totalAprobados);
            // $spreadsheet->getSheetByName('CARA1')->setCellValue('AP9', $totalDesaprobados);

            $spreadsheet->getSheetByName('REVES2')->setCellValue('AP7', $totalMatriculados);
            // $spreadsheet->getSheetByName('CARA2')->setCellValue('AP8', $totalAprobados);
            // $spreadsheet->getSheetByName('CARA2')->setCellValue('AP9', $totalDesaprobados);
        }
    }

    private function obtenerCondicion($pago): string
    {
        if (!$pago) {
            return '';
        }

        return $pago->condicion;
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

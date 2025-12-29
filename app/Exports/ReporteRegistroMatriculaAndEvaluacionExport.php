<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ReporteRegistroMatriculaAndEvaluacionExport
{
    protected $idGrupo;

    public function __construct($idGrupo)
    {
        $this->idGrupo = $idGrupo;
    }

    public function build()
    {
        $spreadsheet = IOFactory::load(
            storage_path('app/templates/REGISTRO DE MATRICULA Y REGISTRO DE EVALUACIÓN POR MÓDULO.xlsx')
        );

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Asistencias');

        /*
        |--------------------------------------------------------------------------
        | 1. ESTUDIANTES ORDENADOS
        |--------------------------------------------------------------------------
        */
        $estudiantes = DB::table('asistencia as a')
            ->join('estudiante as u', 'u.id', '=', 'a.id_estudiante')
            ->where('a.id_grupo', $this->idGrupo)
            ->select(
                'u.id',
                DB::raw("CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo")
            )
            ->distinct()
            ->orderBy('nombre_completo')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 2. EXTENDER PLANTILLA (SIN MERGES)
        |--------------------------------------------------------------------------
        */
        $filaInicio = 6;
        $filasPlantilla = 26;
        $totalEstudiantes = $estudiantes->count();

        if ($totalEstudiantes > $filasPlantilla) {

            $filasAInsertar = $totalEstudiantes - $filasPlantilla;
            $filaModelo = $filaInicio;

            // Insertar filas nuevas
            $sheet->insertNewRowBefore(
                $filaInicio + $filasPlantilla,
                $filasAInsertar
            );

            // Columnas reales de tu tabla (ajusta si usas más)
            $columnas = range('C', 'U');

            for ($i = 0; $i < $filasAInsertar; $i++) {

                $filaDestino = $filaInicio + $filasPlantilla + $i;

                foreach ($columnas as $col) {

                    $celdaModelo  = "{$col}{$filaModelo}";
                    $celdaDestino = "{$col}{$filaDestino}";

                    // Copiar estilos
                    $sheet->duplicateStyle(
                        $sheet->getStyle($celdaModelo),
                        $celdaDestino
                    );

                    // Bordes
                    $sheet->getStyle($celdaDestino)
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }

                // Copiar altura de fila
                $altura = $sheet->getRowDimension($filaModelo)->getRowHeight();
                if ($altura !== null) {
                    $sheet->getRowDimension($filaDestino)->setRowHeight($altura);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 3. FALTAS POR ESTUDIANTE (D → DERECHA)
        |--------------------------------------------------------------------------
        */
        $fila = $filaInicio;

        foreach ($estudiantes as $estudiante) {

            $fechasFalta = DB::table('asistencia')
                ->where('id_grupo', $this->idGrupo)
                ->where('id_estudiante', $estudiante->id)
                ->where('asistencia', 2)
                ->orderBy('fecha_actual')
                ->pluck('fecha_actual')
                ->toArray();

            $columna = 'D';

            if (empty($fechasFalta)) {

                $sheet->setCellValue("{$columna}{$fila}", '—');
                $sheet->getStyle("{$columna}{$fila}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            } else {

                foreach ($fechasFalta as $fecha) {

                    $excelDate = Date::stringToExcel(date('Y-m-d', strtotime($fecha)));

                    $sheet->setCellValueExplicit(
                        "{$columna}{$fila}",
                        $excelDate,
                        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                    );

                    $sheet->getStyle("{$columna}{$fila}")
                        ->getNumberFormat()
                        ->setFormatCode('dd/mm');

                    $sheet->getStyle("{$columna}{$fila}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);


                    $columna++;
                }
            }

            $fila++;
        }

        return $spreadsheet;
    }
}

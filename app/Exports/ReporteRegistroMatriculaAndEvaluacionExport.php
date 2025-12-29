<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

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

        /* ======================================================
        | 1. ESTUDIANTES
        ====================================================== */
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

        /* ======================================================
        | 2. EXTENDER PLANTILLA
        ====================================================== */
        $filaInicio = 6;
        $filasPlantilla = 26;
        $totalEstudiantes = $estudiantes->count();

        if ($totalEstudiantes > $filasPlantilla) {

            $filasAInsertar = $totalEstudiantes - $filasPlantilla;
            $filaModelo = $filaInicio;

            $sheet->insertNewRowBefore(
                $filaInicio + $filasPlantilla,
                $filasAInsertar
            );

            $columnas = range('C', 'U');

            for ($i = 0; $i < $filasAInsertar; $i++) {

                $filaDestino = $filaInicio + $filasPlantilla + $i;

                foreach ($columnas as $col) {

                    $sheet->duplicateStyle(
                        $sheet->getStyle("{$col}{$filaModelo}"),
                        "{$col}{$filaDestino}"
                    );

                    $sheet->getStyle("{$col}{$filaDestino}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }

                $altura = $sheet->getRowDimension($filaModelo)->getRowHeight();
                if ($altura !== null) {
                    $sheet->getRowDimension($filaDestino)->setRowHeight($altura);
                }
            }
        }

        /* ======================================================
        | 3. FALTAS + AUTOINCREMENTO + FÓRMULAS
        ====================================================== */
        $fila = $filaInicio;

        // fila base donde está =CONTAR(D6:S6)
        $filaBaseFormula = $filaInicio;

        $formulaBaseT = $sheet->getCell("T{$filaBaseFormula}")->getValue();
        $formulaBaseU = $sheet->getCell("U{$filaBaseFormula}")->getValue();

        $ultimoNumero = (int) $sheet->getCell("C" . ($filaInicio - 1))->getValue();

        foreach ($estudiantes as $estudiante) {

            /* --- COLUMNA C AUTOINCREMENTAL --- */
            $ultimoNumero++;
            $sheet->setCellValue("C{$fila}", $ultimoNumero);
            $sheet->getStyle("C{$fila}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            /* --- FALTAS DESDE D --- */
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

            /* --- FÓRMULAS T y U (CORRECTAS) --- */
            if ($formulaBaseT && strpos($formulaBaseT, '=') === 0) {
                $sheet->setCellValue(
                    "T{$fila}",
                    str_replace($filaBaseFormula, $fila, $formulaBaseT)
                );
            }

            if ($formulaBaseU && strpos($formulaBaseU, '=') === 0) {

                // SOLO cambia T6 → T{fila}
                $formulaU = str_replace(
                    "T{$filaBaseFormula}",
                    "T{$fila}",
                    $formulaBaseU
                );

                $sheet->setCellValue("U{$fila}", $formulaU);
            }


            $fila++;
        }

        /* ======================================================
        | 4. REGISTRO DE EVALUACIÓN (UNIDADES DIDÁCTICAS)
        ====================================================== */
        // bajar 5 filas después de asistencias
        $fila += 5;

        /* ---------- ESTUDIANTES ---------- */
        $alumnos = DB::table('matricula')
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->where('matricula.id_grupo', $this->idGrupo)
            ->where(function ($q) {
                $q->whereNull('matricula.reserva')
                    ->orWhere('matricula.reserva', 0);
            })
            ->select(
                'estudiante.id',
                'estudiante.nro_documento',
                DB::raw("CONCAT(
            estudiante.apellido_paterno, ' ',
            estudiante.apellido_materno, ', ',
            estudiante.nombre
        ) AS nombre_completo")
            )
            ->orderBy('estudiante.apellido_paterno')
            ->get();

        /* ---------- PLANTILLA ---------- */
        $filaInicioEval     = $fila;
        $filasPlantillaEval = 26;
        $totalAlumnos       = $alumnos->count();

        /* ======================================================
| INSERTAR FILAS (EMPUJA TODO LO DE ABAJO)
====================================================== */
        if ($totalAlumnos > $filasPlantillaEval) {

            $filasAInsertar = $totalAlumnos - $filasPlantillaEval;
            $filaModelo     = $filaInicioEval;

            $sheet->insertNewRowBefore(
                $filaInicioEval + $filasPlantillaEval,
                $filasAInsertar
            );

            $columnas = range('B', 'U');

            for ($i = 0; $i < $filasAInsertar; $i++) {

                $filaDestino = $filaInicioEval + $filasPlantillaEval + $i;

                foreach ($columnas as $col) {

                    $sheet->duplicateStyle(
                        $sheet->getStyle("{$col}{$filaModelo}"),
                        "{$col}{$filaDestino}"
                    );

                    $sheet->getStyle("{$col}{$filaDestino}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }

                // altura de fila
                $altura = $sheet->getRowDimension($filaModelo)->getRowHeight();
                if ($altura !== null) {
                    $sheet->getRowDimension($filaDestino)->setRowHeight($altura);
                }

                // combinar nombre D:U
                $sheet->mergeCells("D{$filaDestino}:U{$filaDestino}");
            }
        }

        /* ---------- CAPACIDADES ---------- */
        $capacidades = DB::table('capacidad_terminal')
            ->where('id_grupo', $this->idGrupo)
            ->orderBy('numero_capacidad', 'asc')
            ->get();

        /* ---------- CABECERA ---------- */
        $col = 'E';
        foreach ($capacidades as $cap) {
            $sheet->setCellValue("{$col}{$fila}", "UD {$cap->numero_capacidad}");
            $sheet->getStyle("{$col}{$fila}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col++;
        }


        /* ---------- NOTAS ---------- */
        $notas = DB::table('nota_capacidad_terminal')
            ->where('id_grupo', $this->idGrupo)
            ->get()
            ->groupBy(fn($n) => $n->id_estudiante . '-' . $n->id_capacidad);

        /* ---------- ÍNDICE COLUMNA B ---------- */
        $ultimoIndice = (int) $sheet->getCell("B" . ($filaInicioEval - 1))->getValue();

        /* ---------- LLENAR ---------- */
        foreach ($alumnos as $alumno) {

            // índice
            $ultimoIndice++;
            $sheet->setCellValue("B{$fila}", $ultimoIndice);
            $sheet->getStyle("B{$fila}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // DNI como STRING
            $sheet->setCellValueExplicit(
                "C{$fila}",
                (string) $alumno->nro_documento,
                DataType::TYPE_STRING
            );

            // nombre (combinado)
            $sheet->mergeCells("D{$fila}:U{$fila}");
            $sheet->setCellValue("D{$fila}", $alumno->nombre_completo);
            $sheet->getStyle("D{$fila}")
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $col = 'V';

            foreach ($capacidades as $cap) {

                $key  = $alumno->id . '-' . $cap->id;
                $nota = $notas->get($key)?->first()?->nota_capacidad;

                $sheet->setCellValue("{$col}{$fila}", $nota ?? '—');
                $sheet->getStyle("{$col}{$fila}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Aplicar color: rojo si < 11, negro si >= 11
                if ($nota !== null) {
                    $color = ($nota < 11) ? 'FF0000' : '000000';
                    $sheet->getStyle("{$col}{$fila}")
                        ->getFont()
                        ->getColor()
                        ->setRGB($color);
                }

                $col++;
            }
            $fila++;
        }

        return $spreadsheet;
    }
}

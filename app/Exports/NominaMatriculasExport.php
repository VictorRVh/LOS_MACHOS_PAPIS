<?php

namespace App\Exports;

use App\Models\Matricula;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class NominaMatriculasExport
{
    protected $idGrupo;

    public function __construct($idGrupo)
    {
        $this->idGrupo = $idGrupo;
    }

    public function build(): Spreadsheet
    {
        // 1. Cargar plantilla
        $spreadsheet = IOFactory::load(storage_path('app/templates/nomina_matricula_tres.xlsx'));
        $sheet = $spreadsheet->getActiveSheet();

        // 2. Obtener datos
        // $matriculas = Matricula::where('id_grupo', $this->idGrupo)
        //     ->with('estudiante')
        //     ->get();

        $matriculas = Matricula::where('id_grupo', $this->idGrupo)
            ->where('matricula.reserva', 0)
            ->with([
                'estudiante',
                'pago',
                'grupo.modulo'
            ])
            ->join('estudiante as e', 'matricula.id_estudiante', '=', 'e.id')
            ->orderBy('e.apellido_paterno', 'asc')
            ->orderBy('e.apellido_materno', 'asc')
            ->orderBy('e.nombre', 'asc')
            ->select('matricula.*')
            ->get();



        $especialidad = $matriculas->first()?->grupo?->especialidad?->especialidadMadre?->nombre_especialidad ?? '';
        $modulo = $matriculas->first()?->grupo?->modulo?->descripcion ?? '';
        $nivel_formativo = $matriculas->first()?->grupo?->programaEstudio?->ciclo?->nombre_ciclo ?? '';
        $turno = $matriculas->first()?->grupo?->turno ?? '';
        $periodo = $matriculas->first()?->grupo?->periodo?->nombre_periodo ?? '';
        $seccion = $matriculas->first()?->grupo?->seccion;
        $nroCapacidades = $matriculas->first()?->grupo?->modulo?->nro_capacidades ?? '';
        $nroCreditos    = $matriculas->first()?->grupo?->modulo?->creditos ?? '';

        // Unir celdas de G10 a Q10
        $sheet->mergeCells('F10:I10');
        $sheet->mergeCells('F11:I11');
        $sheet->mergeCells('F12:I12');
        $sheet->mergeCells('F14:I14');
        $sheet->mergeCells('M10:O10');
        $sheet->mergeCells('M14:O14');

        // Asignar valor
        $sheet->setCellValue('F10', $especialidad);
        $sheet->setCellValue('F11', $modulo);
        $sheet->setCellValue('F12', $nivel_formativo);
        $sheet->setCellValue('F14', $turno);
        $sheet->setCellValue('M10', $periodo);
        $sheet->setCellValue('M14', $seccion);

        // Centrar el texto
        $sheet->getStyle('G10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G10')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // (Opcional) ponerlo en negrita
        $sheet->getStyle('G10')->getFont()->setBold(true);

        // 3. Llenar datos
        $filaInicio = 17;
        $totalMatriculas = $matriculas->count();
        $filasPlantilla = 30;

        if ($totalMatriculas > $filasPlantilla) {

            $filasAInsertar = $totalMatriculas - $filasPlantilla;
            $filaModelo = $filaInicio;

            // Obtener celdas combinadas de la fila modelo
            $mergedCells = [];
            foreach ($sheet->getMergeCells() as $merge) {
                if (str_contains($merge, (string)$filaModelo)) {
                    $mergedCells[] = $merge;
                }
            }

            // Insertar filas nuevas
            $sheet->insertNewRowBefore($filaInicio + $filasPlantilla, $filasAInsertar);

            $columnas = ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'];

            for ($i = 0; $i < $filasAInsertar; $i++) {

                $filaDestino = $filaInicio + $filasPlantilla + $i;

                foreach ($columnas as $col) {

                    $celdaModelo = "{$col}{$filaModelo}";
                    $celdaDestino = "{$col}{$filaDestino}";

                    // Copiar estilo completo
                    $sheet->duplicateStyle(
                        $sheet->getStyle($celdaModelo),
                        $celdaDestino
                    );

                    // Ajustar bordes a THIN
                    $sheet->getStyle($celdaDestino)
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }

                $border = $sheet->getStyle("B{$filaDestino}:O{$filaDestino}")->getBorders();
                // $border->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
                // $border->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
                $border->getLeft()->setBorderStyle(Border::BORDER_MEDIUM);
                $border->getRight()->setBorderStyle(Border::BORDER_MEDIUM);

                // Copiar altura de fila
                $altura = $sheet->getRowDimension($filaModelo)->getRowHeight();
                if ($altura !== null) {
                    $sheet->getRowDimension($filaDestino)->setRowHeight($altura);
                }

                // Aplicar celdas combinadas
                foreach ($mergedCells as $merge) {

                    preg_match('/([A-Z]+)(\d+):([A-Z]+)(\d+)/', $merge, $m);

                    if ($m) {
                        $colIni = $m[1];
                        $colFin = $m[3];

                        $nuevoRango = "{$colIni}{$filaDestino}:{$colFin}{$filaDestino}";
                        $sheet->mergeCells($nuevoRango);
                    }
                }
            }
        }

        // 4. Llenar datos de estudiantes
        $fila = $filaInicio;
        foreach ($matriculas as $index => $matricula) {

            $est = $matricula->estudiante;

            $sheet->setCellValue("B{$fila}", str_pad($index + 1, 2, '0', STR_PAD_LEFT));
            $sheet->setCellValue("C{$fila}", $est->nro_documento);
            $sheet->setCellValue("F{$fila}", "{$est->apellido_paterno} {$est->apellido_materno}, {$est->nombre}");
            // $sheet->setCellValue("K{$fila}", $est->sexo);

            $sexoExcel = match (strtoupper($est->sexo)) {
                'M' => 'H',
                'F' => 'M',
                default => $est->sexo ?? '—',
            };

            $sheet->setCellValue("K{$fila}", $sexoExcel);

            $sheet->setCellValue("L{$fila}", $est->fecha_nacimiento ?? '');

            $sheet->setCellValue(
                "M{$fila}",
                $matricula->pago?->condicion ?? '—'
            );

            $sheet->setCellValue("N{$fila}", $nroCapacidades);

            $sheet->setCellValue("O{$fila}", $nroCreditos);

            $fila++;
        }

        // 5. Limpiar filas sobrantes
        if ($totalMatriculas < $filasPlantilla) {
            for ($i = $filaInicio + $totalMatriculas; $i < $filaInicio + $filasPlantilla; $i++) {
                $sheet->setCellValue("B{$i}", '');
                $sheet->setCellValue("C{$i}", '');
                $sheet->setCellValue("F{$i}", '');
                $sheet->setCellValue("K{$i}", '');
                $sheet->setCellValue("L{$i}", '');
            }
        }

        return $spreadsheet;
    }
}

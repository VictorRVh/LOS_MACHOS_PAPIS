<?php

namespace App\Exports;

use App\Models\Grupo;
use App\Models\Matricula;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReporteConsolidadoNotasExport
{
    protected string $idGrupo;

    public function __construct(string $idGrupo)
    {
        $this->idGrupo = $idGrupo;
    }

    public function build(): Spreadsheet
    {
        $spreadsheet = IOFactory::load(
            storage_path('app/templates/consolidado-notas-ultimo.xlsx')
        );

        $grupo = Grupo::with([
            'especialidad.especialidadMadre',
            'modulo',
            'periodo',
            'programaEstudio.ciclo'
        ])->findOrFail($this->idGrupo);

        $this->llenarDatosGenerales($spreadsheet, $grupo);
        $this->llenarNombreModulo($spreadsheet, $grupo);
        $this->llenarEstudiantes($spreadsheet, $grupo);

        return $spreadsheet;
    }

    private function llenarDatosGenerales(
        Spreadsheet $spreadsheet,
        Grupo $grupo
    ): void {

        $sheet = $spreadsheet->getSheetByName('CONSOLIDADO');

        if (!$sheet) {
            throw new \Exception('La hoja CONSOLIDADO no existe');
        }

        $nombreEspecialidad =
            $grupo->especialidad?->especialidadMadre?->nombre_especialidad ?? '';

        $sheet->setCellValue(
            'S4',
            'PROGRAMA DE ESTUDIOS: ' . $nombreEspecialidad
        );

        $nombreCiclo =
            $grupo->programaEstudio?->ciclo?->nombre_ciclo ?? '';

        $sheet->setCellValue(
            'S7',
            'NIVEL FORMATIVO: ' . $nombreCiclo
        );
    }

    private function llenarNombreModulo(
        Spreadsheet $spreadsheet,
        Grupo $grupo
    ): void {

        $sheet = $spreadsheet->getSheetByName('CONSOLIDADO');

        if (!$sheet) {
            throw new \Exception('La hoja CONSOLIDADO no existe');
        }

        $sheet->setCellValue(
            'K5',
            $grupo->modulo?->descripcion ?? ''
        );
    }

    private function llenarEstudiantes(
        Spreadsheet $spreadsheet,
        Grupo $grupo
    ): void {

        $sheet = $spreadsheet->getSheetByName('CONSOLIDADO');

        $filaInicio = 12;
        $filaFirmas = 32;

        /////////////////////////////////////////////////////
        // OBTENER MATRICULAS (FALTABA ESTO)
        /////////////////////////////////////////////////////

        $matriculas = Matricula::with('estudiante')
            ->where('id_grupo', $grupo->id)
            ->where('reserva', 0)
            ->join('estudiante as e', 'matricula.id_estudiante', '=', 'e.id')
            ->orderBy('e.apellido_paterno')
            ->orderBy('e.apellido_materno')
            ->orderBy('e.nombre')
            ->select('matricula.*')
            ->get();

        $totalEstudiantes = $matriculas->count();

        /////////////////////////////////////////////////////
        // CALCULAR CAPACIDAD DE PLANTILLA
        /////////////////////////////////////////////////////

        $capacidadPlantilla = $filaFirmas - $filaInicio;

        /////////////////////////////////////////////////////
        // INSERTAR FILAS SI ES NECESARIO
        /////////////////////////////////////////////////////

        if ($totalEstudiantes > $capacidadPlantilla) {

            $filasAInsertar = $totalEstudiantes - $capacidadPlantilla;

            $sheet->insertNewRowBefore($filaFirmas, $filasAInsertar);

            $filaModelo = $filaInicio;

            $mergedCells = $sheet->getMergeCells();

            for ($i = 0; $i < $filasAInsertar; $i++) {

                $filaDestino = $filaFirmas + $i;

                foreach (range('A', 'Z') as $col) {

                    $sheet->duplicateStyle(
                        $sheet->getStyle("{$col}{$filaModelo}"),
                        "{$col}{$filaDestino}"
                    );
                }

                $altura = $sheet->getRowDimension($filaModelo)->getRowHeight();

                if ($altura) {
                    $sheet->getRowDimension($filaDestino)
                        ->setRowHeight($altura);
                }

                foreach ($mergedCells as $merge) {

                    if (preg_match('/([A-Z]+)(\d+):([A-Z]+)(\d+)/', $merge, $m)) {

                        if ((int)$m[2] == $filaModelo) {

                            $sheet->mergeCells(
                                "{$m[1]}{$filaDestino}:{$m[3]}{$filaDestino}"
                            );
                        }
                    }
                }
            }
        }

        /////////////////////////////////////////////////////
        // LLENAR ESTUDIANTES
        /////////////////////////////////////////////////////

        foreach ($matriculas as $index => $matricula) {

            $fila = $filaInicio + $index;

            $est = $matricula->estudiante;

            $sheet->setCellValue(
                "B{$fila}",
                $est->nro_documento ?? ''
            );

            $sheet->setCellValue(
                "C{$fila}",
                "{$est->apellido_paterno} {$est->apellido_materno}, {$est->nombre}"
            );
        }
    }
}

<?php

namespace App\Exports;

use App\Models\Grupo;
use App\Models\Matricula;
use App\Models\CapacidadTerminal;
use App\Models\Modulo;
use App\Models\NotaCapacidadTerminal;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

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
            'especialidad',
            'modulo',
            'periodo',
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

        // PROGRAMA DE ESTUDIOS: (nombre de la especialidad)
        $nombreEspecialidad = $grupo->especialidad->especialidadMadre->nombre_especialidad ?? '';

        $sheet->setCellValue(
            'S4',
            'PROGRAMA DE ESTUDIOS: ' . $nombreEspecialidad
        );

        $nombreCiclo = $grupo->programaEstudio->ciclo->nombre_ciclo ?? '';

        // Ciclo
        $sheet->setCellValue(
            'S7',
            'NIVEL FORMATIVO: ' . $nombreCiclo
        );
    }

    private function llenarEstudiantes(
        Spreadsheet $spreadsheet,
        Grupo $grupo
    ): void {
        $sheet = $spreadsheet->getSheetByName('CONSOLIDADO');
        $filaInicio = 12;

        // Obtener estudiantes del grupo actual
        $matriculas = Matricula::with('estudiante')
            ->where('id_grupo', $grupo->id)
            ->where('reserva', 0)
            ->orderBy('id_estudiante')
            ->get();

        foreach ($matriculas as $index => $matricula) {
            $fila = $filaInicio + $index;
            $est = $matricula->estudiante;

            // DNI
            $sheet->setCellValue("B{$fila}", $est->nro_documento);

            // Nombres completos
            $sheet->setCellValue(
                "C{$fila}",
                "{$est->apellido_paterno} {$est->apellido_materno}, {$est->nombre}"
            );
        }
    }

    private function llenarNombreModulo(
        Spreadsheet $spreadsheet,
        Grupo $grupo
    ): void {
        $sheet = $spreadsheet->getSheetByName('CONSOLIDADO');

        if (!$sheet) {
            throw new \Exception('La hoja CONSOLIDADO no existe');
        }

        // Mostrar solo el módulo del grupo actual
        $fila = 5;
        $col = 'K'; // Columna donde va el nombre del módulo

        $sheet->setCellValue(
            "{$col}{$fila}",
            $grupo->modulo->descripcion
        );
    }
}

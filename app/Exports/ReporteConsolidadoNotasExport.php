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
            storage_path('app/templates/consolidado-notas.xlsx')
        );

        $grupo = Grupo::with([
            'capacidadTerminal',
            'especialidad',
            'modulo',
            'periodo',
        ])->findOrFail($this->idGrupo);

        $this->llenarNombresModulos($spreadsheet, $grupo);
        $this->llenarEstudiantesYPromedios($spreadsheet, $grupo);

        return $spreadsheet;
    }

    private function llenarEstudiantesYPromedios(
        Spreadsheet $spreadsheet,
        Grupo $grupo
    ): void {
        $sheet = $spreadsheet->getSheetByName('CONSOLIDADO');
        $filaInicio = 12;

        // Obtener todos los módulos de la especialidad
        $modulos = Modulo::where('id_especialidad', $grupo->id_especialidad)
            ->orderBy('numero_modulo')
            ->limit(7)
            ->get();

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

            // Nombres
            $sheet->setCellValue(
                "C{$fila}",
                "{$est->apellido_paterno} {$est->apellido_materno}, {$est->nombre}"
            );

            // Llenar notas por cada módulo
            $colInicio = 'K'; // Columna donde empiezan los módulos
            $colIndex = Coordinate::columnIndexFromString($colInicio);

            foreach ($modulos as $i => $modulo) {
                // Buscar el grupo del estudiante para este módulo
                $grupoModulo = Grupo::where('id_modulo', $modulo->id)
                    ->where('id_especialidad', $grupo->id_especialidad)
                    ->whereHas('matricula', function ($q) use ($est) {
                        $q->where('id_estudiante', $est->id)
                            ->where('reserva', 0);
                    })
                    ->first();

                if ($grupoModulo) {
                    // Calcular promedio del módulo
                    $promedioModulo = $this->calcularPromedioModuloPorEstudiante(
                        $grupoModulo->id,
                        $est->id
                    );

                    // Columna actual del módulo
                    $col = Coordinate::stringFromColumnIndex($colIndex + $i);

                    // Marcar A, B o C según el promedio
                    $this->marcarRangoNota(
                        $sheet,
                        $fila,
                        $col,
                        $promedioModulo
                    );
                }
            }
        }
    }

    private function calcularPromedioModuloPorEstudiante(
        string $idGrupo,
        string $idEstudiante
    ): ?float {
        // Obtener todas las capacidades terminales del grupo
        $capacidades = CapacidadTerminal::where('id_grupo', $idGrupo)->get();

        if ($capacidades->isEmpty()) {
            return null;
        }

        $suma = 0;
        $contador = 0;

        foreach ($capacidades as $capacidad) {
            // Obtener la nota de la capacidad para este estudiante
            $notaCapacidad = NotaCapacidadTerminal::where('id_grupo', $idGrupo)
                ->where('id_capacidad', $capacidad->id)
                ->where('id_estudiante', $idEstudiante)
                ->first();

            if ($notaCapacidad && is_numeric($notaCapacidad->nota_capacidad)) {
                $suma += $notaCapacidad->nota_capacidad;
                $contador++;
            }
        }

        return $contador > 0 ? round($suma / $contador, 2) : null;
    }

    private function llenarNombresModulos(
        Spreadsheet $spreadsheet,
        Grupo $grupo
    ): void {
        $sheet = $spreadsheet->getSheetByName('CONSOLIDADO');

        if (!$sheet) {
            throw new \Exception('La hoja CONSOLIDADO no existe');
        }

        // Obtener módulos de la especialidad
        $modulos = Modulo::where('id_especialidad', $grupo->id_especialidad)
            ->orderBy('numero_modulo')
            ->limit(7)
            ->get();

        $fila = 5;
        $colInicio = 'K';
        $colIndex = Coordinate::columnIndexFromString($colInicio);

        foreach ($modulos as $i => $modulo) {
            $col = Coordinate::stringFromColumnIndex($colIndex + $i);
            $sheet->setCellValue(
                "{$col}{$fila}",
                $modulo->descripcion
            );
        }
    }

    private function marcarRangoNota(
        $sheet,
        int $fila,
        string $col,
        ?float $promedio
    ): void {
        if ($promedio === null) return;

        // Determinar qué letra corresponde según el promedio
        if ($promedio >= 17) {
            $letra = 'AD'; // Logro destacado
        } elseif ($promedio >= 14) {
            $letra = 'A'; // Logro previsto
        } elseif ($promedio >= 11) {
            $letra = 'B'; // En proceso
        } else {
            $letra = 'C'; // En inicio
        }

        // Escribir la letra en la celda correspondiente
        $sheet->setCellValue("{$col}{$fila}", $letra);
    }
}

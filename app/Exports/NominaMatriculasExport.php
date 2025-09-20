<?php

namespace App\Exports;

use App\Models\Matricula;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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
        $spreadsheet = IOFactory::load(storage_path('app/templates/nomina_matricula.xlsx'));
        $sheet = $spreadsheet->getActiveSheet();

        // 2. Obtener datos
        // $matriculas = Matricula::where('id_grupo', $this->idGrupo)
        //     ->with('estudiante')
        //     ->get();

        $matriculas = Matricula::where('id_grupo', $this->idGrupo)
            ->where('matricula.reserva', 0)
            ->with('estudiante')
            ->join('estudiante as e', 'matricula.id_estudiante', '=', 'e.id')
            ->orderBy('e.apellido_paterno', 'asc')
            ->orderBy('e.apellido_materno', 'asc')
            ->orderBy('e.nombre', 'asc')
            ->select('matricula.*') // importante para evitar conflictos
            ->get();


        $especialidad = $matriculas->first()?->grupo?->especialidad?->especialidadMadre?->nombre_especialidad ?? '';
        $modulo = $matriculas->first()?->grupo?->modulo?->descripcion ?? '';
        $nivel_formativo = $matriculas->first()?->grupo?->programaEstudio?->ciclo?->nombre_ciclo ?? '';
        $turno = $matriculas->first()?->grupo?->turno ?? '';
        $periodo = $matriculas->first()?->grupo?->periodo?->nombre_periodo ?? '';
        $seccion = $matriculas->first()?->grupo?->seccion;

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
        $fila = 17;
        foreach ($matriculas as $index => $matricula) {
            $est = $matricula->estudiante;
            // $sheet->setCellValue("B{$fila}", str_pad($index + 1, 2, '0', STR_PAD_LEFT));
            $sheet->setCellValue("C{$fila}", $est->nro_documento);
            $sheet->setCellValue("F{$fila}", "{$est->apellido_paterno} {$est->apellido_materno}, {$est->nombre}");
            $sheet->setCellValue("K{$fila}", $est->sexo);
            $sheet->setCellValue("L{$fila}", $est->fecha_nacimiento ?? '');
            $fila++;
        }

        return $spreadsheet;
    }
}

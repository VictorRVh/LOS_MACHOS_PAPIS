<?php

namespace App\Exports;

use App\Models\Matricula;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

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

        // 🔥 Formato TEXTO real en columna C
        $sheet->getStyle('C')->getNumberFormat()->setFormatCode('@');

        // 🔥 Ajustar ancho de columnas
        $sheet->getColumnDimension('B')->setWidth(5);   // Correlativo
        $sheet->getColumnDimension('C')->setWidth(6);   // DNI
        $sheet->getColumnDimension('F')->setWidth(30);  // Nombre completo
        $sheet->getColumnDimension('K')->setWidth(6);   // Sexo
        $sheet->getColumnDimension('L')->setWidth(12);  // Fecha nacimiento

        // 2. Obtener alumnos
        $matriculas = Matricula::where('id_grupo', $this->idGrupo)
            ->where('matricula.reserva', 0)
            ->with('estudiante', 'grupo.especialidad.especialidadMadre', 'grupo.modulo', 'grupo.programaEstudio.ciclo', 'grupo.periodo')
            ->join('estudiante as e', 'matricula.id_estudiante', '=', 'e.id')
            ->orderBy('e.apellido_paterno', 'asc')
            ->orderBy('e.apellido_materno', 'asc')
            ->orderBy('e.nombre', 'asc')
            ->select('matricula.*')
            ->get();

        // 3. Datos del encabezado
        $grupo = $matriculas->first()?->grupo;

        $sheet->mergeCells('F10:I10');
        $sheet->mergeCells('F11:I11');
        $sheet->mergeCells('F12:I12');
        $sheet->mergeCells('F14:I14');
        $sheet->mergeCells('M10:O10');
        $sheet->mergeCells('M14:O14');

        $sheet->setCellValue('F10', $grupo?->especialidad?->especialidadMadre?->nombre_especialidad ?? '');
        $sheet->setCellValue('F11', $grupo?->modulo?->descripcion ?? '');
        $sheet->setCellValue('F12', $grupo?->programaEstudio?->ciclo?->nombre_ciclo ?? '');
        $sheet->setCellValue('F14', $grupo?->turno ?? '');
        $sheet->setCellValue('M10', $grupo?->periodo?->nombre_periodo ?? '');
        $sheet->setCellValue('M14', $grupo?->seccion ?? '');

        // 4. Ajustar filas dinámicamente
        $inicio = 17;
        $total_alumnos = count($matriculas);

        // Insertar filas si hay más alumnos que filas existentes
        if ($total_alumnos > 0) {
            $sheet->insertNewRowBefore($inicio, $total_alumnos - 1); // -1 porque ya hay 1 fila inicial
        }

        // -----------------------------
        // 5. Llenar alumnos
        // -----------------------------
        $fila = $inicio;
        $contador = 1;

        foreach ($matriculas as $matricula) {
            $est = $matricula->estudiante;

            // Número correlativo
            $sheet->setCellValueExplicit(
                "B{$fila}",
                str_pad($contador, 2, '0', STR_PAD_LEFT),
                DataType::TYPE_STRING
            );

            // Combinar columnas C, D y E para el DNI
            $sheet->mergeCells("C{$fila}:E{$fila}");

            // DNI como TEXTO centrado
            $sheet->setCellValueExplicit(
                "C{$fila}",
                trim((string)$est->nro_documento),
                DataType::TYPE_STRING
            );
            $sheet->getStyle("C{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Nombre completo
            $sheet->setCellValue(
                "F{$fila}",
                "{$est->apellido_paterno} {$est->apellido_materno}, {$est->nombre}"
            );

            // Sexo
            $sheet->setCellValue("K{$fila}", $est->sexo);

            // Fecha nacimiento
            $sheet->setCellValue("L{$fila}", $est->fecha_nacimiento ?? '');

            $fila++;
            $contador++;
        }

        // -----------------------------
        // 6. Pie de página dinámico
        // -----------------------------
        $pie_final = $fila; // justo después del último alumno

        $sheet->mergeCells("B{$pie_final}:C{$pie_final}");
        $sheet->mergeCells("F{$pie_final}:I{$pie_final}");
        $sheet->mergeCells("M{$pie_final}:O{$pie_final}");

        return $spreadsheet;
    }
}

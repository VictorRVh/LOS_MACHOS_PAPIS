<?php

namespace App\Exports;

use App\Models\EntregaDocente;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\DB;

class ReporteRegistroMatriculaAndEvaluacionExport
{
    protected $idAdmin;

    public function __construct($idAdmin)
    {
        $this->idAdmin = $idAdmin;
    }

    public function generarReporte()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte Entregas');

        // 🔹 Obtener datos de la tabla (antes para obtener info de especialidad y módulo)
        $entregas = EntregaDocente::with([
            'grupo.especialidad.especialidadMadre',
            'grupo.modulo'
        ])
            ->where('id_admin', $this->idAdmin)
            ->get();

        // 🔹 Obtener el primer grupo para extraer especialidad y módulo
        $primerGrupo = $entregas->first()?->grupo;
        $nombreEspecialidad = $primerGrupo?->especialidad?->especialidadMadre?->nombre_especialidad ?? 'Sin especialidad';
        $nombreModulo = $primerGrupo?->modulo?->descripcion ?? 'Sin módulo';

        // 🔹 Título principal
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'REPORTE DE ENTREGA DE DOCENTES');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 🔹 Especialidad (fila 2)
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'Especialidad: ' . $nombreEspecialidad);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // 🔹 Módulo (fila 3)
        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A3', 'Módulo: ' . $nombreModulo);
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // 🔹 Encabezados de columnas (ahora en fila 5)
        $sheet->fromArray([
            ['#', 'Grupo', 'Docente', 'Fecha Inicio', 'Fecha Fin', '¿Cumplió?', 'Fecha Aplazada', 'Días Aplazados']
        ], null, 'A5');

        $sheet->getStyle('A5:H5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '800000']],
        ]);

        // 🔹 Llenar datos (empezando en fila 6)
        $fila = 6;
        $contador = 1;

        foreach ($entregas as $entrega) {
            $sheet->setCellValue("A{$fila}", $contador++);
            $sheet->setCellValue("B{$fila}", $entrega->grupo->seccion ?? 'Sin nombre');
            $sheet->setCellValue("C{$fila}", $entrega->grupo->docente
                ? trim($entrega->grupo->docente->user->name . ' ' . $entrega->grupo->docente->user->apellido_paterno . ' ' . $entrega->grupo->docente->user->apellido_materno)
                : null);
            $sheet->setCellValue("D{$fila}", $entrega->fecha_inicio);
            $sheet->setCellValue("E{$fila}", $entrega->fecha_fin);
            $sheet->setCellValue("F{$fila}", $entrega->cumplio == 1 ? 'Cumplió' : 'No cumplió');
            $sheet->setCellValue("G{$fila}", $entrega->fecha_aplazada ?? '—');
            $sheet->setCellValue("H{$fila}", $entrega->dias_aplazados ?? '—');

            $fila++;
        }

        // 🔹 Auto ajustar columnas
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 🔹 Bordes (ahora desde fila 5 hasta la última)
        $sheet->getStyle("A5:H" . ($fila - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // 🔹 Generar el archivo en memoria y retornarlo
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $contenido = ob_get_clean();

        return $contenido;
    }
}

<?php

namespace App\Exports;

use App\Models\EntregaDocente;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteEntregaDocenteExport
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

        // Misma logica de datos
        $entregas = EntregaDocente::with([
            'entregaDocenteAdmin',
            'grupo.especialidad.especialidadMadre',
            'grupo.modulo',
            'grupo.docente.user',
        ])
            ->where('id_admin', $this->idAdmin)
            ->get();

        $primerEntrega = $entregas->first();
        $primerGrupo = $primerEntrega?->grupo;
        $idPeriodo = $primerEntrega?->entregaDocenteAdmin?->id_periodo;

        $nombreTipoEntrega = $primerEntrega?->entregaDocenteAdmin?->nombre_entrega ?? 'SIN TIPO DE ENTREGA';
        $nombreEspecialidad = $primerGrupo?->especialidad?->especialidadMadre?->nombre_especialidad ?? 'SIN ESPECIALIDAD';
        $nombreModulo = $primerGrupo?->modulo?->descripcion ?? 'SIN MODULO';

        $fechaInicio = $entregas->min('fecha_inicio');
        $fechaFin = $entregas->max('fecha_fin');
        $periodoNombre = '-';
        if ($idPeriodo) {
            $periodoNombre = (string) (DB::table('periodo')->where('id', $idPeriodo)->value('nombre_periodo') ?? '-');
        }

        $cetpro = DB::table('cetpros')->first();
        $nombreCetpro = strtoupper((string) ($cetpro->cetpro ?? 'PUNO'));
        $gestion = strtoupper((string) ($cetpro->tipo_gestion ?? '-'));
        $ugel = strtoupper((string) ($cetpro->ugel ?? '-'));
        $dre = strtoupper((string) ($cetpro->dre ?? '-'));
        $region = strtoupper((string) ($cetpro->region ?? '-'));
        $provincia = strtoupper((string) ($cetpro->provincia ?? '-'));
        $distrito = strtoupper((string) ($cetpro->distrito ?? '-'));
        $direccion = strtoupper((string) ($cetpro->direccion ?? '-'));
        $anio = (string) ($cetpro->anio ?? '-');

        // Encabezado institucional
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');
        $sheet->mergeCells('A3:F3');
        $sheet->mergeCells('A4:F4');
        $sheet->mergeCells('A5:F5');
        $sheet->mergeCells('G3:H5');

        $sheet->setCellValue(
            'A1',
            'REPORTE DE ENTREGA DE DOCENTES - ' . strtoupper((string) $nombreTipoEntrega) . ' - PERIODO: ' . strtoupper($periodoNombre)
        );
        $sheet->setCellValue(
            'A2',
            $nombreCetpro
                . ' | Fecha inicio: ' . ($fechaInicio ? Carbon::parse($fechaInicio)->format('Y-m-d H:i') : '-')
                . ' | Fecha fin: ' . ($fechaFin ? Carbon::parse($fechaFin)->format('Y-m-d H:i') : '-')
                . ' | Generado: ' . now()->format('d/m/Y, h:i:s a')
        );
        $sheet->setCellValue('A3', 'Emitido por: CETPRO ' . $nombreCetpro);
        $sheet->setCellValue('A4', 'Tipo de gestión: ' . $gestion . ' | UGEL: ' . $ugel . ' | DRE: ' . $dre);
        $sheet->setCellValue(
            'A5',
            'Ubicación: ' . $region . ' / ' . $provincia . ' / ' . $distrito
                . ' | Dirección: ' . $direccion
                . ' | Año: ' . $anio
        );

        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F2747'],
            ],
        ]);

        $sheet->getStyle('A2:H2')->applyFromArray([
            'font' => ['size' => 11, 'color' => ['rgb' => '1E3A5F']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CAD2DD'],
            ],
        ]);

        $sheet->getStyle('A3:F5')->applyFromArray([
            'font' => ['size' => 12, 'color' => ['rgb' => '0B172A']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle('A3')->getFont()->setBold(true);

        $sheet->getStyle('G3:H5')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $logoPath = public_path('img/CETPRO_Image.png');
        if (is_file($logoPath)) {
            $drawing = new Drawing();
            $drawing->setPath($logoPath);
            $drawing->setHeight(70);
            $drawing->setCoordinates('G3');
            $drawing->setOffsetX(22);
            $drawing->setOffsetY(2);
            $drawing->setWorksheet($sheet);
        }

        // Datos contextuales del documento
        $sheet->mergeCells('A6:H6');
        $sheet->setCellValue(
            'A6',
            'ESPECIALIDAD: ' . strtoupper((string) $nombreEspecialidad) . '   |   MÓDULO: ' . strtoupper((string) $nombreModulo)
        );
        $sheet->getStyle('A6:H6')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A5F'],
            ],
        ]);

        // Encabezados de tabla
        $sheet->fromArray([
            ['#', 'Grupo', 'Docente', 'Fecha Inicio', 'Fecha Fin', 'Cumplio', 'Fecha Aplazada', 'Dias Aplazados'],
        ], null, 'A7');

        $sheet->getStyle('A7:H7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F2E46'],
            ],
        ]);

        // Llenar datos
        $fila = 8;
        $contador = 1;

        foreach ($entregas as $entrega) {
            $sheet->setCellValue("A{$fila}", $contador++);
            $sheet->setCellValue("B{$fila}", $entrega->grupo->seccion ?? 'SIN GRUPO');
            $sheet->setCellValue(
                "C{$fila}",
                $entrega->grupo->docente && $entrega->grupo->docente->user
                    ? trim(
                        $entrega->grupo->docente->user->name . ' ' .
                        $entrega->grupo->docente->user->apellido_paterno . ' ' .
                        $entrega->grupo->docente->user->apellido_materno
                    )
                    : 'SIN DOCENTE'
            );
            $sheet->setCellValue("D{$fila}", $entrega->fecha_inicio);
            $sheet->setCellValue("E{$fila}", $entrega->fecha_fin);
            $sheet->setCellValue("F{$fila}", $entrega->cumplio == 1 ? 'SI' : 'NO');
            $sheet->setCellValue("G{$fila}", $entrega->fecha_aplazada ?? '-');
            $sheet->setCellValue("H{$fila}", $entrega->dias_aplazados ?? '-');

            if ($entrega->cumplio == 1) {
                $sheet->getStyle("F{$fila}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '166534']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DCFCE7'],
                    ],
                ]);
            } else {
                $sheet->getStyle("F{$fila}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'B91C1C']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEE2E2'],
                    ],
                ]);
            }

            if ($fila % 2 === 0) {
                $sheet->getStyle("A{$fila}:H{$fila}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8FAFC'],
                    ],
                ]);
            }

            $fila++;
        }

        // Ajustes visuales
        $sheet->freezePane('A8');
        $sheet->setAutoFilter('A7:H7');

        $sheet->getDefaultRowDimension()->setRowHeight(21);
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->getRowDimension(2)->setRowHeight(26);
        $sheet->getRowDimension(3)->setRowHeight(24);
        $sheet->getRowDimension(4)->setRowHeight(24);
        $sheet->getRowDimension(5)->setRowHeight(24);
        $sheet->getRowDimension(6)->setRowHeight(22);
        $sheet->getRowDimension(7)->setRowHeight(28);

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(10);
        $sheet->getColumnDimension('C')->setWidth(36);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(19);
        $sheet->getColumnDimension('H')->setWidth(14);

        $sheet->getStyle('A7:H7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A8:B' . ($fila - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D8:H' . ($fila - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A8:H' . ($fila - 1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('D8:E' . ($fila - 1))->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm');

        $sheet->getStyle('A1:H' . max(7, $fila - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);

        // Generar archivo en memoria
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $contenido = ob_get_clean();

        return $contenido;
    }
}

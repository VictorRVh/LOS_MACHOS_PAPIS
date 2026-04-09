<?php

namespace App\Exports;

use App\Models\Cetpro;
use App\Models\EstudianteDocumento;
use App\Models\Periodo;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ReporteCertificadosPeriodoExport
{
    protected string $idPeriodo;

    public function __construct(string $idPeriodo)
    {
        $this->idPeriodo = $idPeriodo;
    }

    public function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Certificados');

        // Misma logica de datos
        $certificados = EstudianteDocumento::with([
            'matricula.estudiante',
            'matricula.grupo.especialidad.especialidadMadre',
            'matricula.grupo.modulo',
            'matricula.grupo.periodo',
            'matricula.grupo.programaEstudio.ciclo',
        ])
            ->whereHas('matricula.grupo', function ($q) {
                $q->where('id_periodo', $this->idPeriodo);
            })
            ->where('tipo_documento', 3)
            ->orderBy('fecha_emision')
            ->get();

        // Periodo siempre desde id solicitado (aunque no haya certificados)
        $periodo = Periodo::where('id', $this->idPeriodo)->value('nombre_periodo')
            ?? $certificados->first()?->matricula?->grupo?->periodo?->nombre_periodo
            ?? '-';
        $cetpro = Cetpro::first();

        $nombreCetpro = strtoupper((string) ($cetpro?->cetpro ?? 'PUNO'));
        $gestion = strtoupper((string) ($cetpro?->tipo_gestion ?? '-'));
        $ugel = strtoupper((string) ($cetpro?->ugel ?? '-'));
        $dre = strtoupper((string) ($cetpro?->dre ?? '-'));
        $region = strtoupper((string) ($cetpro?->region ?? '-'));
        $provincia = strtoupper((string) ($cetpro?->provincia ?? '-'));
        $distrito = strtoupper((string) ($cetpro?->distrito ?? '-'));
        $direccion = strtoupper((string) ($cetpro?->direccion ?? '-'));
        $anio = (string) ($cetpro?->anio ?? '-');

        $fechaInicio = $certificados->min(fn ($c) => $c?->matricula?->grupo?->fecha_inicio) ?: '-';
        $fechaFin = $certificados->max(fn ($c) => $c?->matricula?->grupo?->fecha_fin) ?: '-';

        // Cabecera estilo reportes estadisticos
        $sheet->mergeCells('A1:M1');
        $sheet->mergeCells('A2:M2');
        $sheet->mergeCells('A3:J3');
        $sheet->mergeCells('A4:J4');
        $sheet->mergeCells('A5:J5');
        $sheet->mergeCells('K3:M5');

        $sheet->setCellValue('A1', 'REPORTE CERTIFICADOS - PERIODO ' . strtoupper((string) $periodo));
        $sheet->setCellValue('A2', $nombreCetpro . ' | Fecha inicio: ' . $fechaInicio . ' | Fecha fin: ' . $fechaFin . ' | Generado: ' . now()->format('j/n/Y, g:i:s a'));
        $sheet->setCellValue('A3', 'Emitido por: CETPRO ' . $nombreCetpro);
        $sheet->setCellValue('A4', 'Tipo de gestion: ' . $gestion . ' | UGEL: ' . $ugel . ' | DRE: ' . $dre);
        $sheet->setCellValue('A5', 'Ubicacion: ' . $region . ' / ' . $provincia . ' / ' . $distrito . ' | Direccion: ' . $direccion . ' | Ano: ' . $anio);

        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F2747'],
            ],
        ]);

        $sheet->getStyle('A2:M2')->applyFromArray([
            'font' => ['size' => 12, 'color' => ['rgb' => '1E3A5F']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CAD2DD'],
            ],
        ]);

        $sheet->getStyle('A3:J5')->applyFromArray([
            'font' => ['size' => 15, 'color' => ['rgb' => '0B172A']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);
        $sheet->getStyle('A3')->getFont()->setBold(true);

        $sheet->getStyle('K3:M5')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        $logoPath = public_path('img/CETPRO_Image.png');
        if (is_file($logoPath)) {
            $drawing = new Drawing();
            $drawing->setPath($logoPath);
            $drawing->setHeight(85);
            $drawing->setCoordinates('K3');
            $drawing->setOffsetX(40);
            $drawing->setOffsetY(2);
            $drawing->setWorksheet($sheet);
        }

        // Header tabla
        $sheet->fromArray([
            [
                'NRO',
                'DNI',
                'CODIGO',
                'APELLIDOS Y NOMBRES',
                'SEXO',
                'ESPECIALIDAD',
                'MODULO',
                'INICIO',
                'TERMINO',
                'TIPO',
                'CREDITOS',
                'HORAS',
                'CERTIFICADO',
            ],
        ], null, 'A7');

        $sheet->getStyle('A7:M7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F2E46'],
            ],
        ]);

        // Datos
        $fila = 8;
        $nro = 1;

        foreach ($certificados as $certificado) {
            $matricula = $certificado->matricula;
            $estudiante = $matricula?->estudiante;
            $grupo = $matricula?->grupo;

            $sheet->setCellValue("A{$fila}", $nro++);
            $sheet->setCellValue("B{$fila}", $estudiante?->nro_documento);
            $sheet->setCellValue("C{$fila}", $certificado->codigo);
            $sheet->setCellValue(
                "D{$fila}",
                trim(
                    $estudiante?->apellido_paterno . ' ' .
                    $estudiante?->apellido_materno . ' ' .
                    $estudiante?->nombre
                )
            );
            $sheet->setCellValue("E{$fila}", $estudiante?->sexo);
            $sheet->setCellValue("F{$fila}", $grupo?->especialidad?->especialidadMadre?->nombre_especialidad);
            $sheet->setCellValue("G{$fila}", $grupo?->modulo?->descripcion);
            $sheet->setCellValue("H{$fila}", $grupo?->fecha_inicio);
            $sheet->setCellValue("I{$fila}", $grupo?->fecha_fin);
            $sheet->setCellValue("J{$fila}", $grupo?->programaEstudio?->ciclo?->nombre_ciclo);
            $sheet->setCellValue("K{$fila}", $grupo?->modulo?->creditos);
            $sheet->setCellValue("L{$fila}", $grupo?->modulo?->horas);

            $tipoCertificado = $certificado->duplicado == 1 ? 'DUPLICADO' : 'ORIGINAL';
            $sheet->setCellValue("M{$fila}", $tipoCertificado);

            if ($certificado->duplicado == 1) {
                $sheet->getStyle("M{$fila}")->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => 'B91C1C'],
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEE2E2'],
                    ],
                ]);
            }

            if ($fila % 2 === 0) {
                $sheet->getStyle("A{$fila}:M{$fila}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8FAFC'],
                    ],
                ]);
            }

            $fila++;
        }

        // Layout general
        $sheet->freezePane('A8');
        $sheet->setAutoFilter('A7:M7');

        $sheet->getDefaultRowDimension()->setRowHeight(21);
        $sheet->getRowDimension(1)->setRowHeight(36);
        $sheet->getRowDimension(2)->setRowHeight(30);
        $sheet->getRowDimension(3)->setRowHeight(30);
        $sheet->getRowDimension(4)->setRowHeight(30);
        $sheet->getRowDimension(5)->setRowHeight(30);
        $sheet->getRowDimension(7)->setRowHeight(32);

        $sheet->getColumnDimension('A')->setWidth(7);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(19);
        $sheet->getColumnDimension('D')->setWidth(34);
        $sheet->getColumnDimension('E')->setWidth(9);
        $sheet->getColumnDimension('F')->setWidth(27);
        $sheet->getColumnDimension('G')->setWidth(27);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(12);
        $sheet->getColumnDimension('J')->setWidth(19);
        $sheet->getColumnDimension('K')->setWidth(10);
        $sheet->getColumnDimension('L')->setWidth(10);
        $sheet->getColumnDimension('M')->setWidth(14);

        $sheet->getStyle('A7:M7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A8:C' . ($fila - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E8:E' . ($fila - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H8:M' . ($fila - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A8:M' . ($fila - 1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('H8:I' . ($fila - 1))->getNumberFormat()->setFormatCode('dd/mm/yyyy');

        $sheet->getStyle('A1:M' . max(7, $fila - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);

        return $spreadsheet;
    }
}

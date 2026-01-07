<?php

namespace App\Exports;

use App\Models\EstudianteDocumento;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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

        // 🔹 Obtener certificados del periodo
        $certificados = EstudianteDocumento::with([
            'matricula.estudiante',
            'matricula.grupo.especialidad.especialidadMadre',
            'matricula.grupo.modulo',
            'matricula.grupo.periodo',
            'matricula.grupo.programaEstudio.ciclo'
        ])
            ->whereHas('matricula.grupo', function ($q) {
                $q->where('id_periodo', $this->idPeriodo);
            })
            ->where('tipo_documento', 3)
            ->orderBy('fecha_emision')
            ->get();

        // 🔹 Periodo (para el título)
        $periodo = $certificados->first()?->matricula?->grupo?->periodo?->nombre_periodo ?? '—';

        // 🔹 Título
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue(
            'A1',
            'REGISTRO DE CERTIFICADOS DEL PERIODO ' . strtoupper($periodo)
        );

        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // 🔹 Encabezados (fila 3)
        $sheet->fromArray([
            [
                'NRO',
                'DNI',
                'CODIGO',
                'APELLIDOS Y NOMBRES',
                'SEXO',
                'ESPECIALIDAD',
                'MÓDULO',
                'INICIO',
                'TÉRMINO',
                'TIPO',
                'CRÉDITOS',
                'HORAS',
                'CERTIFICADO'
            ]
        ], null, 'A3');

        $sheet->getStyle('A3:M3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '800000']
            ]
        ]);

        // 🔹 Llenar datos
        $fila = 4;
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
                        'color' => ['rgb' => 'FF0000'], 
                        'bold' => true
                    ]
                ]);
            }

            $fila++;
        }

        // 🔹 Auto tamaño
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 🔹 Bordes
        $sheet->getStyle("A3:M" . ($fila - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ]
        ]);

        return $spreadsheet;
    }
}

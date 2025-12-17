<?php

namespace App\Exports;

use App\Models\EspecialidadMadre;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NominaCensoEducativoExport
{
    public function generarReporte(): string
    {
        // 1. Cargar plantilla
        $spreadsheet = IOFactory::load(
            storage_path('app/templates/censo.xlsx')
        );

        $sheet = $spreadsheet->getActiveSheet();

        // 2. Obtener todas las especialidades activas
        $especialidades = EspecialidadMadre::where('is_deleted', 0)
            ->orderBy('nombre_especialidad')
            ->pluck('nombre_especialidad')
            ->values();

        if ($especialidades->isEmpty()) {
            throw new \Exception('No existen especialidades registradas.');
        }

        // 3. Insertar en OPCIONES OCUPACIONALES
        $filaInicio = 30;

        foreach ($especialidades as $index => $nombre_especialidad) {
            $fila = $filaInicio + $index;

            // N°
            $sheet->setCellValue("B{$fila}", $index + 1);

            // DENOMINACIÓN (celda correcta del merge)
            $sheet->setCellValue("E{$fila}", $nombre_especialidad);

            $sheet->getStyle("E{$fila}")
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER);
        }


        // 4. Generar binario
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }
}

<?php

namespace App\Http\Controllers;

use App\Exports\NominaCensoEducativoExport;
use App\Exports\NominaMatriculasExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Matricula;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function nominaMatriculasExcel($idGrupo)
    {
        $total = Matricula::where('id_grupo', $idGrupo)->count();

        $export = new \App\Exports\NominaMatriculasExport($idGrupo);
        $spreadsheet = $export->build();

        $writer = new Xlsx($spreadsheet);

        $fileName = "nomina_grupo_{$idGrupo}_{$total}_matriculados.xlsx";

        // Descargar como respuesta HTTP
        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }

    public function generarExcelCenso()
    {
        try {
            $export = new NominaCensoEducativoExport();
            $contenido = $export->generarReporte();

            if (strlen($contenido) === 0) {
                return response()->json([
                    'error' => 'El archivo Excel generado está vacío'
                ], 500);
            }

            $nombreArchivo = 'censo_educativo_' . date('Ymd_His') . '.xlsx';

            return response($contenido)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->header('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"')
                ->header('Cache-Control', 'max-age=0')
                ->header('Content-Length', strlen($contenido));
        } catch (\Exception $e) {
            \Log::error('Error generando Censo Excel', [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine()
            ]);

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

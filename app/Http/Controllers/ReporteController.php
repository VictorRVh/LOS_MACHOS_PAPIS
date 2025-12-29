<?php

namespace App\Http\Controllers;

use App\Exports\NominaCensoEducativoExport;
use App\Exports\NominaMatriculasExport;
use App\Exports\RegistroMatriculaInstitucionalExport;
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

    public function exportMatriculaInstitucional($idPeriodo)
    {
        try {
            // 1️⃣ Crear export por PERIODO
            $export = new RegistroMatriculaInstitucionalExport($idPeriodo);

            // 2️⃣ CONSTRUIR EL SPREADSHEET (ESTO FALTABA)
            $spreadsheet = $export->build();

            // 3️⃣ Writer
            $writer = new Xlsx($spreadsheet);

            // 4️⃣ Nombre del archivo
            $fileName = "matricula_institucional_periodo_{$idPeriodo}.xlsx";

            // 5️⃣ Descargar
            return new \Symfony\Component\HttpFoundation\StreamedResponse(
                function () use ($writer) {
                    $writer->save('php://output');
                },
                200,
                [
                    'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
                    'Cache-Control'       => 'max-age=0',
                ]
            );
        } catch (\Throwable $e) {

            \Log::error('Error exportando matrícula institucional', [
                'periodo' => $idPeriodo,
                'error'   => $e->getMessage(),
                'linea'   => $e->getLine(),
            ]);

            return response()->json([
                'error'   => 'Error al generar el reporte de matrícula institucional',
                'detalle' => $e->getMessage(),
            ], 500);
        }
    }

    public function actaEvaluacionExcel($idGrupo)
    {
        $export = new \App\Exports\ReporteActaEvaluacionExport($idGrupo);
        $spreadsheet = $export->build();

        $writer = new Xlsx($spreadsheet);

        $fileName = "acta_evaluacion_{$idGrupo}.xlsx";

        // Descargar como respuesta HTTP
        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }

    public function consolidadoExcel($idGrupo)
    {
        $export = new \App\Exports\ReporteConsolidadoNotasExport($idGrupo);
        $spreadsheet = $export->build();

        $writer = new Xlsx($spreadsheet);

        $fileName = "consolidado_{$idGrupo}.xlsx";

        // Descargar como respuesta HTTP
        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }

    public function RegistroMatricula_RegistroEvaluacionPorModulo($idGrupo)
    {
        $export = new \App\Exports\ReporteRegistroMatriculaAndEvaluacionExport($idGrupo);
        $spreadsheet = $export->build();

        $writer = new Xlsx($spreadsheet);

        $fileName = "Registro_de_Matricula_Registro_de_Evaluacion_Por_Modulo_{$idGrupo}.xlsx";

        // Descargar como respuesta HTTP
        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }
}

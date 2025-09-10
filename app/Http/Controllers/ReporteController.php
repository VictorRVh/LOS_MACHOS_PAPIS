<?php

namespace App\Http\Controllers;

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
        return new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }
}

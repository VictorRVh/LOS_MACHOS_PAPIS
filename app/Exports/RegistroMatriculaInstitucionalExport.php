<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RegistroMatriculaInstitucionalExport
{
    protected string $idPeriodo;

    public function __construct(string $idPeriodo)
    {
        $this->idPeriodo = $idPeriodo;
    }

    public function build(): Spreadsheet
    {
        // 1️⃣ Cargar plantilla
        $spreadsheet = IOFactory::load(
            storage_path('app/templates/Registro_Matricula_Institucional.xlsx')
        );

        $sheet = $spreadsheet->getActiveSheet();


        // 2️⃣ Datos estáticos de institución

        /////////////////////////////////////////////////////
        // datos de la intitucion 
        /////////////////////////////////////////////////////
        $institucion = DB::table('cetpros')->first();
        $nombreInstitucion = $institucion->cetpro ?? '';
        $dre = $institucion->dre ?? '';
        $ugel = $institucion->ugel ?? '';
        $codigoModular = $institucion->codigo_modular ?? '240069';
        $codigoAutorizacion = $institucion->rd_autorizacion ?? 'R.D.00000';
        $codigoConversion = $institucion->rd_conversion ?? 'R.C.00000';
        $tipoGestion = $institucion->tipo_gestion ?? '';
        $departamento = $institucion->region ?? '';
        $provincia = $institucion->provincia ?? '';
        $distrito = $institucion->distrito ?? '';
        $direccion = $institucion->direccion ?? '';



        // $sheet->setCellValue('F4', $nombreInstitucion);
        // $sheet->setCellValue('M4', $dre);
        // $sheet->setCellValue('F5', $codigoModular);
        // $sheet->setCellValue('M5', $ugel);
        // $sheet->setCellValue('F6', $codigoAutorizacion);
        // $sheet->setCellValue('M6', $codigoConversion);

        // $sheet->setCellValue('F7', $departamento);
        // $sheet->setCellValue('M7', $provincia);

        // $sheet->setCellValue('F8', $distrito);
        // $sheet->setCellValue('M8', $tipoGestion);

        // $sheet->setCellValue('F9', $direccion);




        // 3️⃣ Consulta
        $registros = DB::table('grupo as g')
            ->join('programa_estudio as pe', 'g.id_programa', '=', 'pe.id')
            ->join('especialidad_programa as ep', 'g.id_especialidad', '=', 'ep.id')
            ->join('especialidad_madre as em', 'ep.id_especialidad', '=', 'em.id')
            ->join('ciclo_academico as ca', 'em.id_ciclo', '=', 'ca.id')
            ->join('modulos as m', 'g.id_modulo', '=', 'm.id')
            ->join('matricula as ma', function ($q) {
                $q->on('ma.id_grupo', '=', 'g.id')
                    ->where('ma.reserva', 0);
            })
            ->join('estudiante as e', 'e.id', '=', 'ma.id_estudiante')
            ->where('g.id_periodo', $this->idPeriodo)
            ->select(
                'em.nombre_especialidad',
                'ca.nombre_ciclo',
                'pe.numero_rd',
                'm.descripcion as modulo',
                'e.tipo_documento',
                'e.nro_documento',
                'e.apellido_paterno',
                'e.apellido_materno',
                'e.nombre',
                'e.sexo',
                'e.fecha_nacimiento'
            )
            ->orderBy('em.nombre_especialidad')
            ->orderBy('m.numero_modulo')
            ->orderBy('e.apellido_paterno')
            ->get();


        $periodoNombre = DB::table('periodo')
            ->where('id', $this->idPeriodo)
            ->value('nombre_periodo');

        // 📝 Título dinámico
        $nameTitle = "REGISTRO DE MATRÍCULA INSTITUCIONAL {$periodoNombre}";

        // 📌 Imprimir título en Excel
        $sheet->setCellValue('C1', $nameTitle);
        // 4️⃣ Llenado (fila correcta según plantilla)
        $fila = 5;

        foreach ($registros as $r) {
            $sheet->fromArray([
                $departamento,
                $ugel,
                $codigoModular,
                $nombreInstitucion,
                $r->nombre_especialidad,
                $r->nombre_ciclo,
                $codigoAutorizacion,
                $r->modulo,
                $r->tipo_documento,
                $r->nro_documento,
                $r->apellido_paterno,
                $r->apellido_materno,
                $r->nombre,
                $r->sexo,
                $r->fecha_nacimiento
                    ? date('d/m/Y', strtotime($r->fecha_nacimiento))
                    : '',
            ], null, "B{$fila}");
            $sheet->getStyle("B{$fila}:P{$fila}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            $fila++;
        }


        return $spreadsheet;
    }
}

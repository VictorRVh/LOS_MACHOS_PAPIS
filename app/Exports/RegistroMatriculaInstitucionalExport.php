<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class RegistroMatriculaInstitucionalExport
{
    protected string $idPeriodo;

    public function __construct(string $idPeriodo)
    {
        $this->idPeriodo = $idPeriodo;
    }

    public function build(): Spreadsheet
    {
        // =============================
        // 1️⃣ CARGAR PLANTILLA
        // =============================
        $spreadsheet = IOFactory::load(
            storage_path('app/templates/Registro_Matricula_Institucional.xlsx')
        );

        $sheet = $spreadsheet->getActiveSheet();

        // =============================
        // 2️⃣ DATOS INSTITUCIONALES (FIJOS)
        // =============================
        $institucion = [
            'region'         => 'TU REGIÓN',
            'ugel'           => 'TU UGEL',
            'codigo_modular' => '0000000',
            'nombre'         => 'NOMBRE DEL CETPRO',
        ];

        // =============================
        // 3️⃣ CABECERAS
        // =============================
        // $sheet->fromArray([
        //     'REGIÓN',
        //     'UGEL',
        //     'CÓDIGO MODULAR',
        //     'NOMBRE DEL CETPRO',
        //     'PROGRAMA / ESPECIALIDAD',
        //     'CICLO',
        //     'N° RESOLUCIÓN',
        //     'MÓDULO',
        //     'TIPO DOCUMENTO',
        //     'N° DOCUMENTO',
        //     'APELLIDO PATERNO',
        //     'APELLIDO MATERNO',
        //     'NOMBRES',
        //     'SEXO',
        //     'FECHA DE NACIMIENTO',
        // ], null, 'A1');

        // =============================
        // 4️⃣ CONSULTA REAL (CON CICLO)
        // =============================
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

        // =============================
        // 5️⃣ LLENADO
        // =============================
        $fila = 6;

        foreach ($registros as $r) {
            $sheet->fromArray([
                // $institucion['region'],
                // $institucion['ugel'],
                // $institucion['codigo_modular'],
                // $institucion['nombre'],
                $r->nombre_especialidad,
                $r->nombre_ciclo, // 🔥 AQUÍ VA EL CICLO REAL
                $r->numero_rd,
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
            ], null, "A{$fila}");

            $fila++;
        }

        // =============================
        // 6️⃣ AUTO AJUSTE
        // =============================
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}

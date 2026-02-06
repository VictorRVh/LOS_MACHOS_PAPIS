<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ReporteRegistroMatriculaAndEvaluacionExport
{
    protected $idGrupo;

    public function __construct($idGrupo)
    {
        $this->idGrupo = $idGrupo;
    }

    public function build()
    {
        $spreadsheet = IOFactory::load(
            storage_path('app/templates/REGISTRO DE MATRICULA Y REGISTRO DE EVALUACIÓN POR MÓDULO nuevo.xlsx')
        );

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Asistencias');

        // 1. Insertar fechas de asistencia en la cabecera
        $this->insertarFechasAsistenciaCabecera($sheet);

        // 2. Rellenar estudiantes y sus faltas
        // $this->rellenarEstudiantesYFaltas($sheet);

        // 3. Rellenar registro de evaluación (unidades didácticas)
        $this->rellenarEstudiantesYNotas($sheet);

        return $spreadsheet;
    }

    /**
     * Inserta las fechas de asistencia en la fila 6 desde D6 hasta S6
     */
    private function insertarFechasAsistenciaCabecera($sheet): void
    {

        $data = DB::table('grupo as g')

            ->join('programa_estudio as pe', 'pe.id', '=', 'g.id_programa')
            ->join('ciclo_academico as ca', 'ca.id', '=', 'pe.id_ciclo')
            ->join('modulos as m', 'm.id', '=', 'g.id_modulo')
            ->join('periodo as p', 'p.id', '=', 'g.id_periodo')
            ->join('especialidad_programa as ep', 'ep.id', '=', 'g.id_especialidad')
            ->join('especialidad_madre as em', 'em.id', '=', 'ep.id_especialidad')
            ->leftJoin('docente as d', 'd.id', '=', 'g.id_docente')
            ->leftJoin('users as u', 'u.id', '=', 'd.user_id')
            ->leftJoin('capacidad_terminal as ct', 'ct.id_grupo', '=', 'g.id')

            ->where('g.id', $this->idGrupo)

            ->select(
                // 🔹 PROGRAMA
                'pe.año as programa_estudios',
                'ca.nombre_ciclo as ciclo_formativo',
                // 🔹 MÓDULO
                'm.descripcion as modulo_formativo',

                // 🔹 ESPECIALIDAD
                'em.nombre_especialidad as formacion',

                // 🔹 PERIODO
                'p.nombre_periodo as periodo_academico',

                // 🔹 GRUPO
                'g.seccion',
                'g.turno',

                // 🔹 DOCENTE
                DB::raw("CONCAT(u.apellido_paterno,' ',u.apellido_materno,', ',u.name) as docente"),

                // 🔹 HORAS Y CRÉDITOS (SUMA REAL)
                DB::raw('SUM(ct.horas) as horas_totales'),
                DB::raw('SUM(ct.creditos_teoricos) as horas_teoricas'),
                DB::raw('SUM(ct.creditos_practicos) as horas_practicas'),
                DB::raw('SUM(ct.creditos_teoricos + ct.creditos_practicos) as creditos')
            )
            ->groupBy(
                'pe.año',
                'm.descripcion',
                'ca.nombre_ciclo',
                'em.nombre_especialidad',
                'p.nombre_periodo',
                'g.seccion',
                'g.turno',
                'u.apellido_paterno',
                'u.apellido_materno',
                'u.name'
            )
            ->first();

        if ($data) {

            // PROGRAMA DE ESTUDIOS
            $sheet->setCellValue('AF13', $data->formacion);

            // MÓDULO FORMATIVO
            $sheet->setCellValue('AF16', $data->ciclo_formativo);

            // FORMACIÓN (ESPECIALIDAD)
            $sheet->setCellValue('AI18', "");

            // MODALIDAD (si es fijo puedes dejarlo así)
            $sheet->setCellValue('Ak20', 'Presencial');

            // PERIODO ACADÉMICO
            $sheet->setCellValue('AJ22', $data->periodo_academico);
            $sheet->setCellValue('AI8', $data->periodo_academico);
            // CRÉDITOS
            $sheet->setCellValue('AH24', $data->creditos);

            // HORAS TEÓRICAS
            $sheet->setCellValue('AF26', "HORAS TEÓRICAS " . $data->horas_teoricas . " /HORAS PRÁCTICAS:" . $data->horas_practicas);

            // DOCENTE
            $sheet->setCellValue('AH28', $data->docente);

            // SECCIÓN
            $sheet->setCellValue('AF31', "SECCIÓN: " . $data->seccion);

            // TURNO
            $sheet->setCellValue('AK31', "TURNO: " . $data->turno);
        }

        $fechasAsistencia = DB::table('asistencia')
            ->where('id_grupo', $this->idGrupo)
            ->select('fecha_actual')
            ->distinct()
            ->orderBy('fecha_actual')
            ->pluck('fecha_actual')
            ->toArray();

        $filaCabecera = 6;
        $colInicio = 'D';
        $colIndex = Coordinate::columnIndexFromString($colInicio);

        foreach ($fechasAsistencia as $i => $fecha) {

            if ($i >= 16) break; // máximo hasta columna S (D=4, S=19, diferencia=15+1=16)

            $col = Coordinate::stringFromColumnIndex($colIndex + $i);
            $excelDate = Date::stringToExcel(date('Y-m-d', strtotime($fecha)));

            $sheet->setCellValueExplicit(
                "{$col}{$filaCabecera}",
                $excelDate,
                DataType::TYPE_NUMERIC
            );

            $sheet->getStyle("{$col}{$filaCabecera}")
                ->getNumberFormat()
                ->setFormatCode('dd/mm');

            $sheet->getStyle("{$col}{$filaCabecera}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
    }

    /**
     * Llena los datos de cada estudiante: número, documento, nombre y faltas
     */
    private function rellenarEstudiantesYNotas($sheet): void
    {
        // Obtener estudiantes matriculados
        $estudiantes = DB::table('matricula')
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->where('matricula.id_grupo', $this->idGrupo)
            ->where(function ($q) {
                $q->whereNull('matricula.reserva')
                    ->orWhere('matricula.reserva', 0);
            })
            ->select(
                'estudiante.id',
                'estudiante.nro_documento',
                DB::raw("CONCAT(
                estudiante.apellido_paterno, ' ',
                estudiante.apellido_materno, ', ',
                estudiante.nombre
            ) AS nombre_completo")
            )
            ->orderBy('estudiante.apellido_paterno')
            ->get();

        $filaInicio = 37;
        $filasPlantilla = 62;
        $totalEstudiantes = $estudiantes->count();

        // Extender plantilla si hay más estudiantes que filas
        if ($totalEstudiantes > $filasPlantilla) {
            $this->extenderPlantillaEstudiantes($sheet, $filaInicio, $filasPlantilla, $totalEstudiantes);
        }

        // Obtener capacidades terminales
        $capacidades = $this->obtenerCapacidades();

        // Obtener notas agrupadas por estudiante-capacidad
        $notas = DB::table('nota_capacidad_terminal')
            ->where('id_grupo', $this->idGrupo)
            ->get()
            ->groupBy(fn($n) => $n->id_estudiante . '-' . $n->id_capacidad);

        // Llenar datos
        $this->llenarListaEstudiantesConNotas($sheet, $estudiantes, $capacidades, $notas, $filaInicio);
    }

    /**
     * Extiende la plantilla insertando filas adicionales
     */
    private function extenderPlantillaEstudiantes(
        $sheet,
        int $filaInicio,
        int $filasPlantilla,
        int $totalEstudiantes
    ): void {
        $filasAInsertar = $totalEstudiantes - $filasPlantilla;
        $filaModelo = $filaInicio;

        $sheet->insertNewRowBefore(
            $filaInicio + $filasPlantilla,
            $filasAInsertar
        );

        $columnas = range('C', 'AH'); // Ajusta según tus necesidades

        for ($i = 0; $i < $filasAInsertar; $i++) {

            $filaDestino = $filaInicio + $filasPlantilla + $i;

            foreach ($columnas as $col) {

                $sheet->duplicateStyle(
                    $sheet->getStyle("{$col}{$filaModelo}"),
                    "{$col}{$filaDestino}"
                );

                $sheet->getStyle("{$col}{$filaDestino}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
            }

            $altura = $sheet->getRowDimension($filaModelo)->getRowHeight();
            if ($altura !== null) {
                $sheet->getRowDimension($filaDestino)->setRowHeight($altura);
            }
        }
    }

    /**
     * Llena los datos de cada estudiante: número, DNI, nombre y notas
     */
    private function llenarListaEstudiantesConNotas(
        $sheet,
        $estudiantes,
        $capacidades,
        $notas,
        int $filaInicio
    ): void {
        $fila = $filaInicio;

        foreach ($estudiantes as $estudiante) {

            $sheet->getStyle("C{$fila}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // D: DNI
            $sheet->setCellValueExplicit(
                "C{$fila}",
                (string) $estudiante->nro_documento,
                DataType::TYPE_STRING
            );
            $sheet->getStyle("D{$fila}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // E: Nombre completo
            $sheet->setCellValue("D{$fila}", $estudiante->nombre_completo);
            $sheet->getStyle("D{$fila}")
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // V en adelante: Notas por capacidad
            $this->llenarNotasCapacidades($sheet, $estudiante, $capacidades, $notas, $fila);

            $fila++;
        }
    }

    /**
     * Obtiene las capacidades terminales del grupo
     */
    private function obtenerCapacidades()
    {
        return DB::table('capacidad_terminal')
            ->where('id_grupo', $this->idGrupo)
            ->orderBy('numero_capacidad', 'asc')
            ->get();
    }

    /**
     * Llena las notas de capacidades para un estudiante específico
     */
    private function llenarNotasCapacidades(
        $sheet,
        $alumno,
        $capacidades,
        $notas,
        int $fila
    ): void {
        $colInicio = 'V';
        $colIndex = Coordinate::columnIndexFromString($colInicio);

        foreach ($capacidades as $i => $cap) {

            $col = Coordinate::stringFromColumnIndex($colIndex + $i);
            $key = $alumno->id . '-' . $cap->id;
            $nota = $notas->get($key)?->first()?->nota_capacidad;

            $sheet->setCellValue("{$col}{$fila}", $nota ?? '—');
            $sheet->getStyle("{$col}{$fila}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Aplicar color: rojo si < 11, negro si >= 11
            if ($nota !== null) {
                $color = ($nota < 11) ? 'FF0000' : '000000';
                $sheet->getStyle("{$col}{$fila}")
                    ->getFont()
                    ->getColor()
                    ->setRGB($color);
            }
        }
    }
}

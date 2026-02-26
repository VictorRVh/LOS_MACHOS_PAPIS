<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\ReferenceHelper;

class ReporteRegistroMatriculaAndEvaluacionExport
{
    protected $idGrupo;
    protected $filasExtraAsistencia = 0;


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

        // 🔥 AGREGA ESTA LÍNEA
        $this->llenarUnidadesCompetencia($sheet);

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
            ->orderBy('estudiante.apellido_materno')
            ->orderBy('estudiante.nombre')
            ->get();

        // Obtener SOLO faltas
        $faltas = DB::table('asistencia')
            ->where('id_grupo', $this->idGrupo)
            ->where('asistencia', '2') // ⚠️ cambia si tu campo es diferente
            ->orderBy('fecha_actual')
            ->get()
            ->groupBy('id_estudiante');

        $filaInicio = 6;          // donde empiezan los estudiantes
        $filasPlantilla = 26;     // tu plantilla soporta 26
        $totalMatriculas = $estudiantes->count();

        // 🔥 EXTENDER SI HAY MÁS DE 26
        if ($totalMatriculas > $filasPlantilla) {

            $filasAInsertar = $totalMatriculas - $filasPlantilla;

            $this->filasExtraAsistencia = $filasAInsertar;
            $filaModelo = $filaInicio + $filasPlantilla - 1;


            // Detectar merges de la fila modelo
            $mergedCells = [];
            foreach ($sheet->getMergeCells() as $merge) {
                if (preg_match("/[A-Z]+{$filaModelo}:[A-Z]+{$filaModelo}/", $merge)) {

                    $mergedCells[] = $merge;
                }
            }

            // Insertar debajo de la última fila del bloque
            $sheet->insertNewRowBefore($filaModelo + 1, $filasAInsertar);

            $columnas = range('B', 'U');


            for ($i = 0; $i < $filasAInsertar; $i++) {

                $filaNueva = $filaModelo  + 1 + $i;

                foreach ($columnas as $col) {

                    $celdaModelo  = "{$col}{$filaModelo}";
                    $celdaDestino = "{$col}{$filaNueva}";

                    // Copiar estilo
                    $sheet->duplicateStyle(
                        $sheet->getStyle($celdaModelo),
                        $celdaDestino
                    );

                    $celda = $sheet->getCell($celdaModelo);

                    if ($celda->isFormula()) {

                        $formulaOriginal = $celda->getValue();

                        $formulaAjustada = ReferenceHelper::getInstance()
                            ->updateFormulaReferences(
                                $formulaOriginal,
                                $celdaModelo,
                                0, // columnas
                                $filaNueva - $filaModelo // diferencia de filas
                            );

                        $sheet->setCellValue($celdaDestino, $formulaAjustada);
                    } else {

                        // 🔥 SI ES COLUMNA C (índice)
                        if ($col === 'C') {

                            $filaAnterior = $filaNueva - 1;
                            $indiceAnterior = $sheet->getCell("C{$filaAnterior}")->getValue();

                            $sheet->setCellValue($celdaDestino, $indiceAnterior + 1);
                        }
                    }
                }
            }
        }


        // ahora sí empezamos a escribir
        $fila = $filaInicio;
        $colBase = Coordinate::columnIndexFromString('D');


        foreach ($estudiantes as $estudiante) {

            $colIndex = $colBase;
            $contador = 0; // 🔥 contador de faltas

            $faltasEstudiante = $faltas->get($estudiante->id);

            if ($faltasEstudiante) {

                foreach ($faltasEstudiante as $falta) {

                    if ($contador >= 16) break; // 🔥 máximo 16 inasistencias

                    $col = Coordinate::stringFromColumnIndex($colIndex);

                    $sheet->setCellValue(
                        "{$col}{$fila}",
                        date('d/m', strtotime($falta->fecha_actual))
                    );

                    $sheet->getStyle("{$col}{$fila}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $colIndex++;
                    $contador++; // 🔥 aumentamos contador
                }
            }

            $fila++;
        }
    }
    private function llenarUnidadesCompetencia($sheet): void
    {
        $data = DB::table('capacidad_terminal as ct')
            ->join('capacidades_competencias as cc', 'cc.id_capacidad_terminal', '=', 'ct.id')
            ->join('competencias as c', 'cc.id_competencia', '=', 'c.id')
            ->where('ct.id_grupo', $this->idGrupo)

           // ->orderBy('c.id') // primero competencia

            ->orderByRaw('CAST(ct.numero_capacidad AS UNSIGNED) ASC')

           // ->orderBy('cc.id', 'asc') // orden capacidades dentro de unidad

            ->select([
                'c.id as competencia_id',
                'c.descripcion as competencia',

                'ct.id as unidad_id',
                'ct.nombre_capacidad as unidad',

                'ct.numero_capacidad', // recomendable incluirlo

                'cc.descripcion as capacidad'
            ])
            ->get();

        /*
        =====================================
        CONTAR FILAS POR COMPETENCIA Y UNIDAD
        =====================================
        */

        $conteoCompetencia = [];
        $conteoUnidad = [];

        foreach ($data as $item) {
            $conteoCompetencia[$item->competencia_id] =
                ($conteoCompetencia[$item->competencia_id] ?? 0) + 1;

            $conteoUnidad[$item->unidad_id] =
                ($conteoUnidad[$item->unidad_id] ?? 0) + 1;
        }

        /*
        =====================================
        ESCRIBIR EN EXCEL
        =====================================
        */

        $fila = 6;

        $competenciasEscritas = [];
        $unidadesEscritas = [];

        foreach ($data as $item) {
            /*
            =========================
            COMPETENCIA (col W)
            =========================
            */

            if (!isset($competenciasEscritas[$item->competencia_id])) {
                $alto = $conteoCompetencia[$item->competencia_id];

                $sheet->setCellValue("W{$fila}", $item->competencia);

                // SOLO merge si tiene más de 1 fila
                if ($alto > 1) {
                    $sheet->mergeCells("W{$fila}:W" . ($fila + $alto - 1));
                }

                $competenciasEscritas[$item->competencia_id] = true;
            }

            /*
            =========================
            UNIDAD DIDACTICA (AB:AD)
            =========================
            */
            if (!isset($unidadesEscritas[$item->unidad_id])) {

                $alto = $conteoUnidad[$item->unidad_id];

                // escribir unidad
                $sheet->setCellValue("AB{$fila}", $item->unidad);

                // SIEMPRE unir horizontal AB:AD
                $sheet->mergeCells("AB{$fila}:AD{$fila}");

                // SI tiene más de una capacidad, unir vertical también
                if ($alto > 1) {

                    // primero deshacer horizontal para evitar conflicto
                    $sheet->unmergeCells("AB{$fila}:AD{$fila}");

                    // unir todo el bloque correctamente
                    $sheet->mergeCells("AB{$fila}:AD" . ($fila + $alto - 1));
                }

                $unidadesEscritas[$item->unidad_id] = true;
            }

            /*
            =========================
            CAPACIDAD (X:AA)
            =========================
            */

            $sheet->setCellValue("X{$fila}", $item->capacidad);

            $sheet->mergeCells("X{$fila}:AA{$fila}");

            $fila++;
        }

        /*
            =========================
            ESTILOS
            =========================
            */

        $ultimaFila = $fila - 1;

        $sheet->getStyle("W6:W{$ultimaFila}")
            ->getAlignment()
            ->setTextRotation(90)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle("X6:AA{$ultimaFila}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle("AB6:AD{$ultimaFila}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
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

        $notasExperiencia = DB::table('nota_experiencia_formativa')
            ->where('id_grupo', $this->idGrupo)
            ->get()
            ->keyBy('id_estudiante');


        $filaInicio = 37 + $this->filasExtraAsistencia;

        $filasPlantilla = 26;
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
        $this->llenarListaEstudiantesConNotas($sheet, $estudiantes, $capacidades, $notas, $notasExperiencia, $filaInicio);
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
        $filaModelo = $filaInicio + $filasPlantilla - 1;

        // ✅ 1. GUARDAR merges ANTES de insertar
        $merges = $sheet->getMergeCells();

        // ✅ 2. Deshacer merges
        foreach ($merges as $merge) {
            $sheet->unmergeCells($merge);
        }

        // ✅ 3. Insertar filas
        $sheet->insertNewRowBefore($filaModelo + 1, $filasAInsertar);

        // ✅ 4. Volver a aplicar merges desplazados
        foreach ($merges as $merge) {

            if (preg_match("/([A-Z]+)(\d+):([A-Z]+)(\d+)/", $merge, $matches)) {

                $col1 = $matches[1];
                $row1 = (int)$matches[2];
                $col2 = $matches[3];
                $row2 = (int)$matches[4];

                // Si el merge estaba debajo del bloque, desplazarlo
                if ($row1 > $filaModelo) {
                    $row1 += $filasAInsertar;
                    $row2 += $filasAInsertar;
                }

                $sheet->mergeCells("{$col1}{$row1}:{$col2}{$row2}");
            }
        }

        // 🔥 Ahora copiar estilos
        $columnas = range('C', 'AH');

        for ($i = 0; $i < $filasAInsertar; $i++) {

            $filaNueva = $filaModelo + 1 + $i;
            // 🔥 Generar índice automático en columna B
            $filaAnterior = $filaNueva - 1;
            $indiceAnterior = $sheet->getCell("B{$filaAnterior}")->getValue();

            if (is_numeric($indiceAnterior)) {
                $sheet->setCellValue("B{$filaNueva}", $indiceAnterior + 1);
            }

            foreach ($columnas as $col) {

                $celdaModelo  = "{$col}{$filaModelo}";
                $celdaDestino = "{$col}{$filaNueva}";

                $sheet->duplicateStyle(
                    $sheet->getStyle($celdaModelo),
                    $celdaDestino
                );

                $celda = $sheet->getCell($celdaModelo);

                if ($celda->isFormula()) {

                    $formulaOriginal = $celda->getValue();

                    $formulaAjustada = ReferenceHelper::getInstance()
                        ->updateFormulaReferences(
                            $formulaOriginal,
                            $celdaModelo,
                            0,
                            $filaNueva - $filaModelo
                        );

                    $sheet->setCellValue($celdaDestino, $formulaAjustada);
                }
            }

            $altura = $sheet->getRowDimension($filaModelo)->getRowHeight();
            if ($altura !== null) {
                $sheet->getRowDimension($filaNueva)->setRowHeight($altura);
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
        $notasExperiencia,
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


            // 📌 Columna después de la última capacidad
            $colExperiencia = Coordinate::stringFromColumnIndex(
                Coordinate::columnIndexFromString('AG')
            );

            // Obtener nota de experiencia del estudiante
            $notaExp = $notasExperiencia->get($estudiante->id)?->nota;

            // Escribir nota
            $sheet->setCellValue("{$colExperiencia}{$fila}", $notaExp ?? '—');
            $sheet->getStyle("{$colExperiencia}{$fila}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Color (igual que capacidades)
            if ($notaExp !== null) {
                $color = ($notaExp < 11) ? 'FF0000' : '000000';
                $sheet->getStyle("{$colExperiencia}{$fila}")
                    ->getFont()
                    ->getColor()
                    ->setRGB($color);
            }


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

<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstadisticaController extends Controller
{
    public function estadistica101Data(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin    = $request->input('fecha_fin');

        /*
    |--------------------------------------------------------------------------
    | APROBADOS
    |--------------------------------------------------------------------------
    */

        $aprobadosQuery = Matricula::query()
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->join('grupo', 'matricula.id_grupo', '=', 'grupo.id')
            ->join('modulos', 'grupo.id_modulo', '=', 'modulos.id')
            ->join('programa_estudio', 'grupo.id_programa', '=', 'programa_estudio.id')
            ->join('ciclo_academico', 'programa_estudio.id_ciclo', '=', 'ciclo_academico.id')

            ->where('matricula.matriculado', 1)

            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('nota_experiencia_formativa as nef')
                    ->whereColumn('nef.id_estudiante', 'matricula.id_estudiante')
                    ->whereColumn('nef.id_grupo', 'matricula.id_grupo');
            })

            ->whereRaw('
            (
                SELECT COUNT(DISTINCT nct.id_capacidad)
                FROM nota_capacidad_terminal nct
                WHERE nct.id_grupo = matricula.id_grupo
                  AND nct.id_estudiante = matricula.id_estudiante
                  AND CAST(nct.nota_capacidad AS UNSIGNED) >= 11
            )
            = modulos.nro_capacidades
        ')

            ->where('modulos.nro_capacidades', '>', 0);

        if ($fechaInicio) {
            $aprobadosQuery->whereDate('matricula.created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $aprobadosQuery->whereDate('matricula.created_at', '<=', $fechaFin);
        }

        $aprobados = $aprobadosQuery
            ->select(
                'ciclo_academico.nombre_ciclo as ciclo',
                'estudiante.sexo',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('ciclo_academico.nombre_ciclo', 'estudiante.sexo')
            ->get();

        /*
    |--------------------------------------------------------------------------
    | RETIRADOS
    |--------------------------------------------------------------------------
    */

        $retiradosQuery = Matricula::query()
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->join('grupo', 'matricula.id_grupo', '=', 'grupo.id')
            ->join('programa_estudio', 'grupo.id_programa', '=', 'programa_estudio.id')
            ->join('ciclo_academico', 'programa_estudio.id_ciclo', '=', 'ciclo_academico.id')
            ->where('matricula.matriculado', 2);

        if ($fechaInicio) {
            $retiradosQuery->whereDate('matricula.created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $retiradosQuery->whereDate('matricula.created_at', '<=', $fechaFin);
        }

        $retirados = $retiradosQuery
            ->select(
                'ciclo_academico.nombre_ciclo as ciclo',
                'estudiante.sexo',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('ciclo_academico.nombre_ciclo', 'estudiante.sexo')
            ->get();

        /*
    |--------------------------------------------------------------------------
    | ESTRUCTURA FINAL
    |--------------------------------------------------------------------------
    */

        $resultado = [
            'aprobados' => [
                'total'  => 0,
                'auxiliar_tecnico' => ['H' => 0, 'M' => 0],
                'tecnico'  => ['H' => 0, 'M' => 0],
            ],
            'retirados' => [
                'total'  => 0,
                'auxiliar_tecnico' => ['H' => 0, 'M' => 0],
                'tecnico'  => ['H' => 0, 'M' => 0],
            ],
        ];

        foreach ($aprobados as $row) {
            $sexoBD = strtoupper($row->sexo); // M o F desde la BD

            // Mapeo: M (Masculino BD) -> H (Hombre Reporte), F (Femenino BD) -> M (Mujer Reporte)
            $sexoReporte = $sexoBD === 'M' ? 'H' : 'M';

            // Determinar nivel según nombre del ciclo
            // "Ciclo Auxiliar Técnico" -> basico
            // "Ciclo Técnico" -> medio
            $cicloNombre = $row->ciclo;
            $nivel = (str_contains($cicloNombre, 'Auxiliar') || str_contains($cicloNombre, 'auxiliar'))
                ? 'auxiliar_tecnico'
                : 'tecnico';

            $resultado['aprobados']['total'] += $row->total;
            $resultado['aprobados'][$nivel][$sexoReporte] += $row->total;
        }

        foreach ($retirados as $row) {
            $sexoBD = strtoupper($row->sexo); // M o F desde la BD

            // Mapeo: M (Masculino BD) -> H (Hombre Reporte), F (Femenino BD) -> M (Mujer Reporte)
            $sexoReporte = $sexoBD === 'M' ? 'H' : 'M';

            // Determinar nivel según nombre del ciclo
            $cicloNombre = $row->ciclo;
            $nivel = (str_contains($cicloNombre, 'Auxiliar') || str_contains($cicloNombre, 'auxiliar'))
                ? 'auxiliar_tecnico'
                : 'tecnico';

            $resultado['retirados']['total'] += $row->total;
            $resultado['retirados'][$nivel][$sexoReporte] += $row->total;
        }

        return response()->json($resultado);
    }

    public function matriculadosRetiradosPorCarrera(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin    = $request->input('fecha_fin');

        // Query principal: partimos desde matricula para asegurar datos reales
        $query = DB::table('matricula')
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->join('grupo', 'matricula.id_grupo', '=', 'grupo.id')
            ->join('especialidad_programa', 'grupo.id_especialidad', '=', 'especialidad_programa.id')
            ->join('especialidad_madre', 'especialidad_programa.id_especialidad', '=', 'especialidad_madre.id')
            ->join('programa_estudio', 'grupo.id_programa', '=', 'programa_estudio.id')
            ->join('ciclo_academico', 'programa_estudio.id_ciclo', '=', 'ciclo_academico.id')
            ->where('especialidad_madre.is_deleted', 0)
            ->whereIn('matricula.matriculado', [1, 2]); // Solo matriculados y retirados

        // Filtro de fechas
        if ($fechaInicio) {
            $query->whereDate('matricula.created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $query->whereDate('matricula.created_at', '<=', $fechaFin);
        }

        $rows = $query->select(
            'especialidad_madre.id as carrera_id',
            'especialidad_madre.nombre_especialidad as carrera',
            'ciclo_academico.nombre_ciclo as ciclo',
            'estudiante.sexo',
            'matricula.matriculado',
            DB::raw('COUNT(matricula.id) as total')
        )
            ->groupBy(
                'especialidad_madre.id',
                'especialidad_madre.nombre_especialidad',
                'ciclo_academico.nombre_ciclo',
                'estudiante.sexo',
                'matricula.matriculado'
            )
            ->orderBy('especialidad_madre.nombre_especialidad')
            ->get();

        // Obtener todas las especialidades (para mostrar todas, incluso sin matrículas)
        $todasEspecialidades = DB::table('especialidad_madre')
            ->where('is_deleted', 0)
            ->select('id', 'nombre_especialidad')
            ->orderBy('nombre_especialidad')
            ->get();

        // Inicializar resultado con todas las especialidades
        $resultado = [];
        foreach ($todasEspecialidades as $esp) {
            $resultado[$esp->id] = [
                'nombre' => $esp->nombre_especialidad,
                'total' => [
                    'matriculados' => ['H' => 0, 'M' => 0],
                    'retirados'    => ['H' => 0, 'M' => 0],
                ],
                'basico' => [
                    'matriculados' => ['H' => 0, 'M' => 0],
                    'retirados'    => ['H' => 0, 'M' => 0],
                ],
                'medio' => [
                    'matriculados' => ['H' => 0, 'M' => 0],
                    'retirados'    => ['H' => 0, 'M' => 0],
                ],
            ];
        }

        // Procesar los resultados
        foreach ($rows as $row) {
            $id = $row->carrera_id;

            if (!isset($resultado[$id])) {
                continue;
            }

            // Mapeo de sexo: M (BD) -> H (Reporte), F (BD) -> M (Reporte)
            $sexoBD = strtoupper($row->sexo);
            $sexoReporte = $sexoBD === 'M' ? 'H' : 'M';

            // Estado: 1 = matriculados, 2 = retirados
            $estado = $row->matriculado == 1 ? 'matriculados' : 'retirados';

            // Nivel según nombre del ciclo
            // "Ciclo Auxiliar Técnico" -> basico
            // "Ciclo Técnico" -> medio
            $nivel = (str_contains($row->ciclo, 'Auxiliar') || str_contains($row->ciclo, 'auxiliar'))
                ? 'basico'
                : 'medio';

            $resultado[$id]['total'][$estado][$sexoReporte] += $row->total;
            $resultado[$id][$nivel][$estado][$sexoReporte] += $row->total;
        }

        return response()->json(array_values($resultado));
    }

    public function debugEstudianteEspecifico(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $matriculasSastreria = DB::table('matricula')
            ->join('grupo', 'matricula.id_grupo', '=', 'grupo.id')
            ->join('especialidad_programa', 'grupo.id_especialidad', '=', 'especialidad_programa.id')
            ->join('especialidad_madre', 'especialidad_programa.id_especialidad', '=', 'especialidad_madre.id')
            ->where('especialidad_madre.nombre_especialidad', 'SASTRERIA')
            ->select(
                'matricula.id',
                'matricula.created_at',
                'matricula.matriculado'
            )
            ->get();

        return response()->json([
            'fecha_inicio_request' => $fechaInicio,
            'fecha_fin_request' => $fechaFin,
            'matriculas_sastreria' => $matriculasSastreria,
        ]);
    }

    public function matriculadosPorCicloYSexo(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin    = $request->input('fecha_fin');

        $query = DB::table('especialidad_madre')
            ->join('especialidad_programa', 'especialidad_programa.id_especialidad', '=', 'especialidad_madre.id')
            ->join('grupo', 'grupo.id_especialidad', '=', 'especialidad_programa.id')
            ->join('programa_estudio', 'grupo.id_programa', '=', 'programa_estudio.id')
            ->join('ciclo_academico', 'programa_estudio.id_ciclo', '=', 'ciclo_academico.id')
            ->join('matricula', 'matricula.id_grupo', '=', 'grupo.id')
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->where('especialidad_madre.is_deleted', 0)
            ->where('matricula.matriculado', 1);

        if ($fechaInicio) {
            $query->whereDate('matricula.created_at', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('matricula.created_at', '<=', $fechaFin);
        }

        $rows = $query->select(
            'especialidad_madre.id as especialidad_id',
            'especialidad_madre.nombre_especialidad as especialidad',
            'ciclo_academico.nombre_ciclo as ciclo',
            'estudiante.sexo',
            DB::raw('COUNT(*) as total')
        )
            ->groupBy(
                'especialidad_madre.id',
                'especialidad_madre.nombre_especialidad',
                'ciclo_academico.nombre_ciclo',
                'estudiante.sexo'
            )
            ->orderBy('especialidad_madre.nombre_especialidad')
            ->get();

        /* ------------------------------------------------------------
       ARMADO DE RESPUESTA
    ------------------------------------------------------------ */

        $resultado = [];

        foreach ($rows as $row) {
            $id = $row->especialidad_id;

            if (!isset($resultado[$id])) {
                $resultado[$id] = [
                    'nombre' => $row->especialidad,
                    'auxiliar_tecnico' => ['H' => 0, 'M' => 0],
                    'tecnico'  => ['H' => 0, 'M' => 0],
                ];
            }

            // Sexo
            $sexo = strtoupper($row->sexo) === 'M' ? 'H' : 'M';

            // Ciclo → nivel
            $nivel = str_contains(strtolower($row->ciclo), 'auxiliar')
                ? 'auxiliar_tecnico'
                : 'tecnico';

            $resultado[$id][$nivel][$sexo] += $row->total;
        }

        return response()->json(array_values($resultado));
    }

    public function matriculaPorNivelEducativoCicloSexo(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin    = $request->input('fecha_fin');

        $query = DB::table('matricula')
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->join('grupo', 'matricula.id_grupo', '=', 'grupo.id')
            ->join('programa_estudio', 'grupo.id_programa', '=', 'programa_estudio.id')
            ->join('ciclo_academico', 'programa_estudio.id_ciclo', '=', 'ciclo_academico.id')
            ->where('matricula.matriculado', 1);

        if ($fechaInicio) {
            $query->whereDate('matricula.created_at', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('matricula.created_at', '<=', $fechaFin);
        }

        $rows = $query->select(
            DB::raw('LOWER(estudiante.grado_instruccion) as nivel'),
            'ciclo_academico.nombre_ciclo as ciclo',
            'estudiante.sexo',
            DB::raw('COUNT(*) as total')
        )
            ->groupBy(
                DB::raw('LOWER(estudiante.grado_instruccion)'),
                'ciclo_academico.nombre_ciclo',
                'estudiante.sexo'
            )
            ->get();

        /* ------------------------------------------------------------
       CATÁLOGO REAL (IGUAL A TU FRONT)
    ------------------------------------------------------------ */

        $niveles = [
            'sin nivel'               => 'Sin Nivel',
            'primaria incompleta'     => 'Primaria incompleta',
            'primaria completa'       => 'Primaria completa',
            'secundaria incompleta'   => 'Secundaria incompleta',
            'secundaria completa'     => 'Secundaria completa',
            'superior incompleta'     => 'Superior incompleta',
            'superior completa'       => 'Superior completa',
        ];

        $resultado = [];

        foreach ($niveles as $key => $label) {
            $resultado[$key] = [
                'nivel' => $label,

                'total' => ['H' => 0, 'M' => 0],

                'auxiliar_tecnico' => ['H' => 0, 'M' => 0],
                'tecnico'          => ['H' => 0, 'M' => 0],
            ];
        }

        /* ------------------------------------------------------------
       PROCESAMIENTO
    ------------------------------------------------------------ */

        foreach ($rows as $row) {

            if (!isset($resultado[$row->nivel])) {
                continue;
            }

            // Sexo BD → Reporte
            $sexo = strtoupper($row->sexo) === 'M' ? 'H' : 'M';

            // Ciclo → nivel
            $nivelCiclo = str_contains(strtolower($row->ciclo), 'auxiliar')
                ? 'auxiliar_tecnico'
                : 'tecnico';

            // Total general
            $resultado[$row->nivel]['total'][$sexo] += $row->total;

            // Total por ciclo
            $resultado[$row->nivel][$nivelCiclo][$sexo] += $row->total;
        }

        return response()->json(array_values($resultado));
    }

    public function seccionesPorCicloTurno(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin    = $request->input('fecha_fin');

        $query = DB::table('grupo')
            ->join('programa_estudio', 'grupo.id_programa', '=', 'programa_estudio.id')
            ->join('ciclo_academico', 'programa_estudio.id_ciclo', '=', 'ciclo_academico.id')
            ->where('grupo.status', 1)
            ->where('programa_estudio.is_deleted', 0);

        if ($fechaInicio) {
            $query->whereDate('grupo.created_at', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('grupo.created_at', '<=', $fechaFin);
        }

        $rows = $query->select(
            'ciclo_academico.nombre_ciclo as ciclo',
            'grupo.turno',
            DB::raw('COUNT(grupo.id) as total')
        )
            ->groupBy(
                'ciclo_academico.nombre_ciclo',
                'grupo.turno'
            )
            ->get();

        /* ------------------------------------------------------------
       ARMADO DE RESPUESTA
    ------------------------------------------------------------ */

        $resultado = [];

        foreach ($rows as $row) {

            if (!isset($resultado[$row->ciclo])) {
                $resultado[$row->ciclo] = [
                    'ciclo' => $row->ciclo,
                    'total' => 0,
                    'turnos' => []
                ];
            }

            $turno = $row->turno ?? 'S/D'; // por si hay nulos

            $resultado[$row->ciclo]['turnos'][$turno] =
                ($resultado[$row->ciclo]['turnos'][$turno] ?? 0)
                + $row->total;

            $resultado[$row->ciclo]['total'] += $row->total;
        }

        return response()->json(array_values($resultado));
    }

    public function matriculaPorCicloSexoEdad(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin    = $request->input('fecha_fin');

        $query = DB::table('matricula')
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->join('grupo', 'matricula.id_grupo', '=', 'grupo.id')
            ->join('programa_estudio', 'grupo.id_programa', '=', 'programa_estudio.id')
            ->join('ciclo_academico', 'programa_estudio.id_ciclo', '=', 'ciclo_academico.id')
            ->where('matricula.matriculado', 1);

        if ($fechaInicio) {
            $query->whereDate('matricula.created_at', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('matricula.created_at', '<=', $fechaFin);
        }

        $rows = $query->select(
            DB::raw('TIMESTAMPDIFF(YEAR, estudiante.fecha_nacimiento, matricula.created_at) as edad'),
            'estudiante.sexo',
            'ciclo_academico.nombre_ciclo as ciclo',
            DB::raw('COUNT(*) as total')
        )
            ->groupBy(
                DB::raw('edad'),
                'estudiante.sexo',
                'ciclo_academico.nombre_ciclo'
            )
            ->get();

        /* ------------------------------------------------------------
       CATÁLOGO DE EDADES (FORMATO OFICIAL)
    ------------------------------------------------------------ */

        $edades = [
            '12' => '12 AÑOS',
            '13' => '13 AÑOS',
            '14' => '14 AÑOS',
            '15' => '15 AÑOS',
            '16' => '16 AÑOS',
            '17' => '17 AÑOS',
            '18' => '18 AÑOS',
            '19' => '19 AÑOS',
            '20' => '20 AÑOS',
            '21' => '21 AÑOS',
            '22' => '22 AÑOS',
            '23' => '23 AÑOS',
            '24' => '24 AÑOS',
            '25-29' => '25-29 AÑOS',
            '30-34' => '30-34 AÑOS',
            '35-39' => '35-39 AÑOS',
            '40-44' => '40-44 AÑOS',
            '45-49' => '45-49 AÑOS',
            '50-54' => '50-54 AÑOS',
            '55-59' => '55-59 AÑOS',
            '60+'    => '60 y más AÑOS',
        ];

        $resultado = [];

        // Inicialización
        foreach ($edades as $key => $label) {
            $resultado[$key] = [
                'edad' => $label,
                'total' => ['H' => 0, 'M' => 0],
                'auxiliar_tecnico' => ['H' => 0, 'M' => 0],
                'tecnico' => ['H' => 0, 'M' => 0],
            ];
        }

        /* ------------------------------------------------------------
       PROCESAMIENTO
    ------------------------------------------------------------ */

        foreach ($rows as $row) {

            $edad = (int) $row->edad;

            // Determinar rango
            if ($edad >= 60) $rango = '60+';
            elseif ($edad >= 55) $rango = '55-59';
            elseif ($edad >= 50) $rango = '50-54';
            elseif ($edad >= 45) $rango = '45-49';
            elseif ($edad >= 40) $rango = '40-44';
            elseif ($edad >= 35) $rango = '35-39';
            elseif ($edad >= 30) $rango = '30-34';
            elseif ($edad >= 25) $rango = '25-29';
            else $rango = (string) $edad;

            if (!isset($resultado[$rango])) continue;

            // Sexo
            $sexo = strtoupper($row->sexo) === 'M' ? 'H' : 'M';

            // Ciclo
            $nivelCiclo = str_contains(strtolower($row->ciclo), 'auxiliar')
                ? 'auxiliar_tecnico'
                : 'tecnico';

            // Totales
            $resultado[$rango]['total'][$sexo] += $row->total;
            $resultado[$rango][$nivelCiclo][$sexo] += $row->total;
        }

        /* ------------------------------------------------------------
       TOTAL GENERAL
    ------------------------------------------------------------ */

        $totalGeneral = [
            'edad' => 'TOTAL GENERAL',
            'total' => ['H' => 0, 'M' => 0],
            'auxiliar_tecnico' => ['H' => 0, 'M' => 0],
            'tecnico' => ['H' => 0, 'M' => 0],
        ];

        foreach ($resultado as $fila) {
            foreach (['H', 'M'] as $s) {
                $totalGeneral['total'][$s] += $fila['total'][$s];
                $totalGeneral['auxiliar_tecnico'][$s] += $fila['auxiliar_tecnico'][$s];
                $totalGeneral['tecnico'][$s] += $fila['tecnico'][$s];
            }
        }

        return response()->json(array_merge(
            [$totalGeneral],
            array_values($resultado)
        ));
    }
}

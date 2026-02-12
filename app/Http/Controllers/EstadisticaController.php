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
}

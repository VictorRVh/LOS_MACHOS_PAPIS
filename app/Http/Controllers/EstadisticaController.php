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
        $fechaFin = $request->input('fecha_fin');

        $query = Matricula::query()
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->join('grupo', 'matricula.id_grupo', '=', 'grupo.id')
            ->join('especialidad_programa', 'grupo.id_especialidad', '=', 'especialidad_programa.id')
            ->join('programa_estudio', 'especialidad_programa.id_programa', '=', 'programa_estudio.id')
            ->join('ciclo_academico', 'programa_estudio.id_ciclo', '=', 'ciclo_academico.id');
        if ($fechaInicio) {
            $query->where('matricula.created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $query->where('matricula.created_at', '<=', $fechaFin);
        }

        $estadisticas = $query->select(
            'ciclo_academico.nombre_ciclo as ciclo',
            'estudiante.sexo',
            'matricula.matriculado as estado',
            DB::raw('COUNT(*) as total')
        )
            ->groupBy('ciclo_academico.nombre_ciclo', 'estudiante.sexo', 'matricula.matriculado')
            ->get();

        $resultado = [
            'aprobados' => [
                'total' => 0,
                'basico' => ['H' => 0, 'M' => 0],
                'medio' => ['H' => 0, 'M' => 0]
            ],
            'retirados' => [
                'total' => 0,
                'basico' => ['H' => 0, 'M' => 0],
                'medio' => ['H' => 0, 'M' => 0]
            ]
        ];

        foreach ($estadisticas as $stat) {
            $sexo = strtoupper($stat->sexo);
            $cicloLower = strtolower($stat->ciclo);
            $tipo = in_array($cicloLower, ['básico', 'basico']) ? 'basico' : 'medio';

            if ($stat->estado == Matricula::STATUS_MATRICULADO) {
                $resultado['aprobados']['total'] += $stat->total;
                if (in_array($sexo, ['H', 'M'])) {
                    $resultado['aprobados'][$tipo][$sexo] += $stat->total;
                }
            } elseif (in_array($stat->estado, [Matricula::STATUS_RETIRADO, Matricula::STATUS_RETIRADO_JUSTIFICADO])) {
                $resultado['retirados']['total'] += $stat->total;
                if (in_array($sexo, ['H', 'M'])) {
                    $resultado['retirados'][$tipo][$sexo] += $stat->total;
                }
            }
        }

        return response()->json($resultado);
    }
}

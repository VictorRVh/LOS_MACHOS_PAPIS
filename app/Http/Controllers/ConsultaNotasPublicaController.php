<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Matricula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultaNotasPublicaController extends Controller
{
    private function redondearNotaFinal(?float $promedio): ?int
    {
        if ($promedio === null) {
            return null;
        }

        return (int) round($promedio, 0, PHP_ROUND_HALF_UP);
    }

    public function consultar(Request $request)
    {
        $validated = $request->validate([
            'nro_documento' => ['required', 'string', 'max:15'],
            'fecha_nacimiento' => ['required', 'date'],
        ]);

        $estudiante = Estudiante::query()
            ->where('nro_documento', trim($validated['nro_documento']))
            ->whereDate('fecha_nacimiento', $validated['fecha_nacimiento'])
            ->first();

        if (!$estudiante) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron notas con los datos ingresados.',
            ], 404);
        }

        $informacionAcademica = DB::table('matricula as m')
            ->join('grupo as g', 'm.id_grupo', '=', 'g.id')
            ->join('especialidad_programa as ep', 'g.id_especialidad', '=', 'ep.id')
            ->join('especialidad_madre as em', 'ep.id_especialidad', '=', 'em.id')
            ->join('periodo as p', 'g.id_periodo', '=', 'p.id')
            ->join('modulos as mod', 'g.id_modulo', '=', 'mod.id')
            ->join('programa_estudio as pe', 'g.id_programa', '=', 'pe.id')
            ->where('m.id_estudiante', $estudiante->id)
            ->select(
                'm.id as matricula_id',
                'm.turno as matricula_turno',
                'm.reserva',
                'm.fecha_reserva',
                'm.matriculado',
                'g.id as grupo_id',
                'g.seccion',
                'g.turno as grupo_turno',
                'g.fecha_inicio',
                'g.fecha_fin',
                'g.status as grupo_status',
                'em.id as especialidad_id',
                'em.nombre_especialidad',
                'ep.nro_modulos as total_modulos_especialidad',
                'p.id as periodo_id',
                'p.nombre_periodo',
                'pe.id as programa_id',
                'pe.descripcion as nombre_programa',
                'mod.id as modulo_id',
                'mod.numero_modulo',
                'mod.descripcion as modulo_descripcion',
                'mod.creditos',
                'mod.horas',
                'mod.nro_capacidades'
            )
            ->orderBy('p.nombre_periodo', 'desc')
            ->orderBy('mod.numero_modulo', 'asc')
            ->get();

        $grupoIds = $informacionAcademica->pluck('grupo_id')->unique()->values();

        $capacidadesPorGrupo = collect();
        $notasPorGrupo = collect();
        $notaExperienciaPorGrupo = collect();
        $asistenciaResumenPorGrupo = collect();

        if ($grupoIds->isNotEmpty()) {
            $capacidadesPorGrupo = DB::table('capacidad_terminal')
                ->whereIn('id_grupo', $grupoIds)
                ->select('id', 'id_grupo', 'numero_capacidad', 'nombre_capacidad')
                ->orderByRaw('CAST(numero_capacidad AS UNSIGNED) ASC')
                ->get()
                ->groupBy('id_grupo');

            $notasPorGrupo = DB::table('nota_capacidad_terminal')
                ->where('id_estudiante', $estudiante->id)
                ->whereIn('id_grupo', $grupoIds)
                ->select('id_grupo', 'id_capacidad', 'nota_capacidad')
                ->get()
                ->groupBy('id_grupo')
                ->map(fn($items) => $items->keyBy('id_capacidad'));

            $notaExperienciaPorGrupo = DB::table('nota_experiencia_formativa')
                ->where('id_estudiante', $estudiante->id)
                ->whereIn('id_grupo', $grupoIds)
                ->where('status', 1)
                ->whereNotNull('nota')
                ->orderByDesc('updated_at')
                ->orderByDesc('tipo_practicas')
                ->select('id_grupo', 'nota')
                ->get()
                ->groupBy('id_grupo')
                ->map(fn($items) => $items->first());

            $asistenciaResumenPorGrupo = DB::table('asistencia')
                ->where('id_estudiante', $estudiante->id)
                ->whereIn('id_grupo', $grupoIds)
                ->select(
                    'id_grupo',
                    DB::raw('COUNT(*) as total_registros'),
                    DB::raw('SUM(CASE WHEN asistencia = 1 THEN 1 ELSE 0 END) as asistio'),
                    DB::raw('SUM(CASE WHEN asistencia = 2 THEN 1 ELSE 0 END) as faltas'),
                    DB::raw('SUM(CASE WHEN asistencia = 3 THEN 1 ELSE 0 END) as tardanzas'),
                    DB::raw('SUM(CASE WHEN asistencia = 4 THEN 1 ELSE 0 END) as permisos')
                )
                ->groupBy('id_grupo')
                ->get()
                ->keyBy('id_grupo');
        }

        $especialidades = [];

        foreach ($informacionAcademica as $registro) {
            $capacidadesGrupo = $capacidadesPorGrupo->get($registro->grupo_id, collect());
            $notasGrupo = $notasPorGrupo->get($registro->grupo_id, collect());
            $notaExperienciaRegistro = $notaExperienciaPorGrupo->get($registro->grupo_id);
            $notaExperiencia = 0.0;
            $experienciaRegistrada = false;

            if ($notaExperienciaRegistro && $notaExperienciaRegistro->nota !== null && is_numeric($notaExperienciaRegistro->nota)) {
                $notaExperiencia = (float) $notaExperienciaRegistro->nota;
                $experienciaRegistrada = true;
            }

            $unidadesNotas = $capacidadesGrupo->map(function ($cap) use ($notasGrupo) {
                $notaRegistro = $notasGrupo->get($cap->id);
                $nota = $notaRegistro ? $notaRegistro->nota_capacidad : null;

                return [
                    'id_capacidad' => $cap->id,
                    'numero_unidad' => $cap->numero_capacidad,
                    'nombre_unidad' => $cap->nombre_capacidad,
                    'nota' => $nota !== null && is_numeric($nota) ? (float) $nota : 0.0,
                ];
            })->values();

            $unidadesNotas->push([
                'id_capacidad' => null,
                'numero_unidad' => 'EF',
                'nombre_unidad' => 'Experiencia formativa',
                'nota' => $notaExperiencia,
                'es_experiencia_formativa' => true,
                'registrada' => $experienciaRegistrada,
            ]);

            $notasValidas = $unidadesNotas->pluck('nota');
            $promedioBase = $notasValidas->count() > 0 ? (float) $notasValidas->avg() : null;
            $promedioNotas = $this->redondearNotaFinal($promedioBase);

            $asistenciaGrupo = $asistenciaResumenPorGrupo->get($registro->grupo_id);
            $totalAsistencia = $asistenciaGrupo->total_registros ?? 0;
            $asistio = $asistenciaGrupo->asistio ?? 0;
            $tardanzas = $asistenciaGrupo->tardanzas ?? 0;
            $faltas = $asistenciaGrupo->faltas ?? 0;
            $permisos = $asistenciaGrupo->permisos ?? 0;
            $porcentajeAsistencia = $totalAsistencia > 0
                ? round((($asistio + $tardanzas) / $totalAsistencia) * 100, 1)
                : null;
            $matriculadoEstado = is_numeric($registro->matriculado) ? (int) $registro->matriculado : 0;
            $reservaEstado = is_numeric($registro->reserva) ? (int) $registro->reserva : 0;
            $reservaTexto = null;

            if ($reservaEstado === 1) {
                $reservaTexto = 'Reserva activa';
            } elseif ($reservaEstado === 3) {
                $reservaTexto = 'Reserva utilizada';
            }

            $espId = $registro->especialidad_id;

            if (!isset($especialidades[$espId])) {
                $especialidades[$espId] = [
                    'id' => $registro->especialidad_id,
                    'nombre' => $registro->nombre_especialidad,
                    'programa' => [
                        'id' => $registro->programa_id,
                        'nombre' => $registro->nombre_programa,
                    ],
                    'total_modulos' => $registro->total_modulos_especialidad,
                    'periodos' => [],
                ];
            }

            $periodoId = $registro->periodo_id;

            if (!isset($especialidades[$espId]['periodos'][$periodoId])) {
                $especialidades[$espId]['periodos'][$periodoId] = [
                    'id' => $registro->periodo_id,
                    'nombre' => $registro->nombre_periodo,
                    'modulos' => [],
                ];
            }

            $especialidades[$espId]['periodos'][$periodoId]['modulos'][] = [
                'matricula_id' => $registro->matricula_id,
                'modulo' => [
                    'id' => $registro->modulo_id,
                    'numero' => $registro->numero_modulo,
                    'descripcion' => $registro->modulo_descripcion,
                    'creditos' => $registro->creditos,
                    'horas' => $registro->horas,
                    'nro_capacidades' => $registro->nro_capacidades,
                ],
                'grupo' => [
                    'id' => $registro->grupo_id,
                    'seccion' => $registro->seccion,
                    'turno' => $registro->grupo_turno,
                    'fecha_inicio' => $registro->fecha_inicio,
                    'fecha_fin' => $registro->fecha_fin,
                    'status' => $registro->grupo_status,
                ],
                'estado' => (int) $registro->matriculado,
                'matricula' => [
                    'turno' => $registro->matricula_turno,
                    'reserva' => (bool) $registro->reserva,
                    'fecha_reserva' => $registro->fecha_reserva,
                    'matriculado' => (bool) $registro->matriculado,
                    'reserva_estado' => $reservaEstado,
                    'matriculado_estado' => $matriculadoEstado,
                    'estado_texto' => Matricula::STATUS[$matriculadoEstado] ?? 'Desconocido',
                    'reserva_texto' => $reservaTexto,
                ],
                'notas_unidades' => $unidadesNotas,
                'promedio_notas' => $promedioNotas,
                'nota_experiencia_formativa' => $notaExperiencia,
                'asistencia_resumen' => [
                    'total_registros' => (int) $totalAsistencia,
                    'asistio' => (int) $asistio,
                    'tardanzas' => (int) $tardanzas,
                    'faltas' => (int) $faltas,
                    'permisos' => (int) $permisos,
                    'porcentaje_asistencia' => $porcentajeAsistencia,
                ],
            ];
        }

        $especialidades = array_values($especialidades);
        foreach ($especialidades as &$especialidad) {
            $especialidad['periodos'] = array_values($especialidad['periodos']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'estudiante' => [
                    'nro_documento' => $estudiante->nro_documento,
                    'apellido_paterno' => $estudiante->apellido_paterno,
                    'apellido_materno' => $estudiante->apellido_materno,
                    'nombre' => $estudiante->nombre,
                    'nombre_completo' => trim("{$estudiante->apellido_paterno} {$estudiante->apellido_materno} {$estudiante->nombre}"),
                ],
                'historial_academico' => $especialidades,
            ],
        ]);
    }
}

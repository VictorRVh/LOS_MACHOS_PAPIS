<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\CalendarioAdmin;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsistenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $asistencias = Asistencia::with(['grupo', 'estudiante', 'calendarioAdmin'])->get();
        return response()->json($asistencias);
    }

    // GET /api/asistencia/{id}
    public function show($id)
    {
        $asistencia = Asistencia::with(['grupo', 'estudiante', 'calendarioAdmin'])->find($id);

        if (!$asistencia) {
            return response()->json(['message' => 'Asistencia no encontrada'], 404);
        }

        return response()->json($asistencia);
    }

    // POST /api/asistencia
    public function store(Request $request)
    {
        $request->validate([
            'id_grupo' => 'required|uuid',
            'id_calendario' => 'required|uuid',
            'fecha_actual' => 'required|date',
            'observacion' => 'nullable|string',
            'estudiantes' => 'required|array',
            'estudiantes.*.id_estudiante' => 'required|uuid',
            'estudiantes.*.asistencia' => 'required|integer',
            // 'estudiantes.*.observacion' => 'nullable|string|max:255',
        ]);

        $fecha = Carbon::parse($request->fecha_actual)->format('Y-m-d');
        $now = Carbon::now();

        $calendario = CalendarioAdmin::find($request->id_calendario);
        if (!$calendario) {
            return response()->json(['message' => 'Calendario no encontrado.'], 404);
        }

        $yaRegistrado = Asistencia::where('id_calendario', $request->id_calendario)
            ->whereDate('fecha_actual', $fecha)
            ->exists();

        if ($yaRegistrado) {
            return response()->json([
                'message' => 'Ya existe registro de asistencias para esta fecha',
            ], 409);
        }

        DB::transaction(function () use ($request, $fecha, $now, $calendario) {

            $asistencias = collect($request->estudiantes)->map(function ($est) use ($request, $fecha, $now) {
                return [
                    'id' => (string) Str::uuid(),
                    'fecha_actual' => $fecha,
                    'asistencia' => $est['asistencia'],
                    'observacion' => $est['observacion'] ?? null,
                    'id_grupo' => $request->id_grupo,
                    'id_estudiante' => $est['id_estudiante'],
                    'id_calendario' => $request->id_calendario,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->toArray();

            Asistencia::insert($asistencias);

            $calendario->update([
                // 'laborable' => $request->laborable ?? 0,
                'laborable' => 1,
                'descripcion' => $request->observacion ?? 'Sesion de hoy realizada correctamente.',
                'updated_at' => $now,
            ]);
        });

        return response()->json([
            'message' => 'Asistencias registradas correctamente.',
            'total_insertados' => count($request->estudiantes),
        ], 201);
    }

    // PATCH /api/asistencia/{id}
    public function update(Request $request, $id)
    {
        $asistencia = Asistencia::find($id);

        if (!$asistencia) {
            return response()->json(['message' => 'Asistencia no encontrada'], 404);
        }

        $request->validate([
            'fecha_actual'   => 'sometimes|date',
            'asistencia'     => 'sometimes',
            'observacion'    => 'nullable|string|max:255',
            'id_grupo'       => 'sometimes|uuid|exists:grupo,id',
            'id_estudiante'  => 'sometimes|uuid|exists:estudiante,id',
            'id_calendario'  => 'sometimes|uuid|exists:calendario_admin,id',
        ]);

        $asistencia->update($request->all());

        return response()->json([
            'message' => 'Asistencia actualizada correctamente',
            'data' => $asistencia
        ]);
    }

    // DELETE /api/asistencia/{id}
    public function destroy($id)
    {
        $asistencia = Asistencia::find($id);

        if (!$asistencia) {
            return response()->json(['message' => 'Asistencia no encontrada'], 404);
        }

        $asistencia->delete();

        return response()->json(['message' => 'Asistencia eliminada correctamente']);
    }

    public function obtenerSesionPorEntrega($idEntrega)
    {
        $hoy = Carbon::now('America/Lima')->toDateString();

        $sesion = DB::table('sesiones as s')
            ->join('calendario_admin as c', 'c.id_sesion', '=', 's.id')
            ->where('s.id_entrega', $idEntrega)
            ->whereDate('c.fecha', '=', $hoy)
            ->select(
                's.id as id_sesion',
                's.nombre_sesion',
                's.descripcion',
                's.archivo_sesion',
                'c.id as id_calendario',
                'c.fecha',
                'c.laborable',
                'c.descripcion as descripcion_calendario'
            )
            ->first();

        if (!$sesion) {
            return response()->json([
                'message' => 'No se encontró una sesión para la fecha actual',
                'fecha_actual' => $hoy,
                'id_entrega' => $idEntrega
            ], 404);
        }

        return response()->json([
            'id' => $sesion->id_sesion,
            'fecha_actual' => $hoy,
            'nombre_sesion' => $sesion->nombre_sesion,
            'descripcion' => $sesion->descripcion,
            'archivo_sesion' => $sesion->archivo_sesion,
            'calendario' => [
                'id' => $sesion->id_calendario,
                'laborable' => $sesion->laborable,
                'descripcion_calendario' => $sesion->descripcion_calendario
            ]
        ]);
    }

    public function obtenerAsistenciaEstudiantes($idGrupo)
    {
        $hoy = Carbon::now('America/Lima')->toDateString();

        $sesion = DB::table('sesiones as s')
            ->join('entrega_docente as ed', 'ed.id', '=', 's.id_entrega')
            ->join('calendario_admin as c', 'c.id_sesion', '=', 's.id')
            ->where('ed.id_grupo', $idGrupo)
            ->whereDate('c.fecha', '=', $hoy)
            ->select(
                's.id as id_sesion',
                's.nombre_sesion',
                's.descripcion',
                's.archivo_sesion',
                'c.id as id_calendario',
                'c.fecha',
                'c.laborable',
                'c.descripcion as descripcion_calendario'
            )
            ->first();

        $estudiantes = DB::table('matricula as m')
            ->join('estudiante as e', 'e.id', '=', 'm.id_estudiante')
            ->leftJoin('asistencia as a', function ($join) use ($sesion, $idGrupo) {
                if ($sesion) {
                    // Si hay sesión hoy, usar esa
                    $join->on('a.id_estudiante', '=', 'm.id_estudiante')
                        ->where('a.id_calendario', '=', $sesion->id_calendario)
                        ->where('a.id_grupo', '=', $idGrupo);
                } else {
                    // Si no hay sesión hoy, traer la última asistencia registrada
                    $join->on('a.id_estudiante', '=', 'm.id_estudiante')
                        ->where('a.id_grupo', '=', $idGrupo)
                        ->whereRaw('a.fecha_actual = (SELECT MAX(fecha_actual) FROM asistencia WHERE id_estudiante = m.id_estudiante AND id_grupo = ?)', [$idGrupo]);
                }
            })
            ->where('m.id_grupo', $idGrupo)
            ->where('m.reserva', 0)
            ->select(
                'e.id as id_estudiante',
                DB::raw("CONCAT(e.apellido_paterno, ' ', e.apellido_materno, ' ', e.nombre) as nombre_completo"),
                'e.apellido_paterno',
                'e.apellido_materno',
                'e.nombre',
                'e.nro_documento',
                'm.matriculado',
                DB::raw('COALESCE(a.asistencia, 0) as asistencia')
            )
            ->orderBy('e.apellido_paterno', 'asc')
            ->get()
            ->map(function ($item) {
                $item->ultima_vez = Asistencia::STATUS[$item->asistencia] ?? 'Desconocido';

                $totales = DB::table('asistencia')
                    ->where('id_estudiante', $item->id_estudiante)
                    ->selectRaw("
                    SUM(CASE WHEN asistencia = 1 THEN 1 ELSE 0 END) as asistencias,
                    SUM(CASE WHEN asistencia = 2 THEN 1 ELSE 0 END) as faltas,
                    SUM(CASE WHEN asistencia = 3 THEN 1 ELSE 0 END) as tardanzas,
                    SUM(CASE WHEN asistencia = 4 THEN 1 ELSE 0 END) as permisos,
                    SUM(CASE WHEN asistencia = 5 THEN 1 ELSE 0 END) as retirados
                ")->first();

                $item->totales = $totales;

                $item->estado = $item->matriculado ? 'Matriculado' : 'Retirado';

                return $item;
            });

        return response()->json([
            'fecha_actual' => $hoy,
            'hay_sesion' => $sesion ? true : false,
            'id_calendario' => $sesion->id_calendario ?? null,
            'sesion' => $sesion ? [
                'id' => $sesion->id_sesion,
                'nombre_sesion' => $sesion->nombre_sesion,
                'descripcion' => $sesion->descripcion,
                'archivo_sesion' => $sesion->archivo_sesion,
                'calendario' => [
                    'id' => $sesion->id_calendario,
                    'laborable' => $sesion->laborable,
                    'descripcion_calendario' => $sesion->descripcion_calendario
                ]
            ] : null,
            'estudiantes' => $estudiantes,
            'message' => $sesion
                ? 'Sesión encontrada para la fecha actual.'
                : 'No se encontró una sesión para la fecha actual, mostrando datos del grupo.'
        ]);
    }
}

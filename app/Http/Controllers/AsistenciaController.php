<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
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
            'fecha_actual'   => 'required|date',
            'asistencia'     => 'required',
            'observacion'    => 'nullable|string|max:255',
            'id_grupo'       => 'required|uuid|exists:grupo,id',
            'id_estudiante'  => 'required|uuid|exists:estudiante,id',
            'id_calendario'  => 'required|uuid|exists:calendario_admin,id',
        ]);

        $asistencia = Asistencia::create($request->all());

        return response()->json([
            'message' => 'Asistencia registrada correctamente',
            'data' => $asistencia
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

    public function obtenerAsistenciaEstudiantes($idEntrega)
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

        $grupo = DB::table('entrega_docente')
            ->where('id', $idEntrega)
            ->value('id_grupo');

        if (!$grupo) {
            return response()->json([
                'message' => 'No se encontró el grupo para esta entrega'
            ], 404);
        }

        $estudiantes = DB::table('matricula as m')
            ->join('estudiante as e', 'e.id', '=', 'm.id_estudiante')
            ->leftJoin('asistencia as a', function ($join) use ($sesion) {
                $join->on('a.id_estudiante', '=', 'm.id_estudiante')
                    ->where('a.id_calendario', '=', $sesion->id_calendario);
            })
            ->where('m.id_grupo', $grupo)
            ->select(
                'e.id as id_estudiante',
                DB::raw("CONCAT(e.apellido_paterno, ' ', e.apellido_materno, ' ', e.nombre) as nombre_completo"),
                'e.nro_documento',
                DB::raw('COALESCE(a.asistencia, 0) as asistencia') // 0 si aún no hay registro
            )
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

                return $item;
            });

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
            ],
            'estudiantes' => $estudiantes
        ]);
    }
}

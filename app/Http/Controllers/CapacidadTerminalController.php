<?php

namespace App\Http\Controllers;

use App\Models\CapacidadTerminal;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CapacidadTerminalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $capacidades = CapacidadTerminal::with('grupo')->get();
        return response()->json($capacidades);
    }

    public function indexGrupo($id)
    {
        // 1️⃣ Obtener las capacidades terminales del grupo
        $capacidades = CapacidadTerminal::where('id_grupo', $id)
            ->orderBy('fecha_inicio', 'desc')
            ->select('id', 'id_grupo', 'numero_capacidad', 'nombre_capacidad', 'fecha_inicio', 'fecha_fin', 'status')
            ->get();

        // 2️⃣ Obtener el número de capacidades del módulo asociado al grupo
        $nroCapacidades = Grupo::join('modulos', 'grupo.id_modulo', '=', 'modulos.id')
            ->where('grupo.id', $id)
            ->value('modulos.nro_capacidades');

        // 3️⃣ Devolver ambos datos en la respuesta JSON
        return response()->json([
            'nro_capacidades' => $nroCapacidades,
            'capacidades' => $capacidades
        ]);
    }

    public function nroCapacidades($id)
    {
        $grupo = Grupo::with('modulo')->find($id);

        if (!$grupo || !$grupo->modulo) {
            return response()->json(['error' => 'Grupo o módulo no encontrado'], 404);
        }

        $numeroCapacidades = $grupo->modulo->nro_capacidades;

        return response()->json(['nro_capacidades' => $numeroCapacidades]);
    }


    // GET /api/capacidad-terminal/{id}
    public function show($id)
    {
        $capacidad = CapacidadTerminal::with('grupo')->find($id);

        if (!$capacidad) {
            return response()->json(['message' => 'Capacidad no encontrada'], 404);
        }

        return response()->json($capacidad);
    }
    // POST /api/capacidad-terminal
    public function store(Request $request)
    {
        $request->validate([
            'numero_capacidad' => 'required|string|max:255',
            'nombre_capacidad' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'id_grupo' => 'required|exists:grupo,id',
            'status' => 'required|in:0,1,2,3',
        ]);

        $capacidad = CapacidadTerminal::create($request->all());

        return response()->json($capacidad, 201);
    }

    // PUT/PATCH /api/capacidad-terminal/{id}
    public function update(Request $request, $id)
    {
        $capacidad = CapacidadTerminal::find($id);

        if (!$capacidad) {
            return response()->json(['message' => 'Capacidad no encontrada'], 404);
        }

        $request->validate([
            'nombre_capacidad' => 'sometimes|string|max:255',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'sometimes|date|after_or_equal:fecha_inicio',
            'id_grupo' => 'sometimes|exists:grupo,id',
            'status' => 'sometimes|in:0,1,2,3',
        ]);

        $capacidad->update($request->all());

        return response()->json($capacidad);
    }

    // DELETE /api/capacidad-terminal/{id}
    public function destroy($id)
    {
        $capacidad = CapacidadTerminal::find($id);

        if (!$capacidad) {
            return response()->json(['message' => 'Capacidad no encontrada'], 404);
        }

        $capacidad->delete();

        return response()->json(['message' => 'Capacidad eliminada correctamente'], 204);
    }

    public function getMatriculadosPorGrupoParaNotas($idGrupo)
    {

        // Estudiantes matriculados (sin reserva)
        $estudiantes = DB::table('matricula')
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->where('matricula.id_grupo', $idGrupo)
            ->where(function ($q) {
                $q->whereNull('matricula.reserva')->orWhere('matricula.reserva', 0);
            })
            ->select(
                'estudiante.id as id_estudiante',
                DB::raw("CONCAT(estudiante.apellido_paterno, ' ', estudiante.apellido_materno, ', ', estudiante.nombre) as apellidos_nombres")
            )
            ->orderBy('estudiante.apellido_paterno', 'asc')
            ->get();

        // Capacidades terminales activas
        $capacidades = DB::table('capacidad_terminal')
            ->where('id_grupo', $idGrupo)
            ->where('status', 1)
            ->select('id as id_capacidad', 'numero_capacidad', 'nombre_capacidad')
            ->orderBy('numero_capacidad', 'asc')
            ->get();

        // Construir la lista de estudiantes con sus capacidades y notas (si existen)
        $resultado = $estudiantes->map(function ($est) use ($capacidades, $idGrupo) {
            $capConNotas = $capacidades->map(function ($cap) use ($est, $idGrupo) {
                // Buscar si ya existe nota para este estudiante y capacidad
                $nota = DB::table('nota_capacidad_terminal')
                    ->where('id_grupo', $idGrupo)
                    ->where('id_estudiante', $est->id_estudiante)
                    ->where('id_capacidad', $cap->id_capacidad)
                    ->select('id', 'nota_capacidad')
                    ->first();

                return [
                    'id_capacidad' => $cap->id_capacidad,
                    'numero_capacidad' => $cap->numero_capacidad,
                    // 'nombre_capacidad' => $cap->nombre_capacidad,
                    // 'id_nota' => $nota->id ?? null,
                    'nota_capacidad' => $nota->nota_capacidad ?? null,
                ];
            });

            return [
                'id_estudiante' => $est->id_estudiante,
                'apellidos_nombres' => $est->apellidos_nombres,
                'capacidades' => $capConNotas
            ];
        });

        // Respuesta final
        return response()->json($resultado);
    }
}

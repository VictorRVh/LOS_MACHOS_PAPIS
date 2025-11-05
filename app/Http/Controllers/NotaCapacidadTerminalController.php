<?php

namespace App\Http\Controllers;

use App\Models\CapacidadTerminal;
use App\Models\Grupo;
use App\Models\Matricula;
use App\Models\NotaCapacidadTerminal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotaCapacidadTerminalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notas = NotaCapacidadTerminal::with(['grupo', 'capacidadTerminal', 'estudiante'])->get();
        return response()->json($notas);
    }

    public function index_grupo_capacidad_terminal($id)
    {
        // ✅ Cantidad total de capacidades (independiente de si tienen notas)
        $cantidadCapacidades = CapacidadTerminal::where('id_grupo', $id)->count();

        // ✅ Solo capacidades que no tienen notas asignadas
        $capacidades = CapacidadTerminal::where('id_grupo', $id)
            ->orderBy('fecha_inicio', 'desc')
            ->select(
                'id',
                'id_grupo',
                'nombre_capacidad',
                'fecha_inicio',
                'fecha_fin',
                'status'
            )
            ->whereDoesntHave('notaCapacidadTerminal') // 👈 Filtro para que no tengan notas asociadas
            ->get();

        return response()->json([
            'capacidades' => $capacidades,
            'cantidad_capacidades' => $cantidadCapacidades, // 👈 Total de capacidades, incluyendo con notas
        ]);
    }


    public function index_grupo_alumnos($idGrupo)
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


    // GET /api/nota-capacidad-terminal/{id}
    public function show($id)
    {
        $nota = NotaCapacidadTerminal::with(['grupo', 'capacidadTerminal', 'estudiante'])->find($id);

        if (!$nota) {
            return response()->json(['message' => 'Nota no encontrada'], 404);
        }

        return response()->json($nota);
    }

    // POST /api/nota-capacidad-terminal
    public function store(Request $request)
    {
        // Validar estructura básica del request
        $request->validate([
            'id_grupo' => 'required|exists:grupo,id',
            'id_capacidad_terminal' => 'required|exists:capacidad_terminal,id',
            'notas' => 'required|array',
            'notas.*.id_estudiante' => 'required|exists:estudiante,id',
            'notas.*.nota' => 'required|numeric|min:0|max:20',
        ]);

        try {
            DB::beginTransaction();

            $count = 0; 

            foreach ($request->notas as $nota) {
                NotaCapacidadTerminal::create([ // USAR INSERT EN CASO HAYA UNA INSERCION DE VARIOS DATOS
                    'nota_capacidad' => $nota['nota'],
                    'id_grupo' => $request->id_grupo,
                    'id_capacidad' => $request->id_capacidad_terminal,
                    'id_estudiante' => $nota['id_estudiante'],
                ]);

                $count++;
            }

            DB::commit();

            return response()->json([
                'message' => 'Notas registradas correctamente',
                'count' => $count,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al registrar las notas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // PUT/PATCH /api/nota-capacidad-terminal/{id}
    public function update(Request $request, $id)
    {
        $nota = NotaCapacidadTerminal::find($id);

        if (!$nota) {
            return response()->json(['message' => 'Nota no encontrada'], 404);
        }

        $request->validate([
            'nota_capacidad' => 'sometimes|numeric|min:0|max:20',
            'id_grupo' => 'sometimes|exists:grupo,id',
            'id_capacidad' => 'sometimes|exists:capacidad_terminal,id',
            'id_estudiante' => 'sometimes|exists:estudiante,id',
        ]);

        $nota->update($request->all());

        return response()->json($nota);
    }

    // DELETE /api/nota-capacidad-terminal/{id}
    public function destroy($id)
    {
        $nota = NotaCapacidadTerminal::find($id);

        if (!$nota) {
            return response()->json(['message' => 'Nota no encontrada'], 404);
        }

        $nota->delete();

        return response()->json(['message' => 'Nota eliminada correctamente']);
    }

    public function listaAlumnosNotas($id)
    {
        $grupo = Grupo::findOrFail($id);

        $alumnos = $grupo->estudiantes()
            ->select('estudiante.id', 'apellido_paterno', 'apellido_materno', 'nombre')
            ->get()
            ->makeHidden(['pivot']);

        return response()->json($alumnos);
    }
}

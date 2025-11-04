<?php

namespace App\Http\Controllers;

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


    public function index_grupo_alumnos($grupoId)
    {
        // Traer solo la info de especialidad y módulo
        $infoGrupo = DB::table('grupo as g')
            ->join('especialidad_programa as ep', 'g.id_especialidad', '=', 'ep.id')
            ->join('especialidad_madre as em', 'ep.id_especialidad', '=', 'em.id')
            ->join('modulos as mo', 'g.id_modulo', '=', 'mo.id')
            ->where('g.id', $grupoId)
            ->select(
                'em.nombre_especialidad as especialidad',
                'mo.descripcion as modulo'
            )
            ->first();

        // Traer a los estudiantes matriculados en el grupo
        $matriculados = DB::table('matricula as m')
            ->join('estudiante as e', 'm.id_estudiante', '=', 'e.id')
            ->where('m.id_grupo', $grupoId)
            ->where(function ($q) {
                $q->whereNull('m.reserva')
                    ->orWhere('m.reserva', 0);
            })
            ->select(
                'm.id as id_matricula',
                'e.id as id_estudiante',
                DB::raw("CONCAT(e.apellido_paterno, ' ', e.apellido_materno, ', ', e.nombre) as estudiante"),
                'e.nro_documento',
                'e.sexo',
                'e.fecha_nacimiento',
                'm.turno',
                'm.reserva'
            )
            ->orderBy('e.apellido_paterno', 'asc')
            ->orderBy('e.apellido_materno', 'asc')
            ->orderBy('e.nombre', 'asc')
            ->get();

        return response()->json([
            'especialidad' => $infoGrupo->especialidad,
            'modulo' => $infoGrupo->modulo,
            'matriculados' => $matriculados,
        ]);

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
        $request->validate([
            // 'nota_capacidad' => 'sometimes|numeric|min:0|max:20',
            'nota_capacidad' => 'required|string',
            'id_grupo' => 'required|exists:grupo,id',
            'id_capacidad' => 'required|exists:capacidad_terminal,id',
            'id_estudiante' => 'required|exists:estudiante,id',
        ]);

        $nota = NotaCapacidadTerminal::create($request->all());

        return response()->json($nota, 201);
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
}

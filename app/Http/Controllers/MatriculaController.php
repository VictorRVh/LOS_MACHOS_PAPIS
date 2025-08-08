<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Matricula;
use App\Models\ProgramaEstudio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatriculaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $matriculas = Matricula::with([
            'grupo:id,seccion,turno',
            'estudiante:id,nombre,apellido_paterno,apellido_materno,dni',
            'pago:id,monto,fecha_pago'
        ])->get();

        return response()->json($matriculas);
    }

    // POST /api/matriculas
    public function store(Request $request)
    {
        $request->validate([
            'id_grupo'      => 'required|uuid|exists:grupo,id',
            'turno'         => 'required|string|max:10',
            'id_estudiante' => 'required|uuid|exists:estudiante,id',
            'id_pago'       => 'nullable|uuid|exists:pago,id',
            'reserva'       => 'nullable|boolean'
        ]);

        $matricula = Matricula::create($request->all());

        return response()->json(['message' => 'Matrícula registrada con éxito', 'data' => $matricula], 201);
    }

    // GET /api/matriculas/{id}
    public function show($id)
    {
        $matricula = Matricula::with(['grupo', 'estudiante', 'pago'])->find($id);

        if (!$matricula) {
            return response()->json(['message' => 'Matrícula no encontrada'], 404);
        }

        return response()->json($matricula);
    }

    // PATCH /api/matriculas/{id}
    public function update(Request $request, $id)
    {
        $matricula = Matricula::find($id);

        if (!$matricula) {
            return response()->json(['message' => 'Matrícula no encontrada'], 404);
        }

        $request->validate([
            'id_grupo'      => 'sometimes|uuid|exists:grupo,id',
            'turno'         => 'sometimes|string|max:10',
            'id_estudiante' => 'sometimes|uuid|exists:estudiante,id',
            'id_pago'       => 'nullable|uuid|exists:pago,id',
            'reserva'       => 'nullable|boolean'
        ]);

        $matricula->update($request->all());

        return response()->json(['message' => 'Matrícula actualizada con éxito', 'data' => $matricula]);
    }

    // DELETE /api/matriculas/{id}
    public function destroy($id)
    {
        $matricula = Matricula::find($id);

        if (!$matricula) {
            return response()->json(['message' => 'Matrícula no encontrada'], 404);
        }

        $matricula->delete();

        return response()->json(['message' => 'Matrícula eliminada con éxito']);
    }

    // END POINTS PARA MATRICULA

    public function getProgramasPorCiclo($idCiclo)
    {
        $programas = ProgramaEstudio::with(['especialidadPrograma.especialidadMadre'])
            ->where('id_ciclo', $idCiclo)
            ->get()
            ->map(function ($programa) {
                return [
                    'id_programa' => $programa->id,
                    'año' => $programa->año,
                    'especialidades' => $programa->especialidadPrograma->map(function ($espProg) {
                        return [
                            'id' => $espProg->id,
                            'nombre_especialidad' => $espProg->especialidadMadre->nombre_especialidad
                        ];
                    })
                ];
            });

        return response()->json($programas);
    }

    public function getGruposPorEspecialidad($idEspecialidad)
    {
        $grupos = Grupo::with(['periodo', 'modulo', 'docente.user'])
            ->where('id_especialidad', $idEspecialidad)
            ->get()
            ->map(function ($grupo) {
                return [
                    'id' => $grupo->id,
                    'periodo' => $grupo->periodo->nombre_periodo ?? null,
                    'modulo' => $grupo->modulo->descripcion ?? null,
                    'horas' => $grupo->modulo->horas ?? null,
                    'seccion' => $grupo->seccion,
                    'turno' => $grupo->turno,
                    'duracion' => $grupo->fecha_inicio . ' a ' . $grupo->fecha_fin, 
                    // 'convenio' => $grupo->convenio ?? null,
                    'convenio' => $grupo->convenio->nombre_institucion ?? null,
                    'docente' => $grupo->docente && $grupo->docente->user
                    ? trim(
                        $grupo->docente->user->apellido_paterno . ' ' .
                        $grupo->docente->user->apellido_materno . ' ' .
                        $grupo->docente->user->name
                      )
                    : null
                ];
            });

        return response()->json($grupos);
    }
}

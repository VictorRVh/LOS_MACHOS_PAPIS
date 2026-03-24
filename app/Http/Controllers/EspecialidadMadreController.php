<?php

namespace App\Http\Controllers;

use App\Models\CicloAcademico;
use App\Models\EspecialidadMadre;
use Illuminate\Http\Request;
use App\Traits\Helpers;
use Illuminate\Support\Facades\DB;

class EspecialidadMadreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use Helpers;
    public function index()
    {
        $especialidades = EspecialidadMadre::with('cicloAcademico')
            ->where('is_deleted', 0)    // Solo las activas
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($especialidades);
    }


    // Mostrar uno por ID
    public function show($id)
    {
        $especialidad = EspecialidadMadre::with('cicloAcademico')->find($id);

        if (!$especialidad) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }

        return response()->json($especialidad);
    }
    public function getprogramasweb()
    {
        $programas = EspecialidadMadre::with('cicloAcademico')
            ->where('is_deleted', 0)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nombre' => $item->nombre_especialidad,
                    'ciclo' => $item->cicloAcademico->nombre_ciclo ?? null,
                    'label' => $item->nombre_especialidad . ' - ' . ($item->cicloAcademico->nombre_ciclo ?? 'Sin ciclo'),
                ];
            });

        return response()->json($programas);
    }
    // Crear nueva especialidad
    public function store(Request $request)
    {
        $request->validate([
            'nombre_especialidad' => 'required|string|max:100',
            'id_ciclo' => 'required|exists:ciclo_academico,id',
        ]);

        $especialidad = EspecialidadMadre::create($request->all());
        $this->registrarActividad(
            "Creó la especialidad '{$especialidad->nombre_especialidad}'",
            "Creado"
        );
        return response()->json($especialidad, 201);
    }

    // Actualizar especialidad existente
    public function update(Request $request, $id)
    {
        $especialidad = EspecialidadMadre::find($id);

        if (!$especialidad) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }

        $request->validate([
            'nombre_especialidad' => 'sometimes|required|string|max:100',
            'id_ciclo' => 'sometimes|required|exists:ciclo_academico,id',
        ]);

        $especialidad->update($request->all());
        $this->registrarActividad(
            "Actualizó la especialidad '{$especialidad->nombre_especialidad}'",
            "Actualizado"
        );

        return response()->json($especialidad);
    }

    // Eliminar especialidad
    public function destroy($id)
    {
        $especialidad = EspecialidadMadre::find($id);

        if (!$especialidad) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }

        $especialidad->is_deleted = 1;
        $especialidad->save();
        // ACTIVIDAD
        $this->registrarActividad(
            "Eliminó la especialidad '{$especialidad->nombre_especialidad}'",
            "Eliminado"
        );
        return response()->json(['message' => 'Especialidad eliminada'], 204);
    }


    public function getEspecialidadesPorCiclo($idCiclo)
    {
        // $ciclo = CicloAcademico::with('especialidades')->find($idCiclo);

        // if (!$ciclo) {
        //     return response()->json(['mensaje' => 'Ciclo no encontrado'], 404);
        // }

        // return response()->json([
        //     'ciclo' => $ciclo->nombre_ciclo,
        //     'especialidades' => $ciclo->especialidades
        // ]);

        $ciclo = CicloAcademico::find($idCiclo);

        if (!$ciclo) {
            return response()->json(['mensaje' => 'Ciclo no encontrado'], 404);
        }

        // CORRECCIÓN: Filtrar solo especialidades activas (is_deleted = 0)
        $especialidades = DB::table('especialidad_madre')
            ->where('id_ciclo', $idCiclo)
            ->where('is_deleted', 0)
            ->select('id', 'nombre_especialidad')
            ->orderBy('nombre_especialidad')
            ->get();

        return response()->json([
            'ciclo' => $ciclo->nombre_ciclo,
            'id_ciclo' => $ciclo->id,
            'especialidades' => $especialidades
        ]);
    }

    public function getEspecialidades($periodoId)
    {
        return DB::table('grupo')
            ->join('especialidad_programa', 'especialidad_programa.id', '=', 'grupo.id_especialidad')
            ->join('especialidad_madre', 'especialidad_madre.id', '=', 'especialidad_programa.id_especialidad')
            ->where('grupo.id_periodo', $periodoId)
            ->select(
                'especialidad_madre.id as id_especialidad',
                'especialidad_madre.nombre_especialidad'
            )
            ->distinct()
            ->get();
    }
}

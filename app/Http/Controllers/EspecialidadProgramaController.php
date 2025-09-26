<?php

namespace App\Http\Controllers;

use App\Models\ProgramaEstudio;
use App\Models\EspecialidadPrograma;
use App\Models\Periodo;
use Illuminate\Http\Request;


class EspecialidadProgramaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = EspecialidadPrograma::with(['especialidadMadre', 'programaEstudio'])->get();
        return response()->json($data);
    }

    // Crear uno nuevo
    public function store(Request $request)
    {

        $request->validate([
            'id_especialidad' => 'required|exists:especialidad_madre,id',
            'id_programa' => 'required|exists:programa_estudio,id',
            'nro_modulos' => 'required|integer|min:0'
        ]);

        $nuevo = EspecialidadPrograma::create($request->all());
        return response()->json($nuevo, 201);
    }

    // Mostrar uno específico
    public function show($id)
    {
        $programa = ProgramaEstudio::with([
            'ciclo:id,nombre_ciclo',
            'especialidadPrograma' => function ($q) {
                $q->orderBy('created_at', 'asc'); // 👈 ordena las especialidades por fecha de creación
            },
            'especialidadPrograma.especialidadMadre:id,nombre_especialidad'
        ])->find($id);

        if (!$programa) {
            return response()->json(['message' => 'Programa no encontrado'], 404);
        }

        if ($programa->especialidadPrograma->isEmpty()) {
            return response()->json([
                'ciclo' => $programa->ciclo,
                'message' => 'No se encontraron especialidades asociadas a este programa'
            ]);
        }

        return response()->json([
            'ciclo' => $programa->ciclo,
            'especialidad_programas' => $programa->especialidadPrograma->map(function ($item) {
                return [
                    'id' => $item->id,
                    'id_especialidad' => $item->id_especialidad,
                    'id_programa' => $item->id_programa,
                    'nro_modulos' => $item->nro_modulos,
                    'especialidad_madre' => $item->especialidadMadre,
                ];
            })
        ]);
    }


    // Actualizar
    public function update(Request $request, $id)
    {
        $registro = EspecialidadPrograma::find($id);

        if (!$registro) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        // $validator = Validator::make($request->all(), [
        //     'id_especialidad' => 'sometimes|exists:especialidad_madre,id',
        //     'id_programa' => 'sometimes|exists:programa_estudio,id',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors()], 422);
        // }

        $request->validate([
            'id_especialidad' => 'sometimes|exists:especialidad_madre,id',
            'id_programa' => 'sometimes|exists:programa_estudio,id',
            'nro_modulos' => 'sometimes|integer|min:0'
        ]);

        $registro->update($request->all());
        return response()->json($registro);
    }

    // Eliminar
    public function destroy($id)
    {
        $registro = EspecialidadPrograma::find($id);

        if (!$registro) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        $registro->delete();
        return response()->json(['message' => 'Eliminado correctamente'], 204);
    }

    public function getRelacionadosPorEspecialidadPrograma($id)
    {
        $especialidadesPrograma = EspecialidadPrograma::with([
            'programaEstudio',
            'modulo',
        ])->where('id_especialidad', $id)->get();

        return response()->json([
            'relaciones' => $especialidadesPrograma,
        ]);
    }
}

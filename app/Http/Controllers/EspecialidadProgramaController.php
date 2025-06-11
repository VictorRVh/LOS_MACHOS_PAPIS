<?php

namespace App\Http\Controllers;

use App\Models\EspecialidadPrograma;
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
            'id_especialidad' => 'sometimes|exists:especialidad_madre,id',
            'id_programa' => 'sometimes|exists:programa_estudio,id',
        ]);

        $nuevo = EspecialidadPrograma::create($request->all());
        return response()->json($nuevo, 201);
    }

    // Mostrar uno específico
    public function show($id)
    {
        $registro = EspecialidadPrograma::with(['especialidadMadre', 'programaEstudio'])->find($id);

        if (!$registro) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        return response()->json($registro);
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
        return response()->json(['message' => 'Eliminado correctamente']);
    }
}

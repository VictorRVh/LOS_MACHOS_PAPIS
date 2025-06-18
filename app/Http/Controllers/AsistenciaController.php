<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use Illuminate\Http\Request;

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
}

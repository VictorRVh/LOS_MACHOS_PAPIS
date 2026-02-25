<?php

namespace App\Http\Controllers;

use App\Models\Egresados;
use Illuminate\Http\Request;

class EgresadosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $egresados = Egresados::with(['estudiante', 'especialidad'])->get();
        return response()->json($egresados);
    }

    // GET /api/egresados/{id}
    public function show($id)
    {
        $egresado = Egresados::with(['estudiante', 'especialidad'])->find($id);

        if (!$egresado) {
            return response()->json(['message' => 'Egresado no encontrado'], 404);
        }

        return response()->json($egresado);
    }

    // POST /api/egresados
    public function store(Request $request)
    {
        $request->validate([
            'turno'        => 'nullable|string|max:2',
            'id_estudiante' => 'required|uuid|exists:estudiante,id',
            'id_especialidad'     => 'required|uuid|exists:especialidad_programa,id',
        ]);

        $existe = Egresados::where('id_estudiante', $request->id_estudiante)
            ->where('id_especialidad', $request->id_especialidad)
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'El estudiante ya es egresado en esta especialidad'
            ], 409);
        }

        $egresado = Egresados::create([
            'id_estudiante' => $request->id_estudiante,
            'id_especialidad' => $request->id_especialidad,
        ]);

        return response()->json([
            'message' => 'Egresado registrado correctamente',
            'data' => $egresado
        ], 201);
    }

    // PATCH /api/egresados/{id}
    public function update(Request $request, $id)
    {
        $egresado = Egresados::find($id);

        if (!$egresado) {
            return response()->json(['message' => 'Egresado no encontrado'], 404);
        }

        $request->validate([
            'turno'        => 'sometimes|string|max:2',
            'id_estudiante' => 'sometimes|uuid|exists:estudiante,id',
            'id_especialidad'     => 'sometimes|uuid|exists:especialidad_programa,id',
        ]);

        $egresado->update($request->all());

        return response()->json([
            'message' => 'Egresado actualizado correctamente',
            'data' => $egresado
        ]);
    }

    // DELETE /api/egresados/{id}
    public function destroy($id)
    {
        $egresado = Egresados::find($id);

        if (!$egresado) {
            return response()->json(['message' => 'Egresado no encontrado'], 404);
        }

        $egresado->delete();

        return response()->json(['message' => 'Egresado eliminado correctamente']);
    }
}

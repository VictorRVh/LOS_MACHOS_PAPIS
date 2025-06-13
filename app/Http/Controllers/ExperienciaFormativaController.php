<?php

namespace App\Http\Controllers;

use App\Models\ExperienciaFormativa;
use Illuminate\Http\Request;

class ExperienciaFormativaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $experiencias = ExperienciaFormativa::with('grupo')->get();
        return response()->json($experiencias);
    }

    // GET /api/experiencia_formativa/{id}
    public function show($id)
    {
        $experiencia = ExperienciaFormativa::with('grupo')->find($id);

        if (!$experiencia) {
            return response()->json(['message' => 'Experiencia formativa no encontrada'], 404);
        }

        return response()->json($experiencia);
    }

    // POST /api/experiencia_formativa
    public function store(Request $request)
    {
        $request->validate([
            'nombre_experiencia' => 'required|string|max:255',
            'fecha_inicio'       => 'required|date',
            'fecha_fin'          => 'required|date|after_or_equal:fecha_inicio',
            'horas'              => 'required|integer|min:1',
            'id_grupo'           => 'required|uuid|exists:grupo,id',
            'status'             => 'required|integer|in:0,1,2,3',
        ]);

        $experiencia = ExperienciaFormativa::create($request->all());

        return response()->json(['message' => 'Experiencia creada con éxito', 'data' => $experiencia], 201);
    }

    // PATCH /api/experiencia_formativa/{id}
    public function update(Request $request, $id)
    {
        $experiencia = ExperienciaFormativa::find($id);

        if (!$experiencia) {
            return response()->json(['message' => 'Experiencia formativa no encontrada'], 404);
        }

        $request->validate([
            'nombre_experiencia' => 'sometimes|string|max:255',
            'fecha_inicio'       => 'sometimes|date',
            'fecha_fin'          => 'sometimes|date|after_or_equal:fecha_inicio',
            'horas'              => 'sometimes|integer|min:1',
            'id_grupo'           => 'sometimes|uuid|exists:grupo,id',
            'status'             => 'sometimes|integer|in:0,1,2,3',
        ]);

        $experiencia->update($request->all());

        return response()->json(['message' => 'Experiencia actualizada con éxito', 'data' => $experiencia]);
    }

    // DELETE /api/experiencia_formativa/{id}
    public function destroy($id)
    {
        $experiencia = ExperienciaFormativa::find($id);

        if (!$experiencia) {
            return response()->json(['message' => 'Experiencia formativa no encontrada'], 404);
        }

        $experiencia->delete();

        return response()->json(['message' => 'Experiencia eliminada con éxito']);
    }
}

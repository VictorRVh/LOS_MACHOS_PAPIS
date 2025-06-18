<?php

namespace App\Http\Controllers;

use App\Models\NotaExperienciaFormativa;
use Illuminate\Http\Request;

class NotaExperienciaFormativaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notas = NotaExperienciaFormativa::with(['grupo', 'estudiante', 'experienciaFormativa'])->get();
        return response()->json($notas);
    }

    // GET /api/nota_experiencia_formativa/{id}
    public function show($id)
    {
        $nota = NotaExperienciaFormativa::with(['grupo', 'estudiante', 'experienciaFormativa'])->find($id);

        if (!$nota) {
            return response()->json(['message' => 'Nota no encontrada'], 404);
        }

        return response()->json($nota);
    }

    // POST /api/nota_experiencia_formativa
    public function store(Request $request)
    {
        $request->validate([
            'id_experiencia'  => 'required|uuid|exists:experiencia_formativa,id',
            'lugar'           => 'required|string|max:255',
            'documento'       => 'required|string|max:255',
            'id_estudiante'   => 'required|uuid|exists:estudiante,id',
            'id_grupo'        => 'required|uuid|exists:grupo,id',
            'status'          => 'required|integer|in:0,1,2,3',
        ]);

        $nota = NotaExperienciaFormativa::create($request->all());

        return response()->json(['message' => 'Nota creada con éxito', 'data' => $nota], 201);
    }

    // PATCH /api/nota_experiencia_formativa/{id}
    public function update(Request $request, $id)
    {
        $nota = NotaExperienciaFormativa::find($id);

        if (!$nota) {
            return response()->json(['message' => 'Nota no encontrada'], 404);
        }

        $request->validate([
            'id_experiencia'  => 'sometimes|uuid|exists:experiencia_formativa,id',
            'lugar'           => 'sometimes|string|max:255',
            'documento'       => 'sometimes|string|max:255',
            'id_estudiante'   => 'sometimes|uuid|exists:estudiante,id',
            'id_grupo'        => 'sometimes|uuid|exists:grupo,id',
            'status'          => 'sometimes|integer|in:0,1,2,3',
        ]);

        $nota->update($request->all());

        return response()->json(['message' => 'Nota actualizada con éxito', 'data' => $nota]);
    }

    // DELETE /api/nota_experiencia_formativa/{id}
    public function destroy($id)
    {
        $nota = NotaExperienciaFormativa::find($id);

        if (!$nota) {
            return response()->json(['message' => 'Nota no encontrada'], 404);
        }

        $nota->delete();

        return response()->json(['message' => 'Nota eliminada correctamente']);
    }
}

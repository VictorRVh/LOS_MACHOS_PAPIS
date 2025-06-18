<?php

namespace App\Http\Controllers;

use App\Models\CicloAcademico;
use Illuminate\Http\Request;

class CicloAcademicoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ciclos = CicloAcademico::with('programaEstudio')->get();
        return response()->json($ciclos, 200);
    }

    /**
     * Guarda un nuevo ciclo académico.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_ciclo' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $ciclo = CicloAcademico::create($request->all());

        return response()->json($ciclo, 201);
    }

    /**
     * Muestra un ciclo académico específico con sus programas.
     */
    public function show(string $id)
    {
        $ciclo = CicloAcademico::with('programaEstudio')->findOrFail($id);
        return response()->json($ciclo, 200);
    }

    /**
     * Actualiza un ciclo académico.
     */
    public function update(Request $request, string $id)
    {
        $ciclo = CicloAcademico::findOrFail($id);

        $request->validate([
            'nombre_ciclo' => 'sometimes|required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $ciclo->update($request->all());

        return response()->json($ciclo, 200);
    }

    /**
     * Elimina un ciclo académico.
     */
    public function destroy(string $id)
    {
        $ciclo = CicloAcademico::findOrFail($id);
        $ciclo->delete();

        return response()->json(null, 204);
    }
}

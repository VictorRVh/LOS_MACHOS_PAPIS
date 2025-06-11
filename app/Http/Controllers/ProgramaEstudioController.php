<?php

namespace App\Http\Controllers;

use App\Models\ProgramaEstudio;
use Illuminate\Http\Request;

class ProgramaEstudioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $programas = ProgramaEstudio::with('ciclo')->get();
        return response()->json($programas);
    }

    // Mostrar uno por ID
    public function show($id)
    {
        $programa = ProgramaEstudio::with('ciclo')->find($id);

        if (!$programa) {
            return response()->json(['message' => 'Programa de estudio no encontrado'], 404);
        }

        return response()->json($programa);
    }

    // Crear nuevo programa
    public function store(Request $request)
    {
        $request->validate([
            'id_ciclo'    => 'required|exists:ciclo_academico,id',
            'año'         => 'required|integer|min:2000|max:2100',
            'numero_rd'   => 'required|string|max:50',
            'status'      => 'required|integer|in:0,1,2,3',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $programa = ProgramaEstudio::create($request->all());

        return response()->json($programa, 201);
    }

    // Actualizar un programa existente
    public function update(Request $request, $id)
    {
        $programa = ProgramaEstudio::find($id);

        if (!$programa) {
            return response()->json(['message' => 'Programa de estudio no encontrado'], 404);
        }

        $request->validate([
            'id_ciclo'    => 'sometimes|required|exists:ciclo_academico,id',
            'año'         => 'sometimes|required|integer|min:2000|max:2100',
            'numero_rd'   => 'sometimes|required|string|max:50',
            'status'      => 'sometimes|required|integer|in:0,1,2,3',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $programa->update($request->all());

        return response()->json($programa);
    }

    // Eliminar un programa
    public function destroy($id)
    {
        $programa = ProgramaEstudio::find($id);

        if (!$programa) {
            return response()->json(['message' => 'Programa de estudio no encontrado'], 404);
        }

        $programa->delete();

        return response()->json(['message' => 'Programa eliminado correctamente']);
    }
}

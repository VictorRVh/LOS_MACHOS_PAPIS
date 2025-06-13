<?php

namespace App\Http\Controllers;

use App\Models\NotaCapacidadTerminal;
use Illuminate\Http\Request;

class NotaCapacidadTerminalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notas = NotaCapacidadTerminal::with(['grupo', 'capacidadTerminal', 'estudiante'])->get();
        return response()->json($notas);
    }

    // GET /api/nota-capacidad-terminal/{id}
    public function show($id)
    {
        $nota = NotaCapacidadTerminal::with(['grupo', 'capacidadTerminal', 'estudiante'])->find($id);

        if (!$nota) {
            return response()->json(['message' => 'Nota no encontrada'], 404);
        }

        return response()->json($nota);
    }

    // POST /api/nota-capacidad-terminal
    public function store(Request $request)
    {
        $request->validate([
            // 'nota_capacidad' => 'sometimes|numeric|min:0|max:20',
            'nota_capacidad' => 'required|string',
            'id_grupo' => 'required|exists:grupo,id',
            'id_capacidad' => 'required|exists:capacidad_terminal,id',
            'id_estudiante' => 'required|exists:estudiante,id',
        ]);

        $nota = NotaCapacidadTerminal::create($request->all());

        return response()->json($nota, 201);
    }

    // PUT/PATCH /api/nota-capacidad-terminal/{id}
    public function update(Request $request, $id)
    {
        $nota = NotaCapacidadTerminal::find($id);

        if (!$nota) {
            return response()->json(['message' => 'Nota no encontrada'], 404);
        }

        $request->validate([
            'nota_capacidad' => 'sometimes|numeric|min:0|max:20',
            'id_grupo' => 'sometimes|exists:grupo,id',
            'id_capacidad' => 'sometimes|exists:capacidad_terminal,id',
            'id_estudiante' => 'sometimes|exists:estudiante,id',
        ]);

        $nota->update($request->all());

        return response()->json($nota);
    }

    // DELETE /api/nota-capacidad-terminal/{id}
    public function destroy($id)
    {
        $nota = NotaCapacidadTerminal::find($id);

        if (!$nota) {
            return response()->json(['message' => 'Nota no encontrada'], 404);
        }

        $nota->delete();

        return response()->json(['message' => 'Nota eliminada correctamente']);
    }
}

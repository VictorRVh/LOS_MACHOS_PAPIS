<?php

namespace App\Http\Controllers;

use App\Models\CapacidadTerminal;
use Illuminate\Http\Request;

class CapacidadTerminalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $capacidades = CapacidadTerminal::with('grupo')->get();
        return response()->json($capacidades);
    }

    // GET /api/capacidad-terminal/{id}
    public function show($id)
    {
        $capacidad = CapacidadTerminal::with('grupo')->find($id);

        if (!$capacidad) {
            return response()->json(['message' => 'Capacidad no encontrada'], 404);
        }

        return response()->json($capacidad);
    }

    // POST /api/capacidad-terminal
    public function store(Request $request)
    {
        $request->validate([
            'nombre_capacidad' => 'sometimes|string|max:255',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'sometimes|date|after_or_equal:fecha_inicio',
            'id_grupo' => 'sometimes|exists:grupo,id',
            'status' => 'sometimes|in:0,1,2,3',
        ]);

        $capacidad = CapacidadTerminal::create($request->all());

        return response()->json($capacidad, 201);
    }

    // PUT/PATCH /api/capacidad-terminal/{id}
    public function update(Request $request, $id)
    {
        $capacidad = CapacidadTerminal::find($id);

        if (!$capacidad) {
            return response()->json(['message' => 'Capacidad no encontrada'], 404);
        }

        $request->validate([
            'nombre_capacidad' => 'sometimes|string|max:255',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'sometimes|date|after_or_equal:fecha_inicio',
            'id_grupo' => 'sometimes|exists:grupo,id',
            'status' => 'sometimes|in:0,1,2,3',
        ]);

        $capacidad->update($request->all());

        return response()->json($capacidad);
    }

    // DELETE /api/capacidad-terminal/{id}
    public function destroy($id)
    {
        $capacidad = CapacidadTerminal::find($id);

        if (!$capacidad) {
            return response()->json(['message' => 'Capacidad no encontrada'], 404);
        }

        $capacidad->delete();

        return response()->json(['message' => 'Capacidad eliminada correctamente']);
    }
}

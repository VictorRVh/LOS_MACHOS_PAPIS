<?php

namespace App\Http\Controllers;

use App\Models\Comisiones;
use Illuminate\Http\Request;

class ComisionesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comisiones = Comisiones::all(); // Ya no con 'usuario'
        return response()->json($comisiones);
    }

    // GET /api/comisiones/{id}
    public function show($id)
    {
        $comision = Comisiones::find($id);

        if (!$comision) {
            return response()->json(['message' => 'Comisión no encontrada'], 404);
        }

        return response()->json($comision);
    }

    // POST /api/comisiones
    public function store(Request $request)
    {
        $request->validate([
            'titulo'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        $comision = Comisiones::create($request->only(['titulo', 'descripcion']));

        return response()->json([
            'message' => 'Comisión creada correctamente',
            'data'    => $comision
        ], 201);
    }

    // PATCH /api/comisiones/{id}
    public function update(Request $request, $id)
    {
        $comision = Comisiones::find($id);

        if (!$comision) {
            return response()->json(['message' => 'Comisión no encontrada'], 404);
        }

        $request->validate([
            'titulo'      => 'sometimes|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        $comision->update($request->only(['titulo', 'descripcion']));

        return response()->json([
            'message' => 'Comisión actualizada correctamente',
            'data'    => $comision
        ]);
    }

    // DELETE /api/comisiones/{id}
    public function destroy($id)
    {
        $comision = Comisiones::find($id);

        if (!$comision) {
            return response()->json(['message' => 'Comisión no encontrada'], 404);
        }

        $comision->delete();

        return response()->json(['message' => 'Comisión eliminada correctamente']);
    }
}

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
        $comisiones = Comisiones::with('usuario')->get();
        return response()->json($comisiones);
    }

    // GET /api/comisiones/{id}
    public function show($id)
    {
        $comision = Comisiones::with('usuario')->find($id);

        if (!$comision) {
            return response()->json(['message' => 'Comisión no encontrada'], 404);
        }

        return response()->json($comision);
    }

    // POST /api/comisiones
    public function store(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|exists:users,id',
            'titulo'     => 'required|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        $comision = Comisiones::create($request->all());

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
            'id_usuario' => 'sometimes|exists:users,id',
            'titulo'     => 'sometimes|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        $comision->update($request->all());

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

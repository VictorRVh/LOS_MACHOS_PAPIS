<?php

namespace App\Http\Controllers;

use App\Models\PersonalAdministrativo;
use Illuminate\Http\Request;

class PersonalAdministrativoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $personal = PersonalAdministrativo::with('usuario')->get();
        return response()->json($personal);
    }

    // GET /api/personal-administrativo/{id}
    public function show($id)
    {
        $item = PersonalAdministrativo::with('usuario')->find($id);

        if (!$item) {
            return response()->json(['message' => 'Personal no encontrado'], 404);
        }

        return response()->json($item);
    }

    // POST /api/personal-administrativo
    public function store(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|exists:users,id',
            'turno'      => 'required|string|max:2',
            'local'      => 'required|string|max:100',
        ]);

        $item = PersonalAdministrativo::create($request->all());

        return response()->json([
            'message' => 'Personal administrativo creado correctamente',
            'data'    => $item
        ], 201);
    }

    // PATCH /api/personal-administrativo/{id}
    public function update(Request $request, $id)
    {
        $item = PersonalAdministrativo::find($id);

        if (!$item) {
            return response()->json(['message' => 'Personal no encontrado'], 404);
        }

        $request->validate([
            'id_usuario' => 'sometimes|exists:users,id',
            'turno'      => 'sometimes|string|max:2',
            'local'      => 'sometimes|string|max:100',
        ]);

        $item->update($request->all());

        return response()->json([
            'message' => 'Personal administrativo actualizado correctamente',
            'data'    => $item
        ]);
    }

    // DELETE /api/personal-administrativo/{id}
    public function destroy($id)
    {
        $item = PersonalAdministrativo::find($id);

        if (!$item) {
            return response()->json(['message' => 'Personal no encontrado'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Personal administrativo eliminado correctamente']);
    }
}

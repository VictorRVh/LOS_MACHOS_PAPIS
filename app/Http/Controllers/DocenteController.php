<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use Illuminate\Http\Request;

class DocenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $docentes = Docente::with('user')->get();
        return response()->json($docentes);
    }

    // Crear un nuevo docente
    public function store(Request $request)
    {
        $request->validate([
            'codigo_modular'     => 'required|string|max:20',
            'especialidad'       => 'required|string|max:100',
            'condicion'          => 'required|string|max:50',
            'escala_magisterial' => 'nullable|string|max:50',
            'rd_nombramiento'    => 'nullable|string|max:50',
            'user_id'            => 'required|exists:users,id',
        ]);

        $docente = Docente::create($request->all());
        return response()->json($docente, 201);
    }

    // Mostrar un docente específico
    public function show($id)
    {
        $docente = Docente::with('user')->find($id);

        if (!$docente) {
            return response()->json(['message' => 'Docente no encontrado'], 404);
        }

        return response()->json($docente);
    }

    // Actualizar un docente
    public function update(Request $request, $id)
    {
        $docente = Docente::find($id);

        if (!$docente) {
            return response()->json(['message' => 'Docente no encontrado'], 404);
        }

        $request->validate([
            'codigo_modular'     => 'sometimes|string|max:20',
            'especialidad'       => 'sometimes|string|max:100',
            'condicion'          => 'sometimes|string|max:50',
            'escala_magisterial' => 'nullable|string|max:50',
            'rd_nombramiento'    => 'nullable|string|max:50',
            'user_id'            => 'sometimes|exists:users,id',
        ]);

        $docente->update($request->all());
        return response()->json($docente);
    }

    // Eliminar un docente
    public function destroy($id)
    {
        $docente = Docente::find($id);

        if (!$docente) {
            return response()->json(['message' => 'Docente no encontrado'], 404);
        }

        $docente->delete();
        return response()->json(['message' => 'Docente eliminado correctamente']);
    }
}

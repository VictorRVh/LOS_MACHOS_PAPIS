<?php

namespace App\Http\Controllers;

use App\Models\Convenios;
use Illuminate\Http\Request;

class ConveniosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Mostrar todos los convenios
    public function index()
    {
        return response()->json(Convenios::all(), 200);
    }

    // Crear un nuevo convenio
    public function store(Request $request)
    {
        $request->validate([
            'nombre_institucion' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
        ]);

        $convenio = Convenios::create($request->all());
        return response()->json($convenio, 201);
    }

    // Mostrar un convenio específico
    public function show($id)
    {
        $convenio = Convenios::find($id);
        if (!$convenio) {
            return response()->json(['message' => 'Convenio no encontrado'], 404);
        }

        return response()->json($convenio, 200);
    }

    // Actualizar un convenio
    public function update(Request $request, $id)
    {
        $convenio = Convenios::find($id);
        if (!$convenio) {
            return response()->json(['message' => 'Convenio no encontrado'], 404);
        }

        $request->validate([
            'nombre_institucion' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
        ]);

        $convenio->update($request->all());
        return response()->json($convenio, 200);
    }

    // Eliminar un convenio
    public function destroy($id)
    {
        $convenio = Convenios::find($id);
        if (!$convenio) {
            return response()->json(['message' => 'Convenio no encontrado'], 404);
        }

        $convenio->delete();
        return response()->json(['message' => 'Convenio eliminado'], 200);
    }
}

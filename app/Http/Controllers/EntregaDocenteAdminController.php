<?php

namespace App\Http\Controllers;

use App\Models\EntregaDocenteAdmin;
use Illuminate\Http\Request;

class EntregaDocenteAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $entregas = EntregaDocenteAdmin::all();
        return response()->json($entregas);
    }

    // Crear uno nuevo
    public function store(Request $request)
    {
        $request->validate([
            'tipo_entrega' => 'required|string|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'status' => 'required|integer|in:0,1,2,3',
        ]);

        $entrega = EntregaDocenteAdmin::create($request->all());

        return response()->json($entrega, 201);
    }

    // Mostrar uno por ID
    public function show($id)
    {
        $entrega = EntregaDocenteAdmin::findOrFail($id);
        return response()->json($entrega);
    }

    // Actualizar
    public function update(Request $request, $id)
    {
        $entrega = EntregaDocenteAdmin::findOrFail($id);

        $request->validate([
            'tipo_entrega' => 'required|string|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'status' => 'required|integer|in:0,1,2,3',
        ]);

        $entrega->update($request->all());

        return response()->json($entrega);
    }

    // Eliminar
    public function destroy($id)
    {
        $entrega = EntregaDocenteAdmin::findOrFail($id);
        $entrega->delete();

        return response()->json(null, 204);
    }
}

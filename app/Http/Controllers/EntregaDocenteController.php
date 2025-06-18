<?php

namespace App\Http\Controllers;

use App\Models\EntregaDocente;
use Illuminate\Http\Request;

class EntregaDocenteController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function index()
    {
        $entregas = EntregaDocente::with(['grupo', 'entregaDocenteAdmin', 'entregaRealizada', 'sesiones'])->get();
        return response()->json($entregas);
    }

    // GET /api/entrega_docente/{id}
    public function show($id)
    {
        $entrega = EntregaDocente::with(['grupo', 'entregaDocenteAdmin', 'entregaRealizada', 'sesiones'])->find($id);

        if (!$entrega) {
            return response()->json(['message' => 'Entrega no encontrada'], 404);
        }

        return response()->json($entrega);
    }

    // POST /api/entrega_docente
    public function store(Request $request)
    {
        $request->validate([
            'id_grupo'        => 'required|uuid|exists:grupo,id',
            'fecha_inicio'    => 'required|date',
            'fecha_fin'       => 'required|date|after_or_equal:fecha_inicio',
            'estado'          => 'required|string|max:100',
            'id_admin'        => 'required|uuid|exists:entrega_docente_admin,id',
            'documento_admin' => 'required|string|max:255',
        ]);

        $entrega = EntregaDocente::create($request->all());

        return response()->json(['message' => 'Entrega creada con éxito', 'data' => $entrega], 201);
    }

    // PATCH /api/entrega_docente/{id}
    public function update(Request $request, $id)
    {
        $entrega = EntregaDocente::find($id);

        if (!$entrega) {
            return response()->json(['message' => 'Entrega no encontrada'], 404);
        }

        $request->validate([
            'id_grupo'        => 'sometimes|uuid|exists:grupo,id',
            'fecha_inicio'    => 'sometimes|date',
            'fecha_fin'       => 'sometimes|date|after_or_equal:fecha_inicio',
            'estado'          => 'sometimes|string|max:100',
            'id_admin'        => 'sometimes|uuid|exists:entrega_docente_admin,id',
            'documento_admin' => 'sometimes|string|max:255',
        ]);

        $entrega->update($request->all());

        return response()->json(['message' => 'Entrega actualizada con éxito', 'data' => $entrega]);
    }

    // DELETE /api/entrega_docente/{id}
    public function destroy($id)
    {
        $entrega = EntregaDocente::find($id);

        if (!$entrega) {
            return response()->json(['message' => 'Entrega no encontrada'], 404);
        }

        $entrega->delete();

        return response()->json(['message' => 'Entrega eliminada correctamente']);
    }
}

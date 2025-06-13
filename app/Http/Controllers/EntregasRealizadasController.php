<?php

namespace App\Http\Controllers;

use App\Models\EntregasRealizadas;
use Illuminate\Http\Request;

class EntregasRealizadasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $entregas = EntregasRealizadas::with(['entregaDocente', 'docente'])->get();
        return response()->json($entregas);
    }

    // GET /api/entregas-realizadas/{id}
    public function show($id)
    {
        $entrega = EntregasRealizadas::with(['entregaDocente', 'docente'])->find($id);

        if (!$entrega) {
            return response()->json(['message' => 'Entrega no encontrada'], 404);
        }

        return response()->json($entrega);
    }

    // POST /api/entregas-realizadas
    public function store(Request $request)
    {
        $request->validate([
            'id_entrega'    => 'required|uuid|exists:entrega_docente,id',
            'id_docente'    => 'required|uuid|exists:docente,id',
            'archivo'       => 'nullable|string|max:255',
            'fecha_entrega' => 'required|date',
            'observacion'   => 'nullable|string|max:255',
        ]);

        $entrega = EntregasRealizadas::create($request->all());

        return response()->json([
            'message' => 'Entrega registrada correctamente',
            'data' => $entrega
        ], 201);
    }

    // PATCH /api/entregas-realizadas/{id}
    public function update(Request $request, $id)
    {
        $entrega = EntregasRealizadas::find($id);

        if (!$entrega) {
            return response()->json(['message' => 'Entrega no encontrada'], 404);
        }

        $request->validate([
            'id_entrega'    => 'sometimes|uuid|exists:entrega_docente,id',
            'id_docente'    => 'sometimes|uuid|exists:docente,dni',
            'archivo'       => 'nullable|string|max:255',
            'fecha_entrega' => 'sometimes|date',
            'observacion'   => 'nullable|string|max:255',
        ]);

        $entrega->update($request->all());

        return response()->json([
            'message' => 'Entrega actualizada correctamente',
            'data' => $entrega
        ]);
    }

    // DELETE /api/entregas-realizadas/{id}
    public function destroy($id)
    {
        $entrega = EntregasRealizadas::find($id);

        if (!$entrega) {
            return response()->json(['message' => 'Entrega no encontrada'], 404);
        }

        $entrega->delete();

        return response()->json(['message' => 'Entrega eliminada correctamente']);
    }
}

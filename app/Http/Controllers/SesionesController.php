<?php

namespace App\Http\Controllers;

use App\Models\Sesiones;
use Illuminate\Http\Request;

class SesionesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sesiones = Sesiones::with(['calendarioAdmin', 'capacidadTerminal', 'entregaDocente'])->get();
        return response()->json($sesiones);
    }

    public function show($id)
    {
        $sesion = Sesiones::with(['calendarioAdmin', 'capacidadTerminal', 'entregaDocente'])->find($id);

        if (!$sesion) {
            return response()->json(['message' => 'Sesión no encontrada'], 404);
        }

        return response()->json($sesion);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_sesion'   => 'required|string|max:255',
            'fecha_inicio'    => 'required|date',
            'fecha_fin'       => 'required|date|after_or_equal:fecha_inicio',
            'descripcion'     => 'nullable|string',
            'archivo_sesion'  => 'nullable|string',
            'id_calendario'   => 'required|uuid|exists:calendario_admin,id',
            'id_capacidad'    => 'required|uuid|exists:capacidad_terminal,id',
            'id_entrega'      => 'required|uuid|exists:entrega_docente,id',
            'status'          => 'required|integer|in:0,1,2,3'
        ]);

        $sesion = Sesiones::create($request->all());

        return response()->json(['message' => 'Sesión creada correctamente', 'data' => $sesion], 201);
    }

    public function update(Request $request, $id)
    {
        $sesion = Sesiones::find($id);

        if (!$sesion) {
            return response()->json(['message' => 'Sesión no encontrada'], 404);
        }

        $request->validate([
            'nombre_sesion'   => 'sometimes|string|max:255',
            'fecha_inicio'    => 'sometimes|date',
            'fecha_fin'       => 'sometimes|date|after_or_equal:fecha_inicio',
            'descripcion'     => 'nullable|string',
            'archivo_sesion'  => 'nullable|string',
            'id_calendario'   => 'sometimes|uuid|exists:calendario_admin,id',
            'id_capacidad'    => 'sometimes|uuid|exists:capacidad_terminal,id',
            'id_entrega'      => 'sometimes|uuid|exists:entrega_docente,id',
            'status'          => 'sometimes|integer|in:0,1,2,3'
        ]);

        $sesion->update($request->all());

        return response()->json(['message' => 'Sesión actualizada correctamente', 'data' => $sesion]);
    }

    public function destroy($id)
    {
        $sesion = Sesiones::find($id);

        if (!$sesion) {
            return response()->json(['message' => 'Sesión no encontrada'], 404);
        }

        $sesion->delete();

        return response()->json(['message' => 'Sesión eliminada correctamente']);
    }
}

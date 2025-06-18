<?php

namespace App\Http\Controllers;

use App\Models\Periodo;
use Illuminate\Http\Request;

class PeriodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $periodos = Periodo::all()->map(function ($periodo) {
            return [
                'id'             => $periodo->id,
                'nombre_periodo' => $periodo->nombre_periodo,
                'status'         => $periodo->status,
                'status_texto'   => $periodo->status_texto,
            ];
        });

        return response()->json($periodos);
    }

    // Crear un nuevo periodo
    public function store(Request $request)
    {
        $request->validate([
            'nombre_periodo' => 'required|string|max:100',
            'status'         => 'required|in:0,1,2,3',
        ]);

        $periodo = Periodo::create($request->all());
        return response()->json($periodo, 201);
    }

    // Mostrar un periodo específico
    public function show($id)
    {
        $periodo = Periodo::find($id);

        if (!$periodo) {
            return response()->json(['message' => 'Periodo no encontrado'], 404);
        }

        return response()->json([
            'id'             => $periodo->id,
            'nombre_periodo' => $periodo->nombre_periodo,
            'status'         => $periodo->status,
            'status_texto'   => $periodo->status_texto,
        ]);
    }

    // Actualizar un periodo
    public function update(Request $request, $id)
    {
        $periodo = Periodo::find($id);

        if (!$periodo) {
            return response()->json(['message' => 'Periodo no encontrado'], 404);
        }

        $request->validate([
            'nombre_periodo' => 'sometimes|string|max:100',
            'status'         => 'sometimes|in:0,1,2,3',
        ]);

        $periodo->update($request->all());
        return response()->json($periodo);
    }

    // Eliminar un periodo
    public function destroy($id)
    {
        $periodo = Periodo::find($id);

        if (!$periodo) {
            return response()->json(['message' => 'Periodo no encontrado'], 404);
        }

        $periodo->delete();
        return response()->json(['message' => 'Periodo eliminado correctamente']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CapacidadTerminalCompetencia;
use Illuminate\Http\Request;

class CapacidadTerminalCompetenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CapacidadTerminalCompetencia::with('competencia');

        if ($request->filled('id_competencia')) {
            $query->where('id_competencia', $request->id_competencia);
        }

        return response()->json(
            $query->orderBy('created_at')->get()
        );
    }

    /**
     * Crear capacidad terminal
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_competencia' => ['required', 'uuid', 'exists:competencias,id'],
            'sigla' => ['nullable', 'string', 'max:20'],
            'descripcion' => ['required', 'string'],
        ]);

        $capacidad = CapacidadTerminalCompetencia::create($data);

        return response()->json([
            'message' => 'Capacidad terminal creada correctamente',
            'data' => $capacidad
        ], 201);
    }

    /**
     * Mostrar una capacidad terminal
     */
    public function show(string $id)
    {
        $capacidad = CapacidadTerminalCompetencia::with('competencia')
            ->findOrFail($id);

        return response()->json($capacidad);
    }

    /**
     * Actualizar capacidad terminal
     */
    public function update(Request $request, string $id)
    {
        $capacidad = CapacidadTerminalCompetencia::findOrFail($id);

        $data = $request->validate([
            'sigla' => ['nullable', 'string', 'max:20'],
            'descripcion' => ['required', 'string'],
        ]);

        $capacidad->update($data);

        return response()->json([
            'message' => 'Capacidad terminal actualizada correctamente',
            'data' => $capacidad
        ]);
    }

    /**
     * Eliminar capacidad terminal
     */
    public function destroy(string $id)
    {
        $capacidad = CapacidadTerminalCompetencia::findOrFail($id);
        $capacidad->delete();

        return response()->json([
            'message' => 'Capacidad terminal eliminada correctamente'
        ]);
    }
}

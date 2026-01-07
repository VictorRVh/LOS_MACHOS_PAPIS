<?php

namespace App\Http\Controllers;

use App\Models\Competencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CompetenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Competencia::with('modulo');

        if ($request->filled('id_modulo')) {
            $query->where('id_modulo', $request->id_modulo);
        }

        return response()->json(
            $query->get()
        );
    }

    public function getCompetenciasPorModulo(string $idModulo)
    {
        return DB::table('competencias')
            ->select(
                'tipo',
                DB::raw('MIN(id) as id'),
                DB::raw('MIN(nombre) as nombre'),
                DB::raw('MIN(descripcion) as descripcion')
            )
            ->where('id_modulo', $idModulo)
            ->groupBy('tipo')
            ->orderBy('tipo')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_modulo' => ['required', 'uuid', 'exists:modulos,id'],
            'tipo' => [
                'required',
                Rule::in(['1', '2']) // 1 = Técnica | 2 = Empleabilidad
            ],
            'descripcion' => ['required', 'string'],
        ]);

        $competencia = Competencia::create($data);

        return response()->json([
            'message' => 'Competencia creada correctamente',
            'data' => $competencia
        ], 201);
    }

    /**
     * Mostrar una competencia
     */
    public function show(string $id)
    {
        $competencia = Competencia::with('modulo')->findOrFail($id);

        return response()->json($competencia);
    }

    /**
     * Actualizar competencia
     */
    public function update(Request $request, string $id)
    {
        $competencia = Competencia::findOrFail($id);

        $data = $request->validate([
            'tipo' => [
                'required',
                Rule::in(['1', '2'])
            ],
            'descripcion' => ['required', 'string'],
        ]);

        $competencia->update($data);

        return response()->json([
            'message' => 'Competencia actualizada correctamente',
            'data' => $competencia
        ]);
    }

    /**
     * Eliminar competencia
     */
    public function destroy(string $id)
    {
        $competencia = Competencia::findOrFail($id);
        $competencia->delete();

        return response()->json([
            'message' => 'Competencia eliminada correctamente'
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CapacidadCompetencia;
use App\Models\CapacidadTerminal;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CapacidadCompetenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CapacidadCompetencia::with('competencia');

        if ($request->filled('id_competencia')) {
            $query->where('id_competencia', $request->id_competencia);
        }

        return response()->json(
            $query->orderBy('created_at')->get()
        );
    }

    public function getCapacidadesPorCompetencia($idGrupo)
    {
        // 1️⃣ Validar grupo
        $grupo = Grupo::find($idGrupo);

        if (!$grupo) {
            return response()->json([
                'ok' => false,
                'message' => 'Grupo no encontrado'
            ], 404);
        }

        // 2️⃣ Query PLANO correcto
        $data = DB::table('capacidades_competencias as cc')
            ->join(
                'capacidad_terminal as ct',
                'cc.id_capacidad_terminal',
                '=',
                'ct.id'
            )
            ->join(
                'competencias as c',
                'cc.id_competencia',
                '=',
                'c.id'
            )
            ->where('ct.id_grupo', $idGrupo)
            ->orderBy('ct.numero_capacidad')
            ->orderBy('cc.created_at')
            ->select(
                'cc.id',
                'cc.descripcion',              // ✔ columna real
                'ct.id as id_unidad',
                'ct.numero_capacidad as indice_unidad',
                'ct.nombre_capacidad as unidad',
                'c.nombre as competencia',      // ✔ SOLO aquí va competencia
                'c.tipo as tipo_competencia',      // ✔ SOLO aquí va competencia
                'c.id as id_competencia'      // ✔ SOLO aquí va competencia
            )
            ->get();

        return response()->json($data->values());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_competencia' => ['required', 'uuid', 'exists:competencias,id'],
            'id_capacidad_terminal' => ['required', 'uuid', 'exists:capacidad_terminal,id'],
            'sigla' => ['nullable', 'string', 'max:20'],
            'descripcion' => ['required', 'string'],
        ]);

        $capacidad = CapacidadCompetencia::create($data);

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
        $capacidad = CapacidadCompetencia::with('competencia')
            ->findOrFail($id);

        return response()->json($capacidad);
    }

    /**
     * Actualizar capacidad terminal
     */
    public function update(Request $request, string $id)
    {
        $capacidad = CapacidadCompetencia::findOrFail($id);

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
        $capacidad = CapacidadCompetencia::findOrFail($id);
        $capacidad->delete();

        return response()->json([
            'message' => 'Capacidad eliminada correctamente'
        ],204);
    }
}

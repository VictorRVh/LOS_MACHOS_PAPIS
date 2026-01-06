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

        // 2️⃣ Unidades didácticas del grupo
        $unidades = CapacidadTerminal::where('id_grupo', $idGrupo)
            ->orderByRaw('CAST(numero_capacidad AS UNSIGNED) ASC')
            ->get();

        if ($unidades->isEmpty()) {
            return response()->json([
                'ok' => true,
                'data' => []
            ]);
        }

        // 3️⃣ Capacidades de todas las unidades
        $capacidades = DB::table('capacidades')
            ->whereIn('id_capacidad_terminal', $unidades->pluck('id'))
            ->orderBy('created_at')
            ->get()
            ->groupBy('id_capacidad_terminal');

        // 4️⃣ Armar respuesta FINAL
        $resultado = $unidades->map(function ($unidad) use ($capacidades) {
            return [
                'id' => $unidad->id,
              //  'numero' => $unidad->numero_capacidad,
                'nombre' => $unidad->nombre_capacidad,
             //    'fecha_inicio' => $unidad->fecha_inicio,
             //   'fecha_fin' => $unidad->fecha_fin,
             //   'creditos_teoricos' => $unidad->creditos_teoricos,
             //   'creditos_practicos' => $unidad->creditos_practicos,
             //   'status' => $unidad->status,
                'capacidades' => $capacidades->get($unidad->id, collect())
            ];
        });

        return response()->json([
            'ok' => true,
            'grupo_id' => $idGrupo,
            'unidades' => $resultado
        ]);
    }


    /**
     * Crear capacidad terminal
     */
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
            'message' => 'Capacidad terminal eliminada correctamente'
        ]);
    }
}

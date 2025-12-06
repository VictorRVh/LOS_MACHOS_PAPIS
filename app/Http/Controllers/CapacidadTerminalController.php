<?php

namespace App\Http\Controllers;

use App\Models\CapacidadTerminal;
use App\Models\Grupo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CapacidadTerminalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $capacidades = CapacidadTerminal::with('grupo')->get();
        return response()->json($capacidades);
    }

    public function indexGrupo($id)
    {
        $capacidades = CapacidadTerminal::where('id_grupo', $id)
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        $nroCapacidades = Grupo::join('modulos', 'grupo.id_modulo', '=', 'modulos.id')
            ->where('grupo.id', $id)
            ->value('modulos.nro_capacidades');

        return response()->json([
            'nro_capacidades' => $nroCapacidades,
            'capacidades' => $capacidades
        ]);
    }

    public function nroCapacidades($id)
    {
        $grupo = Grupo::with('modulo')->find($id);

        if (!$grupo || !$grupo->modulo) {
            return response()->json(['error' => 'Grupo o módulo no encontrado'], 404);
        }

        $numeroCapacidades = $grupo->modulo->nro_capacidades;

        return response()->json(['nro_capacidades' => $numeroCapacidades]);
    }


    // GET /api/capacidad-terminal/{id}
    public function show($id)
    {
        $capacidad = CapacidadTerminal::with('grupo')->find($id);

        if (!$capacidad) {
            return response()->json(['message' => 'Capacidad no encontrada'], 404);
        }

        return response()->json($capacidad);
    }
    // POST /api/capacidad-terminal
    public function store(Request $request)
    {
        $request->validate([
            'numero_capacidad' => 'required|string|max:255',
            'nombre_capacidad' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'id_grupo' => 'required|exists:grupo,id',
            'status' => 'required|in:0,1,2,3',
        ]);

        $capacidad = CapacidadTerminal::create($request->all());

        return response()->json($capacidad, 201);
    }

    // PUT/PATCH /api/capacidad-terminal/{id}
    public function update(Request $request, $id)
    {
        $capacidad = CapacidadTerminal::find($id);

        if (!$capacidad) {
            return response()->json(['message' => 'Capacidad no encontrada'], 404);
        }

        $request->validate([
            'nombre_capacidad' => 'sometimes|string|max:255',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'sometimes|date|after_or_equal:fecha_inicio',
            'id_grupo' => 'sometimes|exists:grupo,id',
        ]);

        $capacidad->update($request->all());

        return response()->json($capacidad);
    }

    // DELETE /api/capacidad-terminal/{id}
    public function destroy($id)
    {
        $capacidad = CapacidadTerminal::find($id);

        if (!$capacidad) {
            return response()->json(['message' => 'Capacidad no encontrada'], 404);
        }

        $capacidad->delete();

        return response()->json(['message' => 'Capacidad eliminada correctamente'], 204);
    }

    public function aplazarCapacidadTerminal(Request $request, $id)
    {
        $capacidad = CapacidadTerminal::findOrFail($id);
        $dias = $request->dias_aplazados ?? 1;

        $capacidad->fecha_aplazada = Carbon::now('America/Lima')->startOfMinute()->addDays($dias);
        $capacidad->status = CapacidadTerminal::STATUS_ACTIVO;
        $capacidad->status_nota = 2;
        $capacidad->save();

        return response()->json([
            "message" => "Fecha aplazada correctamente",
            "fecha_aplazada" => $capacidad->fecha_aplazada,
            "status_nota" => $capacidad->status_nota,
        ]);
    }

    public function reactivarNota(Request $request, $id)
    {
        $capacidad = CapacidadTerminal::findOrFail($id);
        $ahora = Carbon::now('America/Lima');

        // Solo se puede reactivar si la nota ya está asignada
        if ($capacidad->status_nota != 1) {
            return response()->json([
                "message" => "La nota no está en un estado que permita reactivación.",
            ], 400);
        }

        // ✅ Usar el accessor fecha_limite_subida que ya calcula todo correctamente
        $fechaLimite = $capacidad->fecha_limite_subida;

        if ($ahora->lte($fechaLimite)) {
            // Reactivar nota
            $capacidad->status_nota = 0;
            $capacidad->save();

            return response()->json([
                "message" => "Nota reactivada correctamente.",
                "status_nota" => $capacidad->status_nota,
                "puede_subir_hasta" => $fechaLimite->format('d/m/Y H:i'),
            ]);
        }

        return response()->json([
            "message" => "No se puede reactivar la nota. La fecha límite ya venció.",
            "fecha_limite_era" => $fechaLimite->format('d/m/Y H:i'),
            "ahora_es" => $ahora->format('d/m/Y H:i'),
        ], 400);
    }
}

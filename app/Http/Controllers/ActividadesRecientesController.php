<?php

namespace App\Http\Controllers;

use App\Models\ActividadesRecientes;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActividadesRecientesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $actividades = ActividadesRecientes::with(['usuario', 'rol'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($act) {

                $nombreCompleto = trim(
                    ($act->usuario->name ?? '') . ' ' .
                        ($act->usuario->apellido_paterno ?? '') . ' ' .
                        ($act->usuario->apellido_materno ?? '')
                );

                return [
                    'role'   => $act->rol->name ?? 'sin-rol',

                    'actor'  => strtoupper($act->rol->name ?? 'Usuario')
                        . ' | ' . $nombreCompleto,

                    'accion' => $act->accion,
                    'detalle' => $act->descripcion,
                    'created_at' => $act->created_at,
                ];
            });

        return response()->json($actividades);
    }

    public function indexByDate(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date',
        ]);

        $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
        $fechaFin    = Carbon::parse($request->fecha_fin)->endOfDay();

        $actividades = ActividadesRecientes::with(['usuario', 'rol'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($act) {

                $nombreCompleto = trim(
                    ($act->usuario->name ?? '') . ' ' .
                        ($act->usuario->apellido_paterno ?? '') . ' ' .
                        ($act->usuario->apellido_materno ?? '')
                );

                return [
                    'role'   => $act->rol->name ?? 'sin-rol',
                    'actor'  => strtoupper($act->rol->name ?? 'Usuario') . ' | ' . $nombreCompleto,
                    'accion' => $act->accion,
                    'detalle' => $act->descripcion,
                    'created_at' => $act->created_at,
                ];
            });

        return response()->json($actividades);
    }

    // GET /api/actividades-recientes/{id}
    public function show($id)
    {
        $actividad = ActividadesRecientes::with(['usuario', 'rol'])->find($id);

        if (!$actividad) {
            return response()->json(['message' => 'Actividad no encontrada'], 404);
        }

        return response()->json($actividad);
    }

    // POST /api/actividades-recientes
    public function store(Request $request)
    {
        $request->validate([
            'id_role'    => 'required|exists:roles,id',
            'id_usuario' => 'required|exists:users,id',
            'descripcion' => 'required|string|max:255',
            'fecha'      => 'required|date',
        ]);

        $actividad = ActividadesRecientes::create($request->all());

        return response()->json([
            'message' => 'Actividad registrada correctamente',
            'data'    => $actividad
        ], 201);
    }

    // PATCH /api/actividades-recientes/{id}
    public function update(Request $request, $id)
    {
        $actividad = ActividadesRecientes::find($id);

        if (!$actividad) {
            return response()->json(['message' => 'Actividad no encontrada'], 404);
        }

        $request->validate([
            'id_role'    => 'sometimes|exists:roles,id',
            'id_usuario' => 'sometimes|exists:users,id',
            'descripcion' => 'sometimes|string|max:255',
            'fecha'      => 'sometimes|date',
        ]);

        $actividad->update($request->all());

        return response()->json([
            'message' => 'Actividad actualizada correctamente',
            'data'    => $actividad
        ]);
    }

    // DELETE /api/actividades-recientes/{id}
    public function destroy($id)
    {
        $actividad = ActividadesRecientes::find($id);

        if (!$actividad) {
            return response()->json(['message' => 'Actividad no encontrada'], 404);
        }

        $actividad->delete();

        return response()->json(['message' => 'Actividad eliminada correctamente']);
    }
}

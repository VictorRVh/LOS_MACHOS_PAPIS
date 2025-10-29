<?php

namespace App\Http\Controllers;

use App\Models\Sesiones;
use App\Models\EntregaDocente;
use App\Models\CalendarioAdmin;
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
    public function indexOneSesion($idGrupo)
    {
        // Buscar la entrega del grupo cuyo padre (admin) tenga tipo_entrega = 2
        $entrega = EntregaDocente::select(
            'id',
            'fecha_inicio',
            'fecha_fin',
            'estado',
            'fecha_aplazada',
            'dias_aplazados',
        )
            ->where('id_grupo', $idGrupo)
            ->whereHas('entregaDocenteAdmin', function ($q) {
                $q->where('tipo_entrega', 2); // Filtra por tipo de entrega = 2
            })
            ->first();

        if (!$entrega) {
            return response()->json(['message' => 'No se encontró la programación de sesión'], 404);
        }

        // Devuelve solo los campos seleccionados del modelo entrega_docente
        return response()->json($entrega);
    }
    public function indexListSesionesDocente($idEntrega)
    {
        // Buscar todas las sesiones asociadas a esta entrega, junto con su calendario
        $sesiones = Sesiones::with([
            'calendarioAdmin' => function ($q) {
                $q->select('id', 'id_sesion', 'fecha', 'laborable')
                    ->orderBy('fecha', 'asc');
            }
        ])
            ->where('id_entrega', $idEntrega)
            ->orderBy('fecha_inicio', 'asc')
            ->get(['id', 'nombre_sesion', 'descripcion', 'fecha_inicio', 'fecha_fin', 'status']);

        if ($sesiones->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron sesiones para esta entrega docente'
            ], 404);
        }

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
        // ✅ Validación de los campos
        $request->validate([
            'nombre_sesion' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'id_capacidad' => 'required|uuid|exists:capacidad_terminal,id',
            'id_entrega' => 'required|uuid|exists:entrega_docente,id',
            'fechas' => 'required|array|min:1',
            'fechas.*' => 'date',
        ]);

        // ✅ Ordenar y obtener fecha inicial y final
        $fechas = collect($request->fechas)->sort()->values();
        $fecha_inicio = $fechas->first();
        $fecha_fin = $fechas->last();

        // ✅ Determinar estado según la fecha actual
        $hoy = now()->toDateString();

        if ($fecha_inicio > $hoy) {
            $status = 0; // Pendiente
        } elseif ($fecha_fin < $hoy) {
            $status = 2; // Finalizada
        } else {
            $status = 1; // En curso
        }

        // ✅ Crear la sesión
        $sesion = Sesiones::create([
            'nombre_sesion' => $request->nombre_sesion,
            'descripcion' => $request->descripcion,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'id_capacidad' => $request->id_capacidad,
            'id_entrega' => $request->id_entrega,
            'status' => $status,
        ]);

        // ✅ Registrar las fechas en calendario_admin relacionadas a esta sesión
        foreach ($fechas as $fecha) {
            CalendarioAdmin::create([
                'id_sesion' => $sesion->id, // 🔹 relación directa
                'fecha' => $fecha,
                'laborable' => true,
            ]);
        }

        // ✅ Respuesta con la sesión y las fechas creadas
        return response()->json([
            'message' => 'Sesión creada correctamente',
            'sesion' => [
                'id' => $sesion->id,
                'nombre_sesion' => $sesion->nombre_sesion,
            ], // Incluye las fechas en la respuesta
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $sesion = Sesiones::find($id);

        if (!$sesion) {
            return response()->json(['message' => 'Sesión no encontrada'], 404);
        }

        $request->validate([
            'nombre_sesion' => 'sometimes|string|max:255',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'sometimes|date|after_or_equal:fecha_inicio',
            'descripcion' => 'nullable|string',
            'archivo_sesion' => 'nullable|string',
            'id_calendario' => 'sometimes|uuid|exists:calendario_admin,id',
            'id_capacidad' => 'sometimes|uuid|exists:capacidad_terminal,id',
            'id_entrega' => 'sometimes|uuid|exists:entrega_docente,id',
            'status' => 'sometimes|integer|in:0,1,2,3'
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

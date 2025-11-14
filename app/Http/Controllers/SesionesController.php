<?php

namespace App\Http\Controllers;

use App\Models\Sesiones;
use App\Models\EntregaDocente;
use App\Models\CalendarioAdmin;
use DB;
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
        // Traemos las capacidades con sus sesiones y dentro de ellas los calendarios
        $capacidades = \App\Models\CapacidadTerminal::with([
            'sesiones' => function ($q) {
                $q->select('id', 'id_capacidad', 'nombre_sesion', 'descripcion', 'fecha_inicio', 'fecha_fin', 'status')
                    ->orderBy('fecha_inicio', 'asc')
                    ->with([
                        'calendarioAdmin' => function ($c) {
                            $c->select('id', 'id_sesion', 'fecha', 'laborable')
                                ->orderBy('fecha', 'asc');
                        }
                    ]);
            }
        ])
            ->whereHas('sesiones', function ($q) use ($idEntrega) {
                $q->where('id_entrega', $idEntrega);
            })
            ->get(['id', 'nombre_capacidad']);

        if ($capacidades->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron capacidades con sesiones para esta entrega docente'
            ], 404);
        }

        // 🔥 Ocultar 'id_sesion' en los calendarios
        $capacidades->each(function ($capacidad) {
            $capacidad->sesiones->each(function ($sesion) {
                $sesion->calendarioAdmin->makeHidden('id_sesion');
            });
        });

        return response()->json($capacidades);
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
                'laborable' => 0,
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

        try {
            // INICIAR TRANSACCIÓN
            DB::beginTransaction();

            $sesion = Sesiones::findOrFail($id);

            // VALIDACIÓN
            $request->validate([
                'nombre_sesion' => 'sometimes|string|max:255',
                'descripcion' => 'nullable|string',
                'fechas' => 'required|array|min:1',
                'fechas.*' => 'date',
            ]);

            // ORDENAR FECHAS
            $fechas = collect($request->fechas)->sort()->values();
            $fecha_inicio = $fechas->first();
            $fecha_fin = $fechas->last();

            // ACTUALIZAR SESIÓN
            $sesion->update([
                'nombre_sesion' => $request->nombre_sesion,
                'descripcion' => $request->descripcion,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
            ]);

            // BORRAR FECHAS ANTERIORES
            CalendarioAdmin::where('id_sesion', $sesion->id)->delete();

            // INSERTAR NUEVAS FECHAS
            foreach ($fechas as $fecha) {
                CalendarioAdmin::create([
                    'id_sesion' => $sesion->id,
                    'fecha' => $fecha,
                    'laborable' => 0,
                ]);
            }

            // TODO CORRECTO → CONFIRMAR
            DB::commit();

            return response()->json([
                'message' => 'Sesión actualizada correctamente',
                'sesion' => $sesion,
            ], 200);
        } catch (\Throwable $e) {

            // SI ALGO FALLA → ROLLBACK
            DB::rollBack();


            return response()->json([
                'message' => 'Error al actualizar la sesión',
                'error' => $e->getMessage(), // puedes ocultarlo en producción si deseas
            ], 500);
        }
    }


    public function destroy($id)
    {
        $sesion = Sesiones::find($id);

        if (!$sesion) {
            return response()->json(['message' => 'Sesión no encontrada'], 404);
        }
        $sesion->delete();
        return response()->json(['message' => 'Sesión eliminada correctamente'], 204);
    }
}

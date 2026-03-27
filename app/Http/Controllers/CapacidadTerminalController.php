<?php

namespace App\Http\Controllers;

use App\Models\CapacidadTerminal;
use App\Models\EntregaDocente;
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
        // 1️⃣ Capacidades con programación activa (eager loading)
        $capacidades = CapacidadTerminal::with('grupo.entregaDocenteActiva')
            ->where('id_grupo', $id)
            ->orderByRaw('CAST(numero_capacidad AS UNSIGNED) ASC')
            ->get();

        // 2️⃣ Número de capacidades del módulo
        $nroCapacidades = Grupo::join('modulos', 'grupo.id_modulo', '=', 'modulos.id')
            ->where('grupo.id', $id)
            ->value('modulos.nro_capacidades');

        // 3️⃣ Programación (una sola vez)
        $entrega = $capacidades->first()?->grupo?->entregaDocenteActiva;

        $canEdit = false;

        if ($entrega) {
            $now = now('America/Lima');

            $canEdit = $now->between($entrega->fecha_inicio, $entrega->fecha_fin)
                && $entrega->estado === EntregaDocente::STATUS_ACTIVO;
        }

        return response()->json([
            'nro_capacidades' => $nroCapacidades,
            'can_edit' => $canEdit,
            'capacidades' => $capacidades->makeHidden(['grupo']),
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
    public function indexUnidadDidactica($idGrupo)
    {
        // Verificar que el grupo exista
        $grupo = Grupo::find($idGrupo);

        if (!$grupo) {
            return response()->json([
                'message' => 'Grupo no encontrado'
            ], 404);
        }

        // Obtener SOLO las unidades didácticas del grupo
        $unidades = CapacidadTerminal::where('id_grupo', $idGrupo)
            ->orderByRaw('CAST(numero_capacidad AS UNSIGNED) ASC')
            ->get([
                'id',
                'nombre_capacidad as descripcion'
            ]);

        return response()->json($unidades);
    }

    // POST /api/capacidad-terminal
    public function store(Request $request)
    {
        $now = now('America/Lima');

        $request->validate([
            'numero_capacidad' => 'required|string|max:255',
            'nombre_capacidad' => 'required|string|max:255',
            'fecha_inicio'     => 'required|date',
            'fecha_fin'        => 'required|date|after_or_equal:fecha_inicio',
            'creditos_teoricos' => 'required|string|max:255',
            'creditos_practicos' => 'required|string|max:255',
            'horas'               => 'required|string|max:255',
            'id_grupo'         => 'required|exists:grupo,id',
            'status'           => 'required|in:0,1,2,3',
        ]);

        // 1️⃣ Obtener programación
        $sesion = EntregaDocente::where('id_grupo', $request->id_grupo)
            ->whereHas('entregaDocenteAdmin', function ($q) use ($request) {
                $q->where('tipo_entrega', $request->tipo_entrega ?? 1);
            })
            ->first();

        if (!$sesion) {
            return response()->json([
                'errorCode' => 13333,
                'errorMessage' => 'No existe programación para este grupo.'
            ], 422);
        }

        if (
            !$now->between($sesion->fecha_inicio, $sesion->fecha_fin) ||
            $sesion->estado !== EntregaDocente::STATUS_ACTIVO
        ) {
            return response()->json([
                'errorCode' => 13333,
                'errorMessage' => 'La programación no permite crear unidades en este momento.'
            ], 403);
        }

        // 2️⃣ Validaciones del modelo
        $error = CapacidadTerminal::validarRangoFechasGrupo($request->all());

        if ($error) {
            return response()->json([
                'errorCode' => 13333,
                'errorMessage' => $error
            ], 422);
        }

        // 3️⃣ Control de cantidad de capacidades
        $grupo = Grupo::with('modulo')->find($request->id_grupo);

        $totalActual = CapacidadTerminal::where('id_grupo', $request->id_grupo)->count();
        $nroPermitido = $grupo->modulo->nro_capacidades ?? 0;

        if ($totalActual >= $nroPermitido) {
            return response()->json([
                'errorCode' => 13333,
                'errorMessage' => 'Ya se alcanzó el número máximo de unidades para este módulo.'
            ], 422);
        }

        // 4️⃣ Crear capacidad
        // $capacidad = CapacidadTerminal::create($request->all());

        $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
        $fechaFin    = Carbon::parse($request->fecha_fin)->endOfDay();

        // 📌 Determinar STATUS automáticamente
        if ($fechaInicio->gt($now)) {
            // Aún no inicia
            $status = 0;
        } elseif ($fechaInicio->lte($now) && $fechaFin->gte($now)) {
            // En ejecución
            $status = 1;
        } elseif ($fechaInicio->lt($now) && $fechaFin->lt($now)) {
            // Finalizado
            $status = 4;
        }

        // 🔹 Crear capacidad con status calculado
        $capacidad = CapacidadTerminal::create([
            'numero_capacidad'   => $request->numero_capacidad,
            'nombre_capacidad'   => $request->nombre_capacidad,
            'fecha_inicio'       => $request->fecha_inicio,
            'fecha_fin'          => $request->fecha_fin,
            'creditos_teoricos'  => $request->creditos_teoricos,
            'creditos_practicos' => $request->creditos_practicos,
            'horas'              => $request->horas,
            'id_grupo'           => $request->id_grupo,
            'status'             => $status,
        ]);


        // 5️⃣ Verificar si ya se completaron todas
        $totalFinal = CapacidadTerminal::where('id_grupo', $request->id_grupo)->count();

        if ($totalFinal == $nroPermitido) {
            $sesion->update([
                'cumplio' => 1
            ]);
        }

        return response()->json($capacidad, 201);
    }

    // PUT/PATCH /api/capacidad-terminal/{id}
    public function update(Request $request, $id)
    {
        $now = now('America/Lima');

        $capacidad = CapacidadTerminal::with('grupo.entregaDocenteActiva')->find($id);

        if (!$capacidad) {
            return response()->json(['message' => 'Capacidad no encontrada'], 404);
        }

        // 1VALIDAR PROGRAMACIÓN (igual que en store)
        $sesion = EntregaDocente::where('id_grupo', $capacidad->id_grupo)
            ->first();

        if (!$sesion) {
            return response()->json([
                'errorCode' => 13333,
                'errorMessage' => 'No existe programación para este grupo.'
            ], 422);
        }

        if (
            !$now->between($sesion->fecha_inicio, $sesion->fecha_fin) ||
            $sesion->estado !== EntregaDocente::STATUS_ACTIVO
        ) {
            return response()->json([
                'errorCode' => 13333,
                'errorMessage' => 'La programación no permite modificar unidades en este momento.'
            ], 403);
        }

        // VALIDACIÓN DE INPUT
        $request->validate([
            'nombre_capacidad' => 'sometimes|string|max:255',
            'fecha_inicio'     => 'sometimes|date',
            'fecha_fin'        => 'sometimes|date|after_or_equal:fecha_inicio',
            'creditos_teoricos' => 'sometimes|string|max:255',
            'creditos_practicos' => 'sometimes|string|max:255',
            'horas' => 'sometimes|string|max:255',
        ]);

        // MEZCLAR DATOS ACTUALES + NUEVOS (clave para validar correctamente)
        $data = array_merge($capacidad->toArray(), $request->all());

        // VALIDACIÓN DE RANGO DE FECHAS (igual que store)
        $error = CapacidadTerminal::validarRangoFechasGrupo($data);

        if ($error) {
            return response()->json([
                'errorCode' => 13333,
                'errorMessage' => $error
            ], 422);
        }

        // CALCULAR STATUS AUTOMÁTICO
        $fechaInicio = isset($data['fecha_inicio'])
            ? Carbon::parse($data['fecha_inicio'])->startOfDay()
            : Carbon::parse($capacidad->fecha_inicio)->startOfDay();

        $fechaFin = isset($data['fecha_fin'])
            ? Carbon::parse($data['fecha_fin'])->endOfDay()
            : Carbon::parse($capacidad->fecha_fin)->endOfDay();

        if ($fechaInicio->gt($now)) {
            $status = 0; // no inicia
        } elseif ($fechaInicio->lte($now) && $fechaFin->gte($now)) {
            $status = 1; // en ejecución
        } elseif ($fechaInicio->lt($now) && $fechaFin->lt($now)) {
            $status = 4; // finalizado
        }

        // ACTUALIZAR
        $capacidad->update([
            'nombre_capacidad'   => $data['nombre_capacidad'] ?? $capacidad->nombre_capacidad,
            'fecha_inicio'       => $data['fecha_inicio'] ?? $capacidad->fecha_inicio,
            'fecha_fin'          => $data['fecha_fin'] ?? $capacidad->fecha_fin,
            'creditos_teoricos'  => $data['creditos_teoricos'] ?? $capacidad->creditos_teoricos,
            'creditos_practicos' => $data['creditos_practicos'] ?? $capacidad->creditos_practicos,
            'horas'              => $data['horas'] ?? $capacidad->horas,
            'status'             => $status,
        ]);

        return response()->json($capacidad);
    }

    // DELETE /api/capacidad-terminal/{id}
    public function destroy($id)
    {
        $capacidad = CapacidadTerminal::with('grupo.entregaDocenteActiva')->find($id);

        if (!$capacidad) {
            return response()->json(['message' => 'Capacidad no encontrada'], 404);
        }

        // 🔒 Validar ventana de edición
        if (!$capacidad->canEdit()) {
            return response()->json([
                'message' => 'La programación no permite eliminar capacidades'
            ], 403);
        }

        $grupoId = $capacidad->id_grupo;

        DB::transaction(function () use ($capacidad, $grupoId) {
            $capacidad->delete();

            // Recalcular cumplio
            $grupo = Grupo::with('modulo')->find($grupoId);

            if ($grupo && $grupo->modulo) {
                $total = CapacidadTerminal::where('id_grupo', $grupoId)->count();
                $permitido = $grupo->modulo->nro_capacidades ?? 0;

                $entrega = EntregaDocente::where('id_grupo', $grupoId)
                    ->where('estado', EntregaDocente::STATUS_ACTIVO)
                    ->first();

                if ($entrega) {
                    $entrega->update([
                        'cumplio' => $total === $permitido ? 1 : 0
                    ]);
                }
            }
        });

        return response()->json(['message' => 'Capacidad eliminada correctamente'], 204);
    }

    public function aplazarCapacidadTerminal(Request $request, $id)
    {
        $capacidad = CapacidadTerminal::findOrFail($id);
        $dias = $request->dias_aplazados ?? 1;

        $capacidad->fecha_aplazada = Carbon::now('America/Lima')->addDays($dias)->endOfDay();
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

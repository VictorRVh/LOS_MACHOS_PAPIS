<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\EntregaDocenteAdmin;

use App\Models\EntregaDocente;
use Illuminate\Http\Request;

use App\Models\CarpetasEntregaDrive;

class EntregaDocenteController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function index()
    {
        $entregas = EntregaDocente::with(['grupo', 'entregaDocenteAdmin', 'entregaRealizada', 'sesiones'])->get();
        return response()->json($entregas);
    }

    // GET /api/entrega_docente/{id}
    public function show($id)
    {
        $entrega = EntregaDocente::with(['grupo', 'entregaDocenteAdmin', 'entregaRealizada', 'sesiones'])->find($id);

        if (!$entrega) {
            return response()->json(['message' => 'Entrega no encontrada'], 404);
        }

        return response()->json($entrega);
    }
    public function subidasPorProgramacion($id_admin)
    {

        // Obtener programación general
        $programacion = EntregaDocenteAdmin::findOrFail($id_admin);

        // Obtener subidas con relaciones
        $subidas = EntregaDocente::with([
            'grupo:id,seccion,turno,id_docente,id_modulo,id_especialidad',
            'grupo.docente:id,user_id',
            'grupo.docente.user:id,name,apellido_paterno,apellido_materno',
            'grupo.modulo:id,descripcion',
            'grupo.especialidad:id,id_especialidad',
            'grupo.especialidad.especialidadMadre:id,nombre_especialidad',
            'carpeta:id,drive_folder_id,id_entrega_docente', // 👈 Agrega esto
        ])
            ->where('id_admin', $id_admin)
            ->get();

        // Transformar resultado
        $gruposProgramados = $subidas->map(function ($item) {
            return [
                'id' => $item->id,
                'id_grupo' => $item->id_grupo,
                'fecha_inicio' => Carbon::parse($item->fecha_inicio)->format('d/m/Y H:i'),
                'fecha_fin' => Carbon::parse($item->fecha_fin)->format('d/m/Y H:i'),
                'estado' => $item->estado,
                'documento_admin' => $item->documento_admin,
                'observacion' => $item->observacion,
<<<<<<< HEAD
=======
                'cumplio' => $item->cumplio,
>>>>>>> f2878b34cbce9301735378d1394f4c2bf1f1243e
                // 'created_at' => $item->created_at,

                // 👇 Aquí se devuelven las carpetas asociadas
                'carpetas_drive' => optional($item->carpeta)->drive_folder_id,

                'grupo_detalle' => [
                    'id' => $item->grupo->id,
                    'nombre_especialidad' =>
                        $item->grupo->especialidad->especialidadMadre->nombre_especialidad ?? '',
                    'nombre_modulo' =>
                        $item->grupo->modulo->descripcion ?? '',
                    'nombre_docente' => $item->grupo->docente && $item->grupo->docente->user
                        ? $item->grupo->docente->user->name . ' ' .
                        $item->grupo->docente->user->apellido_paterno . ' ' .
                        $item->grupo->docente->user->apellido_materno
                        : '',
                    'seccion' => $item->grupo->seccion,
                    'turno' => $item->grupo->turno,
                ]
            ];
        });


        return response()->json([
            'total_programados' => $gruposProgramados->count(),
            'programacion' => [
                'id' => $programacion->id,
                'fecha_inicio' => Carbon::parse($programacion->fecha_inicio)->format('d/m/Y H:i'),
                'fecha_fin' => Carbon::parse($programacion->fecha_fin)->format('d/m/Y H:i'),
                'status' => $programacion->status,
                'id_periodo' => $programacion->id_periodo,
                'mostrar' => $programacion->mostrar,
            ],
            'grupos_programados' => $gruposProgramados
        ]);
    }

    // POST /api/entrega_docente
    public function store(Request $request)
    {
        $request->validate([
            'id_grupo' => 'required|uuid|exists:grupo,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'estado' => 'required|string|max:100',
            'id_admin' => 'required|uuid|exists:entrega_docente_admin,id',
            'documento_admin' => 'required|string|max:255',
            'observacion' => 'required|string|max:255',
        ]);

        $entrega = EntregaDocente::create($request->all());

        return response()->json(['message' => 'Entrega creada con éxito', 'data' => $entrega], 201);
    }

    // PATCH /api/entrega_docente/{id}
    public function update(Request $request, $id)
    {
        $entrega = EntregaDocente::find($id);

        if (!$entrega) {
            return response()->json(['message' => 'Entrega no encontrada'], 404);
        }

        // 🧾 Validación
        $request->validate([
            'observacion' => 'nullable|string|max:255',
            'dias_aplazadas' => 'nullable|string|max:255',
        ]);

        // 🧩 Actualiza los demás campos
        $data = $request->only([ 'observacion', 'dias_aplazadas']);

        // ⚙️ Si se aplazan días, actualizar automáticamente el estado
        if ($request->filled('dias_aplazadas')) {
            $data['fecha_aplazada'] = now();
            $data['estado'] = 1;
        }

        // 💾 Guardar cambios
        $entrega->update($data);

        return response()->json([
            'message' => 'Entrega actualizada con éxito',
            'programacion' => $entrega,
        ]);
    }

    // DELETE /api/entrega_docente/{id}
    public function destroy($id)
    {
        $entrega = EntregaDocente::find($id);

        if (!$entrega) {
            return response()->json(['message' => 'Entrega no encontrada'], 404);
        }

        $entrega->delete();

        return response()->json(['message' => 'Entrega eliminada correctamente']);
    }
}

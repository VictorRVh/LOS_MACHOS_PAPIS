<?php

namespace App\Http\Controllers;

use App\Models\EntregaDocente;
use App\Models\EntregaDocenteAdmin;
use App\Models\Grupo;
use App\Models\Periodo;
use Illuminate\Http\Request;

class EntregaDocenteAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $entregas = EntregaDocenteAdmin::all();
        return response()->json($entregas);
    }

    // Crear uno nuevo
    public function store(Request $request)
    {
        $request->validate([
            'tipo_entrega'    => 'required|string|max:255',
            'fecha_inicio'    => 'required|date',
            'fecha_fin'       => 'required|date|after_or_equal:fecha_inicio',
            'id_periodo'      => 'required|exists:periodo,id',
            'mostrar'         => 'nullable|boolean',
            'observavcion'    => 'nullable|string',
        ]);

        // Buscar el periodo
        $periodo = Periodo::findOrFail($request->id_periodo);

        // Crear el registro principal (programación del admin)
        $adminEntrega = EntregaDocenteAdmin::create([
            'id_periodo'    => $periodo->id,
            'tipo_entrega'  => $request->tipo_entrega,
            'fecha_inicio'  => $request->fecha_inicio,
            'fecha_fin'     => $request->fecha_fin,
            'status'        => 1,
            'mostrar' => $request->mostrar ?? false,
        ]);

        // Obtener todos los grupos del periodo
        $grupos = Grupo::where('id_periodo', $periodo->id)->get();

        if ($grupos->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron grupos para el periodo ' . $periodo->nombre_periodo,
            ], 404);
        }

        // Crear entregas individuales para cada grupo
        foreach ($grupos as $grupo) {
            EntregaDocente::create([
                'id_grupo'        => $grupo->id,
                'fecha_inicio'    => $request->fecha_inicio,
                'fecha_fin'       => $request->fecha_fin,
                'estado'          => 1,
                'id_admin'        => $adminEntrega->id,
                'documento_admin' => $request->tipo_entrega,
                'observacion'     => $request->observacion ?? '',
            ]);
        }

        // Respuesta
        return response()->json([
            'message'          => 'Entrega programada para todos los grupos del periodo ' . $periodo->nombre_periodo,
            'cantidad_grupos'  => $grupos->count(),
            'entrega_admin_id' => $adminEntrega->id,
        ]);
    }

    // Mostrar uno por ID
    public function show($id)
    {
        $entrega = EntregaDocenteAdmin::findOrFail($id);
        return response()->json($entrega);
    }

    // Actualizar
    public function update(Request $request, $id)
    {
        $entrega = EntregaDocenteAdmin::findOrFail($id);

        $request->validate([
            'tipo_entrega' => 'sometimes|string|max:100',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'sometimes|date|after_or_equal:fecha_inicio',
            'status' => 'sometimes|integer|in:0,1,2,3',
        ]);

        $entrega->update($request->all());

        return response()->json($entrega);
    }

    // Eliminar
    public function destroy($id)
    {
        $entrega = EntregaDocenteAdmin::findOrFail($id);
        $entrega->delete();

        return response()->json(null, 204);
    }


    // API DE PROGRAMACIOND DEL COORDINADOR
    public function indexByPeriodo($id_periodo)
    {
        try {
            $periodo = Periodo::findOrFail($id_periodo);

            $programaciones = EntregaDocenteAdmin::where('id_periodo', $id_periodo)
                ->orderBy('created_at', 'desc')
                ->get([
                    'id',
                    'tipo_entrega',
                    'fecha_inicio',
                    'fecha_fin',
                    'status',
                    'mostrar',
                    'created_at',
                ]);

            return response()->json([
                'periodo' => $periodo->nombre_periodo,
                'total_programaciones' => $programaciones->count(),
                'programaciones' => $programaciones,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las programaciones',
                'error' => $e->getMessage(),
            ], 500);
        }
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
        ])
            ->where('id_admin', $id_admin)
            ->get();

        // Transformar resultado
        $gruposProgramados = $subidas->map(function ($item) {
            return [
                'id' => $item->id,
                'id_grupo' => $item->id_grupo,
                'fecha_inicio' => $item->fecha_inicio,
                'fecha_fin' => $item->fecha_fin,
                'estado' => $item->estado,
                'documento_admin' => $item->documento_admin,
                'observacion' => $item->observacion,
                'created_at' => $item->created_at,
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
                'tipo_entrega' => $programacion->tipo_entrega,
                'fecha_inicio' => $programacion->fecha_inicio,
                'fecha_fin' => $programacion->fecha_fin,
                'status' => $programacion->status,
                'id_periodo' => $programacion->id_periodo,
                'mostrar' => $programacion->mostrar,
            ],
            'grupos_programados' => $gruposProgramados
        ]);
    }
}

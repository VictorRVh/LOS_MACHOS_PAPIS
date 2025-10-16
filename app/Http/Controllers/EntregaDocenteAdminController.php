<?php

namespace App\Http\Controllers;

use App\Models\EntregaDocente;
use App\Models\EntregaDocenteAdmin;
use App\Models\Grupo;
use App\Models\Periodo;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

        // Crear programación admin
        $adminEntrega = EntregaDocenteAdmin::create([
            'id_periodo'    => $periodo->id,
            'tipo_entrega'  => $request->tipo_entrega,
            'fecha_inicio'  => $request->fecha_inicio,
            'fecha_fin'     => $request->fecha_fin,
            'status'        => EntregaDocenteAdmin::STATUS_PENDIENTE,
            'mostrar'       => $request->mostrar ?? false,
        ]);

        // Obtener grupos del periodo
        $grupos = Grupo::with('carpetaDrive')->where('id_periodo', $periodo->id)->get();

        if ($grupos->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron grupos para el periodo ' . $periodo->nombre_periodo,
            ], 404);
        }

        $driveController = new GoogleDriveController();

        foreach ($grupos as $grupo) {

            //  1. Crear entrega individual
            EntregaDocente::create([
                'id_grupo'        => $grupo->id,
                'fecha_inicio'    => $adminEntrega->fecha_inicio,
                'fecha_fin'       => $adminEntrega->fecha_fin,
                'estado'          => $adminEntrega->status,
                'id_admin'        => $adminEntrega->id,
                'observacion'     => $request->observacion ?? '',
            ]);

            // 2. Crear carpeta en Drive para este tipo de entrega
            if ($grupo->carpetaDrive && $grupo->carpetaDrive->drive_folder_id) {

                $folderName = strtoupper($request->tipo_entrega); // Ej: "ACTA FINAL", "ASISTENCIA", etc.

                $response = $driveController->createFolder(new Request([
                    'folderName'     => $folderName,
                    'parentFolderId' => $grupo->carpetaDrive->drive_folder_id,
                ]));

                if ($response->status() === 201) {
                    $data = $response->getData();
                    // $folderId = $data->id ?? null;

                    // 3. Registrar en tabla de carpetas de entrega (si quieres)
                    // Por ejemplo:
                    // CarpetasEntregaDrive::create([
                    //     'id_entrega_admin' => $adminEntrega->id,
                    //     'id_grupo' => $grupo->id,
                    //     'drive_folder_id' => $folderId,
                    //     'nombre_carpeta' => $folderName
                    // ]);
                } else {
                    \Log::error('Error creando carpeta de tipo_entrega en Drive: ' . $response->getContent());
                }
            }
        }

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

            $programacionesFormateadas = $programaciones->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tipo_entrega' => $item->tipo_entrega,
                    'fecha_inicio' => Carbon::parse($item->fecha_inicio)->setTimezone('America/Lima')->format('d/m/Y H:i'),
                    'fecha_fin' => Carbon::parse($item->fecha_fin)->setTimezone('America/Lima')->format('d/m/Y H:i'),
                    'status' => $item->status,
                    'mostrar' => $item->mostrar,
                    'created_at' => Carbon::parse($item->created_at)->setTimezone('America/Lima')->format('d/m/Y H:i:s'),
                ];
            });

            return response()->json([
                'periodo' => $periodo->nombre_periodo,
                'total_programaciones' => $programacionesFormateadas->count(),
                'programaciones' => $programacionesFormateadas,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las programaciones',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function programacionesPorGrupo($id_grupo)
    {
        $programaciones = EntregaDocente::with([
            'entregaDocenteAdmin:id,tipo_entrega,fecha_inicio,fecha_fin,status',
            'grupo.carpetaDrive'
        ])
            ->where('id_grupo', $id_grupo)
            ->get();

        if ($programaciones->isEmpty()) {
            return response()->json([
                'total' => 0,
                'message' => 'No hay programaciones para este grupo'
            ], 404);
        }

        // 1. Obtener el ID de la carpeta del grupo en Drive
        $driveFolderId = $programaciones->first()->grupo->carpetaDrive->drive_folder_id ?? null;

        // 2. Listar archivos/carpetas dentro del drive_folder_id usando GoogleDriveController
        $driveController = new GoogleDriveController();

        $subcarpetas = [];
        if ($driveFolderId) {
            $requestList = new Request(['folderId' => $driveFolderId]);
            $responseDrive = $driveController->listFiles($requestList);

            if ($responseDrive->status() === 200) {
                $subcarpetas = json_decode($responseDrive->getContent());
            }
        }

        // 3. Armar la respuesta final
        return response()->json([
            'total' => $programaciones->count(),
            'drive_folder_id' => $driveFolderId,
            'carpetas_drive' => $subcarpetas, 
            'programaciones' => $programaciones->map(function ($item) {
                return [
                    'id' => $item->id,
                    'fecha_inicio' => $item->fecha_inicio,
                    'fecha_fin' => $item->fecha_fin,
                    'estado' => $item->estado,
                    'documento_admin' => $item->documento_admin,
                    'observacion' => $item->observacion,
                    'programacion_general' => $item->entregaDocenteAdmin ? [
                        'id' => $item->entregaDocenteAdmin->id,
                        'tipo_entrega' => $item->entregaDocenteAdmin->tipo_entrega,
                        'status' => $item->entregaDocenteAdmin->status,
                    ] : null
                ];
            })
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CarpetasEntregaDrive;
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
            'tipo_entrega' => 'required|string|max:255',
            'nombre_entrega' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'id_periodo' => 'required|exists:periodo,id',
            'mostrar' => 'nullable|boolean',
            'sub_grupos' => 'nullable|boolean',
            'observacion' => 'nullable|string',
        ]);

        // Buscar el periodo
        $periodo = Periodo::findOrFail($request->id_periodo);
        if (!$periodo) {
            return response()->json([
                'message' => 'El periodo seleccionado no existe o fue eliminado.'
            ], 404); // Not Found
        }

        // Verificar si hay grupos asociados al periodo
        $gruposExistentes = Grupo::where('id_periodo', $periodo->id)->exists();

        if (!$gruposExistentes) {

            throw new \Exception('Error|No puedes Crear esta programació, por que aun no tieene grupos en este periodo--404', 13333);
        }
        // 🔹 Verificar si hay grupos en ese periodo


        // Crear la entrega admin
        $adminEntrega = EntregaDocenteAdmin::create([
            'id_periodo' => $periodo->id,
            'tipo_entrega' => $request->tipo_entrega,
            'nombre_entrega' => $request->nombre_entrega,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'status' => EntregaDocenteAdmin::STATUS_PENDIENTE,
            'mostrar' => 0,
            'observacion' => $request->observacion ?? '',
        ]);

        return response()->json([
            'message' => 'Entrega creada en administración, pendiente de replicar a los grupos.',
            'entrega_admin_id' => $adminEntrega->id,
        ], 201);
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
        $request->validate([
            'tipo_entrega' => 'required|string|max:255',
            'nombre_entrega' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        // Buscar la entrega principal (admin)
        $adminEntrega = EntregaDocenteAdmin::findOrFail($id);

        // Actualizar los datos en la tabla principal
        $adminEntrega->update([
            'tipo_entrega' => $request->tipo_entrega,
            'nombre_entrega' => $request->nombre_entrega,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);

        // Si la entrega ya fue replicada a los grupos (mostrar = 1)
        if ($adminEntrega->mostrar) {

            // Actualizamos todas las entregas docentes que dependen de esta entrega admin
            EntregaDocente::where('id_admin', $adminEntrega->id)->update([
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
            ]);
        }

        return response()->json([
            'message' => 'Entrega actualizada correctamente.',
            'replicada' => $adminEntrega->mostrar ? 'Las entregas en los grupos también fueron actualizadas.' : 'Aún no se ha replicado esta programación.',
        ]);
    }


    // Eliminar
    public function destroy($id)
    {
        try {
            $entregaAdmin = EntregaDocenteAdmin::find($id);

            if (!$entregaAdmin) {
                return response()->json(['message' => 'Entrega no encontrada'], 404);
            }
            $driveController = new GoogleDriveController();
            $entregasDocentes = EntregaDocente::where('id_admin', $id)->get();

            foreach ($entregasDocentes as $entrega) {
                $carpetas = CarpetasEntregaDrive::where('id_entrega_docente', $entrega->id)->get();

                foreach ($carpetas as $carpeta) {
                    if ($carpeta->drive_folder_id) {
                        try {
                            $driveController->deleteFile($carpeta->drive_folder_id);
                        } catch (\Exception $e) {
                            return response()->json([
                                'message' => 'No se pudo eliminar carpeta {$carpeta->drive_folder_id} en Drive:',
                                'error' => $e->getMessage(),
                            ], 500);
                        }
                    }

                    $carpeta->delete();
                }

                if (method_exists($entrega, 'entregaRealizada')) {
                    $entrega->entregaRealizada()->delete();
                }

                if (method_exists($entrega, 'sesiones')) {
                    $entrega->sesiones()->delete();
                }

                $entrega->delete();
            }
            $entregaAdmin->delete();

            return response()->json([
                'message' => 'Entrega y carpetas asociadas eliminadas correctamente.',
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la entrega y sus carpetas asociadas.',
                'error' => $e->getMessage(),
            ], 500);
        }
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
                    'nombre_entrega',
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
                    'nombre_entrega' => $item->nombre_entrega,
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
            'entregaDocenteAdmin:id,nombre_entrega,fecha_inicio,fecha_fin,status',
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

        // 1️⃣ Carpeta raíz del grupo
        $driveFolderId = $programaciones->first()->grupo->carpetaDrive->drive_folder_id ?? null;

        if (!$driveFolderId) {
            return response()->json([
                'error' => 'Este grupo no tiene carpeta asignada en Drive.'
            ], 400);
        }

        // 2️⃣ Obtener subcarpetas dentro del folder raíz
        $driveController = new GoogleDriveController();
        $carpetasDrive = $driveController->listFiles($driveFolderId);

        // 3️⃣ Para cada carpeta, buscar si coincide con alguna programación (por tipo_entrega)
        $resultado = [];
        foreach ($carpetasDrive as $carpeta) {
            if ($carpeta->mimeType !== 'application/vnd.google-apps.folder') {
                continue; // solo carpetas
            }

            // Buscar programación con tipo_entrega parecido al nombre de la carpeta
            $programacion = $programaciones->first(function ($p) use ($carpeta) {
                return stripos($carpeta->name, $p->entregaDocenteAdmin->nombre_entrega) !== false;
            });

            // Obtener archivos dentro de esa subcarpeta
            $archivos = $driveController->listFiles($carpeta->id);

            $resultado[] = [
                'id' => $carpeta->id,
                'nombre' => $carpeta->name,
                'webViewLink' => $carpeta->webViewLink ?? null,
                'programacion' => $programacion ? [
                    'id' => $programacion->id,
                    'fecha_inicio' => $programacion->entregaDocenteAdmin->fecha_inicio,
                    'fecha_fin' => $programacion->entregaDocenteAdmin->fecha_fin,
                    'nombre_entrega' => $programacion->entregaDocenteAdmin->nombre_entrega,
                    'tipo_entrega' => $programacion->entregaDocenteAdmin->tipo_entrega,
                    'status' => $programacion->entregaDocenteAdmin->status,
                ] : null,
                'archivos' => collect($archivos)->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'nombre' => $file->name,
                        'mimeType' => $file->mimeType,
                        'webViewLink' => $file->webViewLink,
                    ];
                }),
            ];
        }

        // 4️⃣ Respuesta final
        return response()->json([
            'total' => $programaciones->count(),
            'carpeta_raiz' => [
                'id' => $driveFolderId,
                'nombre' => 'Carpeta raíz del grupo',
            ],
            'subcarpetas' => $resultado
        ]);
    }

    public function updateSubGrupo($id)
    {
        $adminEntrega = EntregaDocenteAdmin::findOrFail($id);
        $periodo = Periodo::findOrFail($adminEntrega->id_periodo);

        // Obtener grupos del periodo
        $grupos = Grupo::with('carpetaDrive')->where('id_periodo', $periodo->id)->get();

        if ($grupos->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron grupos para el periodo ' . $periodo->nombre_periodo,
            ], 404);
        }

        $driveController = new GoogleDriveController();

        foreach ($grupos as $grupo) {

            // 1. Crear entrega individual
            $entregaDocente = EntregaDocente::create([
                'id_grupo' => $grupo->id,
                'fecha_inicio' => $adminEntrega->fecha_inicio,
                'fecha_fin' => $adminEntrega->fecha_fin,
                'estado' => $adminEntrega->status,
                'id_admin' => $adminEntrega->id,
                'observacion' => $adminEntrega->observacion ?? '',
            ]);

            // 2. Crear carpeta en Drive
            if ($grupo->carpetaDrive && $grupo->carpetaDrive->drive_folder_id) {

                $folderName = strtoupper($adminEntrega->nombre_entrega);

                $response = $driveController->createFolder(new Request([
                    'folderName' => $folderName,
                    'parentFolderId' => $grupo->carpetaDrive->drive_folder_id,
                ]));

                if ($response->status() === 201) {
                    $data = $response->getData();
                    $folderId = $data->id ?? null;

                    CarpetasEntregaDrive::create([
                        'id_entrega_docente' => $entregaDocente->id,
                        'id_grupo' => $grupo->id,
                        'drive_folder_id' => $folderId,
                        'nombre_carpeta' => $folderName
                    ]);
                } else {
                    \Log::error('Error creando carpeta de tipo_entrega en Drive: ' . $response->getContent());
                }
            }
        }

        // Activar el campo "mostrar"
        $adminEntrega->update(['mostrar' => 1]);

        return response()->json([
            'message' => 'Entrega correctamente correctamente para los grupos del periodo' . $periodo->nombre_periodo,
            'cantidad_grupos' => $grupos->count(),
        ]);
    }
}

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
            'tipo_entrega'    => 'required|string|max:255',
            'nombre_entrega'  => 'required|string|max:255',
            'fecha_inicio'    => 'required|date',
            'fecha_fin'       => 'required|date|after_or_equal:fecha_inicio',
            'id_periodo'      => 'required|exists:periodo,id',
            'mostrar'         => 'nullable|boolean',
            'sub_grupos'      => 'nullable|boolean',
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
            $entregaDocente = EntregaDocente::create([
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
                    $folderId = $data->id ?? null;

                    // 3. Registrar en tabla de carpetas de entrega (si quieres)
                    // Por ejemplo:
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
            'nombre_entrega' => 'sometimes|string|max:100',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'sometimes|date|after_or_equal:fecha_inicio',
            'status' => 'sometimes|integer|in:0,1,2,3',
            'mostrar' => 'sometimes|boolean',
            'sub_grupos' => 'sometimes|boolean',
        ]);

        $entrega->update($request->all());

        return response()->json($entrega);
    }

    // Eliminar
    public function destroy($id)
    {
        // 1. Obtener la programación con sus carpetas
        $entrega = EntregaDocenteAdmin::with('carpetas')->find($id);

        if (!$entrega) {
            return response()->json(['message' => 'Entrega no encontrada'], 404);
        }

        $driveController = new GoogleDriveController();

        // 2. Eliminar las carpetas en Drive
        foreach ($entrega->carpetas as $carpeta) {
            if ($carpeta->drive_folder_id) {
                $response = $driveController->deleteFile($carpeta->drive_folder_id);

                if ($response->status() !== 200 && $response->status() !== 204) {
                    \Log::error('No se pudo eliminar carpeta en Drive: ' . $carpeta->drive_folder_id);
                }
            }
        }

        $entrega->delete();

        return response()->json(['message' => 'Entrega y carpetas eliminadas correctamente'], 204);
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
                return stripos($carpeta->name, $p->entregaDocenteAdmin->tipo_entrega) !== false;
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
}

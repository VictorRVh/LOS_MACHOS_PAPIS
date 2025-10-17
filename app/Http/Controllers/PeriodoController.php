<?php

namespace App\Http\Controllers;

use App\Models\CarpetasPeriodoDrive;
use App\Models\Periodo;
use Illuminate\Http\Request;

class PeriodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $periodos = Periodo::where('is_deleted', 0)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($periodo) {
                return [
                    'id' => $periodo->id,
                    'nombre_periodo' => $periodo->nombre_periodo,
                    'status' => $periodo->status,
                    'status_texto' => $periodo->status_texto,
                ];
            });

        return response()->json($periodos);
    }


    public function index_filter_status()
    {
        $periodos = Periodo::where('status', 1)
            ->where('is_deleted', 0)
            ->get()
            ->map(function ($periodo) {
                return [
                    'id' => $periodo->id,
                    'nombre_periodo' => $periodo->nombre_periodo,
                ];
            });

        return response()->json($periodos);
    }

    // Crear un nuevo periodo
    public function store(Request $request)
    {
        $request->validate([
            'nombre_periodo' => 'required|string|max:100',
            'status' => 'required|in:0,1,2,3',
        ]);

        // 1️⃣ Crear el periodo en la BD
        $periodo = Periodo::create($request->only(['nombre_periodo', 'status']));

        try {
            // 2️⃣ Carpeta madre fija en Google Drive
            $parentFolderId = '0AB477u4EnjP6Uk9PVA';

            $driveController = new GoogleDriveController();

            $folderRequest = new Request([
                'folderName' => $periodo->nombre_periodo,
                'parentFolderId' => $parentFolderId,
            ]);

            $response = $driveController->createFolder($folderRequest);

            if ($response->status() === 201) {
                $data = $response->getData();

                $periodo->save();

                // 4️⃣ Guardar también en carpetas_periodo_drive
                CarpetasPeriodoDrive::create([
                    'id_periodo'     => $periodo->id,
                    'drive_folder_id' => $data->id,
                    'nombre_carpeta' => $periodo->nombre_periodo,
                ]);
            } else {
                \Log::error('❌ Error creando carpeta del periodo: ' . $response->getContent());
            }
        } catch (\Exception $e) {
            \Log::error('⚠️ Error al crear carpeta del periodo en Drive: ' . $e->getMessage());
        }

        return response()->json($periodo, 201);
    }


    // Mostrar un periodo específico
    public function show($id)
    {
        $periodo = Periodo::find($id);

        if (!$periodo) {
            return response()->json(['message' => 'Periodo no encontrado'], 404);
        }

        return response()->json([
            'id' => $periodo->id,
            'nombre_periodo' => $periodo->nombre_periodo,
            'status' => $periodo->status,
            'status_texto' => $periodo->status_texto,
        ]);
    }

    // Actualizar un periodo
    public function update(Request $request, $id)
    {
        $periodo = Periodo::find($id);

        if (!$periodo) {
            return response()->json(['message' => 'Periodo no encontrado'], 404);
        }

        $request->validate([
            'nombre_periodo' => 'sometimes|string|max:100',
            'status' => 'sometimes|in:0,1,2,3',
        ]);

        $periodo->update($request->all());
        return response()->json($periodo);
    }

    // Eliminar un periodo
    public function destroy($id)
    {
        $periodo = Periodo::findOrFail($id);

        // Cambiar estado a Anulado (3)
        $periodo->is_deleted = 1;
        $periodo->save();

        return response()->json([
            'message' => 'Periodo anulado correctamente (no eliminado físicamente).',
            'data' => $periodo
        ]);
    }
}

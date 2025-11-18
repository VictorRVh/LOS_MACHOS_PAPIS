<?php

namespace App\Http\Controllers;

use App\Models\CarpetasPeriodoDrive;
use App\Models\Periodo;
use Illuminate\Http\Request;
use App\Traits\Helpers;


class PeriodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use Helpers;
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
                    'id_periodo' => $periodo->id,
                    'drive_folder_id' => $data->id,
                    'nombre_carpeta' => $periodo->nombre_periodo,
                ]);

                $this->registrarActividad("Creó el periodo '{$periodo->nombre_periodo}'", "Creado");
            } else {

                throw new \Exception(
                    '❌ Error creando carpeta del periodo:' . $response->getContent(),
                    13333
                );
            }
        } catch (\Exception $e) {

            throw new \Exception(
                '⚠️ Error al crear carpeta del periodo en Drive: ' . $e->getMessage(),
                13333
            );
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

        // Guardamos valores previos
        $nombreAnterior = $periodo->nombre_periodo;
        $statusAnterior = $periodo->status;

        // Actualizamos
        $periodo->update($request->all());

        // 🔥 Detectar cambios reales
        $cambios = [];

        if ($request->has('nombre_periodo') && $nombreAnterior != $periodo->nombre_periodo) {
            $cambios[] = "nombre del periodo  {$nombreAnterior} a {$periodo->nombre_periodo} ";
        }

        if ($request->has('status') && $statusAnterior != $periodo->status) {

            $estadoAnteriorTxt = $statusAnterior == 0 ? 'desactivado' : 'activado';
            $estadoNuevoTxt = $periodo->status == 0 ? 'desactivado' : 'activado';

            $cambios[] = "estado del periodo {$periodo->nombre_periodo} de {$estadoAnteriorTxt} a {$estadoNuevoTxt}";
        }

        // 🔥 Registrar actividad si hubo cambios reales
        if (!empty($cambios)) {
            $lista = implode(" y ", $cambios);
            $this->registrarActividad("Actualizó el {$lista} ", "Actualizado");
        }

        // 🔧 Renombrar en Drive si el nombre cambió (pero sin registrar actividad)
        if ($request->has('nombre_periodo') && $nombreAnterior != $periodo->nombre_periodo) {
            try {
                $carpetaDrive = CarpetasPeriodoDrive::where('id_periodo', $periodo->id)->first();

                if ($carpetaDrive && $carpetaDrive->drive_folder_id) {
                    $driveController = new GoogleDriveController();

                    $renameRequest = new Request([
                        'newName' => $request->nombre_periodo,
                    ]);

                    $response = $driveController->renameFile($renameRequest, $carpetaDrive->drive_folder_id);

                    if ($response->status() === 200) {
                        $data = $response->getData();
                        $carpetaDrive->update([
                            'nombre_carpeta' => $data->name,
                        ]);
                    } else {
                        throw new \Exception('Error al renombrar carpeta: ' . $response->getContent(), 13333);
                    }
                }
            } catch (\Exception $e) {
                throw new \Exception('Error al renombrar carpeta del periodo: ' . $e->getMessage(), 13333);
            }
        }

        return response()->json($periodo);
    }


    // Eliminar un periodo
    public function destroy($id)
    {
        $periodo = Periodo::findOrFail($id);

        // Guardar el nombre para el log
        $nombre = $periodo->nombre_periodo;

        // Cambiar estado a Anulado (soft delete)
        $periodo->is_deleted = 1;
        $periodo->save();

        // 🔥 Registrar actividad
        $this->registrarActividad("Anuló el periodo '{$nombre}' ", "Eliminado");

        return response()->json([
            'message' => 'Periodo anulado correctamente (no eliminado físicamente).',
        ], 204);
    }
}

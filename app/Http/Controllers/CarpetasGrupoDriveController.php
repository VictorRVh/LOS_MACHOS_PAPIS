<?php

namespace App\Http\Controllers;

use App\Models\CarpetasGrupoDrive;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Google_Client;
use Google_Service_Drive;
use Exception;

class CarpetasGrupoDriveController extends Controller
{
    protected $driveService;
    protected $rootFolderId = '0AHvmdsLWgTWyUk9PVA'; 
    // Ejemplo: '0AHvmdsLWgTWyUk9PVA'

    public function __construct()
    {
        $client = new Google_Client();
        $client->setAuthConfig(config_path('google-credentials.json'));
        $client->setScopes([Google_Service_Drive::DRIVE]);
        $client->setSubject(null); // Opcional, si usas impersonation

        $this->driveService = new Google_Service_Drive($client);
    }

    public function crearCarpetaGrupo($id_grupo)
    {
        try {
            // 1. Verificar si ya existe carpeta para este grupo
            $existe = CarpetasGrupoDrive::where('id_grupo', $id_grupo)->first();
            if ($existe) {
                return response()->json([
                    'message' => 'Este grupo ya tiene una carpeta',
                    'data' => $existe
                ], 200);
            }

            $parentFolderId = '0AHvmdsLWgTWyUk9PVA';

            // 2. Obtener datos del grupo
            $grupo = Grupo::findOrFail($id_grupo);

            // Nombre dinámico de la carpeta (cámbialo si deseas)
            $nombreCarpeta = 'Grupo_' . ($grupo->seccion ?? '-') . '_' . ($grupo->turno ?? '-');

            // 3. Crear carpeta en Drive dentro de la unidad compartida
            $folderMetadata = new \Google_Service_Drive_DriveFile([
                'name' => $nombreCarpeta,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => $parentFolderId
            ]);

            $folder = $this->driveService->files->create(
                $folderMetadata,
                [
                    'fields' => 'id, name',
                    'supportsAllDrives' => true // ✅ CLAVE
                ]
            );

            $folderId = $folder->id;

            // 4. Guardar en base de datos
            $carpeta = CarpetasGrupoDrive::create([
                'id' => Str::uuid(),
                'id_grupo' => $id_grupo,
                'drive_folder_id' => $folderId,
                'nombre_carpeta' => $nombreCarpeta,
            ]);

            return response()->json([
                'message' => 'Carpeta creada con éxito',
                'data' => $carpeta
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error al crear carpeta: ' . $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Google_Client;
use Google_Service_Drive;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleDriveController extends Controller
{
    private $driveService;

    public function __construct()
    {
        try {
            // Laravel sabe que config_path() apunta a la carpeta 'config'.
            $keyFilePath = config_path('google-credentials.json');

            if (!file_exists($keyFilePath)) {
                // Este error solo ocurrirá si el archivo no está en la carpeta 'config'
                // o si el nombre no es exactamente 'google-credentials.json'.
                throw new Exception('El archivo de credenciales "google-credentials.json" no se encuentra en la carpeta /config.');
            }

            $client = new Google_Client();
            $client->setAuthConfig($keyFilePath);
            $client->setScopes([Google_Service_Drive::DRIVE]);
            
            $this->driveService = new Google_Service_Drive($client);

        } catch (Exception $e) {
            Log::error('Error al inicializar Google Drive Service: ' . $e->getMessage());
            abort(500, 'Error de configuración del servicio de Google Drive. Verifica el archivo de credenciales y los permisos.');
        }
    }

    public function listFiles(Request $request)
    {
        $folderId = $request->query('folderId', null);
        
        if (!$folderId) {
            return response()->json(['error' => 'Se requiere un ID de carpeta.'], 400);
        }
        
        $query = "'{$folderId}' in parents and trashed = false";

        try {
            $files = $this->driveService->files->listFiles([
                'q' => $query,
                'pageSize' => 100,
                'fields' => 'files(id, name, mimeType, webViewLink, parents)',
                'orderBy' => 'folder desc, name'
            ]);

            return response()->json($files->getFiles());
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al listar archivos: ' . $e->getMessage()], 500);
        }
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'parentFolderId' => 'required|string'
        ]);

        try {
            $file = $request->file('file');
            $fileMetadata = new \Google_Service_Drive_DriveFile([
                'name' => $file->getClientOriginalName(),
                'parents' => [$request->parentFolderId]
            ]);

            $content = file_get_contents($file->getRealPath());
            $uploadedFile = $this->driveService->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $file->getClientMimeType(),
                'uploadType' => 'multipart',
                'fields' => 'id, name'
            ]);

            return response()->json($uploadedFile, 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'No se pudo subir el archivo: ' . $e->getMessage()], 500);
        }
    }

    public function createFolder(Request $request)
    {
        $request->validate([
            'folderName' => 'required|string|max:255',
            'parentFolderId' => 'required|string'
        ]);

        try {
            $folderMeta = new \Google_Service_Drive_DriveFile([
                'name' => $request->folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$request->parentFolderId]
            ]);
            $folder = $this->driveService->files->create($folderMeta, ['fields' => 'id, name']);

            return response()->json($folder, 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'No se pudo crear la carpeta: ' . $e->getMessage()], 500);
        }
    }

    public function deleteFile($fileId)
    {
        try {
            $this->driveService->files->delete($fileId);
            return response()->json(['message' => 'Archivo eliminado con éxito'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'No se pudo eliminar el archivo: ' . $e->getMessage()], 500);
        }
    }
}
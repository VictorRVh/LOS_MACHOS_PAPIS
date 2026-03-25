<?php

namespace App\Http\Controllers;

use App\Models\EntregaDocente;
use App\Models\EntregaDocenteAdmin;
use App\Models\EntregasRealizadas;
use App\Models\Grupo;
use Carbon\Carbon;
use Google_Client;
use Google_Service_Drive;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\Error;

class GoogleDriveController extends Controller
{
    use Error;
    private $driveService;

    public function __construct()
    {
        try {
            $keyFilePath = config_path('google-credentials.json');

            if (!file_exists($keyFilePath)) {
                throw new Exception('El archivo de credenciales "google-credentials.json" no se encuentra en la carpeta /config.');
            }

            $client = new Google_Client();
            $client->setAuthConfig($keyFilePath);
            $client->setScopes([Google_Service_Drive::DRIVE]);

            $this->driveService = new Google_Service_Drive($client);
        } catch (Exception $e) {
            Log::error('Error al inicializar Google Drive Service: ' . $e->getMessage());
            abort(500, 'Error de configuración del servicio de Google Drive.');
        }
    }

    /**
     * Lista los archivos o carpetas dentro de un folderId
     */
    public function listFiles($folderId = null)
    {
        // Permitir recibir folderId como parámetro o desde Request
        if ($folderId instanceof Request) {
            $folderId = $folderId->query('folderId');
        }

        if (!$folderId) {
            return response()->json(['error' => 'Se requiere un ID de carpeta.'], 400);
        }

        $query = "'{$folderId}' in parents and trashed = false";

        try {
            $files = $this->driveService->files->listFiles([
                'q' => $query,
                'pageSize' => 100,
                'fields' => 'files(id, name, mimeType, webViewLink, parents, capabilities(canEdit, canDelete))',
                'orderBy' => 'folder desc, name',
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true
            ]);

            return $files->getFiles();
        } catch (Exception $e) {
            Log::error('Error al listar archivos: ' . $e->getMessage());
            return [];
        }
    }

    public function listFilesNew($folderId)
    {
        if (!$folderId) {
            return response()->json(['error' => 'Se requiere un ID de carpeta.'], 400);
        }

        $query = "'{$folderId}' in parents and trashed = false";

        try {
            $files = $this->driveService->files->listFiles([
                'q' => $query,
                'pageSize' => 100,
                'fields' => 'files(id, name, mimeType, webViewLink, modifiedTime, size)',
                'orderBy' => 'folder desc, name',
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true
            ]);

            // Convertir el resultado de Google a un array limpio
            $cleanFiles = collect($files->getFiles())->map(function ($file) {
                return [
                    'id' => $file->getId(),
                    'name' => $file->getName(),
                    'mimeType' => $file->getMimeType(),
                    'webViewLink' => $file->getWebViewLink(),
                    'modifiedTime' => $file->getModifiedTime(),
                    'size' => $file->getSize(),
                ];
            });

            return response()->json($cleanFiles, 200);
        } catch (Exception $e) {
            Log::error('Error al listar archivos: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo listar los archivos.'], 500);
        }
    }

    public function downloadFile($fileId)
    {
        try {
            $file = $this->driveService->files->get($fileId, [
                'fields' => 'name, mimeType',
                'supportsAllDrives' => true
            ]);

            $mimeType = $file->getMimeType();
            $fileName = $file->getName();

            if (str_starts_with($mimeType, 'application/vnd.google-apps')) {

                $exportMap = [
                    'application/vnd.google-apps.document' =>
                    ['application/pdf', '.pdf'],

                    'application/vnd.google-apps.spreadsheet' =>
                    ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', '.xlsx'],

                    'application/vnd.google-apps.presentation' =>
                    ['application/pdf', '.pdf'],
                ];

                if (!isset($exportMap[$mimeType])) {
                    return response()->json([
                        'error' => 'Este tipo de archivo no puede exportarse'
                    ], 400);
                }

                [$exportMime, $extension] = $exportMap[$mimeType];

                $response = $this->driveService->files->export(
                    $fileId,
                    $exportMime
                );

                return response($response->getBody()->getContents(), 200)
                    ->header('Content-Type', $exportMime)
                    ->header(
                        'Content-Disposition',
                        'attachment; filename="' . $fileName . $extension . '"'
                    );
            }

            $response = $this->driveService->files->get($fileId, [
                'alt' => 'media',
                'supportsAllDrives' => true
            ]);

            return response($response->getBody()->getContents(), 200)
                ->header('Content-Type', $mimeType)
                ->header(
                    'Content-Disposition',
                    'attachment; filename="' . $fileName . '"'
                );
        } catch (\Exception $e) {
            \Log::error('Error al descargar archivo: ' . $e->getMessage());
            return response()->json([
                'error' => 'No se pudo descargar el archivo.'
            ], 500);
        }
    }

    public function createFolder(Request $request)
    {
        $request->validate(['folderName' => 'required|string|max:255', 'parentFolderId' => 'required|string']);
        try {
            $folderMeta = new \Google_Service_Drive_DriveFile([
                'name' => $request->folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$request->parentFolderId]
            ]);
            $folder = $this->driveService->files->create($folderMeta, [
                'fields' => 'id, name',
                'supportsAllDrives' => true
            ]);
            return response()->json($folder, 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'No se pudo crear la carpeta: ' . $e->getMessage()], 500);
        }
    }

    public function renameFile(Request $request, $fileId)
    {
        $request->validate(['newName' => 'required|string|max:255']);
        try {
            $fileMetadata = new \Google_Service_Drive_DriveFile(['name' => $request->newName]);
            $updatedFile = $this->driveService->files->update($fileId, $fileMetadata, [
                'fields' => 'id, name',
                'supportsAllDrives' => true
            ]);
            return response()->json($updatedFile);
        } catch (Exception $e) {
            return response()->json(['error' => 'No se pudo renombrar el archivo: ' . $e->getMessage()], 500);
        }
    }

    public function deleteFile($fileId)
    {
        try {
            $this->driveService->files->delete($fileId, ['supportsAllDrives' => true]);
            return response()->json(null, 204);
        } catch (Exception $e) {
            return response()->json(['error' => 'No se pudo eliminar el archivo: ' . $e->getMessage()], 500);
        }
    }

    public function uploadFile(Request $request)
    {
        $request->validate(['file' => 'required|file', 'parentFolderId' => 'required|string']);
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
                'fields' => 'id, name',
                'supportsAllDrives' => true
            ]);
            return response()->json($uploadedFile, 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'No se pudo subir el archivo: ' . $e->getMessage()], 500);
        }
    }

    public function uploadFileDocente(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'parentFolderId' => 'required|string',
            'id_entrega' => 'required|string',
        ]);

        try {
            // Buscar la entrega asociada (entrega_docente)
            $entrega = EntregaDocente::with(['entregaDocenteAdmin', 'grupo.docente'])
                ->find($request->id_entrega);

            if (!$entrega) {
                return response()->json(['error' => 'No se encontró la entrega asociada.'], 404);
            }

            // 🔹 Verificamos que exista el grupo y el docente
            $grupo = $entrega->grupo;
            if (!$grupo || !$grupo->docente) {
                return response()->json(['error' => 'No se encontró el grupo o el docente asociado.'], 404);
            }

            // Obtenemos el user_id del docente
            $idDocente = $grupo->docente->user_id;

            // Verificar estado y fechas
            $ahora = now('America/Lima');
            $inicio = Carbon::parse($entrega->fecha_inicio)->startOfMinute();
            $fin = Carbon::parse($entrega->fecha_fin)->endOfMinute();

            if ($entrega->estado != EntregaDocenteAdmin::STATUS_ACTIVO) {
                return response()->json(['error' => 'La entrega no está activa. No puede subir archivos.'], 403);
            }

            // Subida a Google Drive
            $file = $request->file('file');
            $fileMetadata = new \Google_Service_Drive_DriveFile([
                'name' => $file->getClientOriginalName(),
                'parents' => [$request->parentFolderId],
            ]);

            $content = file_get_contents($file->getRealPath());
            $uploadedFile = $this->driveService->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $file->getClientMimeType(),
                'uploadType' => 'multipart',
                'fields' => 'id, name',
                'supportsAllDrives' => true,
            ]);

            // Actualizar estado en entrega_docente
            $entrega->update([
                'cumplio' => 1,
            ]);

            // Registrar en entregas_realizadas
            EntregasRealizadas::create([
                'id_entrega' => $entrega->id,
                'id_docente' => $idDocente, // asegúrate que esta columna existe en entrega_docente
                // 'archivo' => $uploadedFile->id,
                'fecha_entrega' => $ahora,
            ]);

            return response()->json([
                'message' => 'Archivo subido correctamente.',
                'file' => $uploadedFile
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo subir el archivo: ' . $e->getMessage()
            ], 500);
        }
    }
}

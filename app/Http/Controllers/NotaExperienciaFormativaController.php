<?php

namespace App\Http\Controllers;

use App\Models\CarpetasPracticasDrive;
use App\Models\NotaExperienciaFormativa;
use Illuminate\Http\Request;

class NotaExperienciaFormativaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notas = NotaExperienciaFormativa::with(['grupo', 'estudiante', 'experienciaFormativa'])->get();
        return response()->json($notas);
    }

    // GET /api/nota_experiencia_formativa/{id}
    public function show($id)
    {
        $nota = NotaExperienciaFormativa::with(['grupo', 'estudiante', 'experienciaFormativa'])->find($id);

        if (!$nota) {
            return response()->json(['message' => 'Nota no encontrada'], 404);
        }

        return response()->json($nota);
    }

    // POST /api/nota_experiencia_formativa
    public function store(Request $request)
    {
        $request->validate([
            'id_experiencia'  => 'required|uuid|exists:experiencia_formativa,id',
            'lugar'           => 'required|string|max:255',
            'file'            => 'required|file',
            'parentFolderId'  => 'required|string',
            'id_estudiante'   => 'required|uuid|exists:estudiante,id',
            'id_grupo'        => 'required|uuid|exists:grupo,id',
        ]);

        try {
            // 1️⃣ Subir archivo a Google Drive usando el request original
            $driveController = new GoogleDriveController();

            // Pasar el request original que ya contiene el archivo
            $response = $driveController->uploadFile($request);

            if ($response->status() !== 201) {
                return response()->json(['error' => 'No se pudo subir el archivo al Drive.'], 500);
            }

            $fileData = $response->getData();

            // 2️⃣ Guardar el registro en la tabla nota_experiencia_formativa
            $nota = NotaExperienciaFormativa::create([
                'id_experiencia' => $request->id_experiencia,
                'lugar'          => $request->lugar,
                'documento'      => $fileData->id,
                'id_estudiante'  => $request->id_estudiante,
                'id_grupo'       => $request->id_grupo,
                'status'         => 1,
            ]);

            // $carpeta = CarpetasPracticasDrive::create([
            //     'id_nota_experiencia' => $nota->id,
            //     'id_estudiante'       => $request->id_estudiante,
            //     'drive_file_id'       => $fileData->id,
            // ]);

            return response()->json([
                'message' => 'Nota registrada y archivo subido con éxito.',
                'data' => $nota,
                'drive_file' => [
                    'id' => $fileData->id,
                    'name' => $fileData->name ?? 'Archivo',
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al guardar la nota: ' . $e->getMessage()], 500);
        }
    }

    // PATCH /api/nota_experiencia_formativa/{id}
    public function update(Request $request, $id)
    {
        $nota = NotaExperienciaFormativa::find($id);

        if (!$nota) {
            return response()->json(['message' => 'Nota no encontrada'], 404);
        }

        $request->validate([
            'id_experiencia'  => 'sometimes|uuid|exists:experiencia_formativa,id',
            'lugar'           => 'sometimes|string|max:255',
            'documento'       => 'sometimes|string|max:255',
            'id_estudiante'   => 'sometimes|uuid|exists:estudiante,id',
            'id_grupo'        => 'sometimes|uuid|exists:grupo,id',
            'status'          => 'sometimes|integer|in:0,1,2,3',
        ]);

        $nota->update($request->all());

        return response()->json(['message' => 'Nota actualizada con éxito', 'data' => $nota]);
    }

    // DELETE /api/nota_experiencia_formativa/{id}
    public function destroy($id)
    {
        $nota = NotaExperienciaFormativa::find($id);

        if (!$nota) {
            return response()->json(['message' => 'Nota no encontrada'], 404);
        }

        $nota->delete();

        return response()->json(['message' => 'Nota eliminada correctamente']);
    }
}

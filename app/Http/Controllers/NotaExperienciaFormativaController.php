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

    public function store(Request $request)
    {
        $request->validate([
            'id_experiencia'  => 'required|uuid|exists:experiencia_formativa,id',
            'tipo_practicas'  => 'required|integer|in:1,2,3',
            'file'            => 'nullable|file', // ✅ CAMBIO AQUÍ
            'nota'            => 'required|numeric|min:0|max:20',
            'parentFolderId'  => 'required|string',
            'id_estudiante'   => 'required|uuid|exists:estudiante,id',
            'id_grupo'        => 'required|uuid|exists:grupo,id',
            'observacion'     => 'nullable',
        ]);

        try {
            $fileData = null;

            // ✅ SOLO sube si hay archivo
            if ($request->hasFile('file')) {
                $driveController = new GoogleDriveController();
                $response = $driveController->uploadFile($request);

                if ($response->status() !== 201) {
                    return response()->json([
                        'error' => 'No se pudo subir el archivo al Drive.'
                    ], 500);
                }

                $fileData = $response->getData();
            }

            // ✅ Guardar con o sin archivo
            $nota = NotaExperienciaFormativa::create([
                'id_experiencia' => $request->id_experiencia,
                'tipo_practicas' => $request->tipo_practicas,
                'documento'      => $fileData->id ?? null,
                'nota'           => $request->nota,
                'id_estudiante'  => $request->id_estudiante,
                'id_grupo'       => $request->id_grupo,
                'observacion'    => $request->input('observacion', ''),
                'status'         => 1,
            ]);

            return response()->json([
                'message' => 'Nota registrada correctamente.',
                'data' => $nota,
                'drive_file' => $fileData ? [
                    'id' => $fileData->id,
                    'name' => $fileData->name ?? 'Archivo',
                ] : null,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al guardar la nota: ' . $e->getMessage()
            ], 500);
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
            'tipo_practicas'  => 'sometimes|integer',
            'documento'       => 'sometimes|string|max:255',
            'nota' => 'sometimes|numeric|min:0|max:20',
            'id_estudiante'   => 'sometimes|uuid|exists:estudiante,id',
            'id_grupo'        => 'sometimes|uuid|exists:grupo,id',
            'observacion'     => 'sometimes',
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

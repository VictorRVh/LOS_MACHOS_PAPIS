<?php

namespace App\Http\Controllers;

use App\Models\CarpetasEntregaDrive;
use App\Models\CarpetasGrupoDrive;
use App\Models\CarpetasPracticasDrive;
use App\Models\ExperienciaFormativa;
use App\Models\Grupo;
use Illuminate\Http\Request;

class ExperienciaFormativaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $experiencias = ExperienciaFormativa::with('grupo')->get();
        return response()->json($experiencias);
    }

    public function indexExperienciaFormativa($id_grupo)
    {
        // 🔹 Cargar grupo y estudiantes
        $grupo = Grupo::with(['matricula.estudiante'])->find($id_grupo);

        if (!$grupo) {
            return response()->json([
                'message' => 'Grupo no encontrado',
                'data' => null
            ], 404);
        }

        // 🔹 Cargar experiencia formativa (si existe)
        $experiencia = ExperienciaFormativa::with(['notas', 'drive'])
            ->where('id_grupo', $id_grupo)
            ->first();

        // 🔹 Carpeta general de la experiencia
        $drive_folder_id = $experiencia?->drive?->first()?->drive_folder_id ?? null;

        // 🔹 Listar estudiantes con su información y documentos
        $estudiantes = $grupo->matricula->where('reserva', 0)->map(function ($matricula) use ($experiencia) {
            $nota = null;
            $documentoDriveUrl = null;

            if ($experiencia) {
                $nota = $experiencia->notas
                    ->where('id_estudiante', $matricula->estudiante->id)
                    ->first();

                // Si el documento tiene un ID de archivo Drive, generamos URL pública (opcional)
                if ($nota && $nota->documento) {
                    $documentoDriveUrl = "https://drive.google.com/file/d/{$nota->documento}/view";
                }
            }

            return [
                'id_estudiante' => $matricula->estudiante->id,
                'apellidos_nombres' =>
                $matricula->estudiante->apellido_paterno . ' ' .
                    $matricula->estudiante->apellido_materno . ', ' .
                    $matricula->estudiante->nombre,
                'dni' => $matricula->estudiante->nro_documento,
                'tipo_practicas' => $nota->tipo_practicas ?? null,
                'tipo_practicas_texto' => $nota->tipo_practicas_texto ?? null,
                'documento_id' => $nota->documento ?? null, // ID interno o en Drive
                'documento_url' => $documentoDriveUrl, // URL visible en Drive
                'matriculado' => $matricula->matriculado // URL visible en Drive
            ];
        });

        $estudiantes = $estudiantes->sortBy('apellidos_nombres')->values();

        // 🔹 Retornar todo junto
        return response()->json([
            'data' => [
                'experiencia' => $experiencia,
                'drive_folder_id' => $drive_folder_id,
                'estudiantes' => $estudiantes,
            ]
        ]);
    }

    public function indexExperienciaFormativaFolderDrive($id_grupo)
    {
        $driveFolderId = CarpetasGrupoDrive::where('id_grupo', $id_grupo)
            ->value('drive_folder_id');

        if (!$driveFolderId) {
            return response()->json(['error' => 'No se encontró la carpeta para ese grupo'], 404);
        }

        return response()->json(['drive_folder_id' => $driveFolderId]);
    }

    // GET /api/experiencia_formativa/{id}
    public function show($id)
    {
        $experiencia = ExperienciaFormativa::with('grupo')->find($id);

        if (!$experiencia) {
            return response()->json(['message' => 'Experiencia formativa no encontrada'], 404);
        }

        return response()->json($experiencia);
    }

    // POST /api/experiencia_formativa
    public function store(Request $request)
    {
        $request->validate([
            'nombre_experiencia' => 'nullable|string|max:255',
            'parentId'           => 'required|string|max:255',
            'fecha_inicio'       => 'required|date',
            'fecha_fin'          => 'required|date|after_or_equal:fecha_inicio',
            'creditos'           => 'required|integer|min:1',
            'horas'              => 'required|integer|min:1',
            'id_grupo'           => 'required|uuid|exists:grupo,id',
            'status'             => 'nullable',
        ]);

        $data = $request->all();
        $data['nombre_experiencia'] = 'PRACTICAS';

        $experiencia = ExperienciaFormativa::create($data);

        $driveFolderId = null;

        try {
            $parentFolderId = $request->parentId;

            $driveController = new GoogleDriveController();

            $folderRequest = new Request([
                'folderName' => $experiencia->nombre_experiencia,
                'parentFolderId' => $parentFolderId,
            ]);

            $response = $driveController->createFolder($folderRequest);

            $status = method_exists($response, 'getStatusCode')
                ? $response->getStatusCode()
                : ($response['status'] ?? 200);

            $data = method_exists($response, 'getData')
                ? $response->getData(true)
                : (array) $response;

            if ($status === 201 && !empty($data['id'])) {
                $driveFolderId = $data['id'];
                CarpetasPracticasDrive::create([
                    'id_experiencia'  => $experiencia->id,
                    'drive_folder_id' => $driveFolderId,
                ]);
            } else {
                \Log::error('❌ Error creando carpeta en Drive: ' . $response->getContent());
            }
        } catch (\Exception $e) {
            \Log::error('⚠️ Error al crear carpeta de la experiencia en Drive: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Experiencia creada con éxito',
            'data' => [
                'id' => $experiencia->id,
                'nombre_experiencia' => $experiencia->nombre_experiencia,
                'fecha_inicio' => $experiencia->fecha_inicio,
                'fecha_fin' => $experiencia->fecha_fin,
<<<<<<< HEAD
=======
                'creditos' => $experiencia->creditos,
>>>>>>> fa50eb356fdfb5288d30bb9d425aa168d8386a36
                'horas' => $experiencia->horas,
                'id_grupo' => $experiencia->id_grupo,
                'drive_folder_id' => $driveFolderId,
            ],
        ], 201);
    }


    // PATCH /api/experiencia_formativa/{id}
    public function update(Request $request, $id)
    {
        $experiencia = ExperienciaFormativa::find($id);

        if (!$experiencia) {
            return response()->json(['message' => 'Experiencia formativa no encontrada'], 404);
        }

        $request->validate([
            'nombre_experiencia' => 'sometimes|string|max:255',
            'fecha_inicio'       => 'sometimes|date',
            'fecha_fin'          => 'sometimes|date|after_or_equal:fecha_inicio',
            'creditos'           => 'sometimes|integer|min:1',
            'horas'              => 'sometimes|integer|min:1',
            'id_grupo'           => 'sometimes|uuid|exists:grupo,id',
            'status'             => 'sometimes|integer|in:0,1,2,3',
        ]);

        $experiencia->update($request->all());

        $carpeta = CarpetasPracticasDrive::where('id_experiencia', $experiencia->id)->first();

        return response()->json([
            'message' => 'Experiencia actualizada con éxito',
            'data' => [
                'id' => $experiencia->id,
                'nombre_experiencia' => $experiencia->nombre_experiencia,
                'fecha_inicio' => $experiencia->fecha_inicio,
                'fecha_fin' => $experiencia->fecha_fin,
<<<<<<< HEAD
=======
                'creditos' => $experiencia->creditos,
>>>>>>> fa50eb356fdfb5288d30bb9d425aa168d8386a36
                'horas' => $experiencia->horas,
                'id_grupo' => $experiencia->id_grupo,
                'drive_folder_id' => optional($carpeta)->drive_folder_id,
            ],
        ]);
    }

    // DELETE /api/experiencia_formativa/{id}
    public function destroy($id)
    {
        $experiencia = ExperienciaFormativa::find($id);

        if (!$experiencia) {
            return response()->json(['message' => 'Experiencia formativa no encontrada'], 404);
        }

        $experiencia->delete();

        return response()->json(['message' => 'Experiencia eliminada con éxito']);
    }
}

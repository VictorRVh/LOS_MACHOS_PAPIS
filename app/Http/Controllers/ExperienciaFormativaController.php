<?php

namespace App\Http\Controllers;

use App\Models\CarpetasEntregaDrive;
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
        $experiencia = ExperienciaFormativa::with(['notas'])
            ->where('id_grupo', $id_grupo)
            ->first();

        if (!$experiencia) {
            return response()->json([
                'message' => 'No se encontró una experiencia formativa para este grupo',
                'data' => null
            ], 404);
        }

        // 🔹 Obtenemos los estudiantes pertenecientes a ese grupo
        $grupo = Grupo::with(['matricula.estudiante'])->find($id_grupo);

        if (!$grupo) {
            return response()->json([
                'message' => 'Grupo no encontrado',
                'data' => null
            ], 404);
        }

        // 🔹 Armamos la lista de estudiantes con sus notas (si existen)
        $estudiantes = $grupo->matricula->map(function ($matricula) use ($experiencia) {
            $nota = $experiencia->notas
                ->where('id_estudiante', $matricula->estudiante->id)
                ->first();

            return [
                'id_estudiante' => $matricula->estudiante->id,
                'apellidos_nombres' => $matricula->estudiante->apellido_paterno . ' ' .
                    $matricula->estudiante->apellido_materno . ', ' .
                    $matricula->estudiante->nombre,
                'dni' => $matricula->estudiante->nro_documento,
                'lugar' => $nota->lugar ?? null,
                'documento' => $nota->documento ?? null,
            ];
        });

        return response()->json([
            'data' => [
                'experiencia' => $experiencia,
                'estudiantes' => $estudiantes,
            ]
        ]);
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
            'nombre_experiencia' => 'required|string|max:255',
            'parentId'           => 'required|string|max:255',
            'fecha_inicio'       => 'required|date',
            'fecha_fin'          => 'required|date|after_or_equal:fecha_inicio',
            'horas'              => 'required|integer|min:1',
            'id_grupo'           => 'required|uuid|exists:grupo,id',
            'status'             => 'nullable',
        ]);

        $experiencia = ExperienciaFormativa::create($request->all());

        try {
            $parentFolderId = $request->parentId;

            $driveController = new GoogleDriveController();

            $folderRequest = new Request([
                'folderName' => $experiencia->nombre_experiencia,
                'parentFolderId' => $parentFolderId,
            ]);

            $response = $driveController->createFolder($folderRequest);

            if ($response->status() === 201) {
                $data = $response->getData();


                // MODELO DE EJEMPLO 

                // ExperienciaFormativaDrive::create([
                //     'id_experiencia' => $experiencia->id,
                //     'drive_folder_id' => $data->id,
                //     'nombre_carpeta' => $experiencia->nombre_experiencia,
                // ]);

                // LA FIJA 

                // CarpetasEntregaDrive::create([
                //     'id_entrega_docente' => $entregaDocente->id,
                //     'id_grupo' => $request->id_grupo,
                //     'drive_folder_id' => $data->id,
                //     'nombre_carpeta' => $experiencia->nombre_experiencia
                // ]);
            } else {
                \Log::error('❌ Error creando carpeta en Drive: ' . $response->getContent());
            }
        } catch (\Exception $e) {
            \Log::error('⚠️ Error al crear carpeta de la experiencia en Drive: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Experiencia creada con éxito',
            'data' => $experiencia,
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
            'horas'              => 'sometimes|integer|min:1',
            'id_grupo'           => 'sometimes|uuid|exists:grupo,id',
            'status'             => 'sometimes|integer|in:0,1,2,3',
        ]);

        $experiencia->update($request->all());

        return response()->json(['message' => 'Experiencia actualizada con éxito', 'data' => $experiencia]);
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

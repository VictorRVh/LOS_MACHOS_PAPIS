<?php

namespace App\Http\Controllers;

use App\Exports\ReporteEntregaDocenteExport;
use Carbon\Carbon;
use App\Models\EntregaDocenteAdmin;

use App\Models\EntregaDocente;
use Illuminate\Http\Request;
use App\Models\CarpetasEntregaDrive;
use App\Http\Controllers\GoogleDriveController;
use Illuminate\Support\Facades\DB;

class EntregaDocenteController extends Controller
{
    protected $googleDriveController;

    public function __construct(GoogleDriveController $googleDriveController)
    {
        $this->googleDriveController = $googleDriveController;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function index()
    {
        $entregas = EntregaDocente::with(['grupo', 'entregaDocenteAdmin', 'entregaRealizada', 'sesiones'])->get();
        return response()->json($entregas);
    }

    // GET /api/entrega_docente/{id}
    public function show($id)
    {
        $entrega = EntregaDocente::with(['grupo', 'entregaDocenteAdmin', 'entregaRealizada', 'sesiones'])->find($id);

        if (!$entrega) {
            return response()->json(['message' => 'Entrega no encontrada'], 404);
        }

        return response()->json($entrega);
    }
    public function subidasPorProgramacion($id_admin)
    {

        // Obtener programación general
        $programacion = EntregaDocenteAdmin::findOrFail($id_admin);

        // Obtener subidas con relaciones
        $subidas = EntregaDocente::with([
            'grupo:id,seccion,turno,id_docente,id_modulo,id_especialidad',
            'grupo.docente:id,user_id',
            'grupo.docente.user:id,name,apellido_paterno,apellido_materno',
            'grupo.modulo:id,descripcion',
            'grupo.especialidad:id,id_especialidad',
            'grupo.especialidad.especialidadMadre:id,nombre_especialidad',
            'carpeta:id,drive_folder_id,id_entrega_docente',
        ])
            ->where('id_admin', $id_admin)
            ->get();

        // Transformar resultado
        $gruposProgramados = $subidas->map(function ($item) {
            return [
                'id' => $item->id,
                'id_grupo' => $item->id_grupo,
                'fecha_inicio' => Carbon::parse($item->fecha_inicio)->format('d/m/Y H:i'),
                'fecha_fin' => Carbon::parse($item->fecha_fin)->format('d/m/Y H:i'),
                'estado' => $item->estado,
                'documento_admin' => $item->documento_admin,
                'observacion' => $item->observacion,
                'cumplio' => $item->cumplio,
                'fecha_aplazada' => Carbon::parse($item->fecha_aplazada)->format('d/m/Y H:i'),
                'dias_aplazados' => $item->dias_aplazados,
                // 'created_at' => $item->created_at,

                'carpetas_drive' => optional($item->carpeta)->drive_folder_id,

                'grupo_detalle' => [
                    'id' => $item->grupo->id,
                    'nombre_especialidad' =>
                    $item->grupo->especialidad->especialidadMadre->nombre_especialidad ?? '',
                    'nombre_modulo' =>
                    $item->grupo->modulo->descripcion ?? '',
                    'nombre_docente' => $item->grupo->docente && $item->grupo->docente->user
                        ? $item->grupo->docente->user->name . ' ' .
                        $item->grupo->docente->user->apellido_paterno . ' ' .
                        $item->grupo->docente->user->apellido_materno
                        : '',
                    'seccion' => $item->grupo->seccion,
                    'turno' => $item->grupo->turno,
                ]
            ];
        });


        return response()->json([
            'total_programados' => $gruposProgramados->count(),
            'programacion' => [
                'id' => $programacion->id,
                'nombre_programacion' => $programacion->nombre_entrega,
                'fecha_inicio' => Carbon::parse($programacion->fecha_inicio)->format('d/m/Y H:i'),
                'fecha_fin' => Carbon::parse($programacion->fecha_fin)->format('d/m/Y H:i'),
                'status' => $programacion->status,
                'id_periodo' => $programacion->id_periodo,
                'mostrar' => $programacion->mostrar,
            ],
            'grupos_programados' => $gruposProgramados
        ]);
    }

    // POST /api/entrega_docente
    public function store(Request $request)
    {
        $request->validate([
            'id_grupo' => 'required|uuid|exists:grupo,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'estado' => 'required|integer|in:0,1,4', // Solo permite estados válidos
            'id_admin' => 'required|uuid|exists:entrega_docente_admin,id',
            'documento_admin' => 'required|string|max:255',
            'observacion' => 'nullable|string|max:255',
            'fecha_aplazada' => 'nullable|date|after_or_equal:fecha_fin',
        ]);

        try {
            DB::beginTransaction();

            $entrega = EntregaDocente::create($request->all());

            // Sincronizar estado si es necesario
            $entrega->sincronizarEstado();

            DB::commit();

            return response()->json([
                'message' => 'Entrega creada con éxito',
                'data' => $entrega,
                'info_estado' => $entrega->obtenerInfoEstado()
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear entrega: ' . $e->getMessage());

            return response()->json([
                'error' => 'No se pudo crear la entrega',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // PATCH /api/entrega_docente/{id}
    public function update(Request $request, $id)
    {
        $entrega = EntregaDocente::find($id);

        if (!$entrega) {
            return response()->json(['message' => 'Entrega no encontrada'], 404);
        }

        // 🔹 Validación básica
        $request->validate([
            'observacion' => 'nullable|string|max:255',
            'dias_aplazados' => 'nullable|integer|min:1',
        ]);

        $data = $request->only(['observacion', 'dias_aplazados']);

        // 🔹 Si se aplican días aplazados
        if ($request->filled('dias_aplazados')) {
            $dias = (int) $request->dias_aplazados;

            $data['fecha_aplazada'] = Carbon::now('America/Lima')
                ->addDays($dias)
                ->setTime(23, 59, 59);
        } else {
            // Si no se especifican días, limpiamos cualquier aplazamiento anterior
            $data['fecha_aplazada'] = null;
            $data['dias_aplazados'] = null;
        }

        // 🔹 Guardar cambios
        $entrega->update($data);

        // 🔹 Sincronizar estado inmediatamente (opcional, pero recomendable)
        $entrega->sincronizarEstado();

        return response()->json([
            'message' => 'Entrega actualizada con éxito.',
            'entrega' => $entrega,
            'info_estado' => $entrega->obtenerInfoEstado(),
        ]);
    }

    // DELETE /api/entrega_docente/{id}
    public function destroy($id)
    {
        try {
            $entrega = EntregaDocente::findOrFail($id);

            // Verificar si tiene entregas realizadas
            if ($entrega->entregasRealizadas()->exists()) {
                return response()->json([
                    'error' => 'No se puede eliminar una entrega que ya tiene registros realizados'
                ], 400);
            }

            $entrega->delete();

            return response()->json([
                'message' => 'Entrega eliminada con éxito'
            ], 204);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar entrega: ' . $e->getMessage());

            return response()->json([
                'error' => 'No se pudo eliminar la entrega',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function generarExcel(Request $request)
    {
        $idAdmin = $request->query('id_admin');

        if (!$idAdmin) {
            return response()->json(['error' => 'Debe enviar el parámetro id_admin'], 400);
        }

        try {
            $export = new ReporteEntregaDocenteExport($idAdmin);
            $contenido = $export->generarReporte();

            // 🔹 DEBUG: Verificar tamaño del contenido
            \Log::info('Tamaño del archivo Excel generado: ' . strlen($contenido) . ' bytes');

            if (strlen($contenido) === 0) {
                return response()->json(['error' => 'El contenido generado está vacío'], 500);
            }

            $nombreArchivo = 'reporte_entrega_docentes_' . date('Ymd_His') . '.xlsx';

            return response($contenido)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->header('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"')
                ->header('Cache-Control', 'max-age=0')
                ->header('Content-Length', strlen($contenido)); // 🔹 Agregar tamaño

        } catch (\Exception $e) {
            \Log::error('Error generando Excel: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'linea' => $e->getLine()
            ], 500);
        }
    }

    public function subirArchivo(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB
            'parentFolderId' => 'required|string',
            'id_entrega' => 'required|uuid|exists:entrega_docente,id',
        ]);

        try {
            DB::beginTransaction();

            // 1. Buscar entrega con relaciones
            $entrega = EntregaDocente::with(['grupo.docente'])
                ->findOrFail($request->id_entrega);

            // 2. Validar grupo y docente
            if (!$entrega->grupo || !$entrega->grupo->docente) {
                return response()->json([
                    'error' => 'No se encontró el grupo o el docente asociado.'
                ], 404);
            }

            // $idDocente = $entrega->grupo->docente->user_id;
            $idDocente = $entrega->grupo->docente->id;

            // 3. ✅ VALIDAR USANDO EL MODELO
            $validacion = $entrega->puedeSubirArchivo();

            if (!$validacion['activa']) {
                return response()->json([
                    'error' => $validacion['mensaje'],
                    'codigo' => $validacion['codigo'],
                    'info_estado' => $entrega->obtenerInfoEstado()
                ], 403);
            }

            // 4. ✅ DELEGAR LA SUBIDA A GOOGLE DRIVE
            // Llamar al método uploadFile del GoogleDriveController
            $uploadRequest = new Request([
                'file' => $request->file('file'),
                'parentFolderId' => $request->parentFolderId
            ]);
            $uploadRequest->files->set('file', $request->file('file'));

            $responseUpload = $this->googleDriveController->uploadFile($uploadRequest);

            // Verificar si la subida fue exitosa
            if ($responseUpload->getStatusCode() !== 201) {
                throw new \Exception('Error al subir archivo a Google Drive');
            }

            $archivoSubido = json_decode($responseUpload->getContent());

            // 5. Marcar como cumplida y registrar
            $entregaRealizada = $entrega->marcarComoCumplida($idDocente);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Archivo subido correctamente.',
                'data' => [
                    'archivo' => $archivoSubido,
                    'entrega_realizada' => $entregaRealizada,
                    'fecha_entrega' => $entregaRealizada->fecha_entrega->format('d/m/Y H:i:s'),
                    'fecha_limite' => $entrega->obtenerFechaFinEfectiva()->format('d/m/Y H:i:s'),
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al subir archivo de entrega: ' . $e->getMessage());

            return response()->json([
                'error' => 'No se pudo completar la entrega',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function verificarEstado($id)
    {
        try {
            $entrega = EntregaDocente::findOrFail($id);

            return response()->json([
                'info_estado' => $entrega->obtenerInfoEstado(),
                'validacion' => $entrega->puedeSubirArchivo(),
                'necesita_sincronizar' => $entrega->necesitaActualizarEstado()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Entrega no encontrada',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function sincronizarEstado($id)
    {
        try {
            $entrega = EntregaDocente::findOrFail($id);

            $actualizado = $entrega->sincronizarEstado();

            return response()->json([
                'message' => $actualizado ? 'Estado sincronizado' : 'Estado ya estaba actualizado',
                'actualizado' => $actualizado,
                'info_estado' => $entrega->obtenerInfoEstado()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo sincronizar el estado',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

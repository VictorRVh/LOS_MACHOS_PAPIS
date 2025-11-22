<?php

namespace App\Http\Controllers;

use App\Models\EntregaDocente;
use App\Models\EntregasRealizadas;
use App\Http\Controllers\GoogleDriveController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntregasRealizadasController extends Controller
{

    protected $googleDriveController;

    public function __construct(GoogleDriveController $googleDriveController)
    {
        $this->googleDriveController = $googleDriveController;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $entregas = EntregasRealizadas::with(['entregaDocente', 'docente'])->get();
        return response()->json($entregas);
    }

    // GET /api/entregas-realizadas/{id}
    public function show($id)
    {
        $entrega = EntregasRealizadas::with(['entregaDocente', 'docente'])->find($id);

        if (!$entrega) {
            return response()->json(['message' => 'Entrega no encontrada'], 404);
        }

        return response()->json($entrega);
    }

    // POST /api/entregas-realizadas
    public function store(Request $request)
    {
        $request->validate([
            'id_entrega'    => 'required|uuid|exists:entrega_docente,id',
            'id_docente'    => 'required|uuid|exists:docente,id',
            'archivo'       => 'nullable|string|max:255',
            'fecha_entrega' => 'required|date',
            'observacion'   => 'nullable|string|max:255',
        ]);

        $entrega = EntregasRealizadas::create($request->all());

        return response()->json([
            'message' => 'Entrega registrada correctamente',
            'data' => $entrega
        ], 201);
    }

    // PATCH /api/entregas-realizadas/{id}
    public function update(Request $request, $id)
    {
        $entrega = EntregasRealizadas::find($id);

        if (!$entrega) {
            return response()->json(['message' => 'Entrega no encontrada'], 404);
        }

        $request->validate([
            'id_entrega'    => 'sometimes|uuid|exists:entrega_docente,id',
            'id_docente'    => 'sometimes|uuid|exists:docente,dni',
            'archivo'       => 'nullable|string|max:255',
            'fecha_entrega' => 'sometimes|date',
            'observacion'   => 'nullable|string|max:255',
        ]);

        $entrega->update($request->all());

        return response()->json([
            'message' => 'Entrega actualizada correctamente',
            'data' => $entrega
        ]);
    }

    // DELETE /api/entregas-realizadas/{id}
    public function destroy($fileId)
    {
        try {
            DB::beginTransaction();

            // 1. Buscar registro en BD por fileId (archivo)
            $registro = EntregasRealizadas::where('archivo', $fileId)->first();

            if (!$registro) {
                return response()->json(['error' => 'Archivo no encontrado en base de datos'], 404);
            }

            // 2. Obtener la entrega asociada
            $entrega = EntregaDocente::find($registro->id_entrega);

            // 3. Eliminar archivo en Google Drive usando tu controlador
            $response = $this->googleDriveController->deleteFile($fileId);

            if ($response->getStatusCode() !== 204) {
                throw new \Exception("No se pudo eliminar el archivo del Drive");
            }

            // 4. Eliminar registro en BD
            $registro->delete();

            // 5. Verificar si aún quedan archivos
            $quedanArchivos = EntregasRealizadas::where('id_entrega', $entrega->id)->exists();

            if (!$quedanArchivos) {
                // actualizar cumplio = 0
                $entrega->update(['cumplio' => 0]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Archivo eliminado correctamente'
            ], 204);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => 'Error al eliminar archivo',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

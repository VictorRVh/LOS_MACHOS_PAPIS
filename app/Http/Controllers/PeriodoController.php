<?php

namespace App\Http\Controllers;

use App\Models\CarpetasPeriodoDrive;
use App\Models\Periodo;
use App\Models\ProgramaEstudio;
use Illuminate\Http\Request;
use App\Traits\Helpers;
use Illuminate\Support\Facades\DB;

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

        // $gruposExistentes = Periodo::where('nombre_periodo', $request->nombre_periodo)->exists();

        // if ($gruposExistentes) {
        //     // return response()->json([
        //     //     'error' => 'Ya existe un periodo con ese nombre.'
        //     // ], 422);
        //     throw new \Exception('Error|Ya existe un periodo con ese nombre--404', 13333);
        // }

        DB::beginTransaction();

        try {

            // 1️⃣ Crear el periodo en BD (aún sin commit)
            $periodo = Periodo::create([
                'nombre_periodo' => $request->nombre_periodo,
                'status' => $request->status,
            ]);

            // 2️⃣ Carpeta madre fija en Google Drive
            $parentFolderId = '0AJvk3zt0rp4dUk9PVA'; 

            $driveController = new GoogleDriveController();

            $folderRequest = new Request([
                'folderName'     => $periodo->nombre_periodo,
                'parentFolderId' => $parentFolderId,
            ]);

            // 3️⃣ Crear carpeta del periodo en Drive
            $response = $driveController->createFolder($folderRequest);

            if ($response->status() !== 201) {

                \Log::error("Error creando carpeta del periodo en Drive: " . $response->getContent());


                return response()->json([
                    'errorCode' => 13333,
                    'errorMessage' => 'Error creando carpeta del periodo en Drive',
                    'errorText' => $response->getContent()
                ], 500);
            }

            $data = $response->getData();

            // 4️⃣ Guardar en carpetas_periodo_drive
            CarpetasPeriodoDrive::create([
                'id_periodo'      => $periodo->id,
                'drive_folder_id' => $data->id,
                'nombre_carpeta'  => $periodo->nombre_periodo,
            ]);

            // 5️⃣ Registrar actividad
            $this->registrarActividad(
                "Creó el periodo '{$periodo->nombre_periodo}'",
                "Creado"
            );

            DB::commit(); // ✔ Todo correcto

            return response()->json($periodo, 201);
        } catch (\Exception $e) {

            DB::rollBack(); // ❗REVERSA TODO (el periodo no queda creado)

            throw new \Exception(
                '⚠️ Error creando periodo y su carpeta: ' . $e->getMessage(),
                13333
            );
        }
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

    public function getAnios()
    {
        $registros = ProgramaEstudio::select('año')->distinct()->pluck('año');

        $anios = [];

        foreach ($registros as $valor) {

            // Si es un rango (ej: "2031-2032")
            if (strpos($valor, '-') !== false) {
                [$inicio, $fin] = explode('-', $valor);

                for ($i = (int)$inicio; $i <= (int)$fin; $i++) {
                    $anios[] = $i;
                }
            } else {
                $anios[] = (int)$valor;
            }
        }

        // Eliminar duplicados y ordenar
        $anios = array_unique($anios);
        sort($anios);

        // Convertir a array de objetos con "anio"
        $resultado = array_map(function ($anio) {
            return ['anio' => (string)$anio];
        }, $anios);

        return $resultado;
    }

    public function getPeriodosFiltrados($anio)
    {
        return DB::table('grupo')
            ->join('periodo', 'periodo.id', '=', 'grupo.id_periodo')
            ->join('programa_estudio', 'programa_estudio.id', '=', 'grupo.id_programa')
            ->where('programa_estudio.año', $anio)
            ->select('periodo.id', 'periodo.nombre_periodo')
            ->distinct()
            ->get();
    }
    
}

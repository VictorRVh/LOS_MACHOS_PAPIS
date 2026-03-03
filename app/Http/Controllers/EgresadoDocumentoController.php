<?php

namespace App\Http\Controllers;

use App\Models\EgresadoDocumento;
use App\Traits\Error;
use App\Traits\Helpers;
use Illuminate\Http\Request;

class EgresadoDocumentoController extends Controller
{
    use Error, Helpers;

    /**
     * Listar documentos
     */
    /**
     * Verificar si el egresado ya tiene ese documento
     */
    public function verificarDocumento(Request $request)
    {
        try {

            $validated = $request->validate([
                'id_egresado' => ['required', 'uuid', 'exists:egresados,id'],
                'tipo_documento' => ['required', 'integer', 'in:1,2'],
            ]);

            $documento = EgresadoDocumento::with(['egresado', 'autor'])
                ->where('id_egresado', $validated['id_egresado'])
                ->where('tipo_documento', $validated['tipo_documento'])
                ->first();

            return response()->json([
                'existe' => (bool) $documento,
                'data' => $documento ? [
                'codigo_institucion' => $documento->codigo_institucion,
                'codigo_ugel'        => $documento->codigo_ugel,
                'tipo'             => $documento->tipo_documento,
            ] : null
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'message' => $error->getMessage()
            ], 500);
        }
    }
    /**
     * Crear documento
     */
    public function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'id_egresado' => ['required', 'uuid', 'exists:egresados,id'],
                'tipo_documento' => ['required', 'integer', 'in:1,2'],
                'codigo_institucion' => ['nullable', 'string', 'max:50'],
                'codigo_ugel' => ['nullable', 'string', 'max:50'],
            ]);

            $fecha_emision = now();
            $codigo = $validated['codigo_institucion'] ?? null;

            $duplicado = 0;

            if ($codigo) {

                $existe = EgresadoDocumento::where('codigo', $codigo)
                    ->where('tipo_documento', $validated['tipo_documento'])
                    ->exists();

                if ($existe) {
                    $duplicado = 1;
                }
            }

            $documento = new EgresadoDocumento();

            $documento->id_egresado = $validated['id_egresado'];
            $documento->tipo_documento = $validated['tipo_documento'];
            $documento->fecha_emision = $fecha_emision;
            $documento->codigo_institucion = $validated['codigo_institucion'] ?? null;
            $documento->codigo_ugel = $validated['codigo_ugel'] ?? null;
            $documento->codigo = $codigo;
            $documento->duplicado = $duplicado;
            $documento->id_autor = auth()->id();

            $documento->save();

            // 🔥 MENSAJE SEGÚN SI ES DUPLICADO O NO
            $mensaje = $duplicado
                ? "Creó CONSTANCIA DUPLICADA con código '{$documento->codigo}'"
                : "Creó constancia con código '{$documento->codigo}'";

            $this->registrarActividad($mensaje, "Creado");

            return response()->json([
                'success' => true,
                'duplicado' => (bool) $duplicado,
                'data' => $documento
            ], 201);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }

    /**
     * Mostrar documento
     */
    public function show(Request $request)
    {
        try {

            $documento = EgresadoDocumento::with(['egresado', 'autor'])
                ->find($request->id);

            if (!$documento) {
                throw new \Exception('Error|Documento no encontrado--404', 13333);
            }

            return response()->json($documento);
        } catch (\Exception $error) {

            return $this->errorResponse($error);
        }
    }

    /**
     * Actualizar documento
     */
    public function update(Request $request)
    {
        try {

            $validated = $request->validate([
                'id' => ['required', 'uuid', 'exists:egresado_documento,id'],
                'tipo_documento' => ['nullable', 'integer'],
                'fecha_emision' => ['nullable', 'date'],
                'codigo_institucion' => ['nullable', 'string', 'max:50'],
                'codigo_ugel' => ['nullable', 'string', 'max:50'],
                'codigo' => ['nullable', 'string'],
                'duplicado' => ['nullable', 'integer']
            ]);

            $documento = EgresadoDocumento::find($request->id);

            if (!$documento) {
                throw new \Exception('Error|Documento no encontrado--404', 13333);
            }

            foreach ($validated as $key => $value) {
                if ($key !== 'id') {
                    $documento->{$key} = $value;
                }
            }

            $documento->save();

            // Registrar actividad
            $this->registrarActividad(
                "Actualizó documento de egresado '{$documento->codigo}'",
                "Actualizado"
            );

            return response()->json($documento);
        } catch (\Exception $error) {

            return $this->errorResponse($error);
        }
    }

    /**
     * Eliminar documento
     */
    public function destroy(Request $request)
    {
        try {

            $documento = EgresadoDocumento::find($request->id);

            if (!$documento) {
                throw new \Exception('Error|Documento no encontrado--404', 13333);
            }

            $codigo = $documento->codigo;

            $documento->delete();

            // Registrar actividad
            $this->registrarActividad(
                "Eliminó documento de egresado '{$codigo}'",
                "Eliminado"
            );

            return response()->json([], 204);
        } catch (\Exception $error) {

            return $this->errorResponse($error);
        }
    }
}

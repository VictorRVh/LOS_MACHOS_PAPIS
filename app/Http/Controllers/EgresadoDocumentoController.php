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
    public function index(Request $request)
    {
        try {

            $documentos = EgresadoDocumento::with(['egresado', 'autor'])->get();

            return response()->json($documentos);
        } catch (\Exception $error) {

            return $this->errorResponse($error);
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
                'tipo_documento' => ['required', 'integer'],
                'codigo_institucion' => ['nullable', 'string', 'max:50'],
                'codigo_ugel' => ['nullable', 'string', 'max:50'],
            ]);

            // Fecha automática
            $fecha_emision = now();

            // Codigo base (viene del frontend)
            $codigo = $validated['codigo_institucion'] ?? null;

            // Verificar si ya existe ese codigo
            $duplicado = 0;

            if ($codigo) {

                $existe = EgresadoDocumento::where('codigo', $codigo)
                    ->where('tipo_documento', $validated['tipo_documento'])
                    ->exists();

                if ($existe) {
                    $duplicado = 1;
                }
            }

            // Crear documento
            $documento = new EgresadoDocumento();

            $documento->id_egresado = $validated['id_egresado'];
            $documento->tipo_documento = $validated['tipo_documento'];
            $documento->fecha_emision = $fecha_emision;
            $documento->codigo_institucion = $validated['codigo_institucion'] ?? null;
            $documento->codigo_ugel = $validated['codigo_ugel'] ?? null;

            // codigo principal
            $documento->codigo = $codigo;

            // duplicado automatico
            $documento->duplicado = $duplicado;

            // autor automatico
            $documento->id_autor = auth()->id();

            $documento->save();

            // Registrar actividad
            $this->registrarActividad(
                "Creó documento de egresado con código '{$documento->codigo}'",
                "Creado"
            );

            return response()->json([
                'success' => true,
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

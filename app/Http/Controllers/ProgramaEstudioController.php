<?php

namespace App\Http\Controllers;

use App\Models\ProgramaEstudio;
use Illuminate\Http\Request;
use App\Traits\Helpers;

class ProgramaEstudioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use Helpers;
    public function index()
    {
        $programas = ProgramaEstudio::with('ciclo')
            ->where('is_deleted', 0)
            ->latest()
            ->get();

        if ($programas->isEmpty()) {
            return response()->json(['message' => 'No hay programas de estudio disponibles'], 404);
        }

        // Procesar los programas: limpiar datos y agregar campo nameCiclo
        $programasProcesados = $programas->map(function ($programa) {
            $nuevoPrograma = $programa->toArray();

            $nombreCiclo = $programa->ciclo->nombre_ciclo ?? 'Ciclo desconocido';
            $nuevoPrograma['nameCiclo'] = $nombreCiclo;

            unset($nuevoPrograma['ciclo']);
            unset($nuevoPrograma['created_at']);
            unset($nuevoPrograma['updated_at']);

            return $nuevoPrograma;
        });

        return response()->json([
            'programas' => $programasProcesados,
        ]);
    }

    public function index_filter_status()
    {
        $programas = ProgramaEstudio::with('ciclo')
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->get();

        if ($programas->isEmpty()) {
            return response()->json(['message' => 'No hay programas de estudio activos'], 404);
        }

        // Procesar los programas: limpiar datos y agregar campo nameCiclo
        $programasProcesados = $programas->map(function ($programa) {
            $nuevoPrograma = $programa->toArray();

            $nombreCiclo = $programa->ciclo->nombre_ciclo ?? 'Ciclo desconocido';
            $nuevoPrograma['nameCiclo'] = $nombreCiclo . ' ' . $programa->año;

            unset($nuevoPrograma['ciclo']);
            unset($nuevoPrograma['año']);
            unset($nuevoPrograma['numero_rd']);
            unset($nuevoPrograma['status']);
            unset($nuevoPrograma['id_ciclo']);
            unset($nuevoPrograma['descripcion']);
            unset($nuevoPrograma['created_at']);
            unset($nuevoPrograma['updated_at']);

            return $nuevoPrograma;
        });

        return response()->json([
            'programas' => $programasProcesados,
        ]);
    }

    // Mostrar uno por ID
    public function show($id)
    {
        $programa = ProgramaEstudio::with('ciclo')->find($id);

        if (!$programa) {
            return response()->json(['message' => 'Programa de estudio no encontrado'], 404);
        }

        return response()->json($programa);
    }

    // Crear nuevo programa
    public function store(Request $request)
    {
        $request->validate([
            'id_ciclo' => 'required|exists:ciclo_academico,id',
            // 'año'         => 'required|integer|min:2000|max:2100',
            'año' => 'required|string',
            'numero_rd' => 'required|string|max:50',
            'status' => 'required|integer|in:0,1,2,3',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $programa = ProgramaEstudio::create($request->all());
        // Registrar actividad
        $this->registrarActividad("Creó el programa '{$programa->numero_rd}' con año '{$programa->año}' ", "Creado");

        return response()->json($programa, 201);
    }

    // Actualizar un programa existente
    public function update(Request $request, $id)
    {
        $programa = ProgramaEstudio::find($id);

        if (!$programa) {
            return response()->json(['message' => 'Programa de estudio no encontrado'], 404);
        }


        $request->validate([
            'id_ciclo' => 'sometimes|required|exists:ciclo_academico,id',
            'año' => ['sometimes', 'required', 'regex:/^\d{4}(-\d{4})?$/'],
            'numero_rd' => 'sometimes|required|string|max:50',
            'status' => 'sometimes|required|integer|in:0,1,2,3',
            'descripcion' => 'nullable|string|max:255',
        ]);

        // Guardamos los valores anteriores
        $original = $programa->toArray();

        // Actualizamos
        $programa->update($request->all());

        // Campos que queremos mostrar en el registro
        $camposImportantes = ['id_ciclo', 'año', 'numero_rd', 'status', 'descripcion'];

        $cambios = [];
        foreach ($programa->getChanges() as $campo => $nuevoValor) {
            if (in_array($campo, $camposImportantes)) {
                $valorAnterior = $original[$campo] ?? 'N/A';
                // Si es id_ciclo, podemos obtener el nombre del ciclo
                if ($campo === 'id_ciclo') {
                    $valorAnterior = optional($programa->ciclo()->find($valorAnterior))->nombre_ciclo ?? 'N/A';
                    $nuevoValor = optional($programa->ciclo)->nombre_ciclo ?? 'N/A';
                }
                $cambios[] = ucfirst($campo) . ": '{$valorAnterior}' → '{$nuevoValor}'";
            }
        }

        // Registrar actividad con formato bonito
        if (!empty($cambios)) {
            $this->registrarActividad(
                "Actualizó el programa '{$request->nameCiclo}' - Número R.D. : '{$programa->numero_rd}' (" . implode(', ', $cambios) . ")",
                "Actualizado"
            );
        } else {
            $this->registrarActividad(
                "Actualizó el programa '{$request->nameCiclo}' Número R.D. : '{$programa->numero_rd}' sin cambios visibles",
                "Actualizado"
            );
        }

        return response()->json($programa);
    }




    // Eliminar un programa
    public function destroy($id)
    {
        $programa = ProgramaEstudio::find($id);

        if (!$programa) {
            return response()->json(['message' => 'Programa de estudio no encontrado'], 404);
        }

        $programa->is_deleted = 1;
        $programa->save();
        $this->registrarActividad("Eliminó el programa '{$programa->numero_rd}' del '{$programa->año}'", "Eliminado");

        return response()->json(['message' => 'Programa eliminado correctamente'], 200);
    }
}

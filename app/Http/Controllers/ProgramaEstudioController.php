<?php

namespace App\Http\Controllers;

use App\Models\ProgramaEstudio;
use Illuminate\Http\Request;

class ProgramaEstudioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $programas = ProgramaEstudio::with('ciclo')
    ->latest() // equivale a orderBy('created_at', 'desc')
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
            ->where('status', 1) // 👈 Solo programas activos
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
            'año' => 'sometimes|required|integer|min:2000|max:2100',
            'año' => 'sometimes|required|string',
            'numero_rd' => 'sometimes|required|string|max:50',
            'status' => 'sometimes|required|integer|in:0,1,2,3',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $programa->update($request->all());

        return response()->json($programa);
    }

    // Eliminar un programa
    public function destroy($id)
    {
        $programa = ProgramaEstudio::find($id);

        if (!$programa) {
            return response()->json(['message' => 'Programa de estudio no encontrado'], 404);
        }

        $programa->delete();

        return response()->json(['message' => 'Programa eliminado correctamente'], 204);
    }
}

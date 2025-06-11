<?php

namespace App\Http\Controllers;

use App\Models\EspecialidadMadre;
use Illuminate\Http\Request;

class EspecialidadMadreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $especialidades = EspecialidadMadre::with('cicloAcademico')->get();
        return response()->json($especialidades);
    }

    // Mostrar uno por ID
    public function show($id)
    {
        $especialidad = EspecialidadMadre::with('cicloAcademico')->find($id);

        if (!$especialidad) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }

        return response()->json($especialidad);
    }

    // Crear nueva especialidad
    public function store(Request $request)
    {
        $request->validate([
            'nombre_especialidad' => 'required|string|max:100',
            'id_ciclo' => 'required|exists:ciclo_academico,id',
        ]);

        $especialidad = EspecialidadMadre::create($request->all());

        return response()->json($especialidad, 201);
    }

    // Actualizar especialidad existente
    public function update(Request $request, $id)
    {
        $especialidad = EspecialidadMadre::find($id);

        if (!$especialidad) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }

        $request->validate([
            'nombre_especialidad' => 'sometimes|required|string|max:100',
            'id_ciclo' => 'sometimes|required|exists:ciclo_academico,id',
        ]);

        $especialidad->update($request->all());

        return response()->json($especialidad);
    }

    // Eliminar especialidad
    public function destroy($id)
    {
        $especialidad = EspecialidadMadre::find($id);

        if (!$especialidad) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }

        $especialidad->delete();

        return response()->json(['message' => 'Especialidad eliminada']);
    }
}

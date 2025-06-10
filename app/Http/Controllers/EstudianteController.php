<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;

class EstudianteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $estudiantes = Estudiante::with([
            'matricula',
            'asistencia',
            'notaCapacidadTerminal',
            'notaExperienciaFormativa',
            'egresados'
        ])->get();

        return response()->json($estudiantes, 200);
    }

    /**
     * Guarda un nuevo estudiante.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo_documento' => 'required|string|max:20',
            'nro_documento' => 'required|string|max:15|unique:estudiante,nro_documento',
            'apellido_paterno' => 'required|string|max:50',
            'apellido_materno' => 'required|string|max:50',
            'nombre' => 'required|string|max:100',
            'sexo' => 'required|in:M,F',
            'pais_nacimiento' => 'required|string|max:100',
            'departamento_nacimiento' => 'required|string|max:100',
            'provincia_nacimiento' => 'required|string|max:100',
            'distrito_nacimiento' => 'required|string|max:100',
            'lugar_nacimiento' => 'required|string|max:100',
            'direccion_residencia' => 'required|string|max:100',
            'fecha_nacimiento' => 'required|string|max:100',
            'estado_civil' => 'required|string|max:100',
            'grado_instruccion' => 'required|string|max:100',
            'trabaja' => 'required|string|max:100',
            'puesto_trabajo' => 'required|string|max:100',
            'carga_familiar' => 'required|string|max:100',
            'correo_electronico' => 'required|string|max:100',
            'correo_electronico' => 'required|string|max:100',
            'celular_personal' => 'required|string|max:100',
            'internet_casa' => 'required|string|max:100',
            'tipo_operador' => 'required|string|max:100',
            'equipo_clases' => 'required|string|max:100',
            'discapacidad' => 'required|string|max:100',
            'celular_referencia' => 'required|string|max:100',
            'parentesco_referencia' => 'required|string|max:100',
            'lengua_originaria' => 'required|string|max:100',
        ]);

        $estudiante = Estudiante::create($request->all());

        return response()->json($estudiante, 201);
    }

    /**
     * Muestra un estudiante específico por ID con relaciones.
     */
    public function show(string $id)
    {
        $estudiante = Estudiante::with([
            'matricula',
            'asistencia',
            'notaCapacidadTerminal',
            'notaExperienciaFormativa',
            'egresados'
        ])->findOrFail($id);

        return response()->json($estudiante, 200);
    }

    /**
     * Actualiza un estudiante existente.
     */
    public function update(Request $request, string $id)
    {
        $estudiante = Estudiante::findOrFail($id);

        $request->validate([
            'tipo_documento' => 'required|string|max:20',
            'nro_documento' => 'required|string|max:15|unique:estudiante,nro_documento',
            'apellido_paterno' => 'required|string|max:50',
            'apellido_materno' => 'required|string|max:50',
            'nombre' => 'required|string|max:100',
            'sexo' => 'required|in:M,F',
            'pais_nacimiento' => 'required|string|max:100',
            'departamento_nacimiento' => 'required|string|max:100',
            'provincia_nacimiento' => 'required|string|max:100',
            'distrito_nacimiento' => 'required|string|max:100',
            'lugar_nacimiento' => 'required|string|max:100',
            'direccion_residencia' => 'required|string|max:100',
            'fecha_nacimiento' => 'required|date',
            'estado_civil' => 'required|string|max:100',
            'grado_instruccion' => 'required|string|max:100',
            'trabaja' => 'required|string|max:100',
            'puesto_trabajo' => 'required|string|max:100',
            'carga_familiar' => 'required|string|max:100',
            'correo_electronico' => 'required|string|max:100',
            'correo_electronico' => 'required|string|max:100',
            'celular_personal' => 'required|string|max:100',
            'internet_casa' => 'required|string|max:100',
            'tipo_operador' => 'required|string|max:100',
            'equipo_clases' => 'required|string|max:100',
            'discapacidad' => 'required|string|max:100',
            'celular_referencia' => 'required|string|max:100',
            'parentesco_referencia' => 'required|string|max:100',
            'lengua_originaria' => 'required|string|max:100',
        ]);
        $estudiante->update($request->all());

        return response()->json($estudiante, 200);
    }

    /**
     * Elimina un estudiante.
     */
    public function destroy(string $id)
    {
        $estudiante = Estudiante::findOrFail($id);
        $estudiante->delete();

        return response()->json(null, 204);
    }
}

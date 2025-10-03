<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
            // 'sexo' => 'required|in:M,F',
            'sexo' => 'required',
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
            'tipo_documento' => 'sometimes|string|max:20',
            'nro_documento' => 'sometimes|string|max:15|unique:estudiante,nro_documento',
            'apellido_paterno' => 'sometimes|string|max:50',
            'apellido_materno' => 'sometimes|string|max:50',
            'nombre' => 'sometimes|string|max:100',
            'sexo' => 'sometimes|in:M,F',
            'pais_nacimiento' => 'sometimes|string|max:100',
            'departamento_nacimiento' => 'sometimes|string|max:100',
            'provincia_nacimiento' => 'sometimes|string|max:100',
            'distrito_nacimiento' => 'sometimes|string|max:100',
            'lugar_nacimiento' => 'sometimes|string|max:100',
            'direccion_residencia' => 'sometimes|string|max:100',
            'fecha_nacimiento' => 'sometimes|date',
            'estado_civil' => 'sometimes|string|max:100',
            'grado_instruccion' => 'sometimes|string|max:100',
            'trabaja' => 'sometimes|string|max:100',
            'puesto_trabajo' => 'sometimes|string|max:100',
            'carga_familiar' => 'sometimes|string|max:100',
            'correo_electronico' => 'sometimes|string|max:100',
            'correo_electronico' => 'sometimes|string|max:100',
            'celular_personal' => 'sometimes|string|max:100',
            'internet_casa' => 'sometimes|string|max:100',
            'tipo_operador' => 'sometimes|string|max:100',
            'equipo_clases' => 'sometimes|string|max:100',
            'discapacidad' => 'sometimes|string|max:100',
            'celular_referencia' => 'sometimes|string|max:100',
            'parentesco_referencia' => 'sometimes|string|max:100',
            'lengua_originaria' => 'sometimes|string|max:100',
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

    public function buscar(Request $request)
    {
        $dni = $request->input('dni');

        if (strlen($dni) !== 8) {
            return response()->json(['error' => 'DNI inválido'], 422);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIzOTE3MSIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vd3MvMjAwOC8wNi9pZGVudGl0eS9jbGFpbXMvcm9sZSI6ImNvbnN1bHRvciJ9.MUQqU8axqigAZZXN-TOWTmSVrFsrQIXpujPPvgPDqBU'
            ])->get("https://api.factiliza.com/v1/dni/info/{$dni}");

            if ($response->failed()) {
                return response()->json(['error' => 'No se pudo consultar el DNI'], 500);
            }

            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en la consulta', 'message' => $e->getMessage()], 500);
        }
    }
}

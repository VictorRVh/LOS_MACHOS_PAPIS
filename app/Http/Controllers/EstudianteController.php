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

    // public function buscar(Request $request)
    // {
    //     $tipo = $request->input('tipo_documento'); // 'DNI' o 'CARNET EXT.'
    //     $numero = $request->input('dni'); // o 'nro_documento'

    //     if (empty($numero)) {
    //         return response()->json(['error' => 'Debe ingresar un número de documento'], 422);
    //     }

    //     // Validar formato según tipo de documento
    //     if ($tipo === 'DNI' && strlen($numero) !== 8) {
    //         return response()->json(['error' => 'DNI inválido'], 422);
    //     }

    //     if ($tipo === 'CARNET EXT.' && strlen($numero) < 9) {
    //         return response()->json(['error' => 'Carnet de extranjería inválido'], 422);
    //     }

    //     try {
    //         // Determinar URL según el tipo
    //         $endpoint = $tipo === 'DNI'
    //             ? "https://api.factiliza.com/v1/dni/info/{$numero}"
    //             : "https://api.factiliza.com/v1/cee/info/{$numero}";

    //         $response = Http::withHeaders([
    //             'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIzOTE3MSIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vd3MvMjAwOC8wNi9pZGVudGl0eS9jbGFpbXMvcm9sZSI6ImNvbnN1bHRvciJ9.MUQqU8axqigAZZXN-TOWTmSVrFsrQIXpujPPvgPDqBU'
    //         ])->get($endpoint);

    //         if ($response->failed()) {
    //             return response()->json(['error' => 'No se pudo consultar el documento'], 500);
    //         }

    //         return $response->json();
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => 'Error en la consulta',
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function buscar(Request $request)
    {
        $tipo = $request->input('tipo_documento');
        $numero = $request->input('dni');

        if (empty($numero)) {
            return response()->json(['error' => 'Debe ingresar un número de documento'], 422);
        }

        // Validaciones básicas
        if ($tipo === 'DNI' && strlen($numero) !== 8) {
            return response()->json(['error' => 'DNI inválido'], 422);
        }

        if ($tipo === 'CARNET EXT.' && strlen($numero) < 9) {
            return response()->json(['error' => 'Carnet de extranjería inválido'], 422);
        }

        // Buscamos al estudiante en nuestra base de datos
        $estudiante = Estudiante::where('nro_documento', $numero)->first();

        if ($estudiante) {
            return response()->json([
                'success' => true,
                'source' => 'database',
                'data' => $estudiante
            ]);
        }

        // Si el estudiante no existe en nuestra BD, consultamos al FACTILIZA
        try {
            $endpoint = $tipo === 'DNI'
                ? "https://api.factiliza.com/v1/dni/info/{$numero}"
                : "https://api.factiliza.com/v1/cee/info/{$numero}";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIzOTE3MSIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vd3MvMjAwOC8wNi9pZGVudGl0eS9jbGFpbXMvcm9sZSI6ImNvbnN1bHRvciJ9.MUQqU8axqigAZZXN-TOWTmSVrFsrQIXpujPPvgPDqBU'
            ])->get($endpoint);

            if ($response->failed()) {
                return response()->json(['error' => 'No se pudo consultar el documento'], 500);
            }

            return $response->json();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error en la consulta',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

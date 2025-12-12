<?php

namespace App\Http\Controllers;

use App\Models\EspecialidadPrograma;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // -------------------------------
        // 1️⃣ BUSCAR EN BD
        // -------------------------------
        $estudiante = Estudiante::where('nro_documento', $numero)->first();

        if ($estudiante) {
            return response()->json([
                'success' => true,
                'source' => 'database',
                'data' => $estudiante  // Devuelve TODOS los campos exactos de tu tabla
            ]);
        }

        // -------------------------------
        // 2️⃣ CONSULTAR A FACTILIZA
        // -------------------------------
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

            $data = $response->json()['data'];

            // ---------------------------------------
            // 3️⃣ MAPEAR DATOS FACTILIZA → CAMPOS BD
            // ---------------------------------------
            function coalesce_non_empty(...$values)
            {
                foreach ($values as $v) {
                    if (isset($v) && $v !== '' && $v !== null) {
                        return $v;
                    }
                }
                return null;
            }
            $mapped = [
                'tipo_documento' => $tipo,
                'nro_documento' => $data['numero'] ?? $numero,
                'apellido_paterno' => $data['apellido_paterno'] ?? null,
                'apellido_materno' => $data['apellido_materno'] ?? null,
                'nombre' => $data['nombres'] ?? null,
                'sexo' => $data['sexo'] ?? null,
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                'departamento_nacimiento' => $data['departamento'] ?? null,
                'provincia_nacimiento' => $data['provincia'] ?? null,
                'distrito_nacimiento' => $data['distrito'] ?? null,
                'direccion_residencia' => coalesce_non_empty(
                    $data['direccion_completa'] ?? null,
                    $data['direccion'] ?? null
                ),
            ];

            return response()->json([
                'success' => true,
                'source' => 'factiliza',
                'data' => $mapped
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error en la consulta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function buscarHistorialEstudiante(Request $request)
    {
        // Validación
        $request->validate([
            'nro_documento' => 'required|string'
        ]);

        // Buscar estudiante
        $estudiante = Estudiante::where('nro_documento', $request->nro_documento)->first();

        if (!$estudiante) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado'
            ], 404);
        }

        // Obtener historial académico con JOINs completos
        $informacionAcademica = DB::table('matricula as m')
            ->join('grupo as g', 'm.id_grupo', '=', 'g.id')
            ->join('especialidad_programa as ep', 'g.id_especialidad', '=', 'ep.id')
            ->join('especialidad_madre as em', 'ep.id_especialidad', '=', 'em.id')
            ->join('periodo as p', 'g.id_periodo', '=', 'p.id')
            ->join('modulos as mod', 'g.id_modulo', '=', 'mod.id')
            ->join('programa_estudio as pe', 'g.id_programa', '=', 'pe.id')
            // ->leftJoin('docente as d', 'g.id_docente', '=', 'd.id')
            ->where('m.id_estudiante', $estudiante->id)

            ->select(
                // Matricula
                'm.id as matricula_id',
                'm.turno as matricula_turno',
                'm.reserva',
                'm.fecha_reserva',
                'm.matriculado',

                // Grupo
                'g.id as grupo_id',
                'g.seccion',
                'g.turno as grupo_turno',
                'g.fecha_inicio',
                'g.fecha_fin',
                'g.status as grupo_status',

                // Especialidad
                'em.id as especialidad_id',
                'em.nombre_especialidad',
                'ep.nro_modulos as total_modulos_especialidad',

                // Programa
                'pe.id as programa_id',
                'pe.descripcion as nombre_programa',

                // Periodo
                'p.id as periodo_id',
                'p.nombre_periodo',

                // Módulo
                'mod.id as modulo_id',
                'mod.numero_modulo',
                'mod.descripcion as modulo_descripcion',
                'mod.creditos',
                'mod.horas',
                'mod.nro_capacidades',

                // Docente
                // 'd.id as docente_id',
                // 'd.apellido_paterno as docente_apellido_paterno',
                // 'd.apellido_materno as docente_apellido_materno',
                // 'd.nombre as docente_nombre',
            )
            ->orderBy('p.nombre_periodo', 'desc')
            ->orderBy('mod.numero_modulo', 'asc')
            ->get();

        // Construir estructura jerárquica
        $especialidades = [];

        foreach ($informacionAcademica as $registro) {

            $espId = $registro->especialidad_id;

            // Crear especialidad si no existe
            if (!isset($especialidades[$espId])) {
                $especialidades[$espId] = [
                    'id' => $registro->especialidad_id,
                    'nombre' => $registro->nombre_especialidad,
                    'programa' => [
                        'id' => $registro->programa_id,
                        'nombre' => $registro->nombre_programa
                    ],
                    'total_modulos' => $registro->total_modulos_especialidad,
                    'periodos' => []
                ];
            }

            $periodoId = $registro->periodo_id;

            // Crear periodo si no existe
            if (!isset($especialidades[$espId]['periodos'][$periodoId])) {
                $especialidades[$espId]['periodos'][$periodoId] = [
                    'id' => $registro->periodo_id,
                    'nombre' => $registro->nombre_periodo,
                    'modulos' => []
                ];
            }

            // Agregar módulo dentro del periodo
            $especialidades[$espId]['periodos'][$periodoId]['modulos'][] = [
                'matricula_id' => $registro->matricula_id,

                'modulo' => [
                    'id' => $registro->modulo_id,
                    'numero' => $registro->numero_modulo,
                    'descripcion' => $registro->modulo_descripcion,
                    'creditos' => $registro->creditos,
                    'horas' => $registro->horas,
                    'nro_capacidades' => $registro->nro_capacidades
                ],

                'grupo' => [
                    'id' => $registro->grupo_id,
                    'seccion' => $registro->seccion,
                    'turno' => $registro->grupo_turno,
                    'fecha_inicio' => $registro->fecha_inicio,
                    'fecha_fin' => $registro->fecha_fin,
                    'status' => $registro->grupo_status
                ],

                // 'docente' => $registro->docente_id ? [
                //     'id' => $registro->docente_id,
                //     'nombre_completo' => trim(
                //         "{$registro->docente_apellido_paterno} {$registro->docente_apellido_materno} {$registro->docente_nombre}"
                //     )
                // ] : null,

                'matricula' => [
                    'turno' => $registro->matricula_turno,
                    'reserva' => (bool)$registro->reserva,
                    'fecha_reserva' => $registro->fecha_reserva,
                    'matriculado' => (bool)$registro->matriculado
                ]
            ];
        }

        // Convertir índices asociativos a índices numéricos
        $especialidades = array_values($especialidades);
        foreach ($especialidades as &$especialidad) {
            $especialidad['periodos'] = array_values($especialidad['periodos']);
        }

        // Respuesta final
        return response()->json([
            'success' => true,
            'data' => [
                'estudiante' => [
                    'id' => $estudiante->id,
                    'tipo_documento' => $estudiante->tipo_documento,
                    'nro_documento' => $estudiante->nro_documento,
                    'apellido_paterno' => $estudiante->apellido_paterno,
                    'apellido_materno' => $estudiante->apellido_materno,
                    'nombre' => $estudiante->nombre,
                    'nombre_completo' => trim("{$estudiante->apellido_paterno} {$estudiante->apellido_materno} {$estudiante->nombre}"),
                    'sexo' => $estudiante->sexo,
                    'fecha_nacimiento' => $estudiante->fecha_nacimiento,
                    'lugar_nacimiento' => [
                        'pais' => $estudiante->pais_nacimiento,
                        'departamento' => $estudiante->departamento_nacimiento,
                        'provincia' => $estudiante->provincia_nacimiento,
                        'distrito' => $estudiante->distrito_nacimiento
                    ]
                ],
                'historial_academico' => $especialidades
            ]
        ]);
    }

    public function getEgresados(Request $request)
    {
        $request->validate([
            'especialidad' => 'required|uuid',
            'periodo' => 'required|uuid',
        ]);

        $especialidadMadreId = $request->query('especialidad');
        $periodoId = $request->query('periodo');

        // Obtener datos de la especialidad madre
        $especialidadMadre = DB::table('especialidad_madre')
            ->where('id', $especialidadMadreId)
            ->first(['id', 'nombre_especialidad']);

        // Obtener datos del periodo
        $periodo = DB::table('periodo')
            ->where('id', $periodoId)
            ->first(['id', 'nombre_periodo']);

        // 1. Obtener programas vinculados
        $especialidadesPrograma = DB::table('especialidad_programa')
            ->where('id_especialidad', $especialidadMadreId)
            ->pluck('id');

        if ($especialidadesPrograma->isEmpty()) {
            return response()->json([
                'especialidad' => $especialidadMadre,
                'periodo' => $periodo,
                'egresados' => []
            ]);
        }

        // 2. Obtener grupos culminados
        $grupos = DB::table('grupo')
            ->whereIn('id_especialidad', $especialidadesPrograma)
            ->where('id_periodo', $periodoId)
            ->where('status', 2)
            ->get();

        if ($grupos->isEmpty()) {
            return response()->json([
                'especialidad' => $especialidadMadre,
                'periodo' => $periodo,
                'egresados' => []
            ]);
        }

        // 3. Módulos requeridos
        $modulosRequeridos = DB::table('modulos')
            ->whereIn('id_especialidad', $especialidadesPrograma)
            ->pluck('id')
            ->toArray();

        $totalModulos = count($modulosRequeridos);

        // 4. Matriculados
        $matriculas = DB::table('matricula')
            ->whereIn('id_grupo', $grupos->pluck('id'))
            ->get();

        if ($matriculas->isEmpty()) {
            return response()->json([
                'especialidad' => $especialidadMadre,
                'periodo' => $periodo,
                'egresados' => []
            ]);
        }

        $modulosCursados = [];

        foreach ($matriculas as $mat) {
            $modulosCursados[$mat->id_estudiante][] = $mat->id_grupo;
        }

        $egresados = [];

        // 5. Validar módulos completados
        foreach ($modulosCursados as $estudianteId => $gruposDelEstudiante) {

            $modulosCompletados = DB::table('grupo')
                ->whereIn('id', $gruposDelEstudiante)
                ->pluck('id_modulo')
                ->unique()
                ->toArray();

            if (count(array_intersect($modulosRequeridos, $modulosCompletados)) == $totalModulos) {

                $estudiante = DB::table('estudiante')
                    ->where('id', $estudianteId)
                    ->first();

                if ($estudiante) {
                    $egresados[] = [
                        'id' => $estudiante->id,
                        'nombre' => $estudiante->nombre,
                        'apellido_paterno' => $estudiante->apellido_paterno,
                        'apellido_materno' => $estudiante->apellido_materno,
                        'dni' => $estudiante->nro_documento ?? $estudiante->dni,
                    ];
                }
            }
        }

        return response()->json([
            'especialidad' => $especialidadMadre,
            'periodo' => $periodo,
            'egresados' => $egresados
        ]);
    }
}

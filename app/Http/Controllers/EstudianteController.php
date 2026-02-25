<?php

namespace App\Http\Controllers;

use App\Models\EspecialidadPrograma;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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

                'ep.id as especialidad_programa',

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

        $grupoIds = $informacionAcademica->pluck('grupo_id')->unique()->values();

        $capacidadesPorGrupo = collect();
        $notasPorGrupo = collect();
        $notaExperienciaPorGrupo = collect();
        $asistenciaResumenPorGrupo = collect();

        if ($grupoIds->isNotEmpty()) {
            $capacidadesPorGrupo = DB::table('capacidad_terminal')
                ->whereIn('id_grupo', $grupoIds)
                ->select('id', 'id_grupo', 'numero_capacidad', 'nombre_capacidad')
                ->orderByRaw('CAST(numero_capacidad AS UNSIGNED) ASC')
                ->get()
                ->groupBy('id_grupo');

            $notasPorGrupo = DB::table('nota_capacidad_terminal')
                ->where('id_estudiante', $estudiante->id)
                ->whereIn('id_grupo', $grupoIds)
                ->select('id_grupo', 'id_capacidad', 'nota_capacidad')
                ->get()
                ->groupBy('id_grupo')
                ->map(function ($items) {
                    return $items->keyBy('id_capacidad');
                });

            $notaExperienciaPorGrupo = DB::table('nota_experiencia_formativa')
                ->where('id_estudiante', $estudiante->id)
                ->whereIn('id_grupo', $grupoIds)
                ->orderByDesc('tipo_practicas')
                ->orderByDesc('created_at')
                ->select('id_grupo', 'nota')
                ->get()
                ->groupBy('id_grupo')
                ->map(function ($items) {
                    return $items->first();
                });

            $asistenciaResumenPorGrupo = DB::table('asistencia')
                ->where('id_estudiante', $estudiante->id)
                ->whereIn('id_grupo', $grupoIds)
                ->select(
                    'id_grupo',
                    DB::raw('COUNT(*) as total_registros'),
                    DB::raw('SUM(CASE WHEN asistencia = 1 THEN 1 ELSE 0 END) as asistio'),
                    DB::raw('SUM(CASE WHEN asistencia = 2 THEN 1 ELSE 0 END) as faltas'),
                    DB::raw('SUM(CASE WHEN asistencia = 3 THEN 1 ELSE 0 END) as tardanzas'),
                    DB::raw('SUM(CASE WHEN asistencia = 4 THEN 1 ELSE 0 END) as permisos')
                )
                ->groupBy('id_grupo')
                ->get()
                ->keyBy('id_grupo');
        }

        // Construir estructura jerárquica
        $especialidades = [];

        $especialidadesEgresadas = DB::table('egresados')
            ->where('id_estudiante', $estudiante->id)
            ->pluck('id_especialidad')
            ->toArray();

        foreach ($informacionAcademica as $registro) {
            $capacidadesGrupo = $capacidadesPorGrupo->get($registro->grupo_id, collect());
            $notasGrupo = $notasPorGrupo->get($registro->grupo_id, collect());
            $notaExperienciaRegistro = $notaExperienciaPorGrupo->get($registro->grupo_id);
            $notaExperiencia = null;

            if ($notaExperienciaRegistro && $notaExperienciaRegistro->nota !== null && is_numeric($notaExperienciaRegistro->nota)) {
                $notaExperiencia = (float) $notaExperienciaRegistro->nota;
            }

            $unidadesNotas = $capacidadesGrupo->map(function ($cap) use ($notasGrupo) {
                $notaRegistro = $notasGrupo->get($cap->id);
                $nota = $notaRegistro ? $notaRegistro->nota_capacidad : null;

                return [
                    'id_capacidad' => $cap->id,
                    'numero_unidad' => $cap->numero_capacidad,
                    'nombre_unidad' => $cap->nombre_capacidad,
                    'nota' => $nota !== null ? (float) $nota : null,
                ];
            })->values();

            if ($notaExperiencia !== null) {
                $unidadesNotas->push([
                    'id_capacidad' => null,
                    'numero_unidad' => 'EF',
                    'nombre_unidad' => 'Experiencia formativa',
                    'nota' => $notaExperiencia,
                    'es_experiencia_formativa' => true,
                ]);
            }

            $notasValidas = $unidadesNotas
                ->pluck('nota')
                ->filter(function ($n) {
                    return $n !== null && is_numeric($n);
                });

            $promedioNotas = $notasValidas->count() > 0 ? round($notasValidas->avg(), 1) : null;

            $asistenciaGrupo = $asistenciaResumenPorGrupo->get($registro->grupo_id);
            $totalAsistencia = $asistenciaGrupo->total_registros ?? 0;
            $asistio = $asistenciaGrupo->asistio ?? 0;
            $tardanzas = $asistenciaGrupo->tardanzas ?? 0;
            $faltas = $asistenciaGrupo->faltas ?? 0;
            $permisos = $asistenciaGrupo->permisos ?? 0;
            $porcentajeAsistencia = $totalAsistencia > 0
                ? round((($asistio + $tardanzas) / $totalAsistencia) * 100, 1)
                : null;

            $espId = $registro->especialidad_id;

            // Crear especialidad si no existe
            if (!isset($especialidades[$espId])) {
                $especialidades[$espId] = [
                    'id' => $registro->especialidad_id,
                    'especialidad_programa' => $registro->especialidad_programa,
                    'es_egresado' => in_array($registro->especialidad_programa, $especialidadesEgresadas),
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
                ],

                'notas_unidades' => $unidadesNotas,
                'promedio_notas' => $promedioNotas,
                'nota_experiencia_formativa' => $notaExperiencia,
                'asistencia_resumen' => [
                    'total_registros' => (int) $totalAsistencia,
                    'asistio' => (int) $asistio,
                    'tardanzas' => (int) $tardanzas,
                    'faltas' => (int) $faltas,
                    'permisos' => (int) $permisos,
                    'porcentaje_asistencia' => $porcentajeAsistencia,
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
            'periodo'      => 'required|uuid',
        ]);

        $especialidadMadreId = $request->query('especialidad');
        $periodoId           = $request->query('periodo');

        $especialidadMadre = DB::table('especialidad_madre')
            ->where('id', $especialidadMadreId)
            ->first(['id', 'nombre_especialidad']);

        $periodo = DB::table('periodo')
            ->where('id', $periodoId)
            ->first(['id', 'nombre_periodo']);

        // 1. Programas vinculados a la especialidad madre
        $especialidadesPrograma = DB::table('especialidad_programa')
            ->where('id_especialidad', $especialidadMadreId)
            ->pluck('id');

        if ($especialidadesPrograma->isEmpty()) {
            return response()->json(['especialidad' => $especialidadMadre, 'periodo' => $periodo, 'egresados' => []]);
        }

        // 2. Grupos culminados (status = 2) del periodo
        $grupos = DB::table('grupo')
            ->whereIn('id_especialidad', $especialidadesPrograma)
            ->where('id_periodo', $periodoId)
            ->where('status', 2)
            ->get(['id', 'id_modulo', 'id_especialidad']);

        // 3. Módulos requeridos para esta especialidad
        $modulosRequeridos = DB::table('modulos')
            ->whereIn('id_especialidad', $especialidadesPrograma)
            ->pluck('id')
            ->toArray();

        $totalModulos = count($modulosRequeridos);

        $egresados     = [];
        $idsIncluidos  = [];

        // --- FUENTE 1: Cálculo automático (módulos completos + notas aprobadas) ---
        if ($grupos->isNotEmpty()) {

            $grupoIds    = $grupos->pluck('id');
            $grupoModulo = $grupos->pluck('id_modulo', 'id'); // [grupo_id => modulo_id]

            // Capacidades terminales por grupo
            $capacidadesPorGrupo = DB::table('capacidad_terminal')
                ->whereIn('id_grupo', $grupoIds)
                ->get(['id', 'id_grupo'])
                ->groupBy('id_grupo');

            // Matrículas
            $matriculas = DB::table('matricula')
                ->whereIn('id_grupo', $grupoIds)
                ->get(['id_estudiante', 'id_grupo']);

            if ($matriculas->isNotEmpty()) {

                // Agrupar grupos por estudiante
                $gruposPorEstudiante = [];
                foreach ($matriculas as $mat) {
                    $gruposPorEstudiante[$mat->id_estudiante][] = $mat->id_grupo;
                }

                // Cargar todas las notas en lote
                $todasLasNotas = DB::table('nota_capacidad_terminal')
                    ->whereIn('id_grupo', $grupoIds)
                    ->get(['id_estudiante', 'id_grupo', 'id_capacidad', 'nota_capacidad']);

                // Indexar: [estudiante_id][grupo_id][capacidad_id] => nota
                $notasIndexadas = [];
                foreach ($todasLasNotas as $nota) {
                    $notasIndexadas[$nota->id_estudiante][$nota->id_grupo][$nota->id_capacidad] = $nota->nota_capacidad;
                }

                $notaMinima = 11; // Ajusta según tu reglamento

                foreach ($gruposPorEstudiante as $estudianteId => $gruposDelEstudiante) {

                    // A. Verificar que cubre todos los módulos requeridos
                    $modulosCompletados = collect($gruposDelEstudiante)
                        ->map(fn($gId) => $grupoModulo[$gId] ?? null)
                        ->filter()
                        ->unique()
                        ->toArray();

                    if (count(array_intersect($modulosRequeridos, $modulosCompletados)) !== $totalModulos) {
                        continue;
                    }

                    // B. Verificar notas aprobatorias en todas las capacidades de cada grupo
                    $aprobado = true;

                    foreach ($gruposDelEstudiante as $grupoId) {

                        $capacidadesDelGrupo = $capacidadesPorGrupo[$grupoId] ?? collect();

                        if ($capacidadesDelGrupo->isEmpty()) {
                            $aprobado = false;
                            break;
                        }

                        foreach ($capacidadesDelGrupo as $capacidad) {
                            $nota = $notasIndexadas[$estudianteId][$grupoId][$capacidad->id] ?? null;

                            if ($nota === null || (float) $nota < $notaMinima) {
                                $aprobado = false;
                                break 2;
                            }
                        }
                    }

                    if (!$aprobado) {
                        continue;
                    }

                    // C. Persistir en tabla egresados si aún no existe
                    $yaEgresado = DB::table('egresados')
                        ->where('id_estudiante', $estudianteId)
                        ->whereIn('id_especialidad', $especialidadesPrograma)
                        ->exists();

                    if (!$yaEgresado) {
                        $idEspecialidadPrograma = DB::table('grupo')
                            ->whereIn('id', $gruposDelEstudiante)
                            ->value('id_especialidad');

                        DB::table('egresados')->insert([
                            'id'              => \Str::uuid(),
                            'id_estudiante'   => $estudianteId,
                            'id_especialidad' => $idEspecialidadPrograma,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }

                    // D. Obtener datos del estudiante
                    $estudiante = DB::table('estudiante')
                        ->where('id', $estudianteId)
                        ->first(['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'nro_documento']);

                    if ($estudiante) {
                        $idsIncluidos[] = $estudianteId;
                        $egresados[]    = [
                            'id'               => $estudiante->id,
                            'nombre'           => $estudiante->nombre,
                            'apellido_paterno' => $estudiante->apellido_paterno,
                            'apellido_materno' => $estudiante->apellido_materno,
                            'dni'              => $estudiante->nro_documento,
                            'egreso_manual'    => false,
                        ];
                    }
                }
            }
        }

        // --- FUENTE 2: Egresados registrados manualmente en tabla egresados ---
        $egresadosTabla = DB::table('egresados as e')
            ->join('especialidad_programa as ep', 'ep.id', '=', 'e.id_especialidad')
            ->where('ep.id_especialidad', $especialidadMadreId)
            ->pluck('e.id_estudiante')
            ->toArray();

        $pendientes = array_diff($egresadosTabla, $idsIncluidos);

        if (!empty($pendientes)) {
            $estudiantesExtra = DB::table('estudiante')
                ->whereIn('id', $pendientes)
                ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'nro_documento']);

            foreach ($estudiantesExtra as $estudiante) {
                $egresados[] = [
                    'id'               => $estudiante->id,
                    'nombre'           => $estudiante->nombre,
                    'apellido_paterno' => $estudiante->apellido_paterno,
                    'apellido_materno' => $estudiante->apellido_materno,
                    'dni'              => $estudiante->nro_documento,
                    'egreso_manual'    => true,
                ];
            }
        }

        return response()->json([
            'especialidad' => $especialidadMadre,
            'periodo'      => $periodo,
            'egresados'    => $egresados,
        ]);
    }

    // -----------------------------------------------------------------------

    public function registrarEgresoManual(Request $request)
    {
        $request->validate([
            'id_estudiante'   => 'required|uuid',
            'id_especialidad' => 'required|uuid', // id de especialidad_programa
            'observacion'     => 'nullable|string|max:500',
        ]);

        $existe = DB::table('egresados')
            ->where('id_estudiante', $request->id_estudiante)
            ->where('id_especialidad', $request->id_especialidad)
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'El estudiante ya figura como egresado de esta especialidad.'
            ], 409);
        }

        DB::table('egresados')->insert([
            'id'              => \Str::uuid(),
            'id_estudiante'   => $request->id_estudiante,
            'id_especialidad' => $request->id_especialidad,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json(['message' => 'Egreso registrado correctamente.'], 201);
    }
}

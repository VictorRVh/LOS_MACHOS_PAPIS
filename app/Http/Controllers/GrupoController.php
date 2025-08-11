<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\EspecialidadPrograma;
use App\Models\Grupo;
use App\Models\Modulo;
use App\Models\Periodo;
use App\Models\ProgramaEstudio;
use Illuminate\Http\Request;

class GrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $grupos = Grupo::with([
            'programaEstudio:id,año',
            'especialidad:id',
            'modulo:id,numero_modulo,descripcion',
            'periodo:id,nombre_periodo',
            'convenio:id,nombre_institucion',
            'docente:id,user_id,codigo_modular',
            'docente.user:id,name,apellido_paterno,apellido_materno'
        ])->get();

        return response()->json($grupos);
    }


    // POST /api/grupos
    public function store(Request $request)
    {
        $request->validate([
            'id_programa'         => 'required|uuid|exists:programa_estudio,id',
            'id_especialidad'     => 'required|uuid|exists:especialidad_programa,id',
            'id_modulo'           => 'required|uuid|exists:modulos,id',
            'id_periodo'          => 'required|uuid|exists:periodo,id',
            'id_convenio'         => 'nullable|uuid|exists:convenios,id',
            'fecha_inicio'        => 'required|date',
            'fecha_fin'           => 'required|date|after_or_equal:fecha_inicio',
            'fecha_entrega_acta'  => 'nullable|date',
            'seccion'             => 'required|string|max:10',
            'turno'               => 'required|string|max:10',
            // 'id_docente'          => 'required|uuid|exists:docente,id',
            'id_docente'          => 'nullable',
            'status'              => 'required|integer|in:0,1,2,3'
        ]);

        $grupo = Grupo::create($request->all());

        return response()->json(['message' => 'Grupo creado con éxito', 'data' => $grupo], 201);
    }

    // GET /api/grupos/{id}
    public function show($id)
    {
        $grupo = Grupo::with([
            'programaEstudio',
            'especialidad',
            'modulo',
            'periodo',
            'convenio',
            'docente'
        ])->find($id);

        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        return response()->json($grupo);
    }

    // PUT /api/grupos/{id}
    public function update(Request $request, $id)
    {
        $grupo = Grupo::find($id);

        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        $request->validate([
            'id_programa'         => 'sometimes|uuid|exists:programa_estudio,id',
            'id_especialidad'     => 'sometimes|uuid|exists:especialidad_programa,id',
            'id_modulo'           => 'sometimes|uuid|exists:modulos,id',
            'id_periodo'          => 'sometimes|uuid|exists:periodo,id',
            'id_convenio'         => 'sometimes|nullable|uuid|exists:convenios,id',
            'fecha_inicio'        => 'sometimes|date',
            'fecha_fin'           => 'sometimes|date|after_or_equal:fecha_inicio',
            'fecha_entrega_acta'  => 'sometimes|nullable|date',
            'seccion'             => 'sometimes|string|max:10',
            'turno'               => 'sometimes|string|max:10',
            'id_docente'          => 'sometimes|uuid|exists:docente,id',
            'status'              => 'sometimes|integer|in:0,1,2,3'
        ]);

        $grupo->update($request->all());

        return response()->json(['message' => 'Grupo actualizado con éxito', 'data' => $grupo]);
    }

    // DELETE /api/grupos/{id}
    public function destroy($id)
    {
        $grupo = Grupo::find($id);

        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        $grupo->delete();

        return response()->json(['message' => 'Grupo eliminado con éxito']);
    }

    //ESPECIALIDADE DE UN PROGRAMA

    public function getEspecialidadesPorPrograma($idPrograma)
    {
        $especialidades = EspecialidadPrograma::with('especialidadMadre')
            ->where('id_programa', $idPrograma)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nombre_especialidad' => $item->especialidadMadre->nombre_especialidad
                ];
            });

        return response()->json($especialidades);
    }

    // MODULOS POR ESPECIALIDAD
    public function getModulosPorEspecialidad($idEspecialidad)
    {
        $modulos = Modulo::where('id_especialidad', $idEspecialidad)
            ->get(['id', 'numero_modulo', 'descripcion']);

        $modulos = $modulos->map(function ($modulo) {
            return [
                'id' => $modulo->id,
                'numero_modulo' => $modulo->numero_modulo,
                'nombre_modulo' => $modulo->descripcion
            ];
        });

        return response()->json($modulos);
    }


    // PERIODO POR MODULO
    public function getPeriodoPorModulo($idModulo)
    {
        $modulo = Modulo::with('periodo')->find($idModulo);

        if (!$modulo) {
            return response()->json(['mensaje' => 'Módulo no encontrado'], 404);
        }

        return response()->json([
            [
                'id' => $modulo->periodo->id,
                'nombre' => $modulo->periodo->nombre_periodo ?? null
            ]
        ]);
    }

    public function docentesPorGrupo()
    {
        $docentes = Docente::with('user:id,name,apellido_paterno,apellido_materno')
            ->get()
            ->map(function ($docente) {
                return [
                    'id' => $docente->id,
                    'nombre' => $docente->user->name,
                    'apellido_paterno' => $docente->user->apellido_paterno,
                    'apellido_materno' => $docente->user->apellido_materno,
                ];
            });

        return response()->json($docentes);
    }

    public function gruposPorCicloAnioPeriodo(Request $request)
    {
        $request->validate([
            'id_ciclo'   => 'required|uuid',
            'anio'       => 'required|string',
            'id_periodo' => 'required|uuid',
        ]);

        // Buscar IDs de programas (incluyendo rangos de año como 2025-2026)
        $programaIds = ProgramaEstudio::where('id_ciclo', $request->id_ciclo)
            ->where('año', 'like', '%' . $request->anio . '%')
            ->pluck('id');

        // Cargar grupos y relaciones
        $grupos = Grupo::with([
            'programaEstudio:id,año,numero_rd',
            'especialidad:id,id_especialidad,id_programa',
            'especialidad.especialidadMadre:id,nombre_especialidad',
            'modulo:id,numero_modulo,descripcion',
            'periodo:id,nombre_periodo',
            'convenio:id,nombre_institucion',
            'docente:id,user_id,codigo_modular',
            'docente.user:id,name,apellido_paterno,apellido_materno'
        ])
            ->whereIn('id_programa', $programaIds)
            ->where('id_periodo', $request->id_periodo)
            ->get();

        // Agrupar por especialidad y mapear módulos con datos extra
        $resultado = $grupos->groupBy('especialidad.id')->map(function ($items) {
            return [
                'especialidad' => [
                    'id' => $items->first()->especialidad->id,
                    'nombre' => $items->first()->especialidad->especialidadMadre->nombre_especialidad ?? null
                ],
                'modulos' => $items->map(function ($grupo) {
                    return [
                        'id_grupo'       => $grupo->id,
                        'programa'       => [
                            'id'     => $grupo->programaEstudio->id ?? null,
                            'nombre' => $grupo->programaEstudio->numero_rd ?? null,
                            'anio'   => $grupo->programaEstudio->año ?? null
                        ],
                        'especialidad'   => [
                            'id'     => $grupo->especialidad->id ?? null,
                            'nombre' => $grupo->especialidad->especialidadMadre->nombre_especialidad ?? null
                        ],
                        'modulo'         => [
                            'id'           => $grupo->modulo->id ?? null,
                            'numero'       => $grupo->modulo->numero_modulo ?? null,
                            'descripcion'  => $grupo->modulo->descripcion ?? null
                        ],
                        'periodo'        => [
                            'id'     => $grupo->periodo->id ?? null,
                            'nombre' => $grupo->periodo->nombre_periodo ?? null
                        ],
                        'ciclo'          => [
                            'id'     => $grupo->programaEstudio->id_ciclo ?? null
                        ],
                        'convenio'       => [
                            'id'     => $grupo->convenio->id ?? null,
                            'nombre' => $grupo->convenio->nombre_institucion ?? null
                        ],
                        'docente'        => [
                            'id'     => $grupo->docente->id ?? null,
                            'nombre' => $grupo->docente
                                ? trim($grupo->docente->user->name . ' ' . $grupo->docente->user->apellido_paterno . ' ' . $grupo->docente->user->apellido_materno)
                                : null
                        ],
                        'fecha_inicio'   => $grupo->fecha_inicio ?? null,
                        'fecha_fin'      => $grupo->fecha_fin ?? null,
                        'entrega_acta'   => $grupo->fecha_entrega_acta ?? null,
                        'seccion'        => $grupo->seccion ?? null,
                        'turno'          => $grupo->turno ?? null
                    ];
                })->sortBy('modulo.numero')->values()

            ];
        });

        return response()->json($resultado->values());
    }


    // NUEVO FORMATO PARA FILTRO DE GRUPOS

    public function getAniosPorCiclo($idCiclo)
    {
        $rangoAnios = ProgramaEstudio::where('id_ciclo', $idCiclo)
            ->pluck('año')
            ->filter()
            ->flatMap(function ($rango) {
                return explode('-', $rango);
            })
            ->unique()
            ->sort()
            ->values();

        return response()->json($rangoAnios);
    }

    public function getPeriodosPorAnio($anio)
    {
        // Filtrar por el nombre del periodo que comience con el año
        $periodos = Periodo::where('nombre_periodo', 'LIKE', "{$anio}-%")->get();

        return response()->json($periodos);
    }
}

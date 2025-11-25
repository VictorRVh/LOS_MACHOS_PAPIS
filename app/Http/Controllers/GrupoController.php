<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use App\Models\CarpetasGrupoDrive;
use App\Models\CarpetasPeriodoDrive;
use App\Models\Docente;
use App\Models\EspecialidadPrograma;
use App\Models\Grupo;
use App\Models\Modulo;
use App\Models\Periodo;
use App\Models\ProgramaEstudio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\Helpers; // <-- AÑADIDO


class GrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use Helpers;
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

    public function infoGrupo($id)
    {
        $grupo = Grupo::with([
            'especialidad.especialidadMadre:id,nombre_especialidad',
            'modulo:id,numero_modulo,descripcion',
            'docente.user:id,name,apellido_paterno,apellido_materno',
        ])
            ->select('id', 'id_especialidad', 'id_modulo', 'id_docente', 'seccion', 'turno')
            ->findOrFail($id);

        return response()->json([
            'id'           => $grupo->id,
            'especialidad' => $grupo->especialidad?->especialidadMadre?->nombre_especialidad,
            // 'modulo'       => $grupo->modulo
            //     ? $grupo->modulo->numero_modulo . ': ' . $grupo->modulo->descripcion
            //     : null,
            'modulo'       => $grupo->modulo
                ? $grupo->modulo->descripcion
                : null,
            'seccion'      => $grupo->seccion,
            'turno'        => $grupo->turno,
            'docente'      => $grupo->docente && $grupo->docente->user
                ? $grupo->docente->user->name . ' '
                . $grupo->docente->user->apellido_paterno . ' '
                . $grupo->docente->user->apellido_materno
                : null,
        ]);
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
            'fecha_entrega_acta'  => 'nullable|date|after:fecha_fin',
            'seccion'             => 'required|string|max:10',
            'turno'               => 'required|string|max:10',
            'id_docente'          => 'nullable',
            'status'              => 'required|integer|in:0,1,2,3'
        ]);

        DB::beginTransaction();

        try {

            // 1️⃣ Verificar que exista carpeta del periodo
            $carpetaPeriodo = CarpetasPeriodoDrive::where('id_periodo', $request->id_periodo)->first();

            if (!$carpetaPeriodo) {

                \Log::warning("No existe carpeta de periodo para id_periodo: " . $request->id_periodo);

                throw new \Exception(
                    'No existe carpeta del periodo en Drive',
                    13333
                );
            }

            // 2️⃣ Crear el grupo SOLO si existe carpeta de periodo
            $grupo = Grupo::create($request->all());

            $modulo = Modulo::find($request->id_modulo);
            $especialidad = EspecialidadPrograma::with('especialidadMadre')->find($request->id_especialidad);

            // Registrar actividad con detalles completos
            $this->registrarActividad(
                "Creó el grupo {$grupo->seccion} | Turno: {$grupo->turno} | Módulo: {$modulo->descripcion} | Especialidad: {$especialidad->especialidadMadre->nombre_especialidad}",
                "Creado"
            );

            // 3️⃣ Crear subcarpeta del grupo en Drive
            $folderName = "Grupo {$grupo->seccion} | Turno: {$grupo->turno} | Módulo: {$modulo->descripcion} | Especialidad: {$especialidad->especialidadMadre->nombre_especialidad}";

            $driveController = new GoogleDriveController();
            $response = $driveController->createFolder(new Request([
                'folderName' => $folderName,
                'parentFolderId' => $carpetaPeriodo->drive_folder_id,
            ]));

            if ($response->status() !== 201) {

                \Log::error("Error creando subcarpeta del grupo en Drive: " . $response->getContent());

                throw new \Exception(
                    'Error creando subcarpeta del grupo en Drive',
                    13333
                );
            }

            $data = $response->getData();
            $driveFolderId = $data->id ?? null;

            CarpetasGrupoDrive::create([
                'id_grupo' => $grupo->id,
                'drive_folder_id' => $driveFolderId,
                'nombre_carpeta'  => $folderName,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Grupo creado con éxito',
                'data'    => $grupo
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack(); // ❗REVERSA TODO (grupo incluido)

            \Log::error("Error en creación de grupo y carpeta: " . $e->getMessage());

            // SIEMPRE devolver código 13333
            throw new \Exception($e->getMessage(), 13333);
        }
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
            // 'id_docente'          => 'sometimes|uuid|exists:docente,id',
            'id_docente'          => 'nullable',
            'status'              => 'sometimes|integer|in:0,1,2,3'
        ]);

        $grupo->update($request->all());

        $modulo = Modulo::find($request->id_modulo);
        $especialidad = EspecialidadPrograma::with('especialidadMadre')->find($request->id_especialidad);

        // Registrar actividad con detalles completos
        $this->registrarActividad(
            "Actualizó el grupo {$grupo->seccion} | Turno: {$grupo->turno} | Módulo: {$modulo->descripcion} | Especialidad: {$especialidad->especialidadMadre->nombre_especialidad}",
            "Actualizado"
        );


        return response()->json(['message' => 'Grupo actualizado con éxito', 'data' => $grupo]);
    }

    // DELETE /api/grupos/{id}
    public function destroy($id)
    {
        $grupo = Grupo::find($id);

        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        // 🔍 Obtener módulo actual del grupo
        $modulo = Modulo::find($grupo->id_modulo);

        // 🔍 Obtener especialidad actual del grupo
        $especialidad = EspecialidadPrograma::with('especialidadMadre')
            ->find($grupo->id_especialidad);

        // Guardar antes de eliminar
        $seccion = $grupo->seccion;
        $turno   = $grupo->turno;

        // 🚮 Eliminar el grupo
        $grupo->delete();

        // 📝 Registrar actividad con datos completos
        $this->registrarActividad(
            "Eliminó el grupo {$seccion} | Turno: {$turno} | Módulo: {$modulo->descripcion} | Especialidad: {$especialidad->especialidadMadre->nombre_especialidad}",
            "Eliminado"
        );

        return response()->json(['message' => 'Grupo eliminado con éxito'], 204);
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

    public function docentesPorGrupo(Request $request)
    {
        $request->validate([
            'turno' => 'required|string',
            'id_periodo' => 'required|string',
            'id_grupo' => 'nullable|string',
        ]);



        // Docentes ocupados en este turno y periodo (excluyendo el grupo actual si existe)
        $ocupados = Grupo::where('turno', $request->turno)
            ->where('id_periodo', $request->id_periodo)
            ->when($request->id_grupo, function ($query) use ($request) {
                $query->where('id', '!=', $request->id_grupo);
            })
            ->pluck('id_docente');

        // Traer docentes que no estén ocupados
        $docentes = Docente::with(['user' => function ($q) {
            $q->select('id', 'name', 'apellido_paterno', 'apellido_materno')
                ->where('is_deleted', 0); 
        }])
            ->whereNotIn('id', $ocupados)
            ->whereHas('user', function ($q) {
                $q->where('is_deleted', 0);
            })
            ->get();


        if ($request->id_grupo) {
            $grupoActual = Grupo::with('docente.user')->find($request->id_grupo);

            if ($grupoActual && $grupoActual->docente) {
                // Solo agregamos al docente si el turno y periodo son los mismos del grupo actual
                if ($grupoActual->turno === $request->turno && $grupoActual->id_periodo === $request->id_periodo) {
                    if (!$docentes->contains('id', $grupoActual->docente->id)) {
                        $docentes->push($grupoActual->docente);
                    }
                }
            }
        }

        // Mapear formato
        $docentes = $docentes->map(function ($docente) {
            return [
                'id' => $docente->id,
                'nombre' => $docente->user->name . ' ' .
                    $docente->user->apellido_paterno . ' ' .
                    $docente->user->apellido_materno,
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
            ->withCount(['matricula as matricula_count' => function ($query) {
                $query->where('reserva', 0); // Solo contar los que NO son reserva
            }])
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
                        'turno'          => $grupo->turno ?? null,
                        'cantidad'       => $grupo->matricula_count
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
        $periodos = Periodo::where('nombre_periodo', 'LIKE', "{$anio}-%")
            ->where('status', 1)
            // ->where('is_deleted', 0)
            ->get();

        return response()->json($periodos);
    }


    public function getPeriodosPorCiclo($cicloId)
    {
        // Obtener el campo año del programa para ese ciclo
        $programa = DB::table('programa_estudio')
            ->where('id_ciclo', $cicloId)
            ->first();

        if (!$programa) {
            return collect(); // vacío si no existe
        }

        // Extraer los años (puede ser "2026" o "2026-2027")
        $anios = explode('-', $programa->año);

        // Buscar los periodos que empiecen con esos años
        $periodos = DB::table('periodo')
            ->where(function ($q) use ($anios) {
                foreach ($anios as $anio) {
                    $q->orWhere('nombre_periodo', 'like', $anio . '-%');
                }
            })
            ->orderBy('nombre_periodo')
            ->get();

        return $periodos;
    }

    public function getGruposPorCicloYPeriodo(Request $request)
    {

        $cicloId = $request->id_ciclo;
        $periodoId = $request->id_periodo;

        $grupos = DB::table('grupo as g')
            ->join('programa_estudio as pe', 'g.id_programa', '=', 'pe.id')
            ->join('ciclo_academico as ca', 'pe.id_ciclo', '=', 'ca.id')
            ->join('periodo as p', 'g.id_periodo', '=', 'p.id')
            ->join('especialidad_programa as ep', 'g.id_especialidad', '=', 'ep.id')
            ->join('especialidad_madre as em', 'ep.id_especialidad', '=', 'em.id')
            ->join('modulos as m', 'g.id_modulo', '=', 'm.id')
            ->leftJoin('docente as d', 'g.id_docente', '=', 'd.id')
            ->leftJoin('users as u', 'd.user_id', '=', 'u.id')
            ->where('ca.id', $cicloId)
            ->where('p.id', $periodoId)
            ->select(
                'g.id',
                'em.nombre_especialidad as especialidad',
                'm.descripcion as modulo',
                'g.seccion',
                'g.turno',
                DB::raw("CONCAT(u.apellido_paterno, ' ', u.apellido_materno, ', ', u.name) as docente")
            )
            ->get();

        return $grupos;
    }

    public function gruposDisponibles(Request $request)
    {
        $idPeriodo = $request->input('periodo');
        $idGrupoActual = $request->input('grupo'); // puede venir null si no lo mandas

        $query = Grupo::with(['periodo', 'modulo', 'docente.user', 'convenio'])
            ->where('id_periodo', $idPeriodo)
            ->where('status', 1); // ✔ solo grupos activos;

        // excluir grupo actual si lo mandan
        if (!empty($idGrupoActual)) {
            $query->where('id', '!=', $idGrupoActual);
        }

        $grupos = $query->get()->map(function ($grupo) {
            $periodo = $grupo->periodo->nombre_periodo ?? '';
            $modulo = $grupo->modulo->descripcion ?? '';
            $seccionTurno = trim($grupo->seccion . '-' . $grupo->turno);
            $docente = $grupo->docente && $grupo->docente->user
                ? trim(
                    $grupo->docente->user->apellido_paterno . ' ' .
                        $grupo->docente->user->apellido_materno . ' ' .
                        $grupo->docente->user->name
                )
                : '';

            return [
                'id' => $grupo->id,
                'nombre_grupo' => "{$periodo} | {$modulo} | {$seccionTurno} | {$docente}",
                'periodo' => $periodo,
                'modulo' => $modulo,
                'horas' => $grupo->modulo->horas ?? null,
                'seccion' => $grupo->seccion,
                'turno' => $grupo->turno,
                'duracion' => $grupo->fecha_inicio . ' a ' . $grupo->fecha_fin,
                'convenio' => $grupo->convenio->nombre_institucion ?? null,
                'docente' => $docente,
            ];
        });

        return response()->json($grupos);
    }
}

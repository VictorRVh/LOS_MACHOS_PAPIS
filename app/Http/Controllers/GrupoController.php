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

            // // 1️⃣ Verificar que exista carpeta del periodo
            $carpetaPeriodo = CarpetasPeriodoDrive::where('id_periodo', $request->id_periodo)->first();

            if (!$carpetaPeriodo) {

                \Log::warning("No existe carpeta de periodo para id_periodo: " . $request->id_periodo);

                throw new \Exception(
                    'No existe carpeta del periodo en Drive',
                    13333
                );
            }

            // 2️⃣ Crear el git grupo SOLO si existe carpeta de periodo
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

    public function docentesPorGrupo(Request $request) //  y seccioens mas lo trae aqui 
    {
        $request->validate([
            'turno' => 'required|string',
            'id_periodo' => 'required|string',
            'id_modulo' => 'required|uuid|exists:modulos,id', // obligatorio para secciones
            'id_grupo' => 'nullable|string',
        ]);

        $turno = $request->turno;
        $idPeriodo = $request->id_periodo;
        $idModulo = $request->id_modulo;
        $idGrupoActual = $request->id_grupo;

        // -----------------------------
        // 1️⃣ DOCENTES (igual que tu función actual)
        // -----------------------------
        $ocupados = Grupo::where('turno', $turno)
            ->where('id_periodo', $idPeriodo)
            ->when($idGrupoActual, fn($q) => $q->where('id', '!=', $idGrupoActual))
            ->pluck('id_docente');

        $docentes = Docente::with(['user' => fn($q) => $q->select('id', 'name', 'apellido_paterno', 'apellido_materno')->where('is_deleted', 0)])
            ->whereNotIn('id', $ocupados)
            ->whereHas('user', fn($q) => $q->where('is_deleted', 0))
            ->get();

        if ($idGrupoActual) {
            $grupoActual = Grupo::with('docente.user')->find($idGrupoActual);
            if ($grupoActual && $grupoActual->docente && $grupoActual->turno === $turno && $grupoActual->id_periodo === $idPeriodo) {
                if (!$docentes->contains('id', $grupoActual->docente->id)) {
                    $docentes->push($grupoActual->docente);
                }
            }
        }

        $docentes = $docentes->map(fn($d) => [
            'id' => $d->id,
            'nombre' => $d->user->name . ' ' . $d->user->apellido_paterno . ' ' . $d->user->apellido_materno,
        ]);

        // -----------------------------
        // 2️⃣ SECCIONES DISPONIBLES
        // -----------------------------
        $todasSecciones = ['A', 'B', 'C', 'D', 'E', 'F'];

        $seccionesOcupadas = Grupo::where('id_modulo', $idModulo)
            ->where('id_periodo', $idPeriodo)
            ->when($idGrupoActual, fn($q) => $q->where('id', '!=', $idGrupoActual))
            ->pluck('seccion')
            ->toArray();

        $seccionesDisponibles = array_values(array_diff($todasSecciones, $seccionesOcupadas));

        $secciones = collect($seccionesDisponibles)->map(fn($s) => [
            'id' => $s,
            'nombre' => "Sección $s"
        ]);

        // -----------------------------
        // 3️⃣ RESPUESTA FINAL
        // -----------------------------
        return response()->json([
            'docentes' => $docentes,
            'secciones' => $secciones,
        ]);
    }


    public function gruposPorCicloAnioPeriodo(Request $request)
    {
        $request->validate([
            'id_ciclo'   => 'required|uuid',
            'anio'       => 'required|string',
            'id_periodo' => 'required|uuid',
        ]);

        // Programas por ciclo + año
        $programaIds = ProgramaEstudio::where('id_ciclo', $request->id_ciclo)
            ->where('año', 'like', '%' . $request->anio . '%')
            ->pluck('id');

        // Grupos con relaciones
        $grupos = Grupo::with([
            'programaEstudio:id,año,numero_rd,id_ciclo',
            'especialidad.especialidadMadre',
            'modulo',
            'periodo',
            'convenio',
            'docente.user'
        ])
            ->withCount([
                'matricula as cantidad_estudiantes' => function ($query) {
                    $query->where('reserva', 0);
                }
            ])
            ->whereIn('id_programa', $programaIds)
            ->where('id_periodo', $request->id_periodo)
            ->get();

        // FORMATO PLANO **COMPLETO**
        $resultado = $grupos->map(function ($g) {
            return [
                'id'          => $g->id,

                // ---- IDs para autocompletar ----
                'id_programa'    => $g->id_programa,
                'id_especialidad' => $g->id_especialidad,
                'id_modulo'      => $g->id_modulo,
                'id_periodo'     => $g->id_periodo,
                'id_convenio'    => $g->id_convenio,
                'id_docente'     => $g->id_docente,
                'ciclo_id'       => $g->programaEstudio->id_ciclo ?? null,

                'status'         => $g->status,
                'especialidad'   => $g->especialidad->especialidadMadre->nombre_especialidad ?? null,
                'modulo'         => $g->modulo->descripcion ?? null,
                'modulo_numero'  => $g->modulo->numero_modulo ?? null,
                'periodo_nombre' => $g->periodo->nombre_periodo ?? null,
                'convenio_nombre' => $g->convenio->nombre_institucion ?? null,

                'seccion'        => $g->seccion,
                'turno'          => $g->turno,

                'docente' => $g->docente
                    ? trim($g->docente->user->apellido_paterno . ' ' . $g->docente->user->apellido_materno . ', ' . $g->docente->user->name)
                    : null,

                // ---- FECHAS ----
                'fecha_inicio'   => $g->fecha_inicio,
                'fecha_fin'      => $g->fecha_fin,
                'fecha_entrega_acta'   => $g->fecha_entrega_acta,

                // ---- MATRÍCULA ----
                'cantidad_estudiantes' => $g->cantidad_estudiantes,
            ];
        });

        return response()->json($resultado);
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
            ->where('is_deleted', 0)
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

            // 🔥 AQUI SE AGREGA EL JOIN PARA MATRÍCULA
            ->leftJoin('matricula as ma', function ($join) {
                $join->on('ma.id_grupo', '=', 'g.id')
                    ->where('ma.reserva', 0); // opcional
            })

            ->where('ca.id', $cicloId)
            ->where('p.id', $periodoId)
            ->select(
                'g.id',
                'em.nombre_especialidad as especialidad',
                'm.descripcion as modulo',
                'g.seccion',
                'g.turno',
                DB::raw("CONCAT(u.apellido_paterno, ' ', u.apellido_materno, ', ', u.name) as docente"),

                // 🔥 CANTIDAD DE ESTUDIANTES
                DB::raw('COUNT(ma.id) as cantidad_estudiantes')
            )
            ->groupBy(
                'g.id',
                'em.nombre_especialidad',
                'm.descripcion',
                'g.seccion',
                'g.turno',
                'u.apellido_paterno',
                'u.apellido_materno',
                'u.name'
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

    public function ultimosGrupos()
    {
        $grupos = DB::table('grupo as g')
            ->join('especialidad_programa as ep', 'ep.id', '=', 'g.id_especialidad')
            ->join('especialidad_madre as em', 'em.id', '=', 'ep.id_especialidad')
            ->join('modulos as m', 'm.id', '=', 'g.id_modulo')
            ->leftJoin('matricula as ma', 'ma.id_grupo', '=', 'g.id')
            ->select(
                'g.id',
                'em.nombre_especialidad',
                'm.numero_modulo',
                'm.descripcion as nombre_modulo',
                'g.seccion',
                'g.turno',
                DB::raw('COUNT(ma.id) as nro_matriculados')
            )
            ->groupBy(
                'g.id',
                'em.nombre_especialidad',
                'm.numero_modulo',
                'm.descripcion',
                'g.seccion',
                'g.turno'
            )
            ->orderBy('g.created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json($grupos);
    }

    public function gruposCulminados()
    {
        $grupos = DB::table('grupo as g')
            ->join('especialidad_programa as ep', 'ep.id', '=', 'g.id_especialidad')
            ->join('especialidad_madre as em', 'em.id', '=', 'ep.id_especialidad')
            ->join('modulos as m', 'm.id', '=', 'g.id_modulo')
            ->leftJoin('matricula as ma', 'ma.id_grupo', '=', 'g.id')
            ->select(
                'g.id',
                'em.nombre_especialidad',
                'm.numero_modulo',
                'm.descripcion as nombre_modulo',
                'g.seccion',
                'g.turno',
                DB::raw('COUNT(ma.id) as nro_matriculados')
            )
            ->where('g.status', 2)
            ->groupBy(
                'g.id',
                'em.nombre_especialidad',
                'm.numero_modulo',
                'm.descripcion',
                'g.seccion',
                'g.turno'
            )
            ->orderBy('g.created_at', 'desc')
            ->take(2)
            ->get();

        return response()->json($grupos);
    }

    public function IngresosByGrupo($idPeriodo)
    {
        // 1. TRAER GRUPOS CON TODAS LAS RELACIONES
        $grupos = Grupo::with([
            'especialidad.especialidadMadre',
            'modulo',
            'docente.user:id,name,apellido_paterno,apellido_materno',
            'matricula' => function ($q) {
                $q->where('reserva', 0)->with('pago');
            }
        ])
            ->where('id_periodo', $idPeriodo)
            ->get();

        if ($grupos->isEmpty()) {
            return response()->json([]);
        }

        // 2. AGRUPACIÓN
        $resultado = [];

        foreach ($grupos as $g) {

            // 🔰 ESPECIALIDAD MADRE
            $especialidad = $g->especialidad->especialidadMadre->nombre_especialidad
                ?? 'SIN ESPECIALIDAD';

            // 🔰 MÓDULO
            $moduloNumero = $g->modulo->numero_modulo;
            $moduloNombre = $g->modulo->descripcion;

            // 🔰 ALUMNOS
            $cantidadEstudiantes = $g->matricula->count();

            // 🔥 INGRESOS POR GRUPO (suma de aportes)
            $ingresoGrupo = $g->matricula->sum(function ($m) {
                return $m->pago->aporte ?? 0;
            });

            // 🔰 DOCENTE con apellidos completos
            $docente = null;
            if ($g->docente && $g->docente->user) {
                $u = $g->docente->user;
                $docente = trim("{$u->apellido_paterno} {$u->apellido_materno}, {$u->name}");
            }

            // ============================
            // CREAR BLOQUES SI NO EXISTEN
            // ============================
            if (!isset($resultado[$especialidad])) {
                $resultado[$especialidad] = [
                    'especialidad' => $especialidad,
                    'modulos' => []
                ];
            }

            if (!isset($resultado[$especialidad]['modulos'][$moduloNumero])) {
                $resultado[$especialidad]['modulos'][$moduloNumero] = [
                    'modulo_numero' => $moduloNumero,
                    'modulo' => $moduloNombre,
                    'grupos' => []
                ];
            }

            // ============================
            // AÑADIR GRUPO
            // ============================
            $resultado[$especialidad]['modulos'][$moduloNumero]['grupos'][] = [
                'id' => $g->id,
                'seccion' => $g->seccion,
                'turno' => $g->turno,

                'docente' => $docente,

                'modulo' => $moduloNombre,
                'modulo_numero' => $moduloNumero,

                'cantidad_estudiantes' => $cantidadEstudiantes,
                'ingreso_grupo' => (float) $ingresoGrupo,
            ];
        }

        // RETORNAR ORDENADO
        return response()->json(array_values($resultado));
    }

    public function dataCertificado($idMatricula)
    {
        // ===============================
        // DATOS PRINCIPALES DEL CERTIFICADO
        // ===============================
        $matricula = DB::table('matricula as m')
            ->join('estudiante as e', 'e.id', '=', 'm.id_estudiante')
            ->join('grupo as g', 'g.id', '=', 'm.id_grupo')
            ->join('modulos as mo', 'mo.id', '=', 'g.id_modulo')
            ->join('especialidad_programa as ep', 'ep.id', '=', 'g.id_especialidad')
            ->join('especialidad_madre as em', 'em.id', '=', 'ep.id_especialidad')
            ->where('m.id', $idMatricula)
            ->select(
                DB::raw("
                CONCAT(
                    e.apellido_paterno, ' ',
                    e.apellido_materno, ' ',
                    e.nombre
                ) as apellidos_nombres
            "),
                'em.nombre_especialidad as especialidad',

                // 🔥 MÓDULO → UNIDAD DE COMPETENCIA
                'mo.descripcion as unidad_competencia',
                   'mo.creditos',
                   'mo.horas',
                'g.fecha_inicio',
                'g.fecha_fin',
                'g.id as id_grupo',
                'e.id as id_estudiante'
            )
            ->first();

        if (!$matricula) {
            return response()->json(['message' => 'Matrícula no encontrada'], 404);
        }

        // ===============================
        // CAPACIDADES TERMINALES → UNIDADES DIDÁCTICAS
        // ===============================
        $unidadesDidacticas = DB::table('capacidad_terminal as ct')
            ->leftJoin('nota_capacidad_terminal as nct', function ($q) use ($matricula) {
                $q->on('nct.id_capacidad', '=', 'ct.id')
                    ->where('nct.id_estudiante', $matricula->id_estudiante)
                    ->where('nct.id_grupo', $matricula->id_grupo);
            })
            ->where('ct.id_grupo', $matricula->id_grupo)
            ->orderBy('ct.numero_capacidad')
            ->select(
                'ct.numero_capacidad as numero_unidad',
                'ct.nombre_capacidad as nombre_unidad',
                'ct.fecha_inicio',
                'ct.fecha_fin',
                DB::raw('IFNULL(nct.nota_capacidad, "") as nota')
            )
            ->get();

        // ===============================
        // RESPUESTA FINAL (PEDAGÓGICA)
        // ===============================
        return response()->json([
            'apellidos_nombres' => $matricula->apellidos_nombres,
            'especialidad'      => $matricula->especialidad,

            // ✅ CORRECTO
            'unidad_competencia' => $matricula->unidad_competencia,
            'horas' => $matricula->horas,
            'creditos' => $matricula->creditos,

            'fecha_inicio'      => $matricula->fecha_inicio,
            'fecha_fin'         => $matricula->fecha_fin,

            // ✅ CORRECTO
            'unidades_didacticas' => $unidadesDidacticas,
        ]);
    }
}

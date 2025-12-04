<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Matricula;
use App\Models\MatriculaHistorial;
use App\Models\Pago;
use App\Models\ProgramaEstudio;
use App\Traits\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatriculaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use Helpers;
    public function index()
    {
        $matriculas = Matricula::with([
            'grupo:id,seccion,turno',
            'estudiante:id,nombre,apellido_paterno,apellido_materno,dni',
            'pago:id,monto,fecha_pago'
        ])->get();

        return response()->json($matriculas);
    }

    // POST /api/matriculas
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Buscar estudiante por tipo y nro de documento
            $estudiante = Estudiante::where('tipo_documento', $request->tipo_documento)
                ->where('nro_documento', $request->nro_documento)
                ->first();

            if ($estudiante) {
                // Actualizar datos si ya existe
                $estudiante->update([
                    'apellido_paterno'        => $request->apellido_paterno,
                    'apellido_materno'        => $request->apellido_materno,
                    'nombre'                  => $request->nombre,
                    'sexo'                    => $request->sexo,
                    'fecha_nacimiento'        => $request->fecha_nacimiento,
                    'pais_nacimiento'         => $request->pais_nacimiento,
                    'departamento_nacimiento' => $request->departamento_nacimiento,
                    'provincia_nacimiento'    => $request->provincia_nacimiento,
                    'distrito_nacimiento'     => $request->distrito_nacimiento,
                    'lugar_nacimiento'        => $request->lugar_nacimiento,
                    'direccion_residencia'    => $request->direccion_residencia,
                    'correo_electronico'      => $request->correo_electronico,
                    'celular_personal'        => $request->celular_personal,
                    'estado_civil'            => $request->estado_civil,
                    'grado_instruccion'       => $request->grado_instruccion,
                    'trabaja'                 => $request->trabaja,
                    'detalle_trabajo'         => $request->trabaja === 'Si' ? $request->detalle_trabajo : null,
                    'carga_familiar'          => $request->carga_familiar,
                    'detalle_carga_familiar'  => $request->carga_familiar === 'Si' ? $request->detalle_carga_familiar : null,
                    'internet_casa'           => $request->internet_casa,
                    'tipo_internet'           => $request->internet_casa === 'Si' ? $request->tipo_internet : null,
                    'equipos_virtuales'       => $request->has('equipos_virtuales') ? json_encode($request->equipos_virtuales) : null,
                    'discapacidad'            => $request->discapacidad,
                    'tipo_discapacidad'       => $request->discapacidad === 'Si' ? $request->tipo_discapacidad : null,
                    'celular_referencia'      => $request->celular_referencia,
                    'parentesco_referencia'   => $request->parentesco_referencia,
                    'lengua_materna'          => $request->lengua_materna,
                    'anio_egreso'             => $request->anio_egreso,
                ]);
            } else {
                // Crear nuevo estudiante si no existe
                $estudiante = Estudiante::create([
                    'tipo_documento'          => $request->tipo_documento,
                    'nro_documento'           => $request->nro_documento,
                    'apellido_paterno'        => $request->apellido_paterno,
                    'apellido_materno'        => $request->apellido_materno,
                    'nombre'                  => $request->nombre,
                    'sexo'                    => $request->sexo,
                    'fecha_nacimiento'        => $request->fecha_nacimiento,
                    'pais_nacimiento'         => $request->pais_nacimiento,
                    'departamento_nacimiento' => $request->departamento_nacimiento,
                    'provincia_nacimiento'    => $request->provincia_nacimiento,
                    'distrito_nacimiento'     => $request->distrito_nacimiento,
                    'lugar_nacimiento'        => $request->lugar_nacimiento,
                    'direccion_residencia'    => $request->direccion_residencia,
                    'correo_electronico'      => $request->correo_electronico,
                    'celular_personal'        => $request->celular_personal,
                    'estado_civil'            => $request->estado_civil,
                    'grado_instruccion'       => $request->grado_instruccion,
                    'trabaja'                 => $request->trabaja,
                    'detalle_trabajo'         => $request->trabaja === 'Si' ? $request->detalle_trabajo : null,
                    'carga_familiar'          => $request->carga_familiar,
                    'detalle_carga_familiar'  => $request->carga_familiar === 'Si' ? $request->detalle_carga_familiar : null,
                    'internet_casa'           => $request->internet_casa,
                    'tipo_internet'           => $request->internet_casa === 'Si' ? $request->tipo_internet : null,
                    'equipos_virtuales'       => $request->has('equipos_virtuales') ? json_encode($request->equipos_virtuales) : null,
                    'discapacidad'            => $request->discapacidad,
                    'tipo_discapacidad'       => $request->discapacidad === 'Si' ? $request->tipo_discapacidad : null,
                    'celular_referencia'      => $request->celular_referencia,
                    'parentesco_referencia'   => $request->parentesco_referencia,
                    'lengua_materna'          => $request->lengua_materna,
                    'anio_egreso'             => $request->anio_egreso,
                ]);
            }

            $matriculaExistente = Matricula::where('id_estudiante', $estudiante->id)
                ->where('id_grupo', $request->id_grupo)
                ->first();

            if ($matriculaExistente) {
                return response()->json([
                    'errorCode' => 13333,
                    'errorMessage' => 'El estudiante ya está matriculado en este grupo',
                    //'errorText' => ''
                ], 400);
            }

            // Crear pago
            $pago = Pago::create([
                'condicion' => $request->condicion,
                'nro_recibo' => $request->nro_recibo,
                'aporte' => $request->aporte,
                'status' => $request->status ?? 0
            ]);

            // Crear matrícula
            $matricula = Matricula::create([
                // 'id_grupo' => $request->id_grupo ?? $request->grupo['value'], // desde el select
                'id_grupo' => $request->id_grupo, // desde el select
                'turno' => $request->turno,
                'id_estudiante' => $estudiante->id,
                'id_pago' => $pago->id,
                'reserva' => $request->reserva ?? 0,
            ]);
            // Datos para actividad
            $grupo = Grupo::with(['modulo', 'especialidad.especialidadMadre'])
                ->find($request->id_grupo);

            $nombreCompleto = "{$estudiante->apellido_paterno} {$estudiante->apellido_materno}, {$estudiante->nombre}";

            $descripcionGrupo = "{$grupo->seccion} | Turno: {$grupo->turno} | "
                . "Módulo: {$grupo->modulo->descripcion} | "
                . "Especialidad: {$grupo->especialidad->especialidadMadre->nombre_especialidad}";

            // Registrar actividad
            $this->registrarActividad(
                "Registró matrícula del estudiante: {$nombreCompleto} en el grupo {$descripcionGrupo}",
                "Registrado"
            );

            DB::commit();

            return response()->json([
                'message' => 'Matrícula registrada con éxito',
                'data' => [
                    'matricula' => $matricula,
                    'estudiante' => $estudiante,
                    'pago' => $pago
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'errorCode' => 13333,
                'errorMessage' => 'Error al registrar matrícula',
                'errorText' => $e->getMessage()
            ], 500);
        }
    }

    // GET /api/matriculas/{id}
    public function show($id)
    {
        $matricula = Matricula::with(['grupo', 'estudiante', 'pago'])->find($id);

        if (!$matricula) {
            return response()->json(['message' => 'Matrícula no encontrada'], 404);
        }

        return response()->json($matricula);
    }

    // PATCH /api/matriculas/{id}
    public function update(Request $request, $id)
    {
        $matricula = Matricula::with(['estudiante', 'pago'])->find($id);

        if (!$matricula) {
            return response()->json(['message' => 'Matrícula no encontrada'], 404);
        }

        /* -------------------------
       VALIDACIÓN COMPLETA
    --------------------------*/
        $request->validate([

            // ESTUDIANTE
            'tipo_documento'        => 'sometimes|string|max:20',
            'nro_documento'         => 'sometimes|string|max:20',
            'apellido_paterno'      => 'sometimes|string|max:255',
            'apellido_materno'      => 'sometimes|string|max:255',
            'nombre'                => 'sometimes|string|max:255',
            'sexo'                  => 'sometimes|string|max:10',
            'fecha_nacimiento'      => 'sometimes|date',
            'pais_nacimiento'       => 'sometimes|string|max:255',
            'departamento_nacimiento' => 'sometimes|string|max:255',
            'provincia_nacimiento'  => 'sometimes|string|max:255',
            'distrito_nacimiento'   => 'sometimes|string|max:255',
            'lugar_nacimiento'      => 'nullable|string|max:255',
            'direccion_residencia'  => 'sometimes|string|max:255',
            'celular_personal'      => 'sometimes|string|max:20',
            'correo_electronico'    => 'sometimes|email|max:255',
            'estado_civil'          => 'sometimes|string|max:255',
            'grado_instruccion'     => 'sometimes|string|max:255',
            'anio_egreso'           => 'nullable|string|max:10',
            'lengua_materna'        => 'sometimes|string|max:255',
            'trabaja'               => 'sometimes|string|max:10',
            'detalle_trabajo'       => 'nullable|string|max:255',
            'carga_familiar'        => 'sometimes|string|max:10',
            'detalle_carga_familiar' => 'nullable|string|max:255',
            'internet_casa'         => 'sometimes|string|max:10',
            'tipo_internet'         => 'nullable|string|max:255',
            'equipos_virtuales'     => 'nullable|array',
            'discapacidad'          => 'sometimes|string|max:10',
            'tipo_discapacidad'     => 'nullable|string|max:255',
            'celular_referencia'    => 'nullable|string|max:20',
            'parentesco_referencia' => 'nullable|string|max:255',

            // PAGO
            'condicion'  => 'sometimes|string|max:255',
            'nro_recibo' => 'sometimes|string|max:255',
            'aporte'     => 'sometimes|numeric',
            'status'     => 'sometimes|integer|min:0|max:1',
        ]);

        /* -------------------------
        ACTUALIZAR ESTUDIANTE
    --------------------------*/
        $estudianteData = $request->only([
            'tipo_documento',
            'nro_documento',
            'apellido_paterno',
            'apellido_materno',
            'nombre',
            'sexo',
            'fecha_nacimiento',
            'pais_nacimiento',
            'departamento_nacimiento',
            'provincia_nacimiento',
            'distrito_nacimiento',
            'lugar_nacimiento',
            'direccion_residencia',
            'celular_personal',
            'correo_electronico',
            'estado_civil',
            'grado_instruccion',
            'anio_egreso',
            'lengua_materna',
            'trabaja',
            'detalle_trabajo',
            'carga_familiar',
            'detalle_carga_familiar',
            'internet_casa',
            'tipo_internet',
            'discapacidad',
            'tipo_discapacidad',
            'celular_referencia',
            'parentesco_referencia',
        ]);

        // equipos_virtuales debe guardarse como JSON
        if ($request->has('equipos_virtuales')) {
            $estudianteData['equipos_virtuales'] = json_encode($request->equipos_virtuales);
        }

        $matricula->estudiante->update($estudianteData);

        /* -------------------------
             ACTUALIZAR PAGO
    --------------------------*/

        $pagoData = $request->only([
            'condicion',
            'nro_recibo',
            'aporte',
            'status'
        ]);

        $matricula->pago->update($pagoData);

        return response()->json([
            'message' => 'Datos actualizados correctamente',
            'matricula' => [
                'id' => $matricula->id,
                'idGrupo' => $matricula->id_grupo,
                'nombre_completo' => $matricula->estudiante->apellido_paterno . ' ' .
                    $matricula->estudiante->apellido_materno . ' ' .
                    $matricula->estudiante->nombre
            ]
        ]);
    }



    // DELETE /api/matriculas/{id}
    public function destroy($id)
    {
        $matricula = Matricula::find($id);

        if (!$matricula) {
            return response()->json(['message' => 'Matrícula no encontrada'], 404);
        }

        // Obtener estudiante
        $estudiante = Estudiante::find($matricula->id_estudiante);

        $nombreCompleto = $estudiante
            ? "{$estudiante->apellido_paterno} {$estudiante->apellido_materno}, {$estudiante->nombre}"
            : "Estudiante no encontrado";

        // Obtener grupo
        $grupo = Grupo::with(['modulo', 'especialidad.especialidadMadre'])
            ->find($matricula->id_grupo);

        $descripcionGrupo = $grupo
            ? "{$grupo->seccion} | Turno: {$grupo->turno} | Módulo: {$grupo->modulo->descripcion} | Especialidad: {$grupo->especialidad->especialidadMadre->nombre_especialidad}"
            : "Grupo no encontrado";

        // Registrar actividad
        $this->registrarActividad(
            "Eliminó matrícula del estudiante: {$nombreCompleto} en el grupo {$descripcionGrupo}",
            "Eliminado"
        );

        // ❗ Eliminar SOLO la matrícula
        $matricula->delete();

        return response()->json(['message' => 'Matrícula eliminada con éxito'], 204);
    }


    // END POINTS PARA MATRICULA

    public function getEspecialidadesPorPrograma($idPrograma)
    {
        $programa = ProgramaEstudio::with(['especialidadPrograma.especialidadMadre'])
            ->where('id', $idPrograma)
            ->first();

        if (!$programa) {
            return response()->json(['message' => 'Programa no encontrado'], 404);
        }

        return response()->json([
            'id_programa'   => $programa->id,
            'año'           => $programa->año,
            'especialidades' => $programa->especialidadPrograma->map(function ($espProg) {
                return [
                    'id_especialidad_programa' => $espProg->id,
                    'id_especialidad'          => $espProg->id_especialidad,
                    'nombre_especialidad'      => $espProg->especialidadMadre->nombre_especialidad
                ];
            })
        ]);
    }

    public function getGruposPorEspecialidad($idEspecialidad)
    {
        $grupos = Grupo::with(['periodo', 'modulo', 'docente.user', 'convenio'])
            ->where('id_especialidad', $idEspecialidad)
            ->where('status', 1) // ✔ solo grupos activos
            ->get()
            ->map(function ($grupo) {
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
                    'docente' => $docente
                ];
            });

        return response()->json($grupos);
    }

    public function getMatriculadosPorGrupo($grupoId)
    {
        // INFO DEL GRUPO + DOCENTE
        $infoGrupo = DB::table('grupo as g')
            ->join('especialidad_programa as ep', 'g.id_especialidad', '=', 'ep.id')
            ->join('especialidad_madre as em', 'ep.id_especialidad', '=', 'em.id')
            ->join('modulos as mo', 'g.id_modulo', '=', 'mo.id')
            ->leftJoin('docente as d', 'g.id_docente', '=', 'd.id')
            ->leftJoin('users as u', 'd.user_id', '=', 'u.id')
            ->where('g.id', $grupoId)
            ->select(
                'em.nombre_especialidad as especialidad',
                'mo.descripcion as modulo',
                'g.id as id_grupo',
                'g.id_periodo',
                'g.turno',
                'g.seccion',
                DB::raw("CONCAT(u.name, ' ', u.apellido_paterno, ' ', u.apellido_materno) as docente")
            )
            ->first();

        if (!$infoGrupo) {
            return response()->json([
                'message' => 'Grupo no encontrado'
            ], 404);
        }

        // MATRICULADOS + INFO DE PAGO (TABLA REAL: pagos)
        $matriculados = DB::table('matricula as m')
            ->join('estudiante as e', 'm.id_estudiante', '=', 'e.id')
            ->leftJoin('pagos as p', 'm.id_pago', '=', 'p.id') // 🔥 CORREGIDO
            ->where('m.id_grupo', $grupoId)
            ->where(function ($q) {
                $q->whereNull('m.reserva')->orWhere('m.reserva', 0);
            })
            ->select(
                'm.id as id_matricula',
                'e.id as id_estudiante',
                'e.nombre',
                DB::raw("CONCAT(e.apellido_paterno, ' ', e.apellido_materno) as apellidos"),
                'e.tipo_documento',
                'e.nro_documento',
                'e.sexo',
                'e.fecha_nacimiento',
                'e.celular_personal',
                'e.correo_electronico',
                DB::raw("DATE(m.created_at) as created_at"),

                // 🔥 CAMPOS DEL PAGO SEGÚN TU MODELO
                'p.id as id_pago',
                'p.condicion',
                'p.nro_recibo',
                'p.aporte',
                'p.status as estado_pago'
            )
            ->orderBy('e.apellido_paterno')
            ->orderBy('e.apellido_materno')
            ->orderBy('e.nombre')
            ->get();

        return response()->json([
            'especialidad'   => $infoGrupo->especialidad,
            'id_periodo'     => $infoGrupo->id_periodo,
            'id_grupo'       => $infoGrupo->id_grupo,
            'modulo'         => $infoGrupo->modulo,
            'turno'          => $infoGrupo->turno,
            'seccion'        => $infoGrupo->seccion,
            'docente'        => $infoGrupo->docente,
            'matriculados'   => $matriculados,
        ]);
    }


    public function getFichaMatricula($estudianteId)
    {
        // Primero obtenemos la matrícula + info relacionada
        $ficha = DB::table('estudiante as e')
            ->join('matricula as m', 'e.id', '=', 'm.id_estudiante')
            ->join('grupo as g', 'm.id_grupo', '=', 'g.id')
            ->join('especialidad_programa as ep', 'g.id_especialidad', '=', 'ep.id')
            ->join('especialidad_madre as em', 'ep.id_especialidad', '=', 'em.id')
            ->join('modulos as mo', 'g.id_modulo', '=', 'mo.id')
            ->join('periodo as p', 'g.id_periodo', '=', 'p.id')
            ->where('e.id', $estudianteId)
            ->select(
                'e.id as id_estudiante',
                DB::raw("CONCAT(e.apellido_paterno, ' ', e.apellido_materno, ', ', e.nombre) as estudiante"),
                'e.nro_documento',
                'e.sexo',
                'e.fecha_nacimiento',
                'm.id as id_matricula',
                'm.turno',
                'g.id as id_grupo',
                'em.nombre_especialidad as especialidad',
                'mo.descripcion as modulo',
                'p.nombre_periodo as periodo',
                DB::raw("CONCAT(DATE_FORMAT(g.fecha_inicio, '%d/%m/%Y'), ' - ', DATE_FORMAT(g.fecha_fin, '%d/%m/%Y')) as periodo_clases")
            )
            ->first();

        if (!$ficha) {
            return response()->json(['error' => 'No existe matrícula para este estudiante'], 404);
        }

        // Obtener capacidades terminales del grupo de esta matrícula
        $capacidades = DB::table('capacidad_terminal')
            ->where('id_grupo', $ficha->id_grupo)
            ->select(
                'id',
                'numero_capacidad',
                'nombre_capacidad',
                'fecha_inicio',
                'fecha_fin',
                'status'
            )
            ->orderBy('numero_capacidad')
            ->get();

        return response()->json([
            'ficha' => $ficha,
            'capacidades_terminales' => $capacidades
        ]);
    }

    public function getMatriculadosPorGrupoExtendido($idGrupo)
    {
        // Datos de la especialidad, módulo y fechas
        $infoGrupo = DB::table('grupo')
            ->join('especialidad_programa', 'grupo.id_especialidad', '=', 'especialidad_programa.id')
            ->join('especialidad_madre', 'especialidad_programa.id_especialidad', '=', 'especialidad_madre.id')
            ->join('modulos', 'grupo.id_modulo', '=', 'modulos.id')
            ->where('grupo.id', $idGrupo)
            ->select(
                'especialidad_madre.nombre_especialidad as especialidad',
                'modulos.descripcion as modulo',
                'modulos.horas as duracion',
                'grupo.fecha_inicio',
                'grupo.fecha_fin',
                'grupo.id_periodo',
                'grupo.id'
            )
            ->first();

        // Datos de los estudiantes matriculados SIN reserva
        $estudiantes = DB::table('matricula')
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->leftJoin('pagos', 'matricula.id_pago', '=', 'pagos.id')
            ->where('matricula.id_grupo', $idGrupo)
            ->where(function ($q) {
                $q->whereNull('matricula.reserva')
                    ->orWhere('matricula.reserva', 0); // solo los SIN reserva
            })
            ->select(
                'matricula.id as id_matricula',
                DB::raw("CONCAT(estudiante.apellido_paterno, ' ', estudiante.apellido_materno, ', ', estudiante.nombre) as apellidos_nombres"),
                'estudiante.sexo',
                DB::raw("TIMESTAMPDIFF(YEAR, estudiante.fecha_nacimiento, CURDATE()) as edad"),
                'matricula.turno as condicion',
                'estudiante.id as id_estudiante',
                'estudiante.nro_documento',
                'estudiante.fecha_nacimiento',
                'estudiante.lugar_nacimiento as lugar',
                'estudiante.estado_civil',
                'estudiante.grado_instruccion',
                'estudiante.celular_personal as telefono',
                'estudiante.correo_electronico',
                'pagos.nro_recibo as nro_recibo',
                'pagos.aporte'
            )
            ->orderBy('estudiante.apellido_paterno', 'asc')
            ->get();

        return response()->json([
            'especialidad'  => $infoGrupo->especialidad ?? null,
            'id_grupo'      => $infoGrupo->id ?? null,
            'id_periodo'    => $infoGrupo->id_periodo ?? null,
            'modulo'        => $infoGrupo->modulo ?? null,
            'duracion'      => $infoGrupo->duracion ?? null,
            'fecha_inicio'  => $infoGrupo->fecha_inicio ?? null,
            'fecha_fin'     => $infoGrupo->fecha_fin ?? null,
            'estudiantes'   => $estudiantes
        ]);
    }

    // CAMBIO DE GRUPOS A ESTUDIANTES

    public function cambiarGrupo(Request $request)
    {
        $request->validate([
            'id_grupo' => 'required|uuid|exists:grupo,id',
            'ids' => 'required|array|min:1',
            'ids.*' => 'uuid|exists:matricula,id',
        ]);

        Matricula::whereIn('id', $request->ids)
            ->update(['id_grupo' => $request->id_grupo]);

        return response()->json([
            'message' => 'Grupo cambiado con éxito',
            'ids' => $request->ids,
        ]);
    }

    // RESERVA DE MATRICULA

    public function reservar($id)
    {
        $matricula = Matricula::findOrFail($id);

        // Alternar entre 0 y 1
        $matricula->reserva = !$matricula->reserva;
        $matricula->save();

        return response()->json([
            'success' => true,
            'message' => $matricula->reserva ? 'Matrícula reservada.' : 'Reserva quitada.',
            'data' => $matricula
        ]);
    }

    // LISTA DE RESERVAS DE MATRICULA

    public function matriculadosConReserva()
    {
        $infoGrupos = DB::table('grupo')
            ->join('especialidad_programa', 'grupo.id_especialidad', '=', 'especialidad_programa.id')
            ->join('especialidad_madre', 'especialidad_programa.id_especialidad', '=', 'especialidad_madre.id')
            ->join('modulos', 'grupo.id_modulo', '=', 'modulos.id')
            ->select(
                'especialidad_madre.nombre_especialidad as especialidad',
                'modulos.descripcion as modulo',
                'modulos.horas as duracion',
                'grupo.fecha_inicio',
                'grupo.fecha_fin',
                'grupo.id_periodo',
                'grupo.id as id_grupo'
            )
            ->get();

        // Datos de los estudiantes matriculados CON reserva (sin filtrar por grupo)
        $estudiantes = DB::table('matricula')
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->leftJoin('pagos', 'matricula.id_pago', '=', 'pagos.id')
            ->join('grupo', 'matricula.id_grupo', '=', 'grupo.id')
            ->join('especialidad_programa', 'grupo.id_especialidad', '=', 'especialidad_programa.id')
            ->join('especialidad_madre', 'especialidad_programa.id_especialidad', '=', 'especialidad_madre.id')
            ->join('modulos', 'grupo.id_modulo', '=', 'modulos.id')
            ->where('matricula.reserva', 1) // solo los CON reserva
            ->select(
                'matricula.id as id_matricula',
                DB::raw("CONCAT(estudiante.apellido_paterno, ' ', estudiante.apellido_materno, ', ', estudiante.nombre) as apellidos_nombres"),
                'estudiante.sexo',
                DB::raw("TIMESTAMPDIFF(YEAR, estudiante.fecha_nacimiento, CURDATE()) as edad"),
                'matricula.turno as condicion',
                'estudiante.id as id_estudiante',
                'estudiante.nro_documento',
                'estudiante.fecha_nacimiento',
                'estudiante.lugar_nacimiento as lugar',
                'estudiante.estado_civil',
                'estudiante.grado_instruccion',
                'estudiante.celular_personal as telefono',
                'estudiante.correo_electronico',
                'pagos.nro_recibo as nro_recibo',
                'pagos.aporte',
                'especialidad_madre.nombre_especialidad as especialidad',
                'modulos.descripcion as modulo',
                'grupo.fecha_inicio',
                'grupo.fecha_fin'
            )
            ->orderBy('estudiante.apellido_paterno', 'asc')
            ->get();

        return response()->json([
            'total'       => $estudiantes->count(),
            'estudiantes' => $estudiantes
        ]);
    }

    // public function retirarAlumno(Request $request)
    // {
    //     $request->validate([
    //         'id_estudiante' => 'required|uuid',
    //         'id_grupo' => 'required|uuid',
    //     ]);

    //     $matricula = Matricula::where('id_estudiante', $request->id_estudiante)
    //         ->where('id_grupo', $request->id_grupo)
    //         ->first();

    //     if (!$matricula) {
    //         return response()->json(['message' => 'Matrícula no encontrada'], 404);
    //     }

    //     // Registrar el cambio + actualizar estado
    //     $matricula->registrarCambioEstado(
    //         Matricula::STATUS_RETIRADO,
    //         "Retirado por faltas"
    //     );

    //     return response()->json([
    //         'message' => 'Alumno retirado correctamente',
    //         'data' => $matricula,
    //     ]);
    // }

    public function retirarAlumno(Request $request)
    {
        $request->validate([
            'id_estudiante' => 'required|uuid',
            'id_grupo' => 'required|uuid',
            'estado' => 'required|integer|in:1,2',
            'motivo' => 'nullable|string',
        ]);

        $matricula = Matricula::where('id_estudiante', $request->id_estudiante)
            ->where('id_grupo', $request->id_grupo)
            ->firstOrFail();

        $matricula->registrarCambioEstado(
            $request->estado,
            $request->motivo
        );

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'data' => $matricula,
        ]);
    }
    public function matriculaAlumnoData($id)
    {
        $matricula = Matricula::with([
            'estudiante',
            'pago',
        ])
            ->select('id', 'id_grupo', 'id_estudiante', 'id_pago') // SOLO estos campos
            ->where('id', $id)
            ->first();

        if (!$matricula) {
            return response()->json(['message' => 'Matrícula no encontrada'], 404);
        }

        return response()->json($matricula);
    }
}

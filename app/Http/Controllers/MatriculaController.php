<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Matricula;
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
                'celular_personal'        => $request->celular,
                'estado_civil'            => $request->estado_civil,
                'grado_instruccion'       => $request->grado_instruccion,

                'trabaja'                 => $request->trabaja,
                'detalle_trabajo'         => $request->trabaja === 'Si' ? $request->detalle_trabajo : null,

                'carga_familiar'          => $request->carga_familiar,
                'detalle_carga_familiar'  => $request->carga_familiar === 'Si' ? $request->detalle_carga_familiar : null,

                'internet_casa'           => $request->internet_casa,
                'tipo_internet'           => $request->internet_casa === 'Si' ? $request->tipo_internet : null,

                // 'tipo_operador'           => $request->tipo_operador,

                'equipos_virtuales'           => $request->has('equipos_virtuales')
                    ? json_encode($request->equipos_virtuales)
                    : null,

                'discapacidad'            => $request->discapacidad,
                'tipo_discapacidad'       => $request->discapacidad === 'Si' ? $request->tipo_discapacidad : null,

                'celular_referencia'      => $request->celular_referencia,
                'parentesco_referencia'   => $request->parentesco_referencia,
                'lengua_materna'       => $request->lengua_materna,
                'anio_egreso'       => $request->anio_egreso,
            ]);

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
                'message' => 'Error al registrar matrícula',
                'error' => $e->getMessage()
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
        $matricula = Matricula::find($id);

        if (!$matricula) {
            return response()->json(['message' => 'Matrícula no encontrada'], 404);
        }

        // Validar campos
        $request->validate([
            'id_grupo'      => 'sometimes|uuid|exists:grupo,id',
            'turno'         => 'sometimes|string|max:10',
            'id_estudiante' => 'sometimes|uuid|exists:estudiante,id',
            'id_pago'       => 'nullable|uuid|exists:pago,id',
            'reserva'       => 'nullable|boolean'
        ]);

        // Actualizar matrícula
        $matricula->update($request->all());

        // Obtener estudiante
        $estudiante = Estudiante::find($matricula->id_estudiante);

        $nombreCompleto = "{$estudiante->apellido_paterno} {$estudiante->apellido_materno}, {$estudiante->nombre}";

        // Obtener grupo (el nuevo o el que ya estaba)
        $grupo = Grupo::with(['modulo', 'especialidad.especialidadMadre'])
            ->find($matricula->id_grupo);

        $descripcionGrupo =
            "{$grupo->seccion} | Turno: {$grupo->turno} | " .
            "Módulo: {$grupo->modulo->descripcion} | " .
            "Especialidad: {$grupo->especialidad->especialidadMadre->nombre_especialidad}";

        // Registrar actividad
        $this->registrarActividad(
            "Actualizó matrícula del estudiante: {$nombreCompleto} en el grupo {$descripcionGrupo}",
            "Actualizado"
        );

        return response()->json([
            'message' => 'Matrícula actualizada con éxito',
            'data' => $matricula
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

        $nombreCompleto = "{$estudiante->apellido_paterno} {$estudiante->apellido_materno}, {$estudiante->nombre}";

        // Obtener grupo asociado
        $grupo = Grupo::with(['modulo', 'especialidad.especialidadMadre'])
            ->find($matricula->id_grupo);

        $descripcionGrupo =
            "{$grupo->seccion} | Turno: {$grupo->turno} | " .
            "Módulo: {$grupo->modulo->descripcion} | " .
            "Especialidad: {$grupo->especialidad->especialidadMadre->nombre_especialidad}";

        // Registrar actividad
        $this->registrarActividad(
            "Eliminó matrícula del estudiante: {$nombreCompleto} en el grupo {$descripcionGrupo}",
            "Eliminado"
        );

        // Eliminar matrícula
        $matricula->delete();

        return response()->json(['message' => 'Matrícula eliminada con éxito']);
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
        // INFO DEL GRUPO
        $infoGrupo = DB::table('grupo as g')
            ->join('especialidad_programa as ep', 'g.id_especialidad', '=', 'ep.id')
            ->join('especialidad_madre as em', 'ep.id_especialidad', '=', 'em.id')
            ->join('modulos as mo', 'g.id_modulo', '=', 'mo.id')
            ->where('g.id', $grupoId)
            ->select(
                'em.nombre_especialidad as especialidad',
                'mo.descripcion as modulo',
                'g.id as id_grupo',
                'g.id_periodo',
                'g.turno',
                'g.seccion'
            )
            ->first();

        // Si no existe el grupo, devolver 404 o respuesta vacía según prefieras
        if (!$infoGrupo) {
            return response()->json([
                'message' => 'Grupo no encontrado'
            ], 404);
        }

        // MATRICULADOS DEL GRUPO
        $matriculados = DB::table('matricula as m')
            ->join('estudiante as e', 'm.id_estudiante', '=', 'e.id')
            ->where('m.id_grupo', $grupoId)
            ->where(function ($q) {
                $q->whereNull('m.reserva')->orWhere('m.reserva', 0);
            })
            ->select(
                'm.id as id_matricula',
                'e.id as id_estudiante',
                // Nombre completo
                DB::raw("CONCAT(e.apellido_paterno, ' ', e.apellido_materno, ', ', e.nombre) as estudiante"),
                // Campos REALES del estudiante
                'e.tipo_documento',
                'e.nro_documento',
                'e.sexo',
                'e.fecha_nacimiento',
                'e.celular_personal',
                'e.correo_electronico',
                 DB::raw("DATE(m.created_at) as created_at")
            )
            ->orderBy('e.apellido_paterno')
            ->orderBy('e.apellido_materno')
            ->orderBy('e.nombre')
            ->get();

        return response()->json([
            'especialidad' => $infoGrupo->especialidad,
            'id_periodo' => $infoGrupo->id_periodo,
            'id_grupo' => $infoGrupo->id_grupo,
            'modulo' => $infoGrupo->modulo,
            'turno' => $infoGrupo->turno,
            'seccion' => $infoGrupo->seccion,
            'matriculados' => $matriculados,
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

    public function retirarAlumno(Request $request)
    {
        $request->validate([
            'id_estudiante' => 'required|uuid',
            'id_grupo' => 'required|uuid',
        ]);

        $matricula = Matricula::where('id_estudiante', $request->id_estudiante)
            ->where('id_grupo', $request->id_grupo)
            ->first();

        if (!$matricula) {
            return response()->json(['message' => 'Matrícula no encontrada'], 404);
        }

        $matricula->matriculado = 0;
        $matricula->save();

        return response()->json([
            'message' => 'Alumno retirado correctamente',
            'data' => $matricula,
        ]);
    }
}

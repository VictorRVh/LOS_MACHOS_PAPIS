<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Matricula;
use App\Models\Pago;
use App\Models\ProgramaEstudio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatriculaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
            // Crear estudiante
            $estudiante = Estudiante::create([
                'tipo_documento'       => $request->tipo_documento,
                'nro_documento'        => $request->nro_documento,
                'apellido_paterno'     => $request->apellido_paterno,
                'apellido_materno'     => $request->apellido_materno,
                'nombre'               => $request->nombre,
                'sexo'                 => $request->sexo,
                'fecha_nacimiento'     => $request->fecha_nacimiento,
                'pais_nacimiento'      => $request->pais_nacimiento,
                'departamento_nacimiento' => $request->departamento_nacimiento,
                'provincia_nacimiento' => $request->provincia_nacimiento,
                'distrito_nacimiento'  => $request->distrito_nacimiento,
                'lugar_nacimiento'     => $request->lugar_nacimiento,
                'direccion_residencia' => $request->direccion_residencia,
                'correo'               => $request->correo,
                'celular'              => $request->celular,
                'estado_civil'         => $request->estado_civil,
                'grado_instruccion'    => $request->grado_instruccion,
                'trabaja'              => $request->trabaja,
                'puesto_trabajo'       => $request->puesto_trabajo,
                'carga_familiar'       => $request->carga_familiar,
                'internet_casa'        => $request->internet_casa,
                'operador_celular'     => $request->operador_celular,
                'equipo_virtual'       => $request->equipo_virtual,
                'discapacidad'         => $request->discapacidad,
                'celular_referencia'   => $request->celular_referencia,
                'parentesco_referencia' => $request->parentesco_referencia,
                'lengua_originaria'    => $request->lengua_originaria
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
                'reserva' => $request->reserva ?? 0
            ]);

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

        $request->validate([
            'id_grupo'      => 'sometimes|uuid|exists:grupo,id',
            'turno'         => 'sometimes|string|max:10',
            'id_estudiante' => 'sometimes|uuid|exists:estudiante,id',
            'id_pago'       => 'nullable|uuid|exists:pago,id',
            'reserva'       => 'nullable|boolean'
        ]);

        $matricula->update($request->all());

        return response()->json(['message' => 'Matrícula actualizada con éxito', 'data' => $matricula]);
    }

    // DELETE /api/matriculas/{id}
    public function destroy($id)
    {
        $matricula = Matricula::find($id);

        if (!$matricula) {
            return response()->json(['message' => 'Matrícula no encontrada'], 404);
        }

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
}

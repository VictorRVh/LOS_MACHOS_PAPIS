<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\EspecialidadPrograma;
use App\Models\Grupo;
use App\Models\Modulo;
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

    public function gruposPorProgramaAnioPeriodo(Request $request)
    {
        $request->validate([
            'id_programa' => 'required|uuid',
            'anio'        => 'required|string',
            'id_periodo'  => 'required|uuid',
        ]);

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
            ->where('id_programa', $request->id_programa)
            ->where('id_periodo', $request->id_periodo)
            ->whereHas('programaEstudio', function ($query) use ($request) {
                $query->where('año', $request->anio);
            })
            ->get();

        return response()->json($grupos);
    }
}

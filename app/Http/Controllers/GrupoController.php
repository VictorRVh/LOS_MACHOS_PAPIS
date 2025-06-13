<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Illuminate\Http\Request;

class GrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $grupos = Grupo::with([
            'programaEstudio:id,nombre_programa',
            'especialidad:id',
            'modulo:id,numero_modulo,descripcion',
            'periodo:id,nombre_periodo',
            'convenio:id,nombre',
            'docente:id,codigo_modular'
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
            'id_docente'          => 'required|uuid|exists:docente,id',
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
            'id_docente'          => 'required|uuid|exists:docente,id',
            'status'              => 'required|integer|in:0,1,2,3'
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
}

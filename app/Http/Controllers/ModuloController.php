<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use Illuminate\Http\Request;

class ModuloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $modulos = Modulo::with(['periodo', 'especialidadPrograma'])->get();
        return response()->json($modulos);
    }

    // Crear un nuevo módulo
    public function store(Request $request)
    {
        $request->validate([
            'numero_modulo'      => 'required|string|max:10',
            'descripcion'        => 'nullable|string',
            'creditos'           => 'required|integer|min:0',
            'horas'              => 'required|integer|min:0',
            'id_especialidad'    => 'required|exists:especialidad_programa,id',
            'id_periodo'         => 'required|exists:periodo,id',
            'nro_capacidades'    => 'required|integer|min:0',
        ]);

        $modulo = Modulo::create($request->all());

        return response()->json($modulo, 201);
    }

    // Mostrar un módulo específico
    public function show($id)
    {
        $registros = Modulo::with(['especialidadPrograma', 'periodo'])
            ->where('id_especialidad', $id)
            ->get();

        if ($registros->isEmpty()) {
            return response()->json(['message' => 'Sin modulos dentro de la especialidad'], 404);
        }

        return response()->json($registros);
    }

    // Actualizar un módulo
    public function update(Request $request, $id)
    {
        $modulo = Modulo::findOrFail($id);

        $request->validate([
            'numero_modulo'      => 'sometimes|string|max:10',
            'descripcion'        => 'sometimes|nullable|string',
            'creditos'           => 'sometimes|integer|min:0',
            'horas'              => 'sometimes|integer|min:0',
            'id_especialidad'    => 'sometimes|exists:especialidad_programa,id',
            'id_periodo'         => 'sometimes|exists:periodo,id',
            'nro_capacidades'    => 'sometimes|integer|min:0',
        ]);

        $modulo->update($request->all());

        return response()->json($modulo);
    }


    // Eliminar un módulo
    public function destroy($id)
    {
        $modulo = Modulo::find($id);

        if (!$modulo) {
            return response()->json(['message' => 'Modulo no encontrado'], 404);
        }

        $modulo->delete();
        return response()->json(['message' => 'Modulo eliminado correctamente'], 204);
    }
}

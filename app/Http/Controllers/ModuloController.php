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
        $modulos = Modulo::with(['periodo', 'especialidadPrograma'])
            ->orderByRaw('CAST(numero_modulo AS UNSIGNED) ASC')
            ->get();
        return response()->json($modulos);
    }


    // Crear un nuevo módulo
    public function store(Request $request)
    {
        $request->validate([
            'numero_modulo' => 'required|string|max:10',
            'descripcion' => 'nullable|string',
            'creditos' => 'required|integer|min:0',
            'horas' => 'required|integer|min:0',
            'id_especialidad' => 'required|exists:especialidad_programa,id',
            'nro_capacidades' => 'required|integer|min:0',
        ]);

        $modulo = Modulo::create($request->all());

        return response()->json($modulo, 201);
    }

    // Mostrar un módulo específico
    public function show($id)
    {
        $registros = Modulo::with(['especialidadPrograma'])
            ->where('id_especialidad', $id)
            ->orderByRaw('CAST(numero_modulo AS UNSIGNED) ASC')
            ->get();

        if ($registros->isEmpty()) {
            return response()->json(['message' => 'Sin módulos dentro de la especialidad'], 404);
        }

        // Tomar el padre del primer módulo
        $especialidadPrograma = $registros->first()->especialidadPrograma;

        // Limpiar los campos no deseados
        if ($especialidadPrograma) {
            $especialidadPrograma = collect($especialidadPrograma)->only([
                'id',
                'id_especialidad',
                'id_programa',
                'nro_modulos',
            ]);
        }

        // Quitar la relación repetida en cada módulo
        $modulos = $registros->map(function ($modulo) {
            return [
                'id' => $modulo->id,
                'numero_modulo' => $modulo->numero_modulo,
                'descripcion' => $modulo->descripcion,
                'creditos' => $modulo->creditos,
                'horas' => $modulo->horas,
                'nro_capacidades' => $modulo->nro_capacidades,
            ];
        });

        return response()->json([
            'especialidad_programa' => $especialidadPrograma,
            'modulos' => $modulos,
        ]);
    }


    // Actualizar un módulo
    public function update(Request $request, $id)
    {
        $modulo = Modulo::findOrFail($id);

        $request->validate([
            'numero_modulo' => 'sometimes|string|max:10',
            'descripcion' => 'sometimes|nullable|string',
            'creditos' => 'sometimes|integer|min:0',
            'horas' => 'sometimes|integer|min:0',
            'id_especialidad' => 'sometimes|exists:especialidad_programa,id',
            'nro_capacidades' => 'sometimes|integer|min:0',
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

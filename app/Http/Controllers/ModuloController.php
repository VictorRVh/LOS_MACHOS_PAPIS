<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Traits\Helpers;
use Illuminate\Http\Request;
use App\Models\EspecialidadPrograma;

class ModuloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use Helpers;
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
        $this->registrarActividad(
            "Creó el módulo N° '{$modulo->numero_modulo}' para la especialidad '{$modulo->especialidadPrograma->especialidadMadre->nombre_especialidad}'",
            "Creado"
        );
        return response()->json($modulo, 201);
    }

    // Mostrar un módulo específico
    public function show($id)
    {
        // Buscar los módulos con todas las relaciones necesarias
        $registros = Modulo::with([
            'especialidadPrograma.programaEstudio:id,año,id_ciclo',
            'especialidadPrograma.programaEstudio.ciclo:id,nombre_ciclo',
            'especialidadPrograma.especialidadMadre:id,nombre_especialidad'
        ])
            ->where('id_especialidad', $id)
            ->orderByRaw('CAST(numero_modulo AS UNSIGNED) ASC')
            ->get();

        // Si NO hay módulos, igual buscamos la especialidad_programa
        if ($registros->isEmpty()) {
            $especialidadPrograma = EspecialidadPrograma::with([
                'programaEstudio:id,año,id_ciclo',
                'programaEstudio.ciclo:id,nombre_ciclo',
                'especialidadMadre:id,nombre_especialidad'
            ])->find($id);

            if (!$especialidadPrograma) {
                return response()->json(['message' => 'No se encontró la especialidad asociada'], 404);
            }

            // Devolver sin módulos, pero con los nombres jerárquicos
            return response()->json([
                'especialidad_programa' => [
                    'id' => $especialidadPrograma->id,
                    'id_especialidad' => $especialidadPrograma->id_especialidad,
                    'id_programa' => $especialidadPrograma->id_programa,
                    'nro_modulos' => $especialidadPrograma->nro_modulos,
                    'anio' => $especialidadPrograma->programaEstudio?->año,
                    'nombre_ciclo' => $especialidadPrograma->programaEstudio?->ciclo?->nombre_ciclo,
                    'nombre_especialidad' => $especialidadPrograma->especialidadMadre?->nombre_especialidad,
                ],
                'modulos' => [],
            ]);
        }

        // Si SÍ hay módulos
        $especialidadPrograma = $registros->first()->especialidadPrograma;

        if ($especialidadPrograma) {
            $especialidadPrograma = [
                'id' => $especialidadPrograma->id,
                'id_especialidad' => $especialidadPrograma->id_especialidad,
                'id_programa' => $especialidadPrograma->id_programa,
                'nro_modulos' => $especialidadPrograma->nro_modulos,
                'anio' => $especialidadPrograma->programaEstudio?->año,
                'nombre_ciclo' => $especialidadPrograma->programaEstudio?->ciclo?->nombre_ciclo,

                'nombre_especialidad' => $especialidadPrograma->especialidadMadre?->nombre_especialidad,
            ];
        }

        // Armar los módulos
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

        // Respuesta final
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

        // Alias bonitos
        $alias = [
            'numero_modulo' => 'Número del módulo',
            'descripcion'   => 'Descripción',
            'creditos'      => 'Créditos',
            'horas'         => 'Horas',
        ];

        // Valores ANTES
        $antes = $modulo->only(array_keys($alias));

        // Actualizar
        $modulo->update($request->all());

        // Detectar cambios (solo nombres con alias)
        $cambios = [];

        foreach ($antes as $campo => $valorAnterior) {
            if ($modulo->$campo != $valorAnterior) {
                $cambios[] = $alias[$campo];
            }
        }

        // Mensaje
        $descripcionCambios = empty($cambios)
            ? "sin cambios importantes"
            : "campos modificados: " . implode(", ", $cambios);

        // ACTIVIDAD
        $this->registrarActividad(
            "Actualizó el módulo '{$modulo->numero_modulo}' de la especialidad '{$modulo->especialidadPrograma->especialidadMadre->nombre_especialidad}' ({$descripcionCambios})",
            "Actualizado"
        );

        return response()->json($modulo);
    }




    // Eliminar un módulo
    public function destroy($id)
    {
        $modulo = Modulo::find($id);

        if (!$modulo) {
            return response()->json(['message' => 'Modulo no encontrado'], 404);
        }


        $nombreModulo = $modulo->numero_modulo;
        $nombreEspecialidad = $modulo->especialidadPrograma->especialidadMadre->nombre_especialidad ?? "desconocida";


        $modulo->delete();
        // ACTIVIDAD
        // ACTIVIDAD
        $this->registrarActividad(
            "Eliminó el módulo '{$nombreModulo}' de la especialidad '{$nombreEspecialidad}'",
            "Eliminado"
        );

        return response()->json(['message' => 'Modulo eliminado correctamente'], 204);
    }
}

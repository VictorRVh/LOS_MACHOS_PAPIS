<?php

namespace App\Http\Controllers;

use App\Models\Competencia;
use App\Models\Modulo;
use App\Traits\Helpers;
use Illuminate\Http\Request;
use App\Models\EspecialidadPrograma;
use Illuminate\Support\Facades\DB;

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
            'numero_modulo'    => 'required|string|max:10',
            'descripcion'      => 'nullable|string',
            'creditos'         => 'required|integer|min:0',
            'horas'            => 'required|integer|min:0',
            'id_especialidad'  => 'required|exists:especialidad_programa,id',
            'nro_capacidades'  => 'required|integer|min:0',

            // competencias (opcional)
            'competencias'                   => 'nullable|array',
            'competencias.*.tipo'            => 'required|string',
            'competencias.*.nombre'          => 'required|string',
            'competencias.*.descripcion'     => 'required|string|max:225',
        ]);

        DB::beginTransaction();

        try {
            /* =====================
           1️⃣ CREAR MÓDULO
        ====================== */
            $modulo = Modulo::create(
                $request->only([
                    'numero_modulo',
                    'descripcion',
                    'creditos',
                    'horas',
                    'id_especialidad',
                    'nro_capacidades',
                ])
            );

            /* =====================
           2️⃣ CREAR COMPETENCIAS + LOG
        ====================== */
            if ($request->filled('competencias')) {
                foreach ($request->competencias as $competencia) {

                    $nueva = Competencia::create([
                        'id_modulo'   => $modulo->id,
                        'tipo'        => $competencia['tipo'],
                        'nombre'      => $competencia['nombre'],
                        'descripcion' => $competencia['descripcion'],
                    ]);

                    // 🔥 LOG INDIVIDUAL
                    $this->registrarActividad(
                        "Agregó la competencia '{$nueva->nombre}' al módulo '{$modulo->numero_modulo}'",
                        "Creado"
                    );
                }
            }

            /* =====================
           3️⃣ LOG MÓDULO
        ====================== */
            $this->registrarActividad(
                "Creó el módulo N° '{$modulo->numero_modulo}' para la especialidad '{$modulo->especialidadPrograma->especialidadMadre->nombre_especialidad}'",
                "Creado"
            );

            DB::commit();

            return response()->json($modulo->load('competencias'), 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al crear el módulo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Mostrar un módulo específico
    public function show($id)
    {
        // Buscar los módulos con todas las relaciones necesarias + competencias
        $registros = Modulo::with([
            'competencias', // 👈 AQUI
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

        $especialidadPrograma = [
            'id' => $especialidadPrograma->id,
            'id_especialidad' => $especialidadPrograma->id_especialidad,
            'id_programa' => $especialidadPrograma->id_programa,
            'nro_modulos' => $especialidadPrograma->nro_modulos,
            'anio' => $especialidadPrograma->programaEstudio?->año,
            'nombre_ciclo' => $especialidadPrograma->programaEstudio?->ciclo?->nombre_ciclo,
            'nombre_especialidad' => $especialidadPrograma->especialidadMadre?->nombre_especialidad,
        ];

        // Armar los módulos + competencias
        $modulos = $registros->map(function ($modulo) {
            return [
                'id' => $modulo->id,
                'numero_modulo' => $modulo->numero_modulo,
                'descripcion' => $modulo->descripcion,
                'creditos' => $modulo->creditos,
                'horas' => $modulo->horas,
                'nro_capacidades' => $modulo->nro_capacidades,

                // 👇 COMPETENCIAS DEL MÓDULO
                'competencias' => $modulo->competencias->map(function ($competencia) {
                    return [
                        'id' => $competencia->id,
                        'tipo' => $competencia->tipo,
                        'nombre' => $competencia->nombre,
                        'descripcion' => $competencia->descripcion,
                    ];
                }),
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

        DB::transaction(function () use ($request, $modulo) {

            // =========================
            // VALIDACIÓN
            // =========================
            $request->validate([
                'numero_modulo'   => 'sometimes|string|max:10',
                'descripcion'     => 'sometimes|nullable|string',
                'creditos'        => 'sometimes|integer|min:0',
                'horas'           => 'sometimes|integer|min:0',
                'id_especialidad' => 'sometimes|exists:especialidad_programa,id',
                'nro_capacidades' => 'sometimes|integer|min:0',

                'competencias'                 => 'sometimes|array',
                'competencias.*.tipo'          => 'required|string',
                'competencias.*.nombre'        => 'required|string',
                'competencias.*.descripcion'   => 'required|string|max:225',
            ]);

            // =========================
            // ALIAS PARA ACTIVIDAD
            // =========================
            $alias = [
                'numero_modulo'   => 'Número del módulo',
                'descripcion'     => 'Descripción',
                'creditos'        => 'Créditos',
                'horas'           => 'Horas',
                'nro_capacidades' => 'N° de capacidades',
            ];

            $antes = $modulo->only(array_keys($alias));

            // =========================
            // ACTUALIZAR MÓDULO
            // =========================
            $modulo->update($request->except('competencias'));

            // =========================
            // 🔍 VERIFICAR SI TENÍA COMPETENCIAS
            // =========================
            $teniaCompetencias = Competencia::where('id_modulo', $modulo->id)->exists();

            // =========================
            // 🔥 ELIMINAR COMPETENCIAS
            // =========================
            Competencia::where('id_modulo', $modulo->id)->delete();

            if ($teniaCompetencias) {
                $this->registrarActividad(
                    "Eliminó todas las competencias del módulo '{$modulo->numero_modulo}'",
                    "Eliminado"
                );
            }

            // =========================
            // ➕ CREAR NUEVAS COMPETENCIAS
            // =========================
            if ($request->filled('competencias')) {
                foreach ($request->competencias as $competencia) {

                    $nueva = Competencia::create([
                        'id_modulo'   => $modulo->id,
                        'tipo'        => $competencia['tipo'],
                        'nombre'      => $competencia['nombre'],
                        'descripcion' => $competencia['descripcion'],
                    ]);

                    // 🔥 REGISTRO INDIVIDUAL
                    $this->registrarActividad(
                        "Agregó la competencia '{$nueva->nombre}' al módulo '{$modulo->numero_modulo}'",
                        "Creado"
                    );
                }
            }

            // =========================
            // DETECTAR CAMBIOS DEL MÓDULO
            // =========================
            $cambios = [];

            foreach ($antes as $campo => $valorAnterior) {
                if ($modulo->$campo != $valorAnterior) {
                    $cambios[] = $alias[$campo];
                }
            }

            $descripcionCambios = empty($cambios)
                ? "sin cambios importantes"
                : "campos modificados: " . implode(", ", $cambios);

            // =========================
            // 📝 ACTIVIDAD DEL MÓDULO
            // =========================
            if (!empty($cambios)) {
                $this->registrarActividad(
                    "Actualizó el módulo '{$modulo->numero_modulo}' de la especialidad '{$modulo->especialidadPrograma->especialidadMadre->nombre_especialidad}' ({$descripcionCambios})",
                    "Actualizado"
                );
            }
        });

        return response()->json($modulo->load('competencias'));
    }

    // Eliminar un módulo
    public function destroy($id)
    {
        $modulo = Modulo::with('competencias')->find($id);

        if (!$modulo) {
            return response()->json(['message' => 'Módulo no encontrado'], 404);
        }

        DB::transaction(function () use ($modulo) {

            $nombreModulo = $modulo->numero_modulo;
            $nombreEspecialidad = $modulo->especialidadPrograma->especialidadMadre->nombre_especialidad ?? "desconocida";

            // 🔥 ELIMINAR COMPETENCIAS
            $modulo->competencias()->delete();

            // 🔥 ELIMINAR MÓDULO
            $modulo->delete();

            // ACTIVIDAD
            $this->registrarActividad(
                "Eliminó el módulo '{$nombreModulo}' de la especialidad '{$nombreEspecialidad}'",
                "Eliminado"
            );
        });

        return response()->json(['message' => 'Módulo eliminado correctamente'], 204);
    }
}

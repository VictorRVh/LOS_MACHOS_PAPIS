<?php

namespace App\Http\Controllers;

use App\Models\CapacidadTerminal;
use App\Models\Grupo;
use App\Models\Matricula;
use App\Models\NotaCapacidadTerminal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class NotaCapacidadTerminalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notas = NotaCapacidadTerminal::with(['grupo', 'capacidadTerminal', 'estudiante'])->get();
        return response()->json($notas);
    }

    // public function index_grupo_capacidad_terminal($id)
    // {
    //     // ✅ Cantidad total de capacidades (independiente de si tienen notas)
    //     $cantidadCapacidades = CapacidadTerminal::where('id_grupo', $id)->count();

    //     // ✅ Solo capacidades que no tienen notas asignadas
    //     $capacidades = CapacidadTerminal::where('id_grupo', $id)
    //         ->orderBy('numero_capacidad', 'asc')
    //         ->select(
    //             'id',
    //             'id_grupo',
    //             'nombre_capacidad',
    //             'fecha_inicio',
    //             'fecha_fin',
    //             'status'
    //         )
    //         ->whereDoesntHave('notaCapacidadTerminal') // 👈 Filtro para que no tengan notas asociadas
    //         ->get();

    //     return response()->json([
    //         'capacidades' => $capacidades,
    //         'cantidad_capacidades' => $cantidadCapacidades, // 👈 Total de capacidades, incluyendo con notas
    //     ]);
    // }

    public function index_grupo_capacidad_terminal($id)
    {
        $cantidadCapacidades = CapacidadTerminal::where('id_grupo', $id)->count();

        $capacidades = CapacidadTerminal::where('id_grupo', $id)
            ->orderBy('numero_capacidad', 'asc')
            ->get()
            ->filter(function ($capacidad) {
                // 1️⃣ Sin nota registrada → disponible
                if ($capacidad->status_nota === 0) {
                    return true;
                }

                // 2️⃣ Aplazamiento aprobado → solo si la fecha aplazada no pasó
                if ($capacidad->status_nota === 2 && $capacidad->fecha_aplazada) {
                    return now()->lte($capacidad->fecha_aplazada);
                }

                // 3️⃣ Nota registrada → no disponible
                return false;
            })
            ->values(); // reindexa la colección

        return response()->json([
            'capacidades' => $capacidades,
            'cantidad_capacidades' => $cantidadCapacidades,
        ]);
    }

    public function index_grupo_alumnos($idGrupo)
    {
        /* =====================================================
     * 1. Estudiantes matriculados (sin reserva)
     * ===================================================== */
        $estudiantes = DB::table('matricula')
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->where('matricula.id_grupo', $idGrupo)
            ->where(function ($q) {
                $q->whereNull('matricula.reserva')
                    ->orWhere('matricula.reserva', 0);
            })
            ->select(
                'estudiante.id as id_estudiante',
                'matricula.matriculado',
                DB::raw("
                CONCAT(
                    estudiante.apellido_paterno, ' ',
                    estudiante.apellido_materno, ', ',
                    estudiante.nombre
                ) as apellidos_nombres
            ")
            )
            ->orderBy('estudiante.apellido_paterno', 'asc')
            ->get();


        /* =====================================================
     * 2. Capacidades (unidades) activas del grupo
     * ===================================================== */
        $capacidades = DB::table('capacidad_terminal')
            ->where('id_grupo', $idGrupo)
            ->select(
                'id as id_capacidad',
                'numero_capacidad',
                'nombre_capacidad',
                // 'status',
                // 'status_nota'
            )
            ->orderBy('numero_capacidad', 'asc')
            ->get();


        /* =====================================================
     * 3. TODAS las notas del grupo (una sola consulta)
     * ===================================================== */
        $notas = DB::table('nota_capacidad_terminal')
            ->where('id_grupo', $idGrupo)
            ->select(
                'id_estudiante',
                'id_capacidad',
                'nota_capacidad'
            )
            ->get()
            ->groupBy(function ($n) {
                return $n->id_estudiante . '-' . $n->id_capacidad;
            });

        /* =====================================================
        * 3.1 TODAS las notas de experiencia formativa del grupo
        * ===================================================== */
        $notasExperiencia = DB::table('nota_experiencia_formativa')
            ->where('id_grupo', $idGrupo)
            ->where('status', 1)
            ->whereNotNull('nota')
            ->orderByDesc('updated_at')
            ->orderByDesc('tipo_practicas')
            ->select(
                'id_estudiante',
                'nota'
            )
            ->get()
            ->groupBy('id_estudiante')
            ->map(function ($items) {
                return $items->first();
            });



        /* =====================================================
     * 4. Construir respuesta final normalizada
     * ===================================================== */
        $resultado = $estudiantes->map(function ($est) use ($capacidades, $notas, $notasExperiencia) {

            $capConNotas = $capacidades->map(function ($cap) use ($est, $notas) {

                $key = $est->id_estudiante . '-' . $cap->id_capacidad;
                $nota = $notas->get($key)?->first();

                return [
                    'id_capacidad'     => $cap->id_capacidad,
                    'numero_capacidad' => $cap->numero_capacidad,
                    'nota_capacidad'   => $nota->nota_capacidad ?? null,
                ];
            });

            // 👉 Nota de experiencia formativa
            $notaExp = $notasExperiencia->get($est->id_estudiante);

            return [
                'id_estudiante'     => $est->id_estudiante,
                'apellidos_nombres' => $est->apellidos_nombres,
                'matriculado'       => $est->matriculado,
                'nota_experiencia'  => $notaExp->nota ?? null,
                'capacidades'       => $capConNotas,
            ];
        });
        /* =====================================================
            * 5. Response
        * ===================================================== */
        return response()->json($resultado);
    }

    // GET /api/nota-capacidad-terminal/{id}
    public function show($id)
    {
        $nota = NotaCapacidadTerminal::with(['grupo', 'capacidadTerminal', 'estudiante'])->find($id);

        if (!$nota) {
            return response()->json(['message' => 'Nota no encontrada'], 404);
        }

        return response()->json($nota);
    }

    // POST /api/nota-capacidad-terminal
    public function store(Request $request)
    {
        // Validación correcta
        $request->validate([
            'id_grupo' => 'required|exists:grupo,id',
            'id_capacidad_terminal' => 'required|exists:capacidad_terminal,id',
            'notas' => 'required|array',
            'notas.*.id_estudiante' => 'required|exists:estudiante,id',
            'notas.*.nota' => 'nullable|numeric|min:0|max:20',
        ]);

        $capacidad = CapacidadTerminal::find($request->id_capacidad_terminal);

        if (!$capacidad) {
            return response()->json([
                'success' => false,
                'message' => 'Unidad Didáctica no encontrada',
            ], 404);
        }

        if (!$capacidad->puedeSubirNotas()) {
            return response()->json([
                'success' => false,
                'message' => $capacidad->mensaje_subida_notas,
                'estado' => $capacidad->status,
                'fecha_limite' => $capacidad->fecha_limite_subida->format('Y-m-d H:i:s'),
            ], 403);
        }

        try {
            DB::beginTransaction();

            foreach ($request->notas as $nota) {

                // 🔴 Si no tiene nota (retirado o vacío)
                if (is_null($nota['nota'])) {

                    // (Opcional pero recomendado) eliminar si ya existía
                    NotaCapacidadTerminal::where([
                        'id_grupo' => $request->id_grupo,
                        'id_capacidad' => $request->id_capacidad_terminal,
                        'id_estudiante' => $nota['id_estudiante'],
                    ])->delete();

                    continue; // 👈 importante: saltar al siguiente
                }

                // ✅ Solo guarda si SÍ tiene nota
                NotaCapacidadTerminal::updateOrCreate(
                    [
                        'id_grupo' => $request->id_grupo,
                        'id_capacidad' => $request->id_capacidad_terminal,
                        'id_estudiante' => $nota['id_estudiante'],
                    ],
                    [
                        'nota_capacidad' => $nota['nota'],
                        'updated_at' => now(),
                    ]
                );
            }

            $capacidad->status_nota = 1;
            $capacidad->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Notas registradas correctamente',
                'count' => count($request->notas),
                'fecha_limite' => $capacidad->fecha_limite_subida->format('d/m/Y H:i'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar las notas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // PUT/PATCH /api/nota-capacidad-terminal/{id}
    public function update(Request $request)
    {
        $request->validate([
            'id_grupo' => 'required|exists:grupo,id',
            'id_capacidad_terminal' => 'required|exists:capacidad_terminal,id',
            'notas' => 'required|array',
            'notas.*.id_estudiante' => 'required|exists:estudiante,id',
            'notas.*.nota' => 'required|numeric|min:0|max:20',
        ]);

        // Validar si se pueden subir notas
        $capacidad = CapacidadTerminal::find($request->id_capacidad_terminal);

        if (!$capacidad || !$capacidad->puedeSubirNotas()) {
            return response()->json([
                'success' => false,
                'message' => $capacidad ? $capacidad->mensaje_subida_notas : 'Capacidad no encontrada',
            ], 403);
        }

        try {
            DB::beginTransaction();

            $actualizadas = 0;
            $creadas = 0;

            foreach ($request->notas as $nota) {
                $notaExistente = NotaCapacidadTerminal::where('id_capacidad', $request->id_capacidad_terminal)
                    ->where('id_estudiante', $nota['id_estudiante'])
                    ->where('id_grupo', $request->id_grupo)
                    ->first();

                if ($notaExistente) {
                    $notaExistente->update(['nota_capacidad' => $nota['nota']]);
                    $actualizadas++;
                } else {
                    NotaCapacidadTerminal::create([
                        'id' => (string) Str::uuid(),
                        'nota_capacidad' => $nota['nota'],
                        'id_grupo' => $request->id_grupo,
                        'id_capacidad' => $request->id_capacidad_terminal,
                        'id_estudiante' => $nota['id_estudiante'],
                    ]);
                    $creadas++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Notas procesadas correctamente',
                'actualizadas' => $actualizadas,
                'creadas' => $creadas,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar las notas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // DELETE /api/nota-capacidad-terminal/{id}
    public function destroy($id)
    {
        $nota = NotaCapacidadTerminal::find($id);

        if (!$nota) {
            return response()->json(['message' => 'Nota no encontrada'], 404);
        }

        $nota->delete();

        return response()->json(['message' => 'Nota eliminada correctamente']);
    }

    public function listaAlumnosNotas($id)
    {
        $grupo = Grupo::findOrFail($id);

        $alumnos = $grupo->estudiantes()
            ->select('estudiante.id', 'apellido_paterno', 'apellido_materno', 'nombre', 'matricula.matriculado')
            ->get()
            ->makeHidden(['pivot']);

        return response()->json($alumnos);
    }

    public function obtenerInfoCapacidad($id)
    {
        $capacidad = CapacidadTerminal::with('grupo')->find($id);

        if (!$capacidad) {
            return response()->json([
                'success' => false,
                'message' => 'Capacidad no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $capacidad->id,
                'numero_capacidad' => $capacidad->numero_capacidad,
                'nombre_capacidad' => $capacidad->nombre_capacidad,
                'fecha_inicio' => $capacidad->fecha_inicio,
                'fecha_fin' => $capacidad->fecha_fin,
                'fecha_limite_subida' => $capacidad->fecha_limite_subida->format('Y-m-d H:i:s'),
                'puede_subir_notas' => $capacidad->puedeSubirNotas(),
                'estado_visual' => $capacidad->estado_visual,
                'status' => $capacidad->status,
                'status_texto' => $capacidad->status_texto,
                'mensaje' => $capacidad->mensaje_subida_notas,
                'grupo' => $capacidad->grupo,
            ],
        ]);
    }
}

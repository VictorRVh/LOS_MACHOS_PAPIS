<?php

namespace App\Http\Controllers;

use App\Models\Egresados;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EgresadosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $egresados = Egresados::with(['estudiante', 'especialidad'])->get();
        return response()->json($egresados);
    }


    public function datosEstudianteEgresado($id_egresado)
    {
        try {

            $egresado = DB::table('egresados as eg')

                ->join('estudiante as e', 'eg.id_estudiante', '=', 'e.id')

                ->join('especialidad_programa as ep', 'eg.id_especialidad', '=', 'ep.id')

                ->join('especialidad_madre as em', 'ep.id_especialidad', '=', 'em.id')

                ->join('ciclo_academico as ca', 'em.id_ciclo', '=', 'ca.id')

                ->select(

                    'eg.id as id_egresado',

                    DB::raw("
                    CONCAT(
                        e.apellido_paterno,' ',
                        e.apellido_materno,' ',
                        e.nombre
                    ) as apellidos_nombres
                "),

                    'e.nro_documento',

                    'em.nombre_especialidad as especialidad',

                    'ca.nombre_ciclo as ciclo'

                )

                ->where('eg.id', $id_egresado)

                ->first();


            if (!$egresado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Egresado no encontrado'
                ], 404);
            }


            // CETPRO
            $cetpro = DB::table('cetpros')->first();


            // DIRECTOR
            $director = User::role('directora')
                ->select(DB::raw("CONCAT(nombre,' ',apellido) as nombre_completo"))
                ->first();


            return response()->json([
                'success' => true,
                'data' => [

                    'id_egresado' => $egresado->id_egresado,

                    'apellidos_nombres' => $egresado->apellidos_nombres,

                    'nro_documento' => $egresado->nro_documento,

                    'especialidad' => $egresado->especialidad,

                    'ciclo' => $egresado->ciclo,

                    'cetpro' => [
                        'cetpro' => $cetpro->nombre ?? '',
                        'lugar' => $cetpro->lugar ?? '',
                        'director' => $director->nombre_completo ?? '',
                        'rd_autorizacion' => $cetpro->rd_autorizacion ?? '',
                        'rd_conversion' => $cetpro->rd_conversion ?? '',
                        'anio' => date('Y')
                    ]

                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // GET /api/egresados/{id}
    public function show($id)
    {
        $egresado = Egresados::with(['estudiante', 'especialidad'])->find($id);

        if (!$egresado) {
            return response()->json(['message' => 'Egresado no encontrado'], 404);
        }

        return response()->json($egresado);
    }

    // POST /api/egresados
    public function store(Request $request)
    {
        $request->validate([
            'turno'        => 'nullable|string|max:2',
            'id_estudiante' => 'required|uuid|exists:estudiante,id',
            'id_especialidad'     => 'required|uuid|exists:especialidad_programa,id',
        ]);

        $existe = Egresados::where('id_estudiante', $request->id_estudiante)
            ->where('id_especialidad', $request->id_especialidad)
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'El estudiante ya es egresado en esta especialidad'
            ], 409);
        }

        $egresado = Egresados::create([
            'id_estudiante' => $request->id_estudiante,
            'id_especialidad' => $request->id_especialidad,
        ]);

        return response()->json([
            'message' => 'Egresado registrado correctamente',
            'data' => $egresado
        ], 201);
    }

    // PATCH /api/egresados/{id}
    public function update(Request $request, $id)
    {
        $egresado = Egresados::find($id);

        if (!$egresado) {
            return response()->json(['message' => 'Egresado no encontrado'], 404);
        }

        $request->validate([
            'turno'        => 'sometimes|string|max:2',
            'id_estudiante' => 'sometimes|uuid|exists:estudiante,id',
            'id_especialidad'     => 'sometimes|uuid|exists:especialidad_programa,id',
        ]);

        $egresado->update($request->all());

        return response()->json([
            'message' => 'Egresado actualizado correctamente',
            'data' => $egresado
        ]);
    }

    // DELETE /api/egresados/{id}
    public function destroy($id)
    {
        $egresado = Egresados::find($id);

        if (!$egresado) {
            return response()->json(['message' => 'Egresado no encontrado'], 404);
        }

        $egresado->delete();

        return response()->json(['message' => 'Egresado eliminado correctamente']);
    }
}

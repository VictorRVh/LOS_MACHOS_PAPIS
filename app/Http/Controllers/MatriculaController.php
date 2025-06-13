<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use Illuminate\Http\Request;

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
        $request->validate([
            'id_grupo'      => 'required|uuid|exists:grupo,id',
            'turno'         => 'required|string|max:10',
            'id_estudiante' => 'required|uuid|exists:estudiante,id',
            'id_pago'       => 'nullable|uuid|exists:pago,id',
            'reserva'       => 'nullable|boolean'
        ]);

        $matricula = Matricula::create($request->all());

        return response()->json(['message' => 'Matrícula registrada con éxito', 'data' => $matricula], 201);
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
            'id_grupo'      => 'required|uuid|exists:grupo,id',
            'turno'         => 'required|string|max:10',
            'id_estudiante' => 'required|uuid|exists:estudiante,id',
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
}

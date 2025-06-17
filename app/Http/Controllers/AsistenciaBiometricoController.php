<?php

namespace App\Http\Controllers;

use App\Models\AsistenciaBiometrico;
use Illuminate\Http\Request;

class AsistenciaBiometricoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return AsistenciaBiometrico::with(['estudiante', 'calendario'])->get();
    }

    // Registrar nueva asistencia
    public function store(Request $request)
    {
        $request->validate([
            'fecha_actual' => 'required|date',
            'hora' => 'required',
            'tipo_registro' => 'required',
            'id_estudiante' => 'required|exists:estudiante,id',
            'id_calendario' => 'nullable|exists:calendario_admin,id',
            'asistencia' => 'boolean',
            'observacion' => 'nullable|string',
        ]);

        $asistencia = AsistenciaBiometrico::create($request->all());

        return response()->json($asistencia, 201);
    }

    // Mostrar un registro específico
    public function show($id)
    {
        return AsistenciaBiometrico::with(['estudiante', 'calendario'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $convenio = AsistenciaBiometrico::find($id);
        if (!$convenio) {
            return response()->json(['message' => 'Asistencia biometrica'], 404);
        }

        $request->validate([
            'fecha_actual' => 'sometimes|date',
            'hora' => 'required',
            // 'tipo_registro' => 'sometimes|in:entrada,salida',
            'tipo_registro' => 'sometimes',
            'id_estudiante' => 'sometimes|exists:estudiante,id',
            'id_calendario' => 'sometimes|exists:calendario_admin,id',
            'asistencia' => 'boolean',
            'observacion' => 'nullable|string',
        ]);

        $convenio->update($request->all());
        return response()->json($convenio, 200);
    }

    // Eliminar asistencia
    public function destroy($id)
    {
        $asistencia = AsistenciaBiometrico::findOrFail($id);
        $asistencia->delete();

        return response()->json(['mensaje' => 'Asistencia eliminada.']);
    }
}

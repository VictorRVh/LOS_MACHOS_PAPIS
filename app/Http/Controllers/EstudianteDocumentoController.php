<?php

namespace App\Http\Controllers;

use App\Models\EstudianteDocumento;
use Google\Service\ServiceControl\Auth;
use Illuminate\Http\Request;

class EstudianteDocumentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_estudiante' => 'required|uuid|exists:estudiantes,id',
            'tipo_documento' => 'required|integer',
            'fecha_emision' => 'required|date',
        ]);

        $documento = EstudianteDocumento::create([
            'id_estudiante' => $request->id_estudiante,
            'tipo_documento' => $request->tipo_documento,
            'fecha_emision' => $request->fecha_emision,
            'id_autor' => auth()->id()
        ]);

        return response()->json([
            'message' => 'Documento registrado correctamente',
            // 'data' => $documento,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

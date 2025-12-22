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
            'id_matricula' => 'required|uuid|exists:matricula,id',
            'tipo_documento' => 'required|integer',
            'fecha_emision' => 'nullable|date',
        ]);

        EstudianteDocumento::create([
            'id_matricula' => $request->id_matricula,
            'tipo_documento' => $request->tipo_documento,
            'fecha_emision' => $request->fecha_emision,
            'id_autor' => auth()->id()
        ]);

        return response()->json([
            // 'message' => 'Documento generado correctamente',
            'success' => 'true',
            // 'data' => $documento,
        ], 201);
    }

    public function verificarCertificado($codigo)
    {
        $documento = EstudianteDocumento::with([
            'matricula.estudiante',
            'matricula.grupo.modulo',
            'matricula.grupo.especialidad.especialidadMadre',
            'matricula.grupo.periodo',
        ])->where('id', $codigo)->first();

        if (!$documento) {
            return response()->json([
                'estado'  => false,
                'mensaje' => 'Documento NO válido',
            ], 404);
        }

        $matricula = $documento->matricula;
        $grupo = $matricula->grupo;

        return response()->json([
            'estado'  => true,
            'mensaje' => 'Documento válido',
            'data' => [
                'codigo'     => $documento->codigo,
                'documento'  => match ($documento->tipo_documento) {
                    1 => 'Constancia de Estudios',
                    2 => 'Certificado de Estudios',
                    3 => 'Certificado sin notas',
                    default => 'Documento académico',
                },
                'estudiante' => trim(
                    $matricula->estudiante->apellido_paterno . ' ' .
                        $matricula->estudiante->apellido_materno . ' ' .
                        $matricula->estudiante->nombre
                ),
                'especialidad' => $grupo->especialidad->especialidadMadre->nombre_especialidad ?? null,
                'modulo'       => $grupo->modulo->descripcion ?? null,
                'periodo'      => $grupo->periodo->nombre_periodo ?? null,
                'fecha_emision' => $documento->fecha_emision->format('d/m/Y'),
            ],
        ]);
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

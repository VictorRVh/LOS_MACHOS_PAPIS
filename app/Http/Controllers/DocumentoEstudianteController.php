<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentoEstudianteController extends Controller
{
    /**
     * Verificación de certificado (MODO PRUEBA)
     */
    public function verificarCertificado($codigo)
    {
        // Códigos válidos de prueba
        $codigosValidos = [
            'CERT-001',
            'CERT-002',
            'CERT-PRUEBA',
        ];

        if (!in_array($codigo, $codigosValidos)) {
            return response()->json([
                'estado' => false,
                'mensaje' => 'Certificado NO válido'
            ], 404);
        }

        return response()->json([
            'estado' => true,
            'mensaje' => 'Certificado válido (modo prueba)',
            'data' => [
                'estudiante' => 'Juan Pérez',
                'documento' => 'Certificado de Estudios',
                'fecha' => date('d/m/Y')
            ]
        ]);
    }
}

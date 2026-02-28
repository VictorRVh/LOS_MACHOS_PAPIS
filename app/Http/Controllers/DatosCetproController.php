<?php

namespace App\Http\Controllers;

use App\Models\Cetpro;
use Illuminate\Http\Request;
use App\Traits\Helpers;
use Illuminate\Support\Facades\DB;

class DatosCetproController extends Controller
{
    use Helpers;

    /**
     * Obtener datos del CETPRO (unico)
     */
    public function show()
    {
        $cetpro = Cetpro::first();

        return response()->json($cetpro);
    }

    /**
     * Crear o actualizar CETPRO
     * (singleton)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cetpro' => 'required|string|max:255',
            'director' => 'nullable|string|max:255',
            'anio' => 'required|string|max:255',
            'rd_autorizacion' => 'required|string|max:255',
            'rd_conversion' => 'nullable|string|max:255',
            'ugel' => 'required|string|max:255',
            'dre' => 'required|string|max:255',
            'tipo_gestion' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'provincia' => 'required|string|max:255',
            'distrito' => 'required|string|max:255',
            'lugar' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $cetpro = Cetpro::first();

            if ($cetpro) {
                $cetpro->update($validated);

                $this->registrarActividad(
                    "Actualizo los datos del CETPRO '{$cetpro->cetpro}'",
                    'Actualizado'
                );
            } else {
                $cetpro = Cetpro::create($validated);

                $this->registrarActividad(
                    "Registro los datos del CETPRO '{$cetpro->cetpro}'",
                    'Creado'
                );
            }

            DB::commit();

            return response()->json($cetpro, 200);
        } catch (\Exception $e) {
            DB::rollBack();

            throw new \Exception(
                'Error al guardar datos del CETPRO: '.$e->getMessage(),
                13333
            );
        }
    }

    /**
     * Eliminar CETPRO (borrado fisico)
     */
    public function destroy()
    {
        $cetpro = Cetpro::first();

        if (!$cetpro) {
            return response()->json(['message' => 'No existe CETPRO registrado'], 404);
        }

        $nombre = $cetpro->cetpro;

        $cetpro->delete();

        $this->registrarActividad(
            "Elimino los datos del CETPRO '{$nombre}'",
            'Eliminado'
        );

        return response()->json([
            'message' => 'Datos del CETPRO eliminados correctamente',
        ], 204);
    }
}

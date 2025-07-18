<?php

namespace App\Http\Controllers;

use App\Models\ComisionUsuario;
use Illuminate\Http\Request;
use App\Models\Comision;
use App\Models\Comisiones;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;


class ComisionUsuarioController extends Controller
{
    // Listar integrantes de una comisión
    // public function index($comisionId)
    // {
    //     $integrantes = ComisionUsuario::with('usuario')
    //         ->where('id_comision', $comisionId)
    //         ->get();

    //     return response()->json($integrantes);
    // }

    public function index()
    {
        $comisiones = Comisiones::with(['usuarios'])->get(); // Ya no con 'usuario'
        return response()->json($comisiones);
    }

    // Añadir un usuario a una comisión
    public function store(Request $request)
    {
        $request->validate([
            'titulo'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'usuarios'    => 'required|array|min:1',
            'usuarios.*'  => 'exists:users,id'
        ]);

        // Crear la comisión
        $comision = Comisiones::create([
            'titulo'      => $request->titulo,
            'descripcion' => $request->descripcion
        ]);

        // Asociar los usuarios (guardar en tabla pivote)
        foreach ($request->usuarios as $usuarioId) {
            DB::table('comision_usuario')->insert([
                'id_comision' => $comision->id,
                'id_usuario'  => $usuarioId,
            ]);
        }


        return response()->json([
            'message' => 'Comisión creada y usuarios asignados correctamente',
            'data'    => $comision
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'usuarios'    => 'required|array|min:1',
            'usuarios.*'  => 'exists:users,id'
        ]);

        $comision = Comisiones::find($id);

        if (!$comision) {
            return response()->json(['message' => 'Comisión no encontrada'], 404);
        }

        // Actualizar título y descripción
        $comision->update([
            'titulo'      => $request->titulo,
            'descripcion' => $request->descripcion
        ]);

        // Eliminar todos los usuarios actuales de esta comisión
        ComisionUsuario::where('id_comision', $comision->id)->delete();

        // Insertar los nuevos usuarios
        foreach ($request->usuarios as $usuarioId) {
            ComisionUsuario::create([
                'id_comision' => $comision->id,
                'id_usuario'  => $usuarioId
            ]);
        }

        return response()->json([
            'message' => 'Comisión actualizada correctamente',
            'data'    => $comision
        ]);
    }

    // Eliminar un usuario de una comisión
    public function destroy($id)
    {
        $registro = ComisionUsuario::find($id);

        if (!$registro) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $registro->delete();

        return response()->json(['message' => 'Usuario eliminado de la comisión']);
    }
}

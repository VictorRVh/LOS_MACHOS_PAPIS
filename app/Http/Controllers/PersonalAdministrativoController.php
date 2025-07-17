<?php

namespace App\Http\Controllers;

use App\Models\PersonalAdministrativo;
use Illuminate\Http\Request;
use App\Models\User;

class PersonalAdministrativoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    // Obtener todos los usuarios que NO sean docentes
    $usuarios = User::whereDoesntHave('roles', function ($query) {
        $query->where('name', 'docente');
    })
    ->with(['roles', 'personalAdministrativo']) // ahora funciona gracias a la relación agregada
    ->get();

    // Armar la respuesta
    $administrativo = $usuarios->map(function ($usuario) {
        return [
            'id'=> $usuario->id,
            'name' => $usuario->name,
            'apellido_paterno' => $usuario->apellido_paterno,
            'apellido_materno' =>  $usuario->apellido_materno,
            'dni'=>  $usuario->dni,
            'correo' => $usuario->email,
            'roles' => $usuario->roles->pluck('name'),
            'administrativo' => $usuario->personalAdministrativo
                ? [
                    'id' => $usuario->personalAdministrativo->id,
                    'turno' => $usuario->personalAdministrativo->turno,
                    'local' => $usuario->personalAdministrativo->local,
                ]
                : null,
        ];
    });

    return response()->json($administrativo);
}


    // GET /api/personal-administrativo/{id}
    public function show($id)
    {
        $item = PersonalAdministrativo::with('usuario')->find($id);

        if (!$item) {
            return response()->json(['message' => 'Personal no encontrado'], 404);
        }

        return response()->json($item);
    }

    // POST /api/personal-administrativo
    public function store(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|exists:users,id',
            'turno'      => 'required|string|max:2',
            'local'      => 'required|string|max:100',
        ]);

        $item = PersonalAdministrativo::create($request->all());

        return response()->json([
            'message' => 'Datos del personal creado correctamente',
            'data'    => $item
        ], 201);
    }

    // PATCH /api/personal-administrativo/{id}
    public function update(Request $request, $id)
    {
        $item = PersonalAdministrativo::find($id);

        if (!$item) {

            $item = PersonalAdministrativo::create(array_merge(
                $request->all(),
                ['id' => $id] // Solo si quieres mantener el mismo ID
            ));

            return response()->json([
                'message' => 'Datos del personal creado porque no existía',
                'data'    => $item
            ], 201);
        }

        $request->validate([
            'id_usuario' => 'sometimes|exists:users,id',
            'turno'      => 'sometimes|string|max:2',
            'local'      => 'sometimes|string|max:100',
        ]);

        $item->update($request->all());

        return response()->json([
            'message' => 'Datos del personal actualizado correctamente',
            'data'    => $item
        ]);
    }

    // DELETE /api/personal-administrativo/{id}
    public function destroy($id)
    {
        $item = PersonalAdministrativo::find($id);

        if (!$item) {
            return response()->json(['message' => 'Personal no encontrado'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Datos del personal eliminado correctamente']);
    }
}

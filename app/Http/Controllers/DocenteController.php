<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DocenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuariosDocentes = User::whereHas('roles', function ($query) {
            $query->where('name', 'docente');
        })
            ->with('docente')
            ->get();

        return response()->json($usuariosDocentes);
    }


    // Crear un nuevo docente
    public function store(Request $request)
    {
        // Validación de todos los datos
        $request->validate([
            // Campos del usuario
            'name'              => 'required|string|max:255',
            'usuario'           => 'required|string|max:50|unique:users,usuario',
            'dni'               => 'required|string|max:8|unique:users,dni',
            'apellido_paterno'  => 'required|string|max:100',
            'apellido_materno'  => 'required|string|max:100',
            'fecha_nacimiento'  => 'required|date',
            'email'             => 'required|email|unique:users,email',
            'telefono'          => 'nullable|string|max:15',
            'direccion'         => 'nullable|string|max:255',
            'password'          => 'required|string|min:6',
            'status'            => 'required|integer|in:0,1,2,3',

            // Campos del docente
            'codigo_modular'     => 'required|string|max:20',
            'especialidad'       => 'required|string|max:100',
            'condicion'          => 'required|string|max:50',
            'escala_magisterial' => 'nullable|string|max:50',
            'rd_nombramiento'    => 'nullable|string|max:50',
        ]);

        // Opcional: usar una transacción para que si falla algo, se revierta todo
        DB::beginTransaction();

        try {
            // 1. Crear el usuario
            $user = User::create([
                'name'             => $request->name,
                'usuario'          => $request->usuario,
                'dni'              => $request->dni,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'email'            => $request->email,
                'telefono'         => $request->telefono,
                'direccion'        => $request->direccion,
                'password'         => Hash::make($request->password),
                'status'           => $request->status,
            ]);

            // 2. Crear el registro docente con el ID del usuario recién creado
            $docente = Docente::create([
                'user_id'           => $user->id,
                'codigo_modular'    => $request->codigo_modular,
                'especialidad'      => $request->especialidad,
                'condicion'         => $request->condicion,
                'escala_magisterial' => $request->escala_magisterial,
                'rd_nombramiento'   => $request->rd_nombramiento,
            ]);

            // 3. (Opcional) asignar rol docente
            $user->roles()->attach(Role::where('name', 'docente')->first()->id);

            DB::commit();

            return response()->json([
                'message' => 'Docente creado correctamente',
                'user' => $user,
                'docente' => $docente
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al crear docente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Mostrar un docente específico
    public function show($id)
    {
        $docente = Docente::with('user')->find($id);

        if (!$docente) {
            return response()->json(['message' => 'Docente no encontrado'], 404);
        }

        return response()->json($docente);
    }

    // Actualizar un docente
    public function update(Request $request, $id)
    {
        $docente = Docente::find($id);

        if (!$docente) {
            return response()->json(['message' => 'Docente no encontrado'], 404);
        }

        $request->validate([
            'codigo_modular'     => 'sometimes|string|max:20',
            'especialidad'       => 'sometimes|string|max:100',
            'condicion'          => 'sometimes|string|max:50',
            'escala_magisterial' => 'nullable|string|max:50',
            'rd_nombramiento'    => 'nullable|string|max:50',
            'user_id'            => 'sometimes|exists:users,id',
        ]);

        $docente->update($request->all());
        return response()->json($docente);
    }

    // Eliminar un docente
    public function destroy($id)
    {
        $docente = Docente::find($id);

        if (!$docente) {
            return response()->json(['message' => 'Docente no encontrado'], 404);
        }

        $docente->delete();
        return response()->json(['message' => 'Docente eliminado correctamente']);
    }
}

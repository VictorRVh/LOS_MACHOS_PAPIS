<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Traits\Helpers;

class DocenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use Helpers;
    public function index()
    {
        $usuariosDocentes = User::whereHas('roles', function ($query) {
            $query->where('name', 'docente')->where('is_deleted', 0);
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
            'name' => 'required|string|max:255',
            'usuario' => 'required|string|max:50|unique:users,usuario',
            'dni' => 'required|string|max:8|unique:users,dni',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            'fecha_nacimiento' => 'required|date',
            'email' => 'required|email|unique:users,email',
            'telefono' => 'nullable|string|max:15',
            'direccion' => 'nullable|string|max:255',
            'password' => 'required|string|min:6',
            'status' => 'required|integer|in:0,1,2,3',

            // Campos del docente
            'codigo_modular' => 'nullable|string|max:20',
            'especialidad' => 'required|string|max:100',
            'condicion' => 'nullable|string|max:50',
            'escala_magisterial' => 'nullable|string|max:50',
            'rd_nombramiento' => 'nullable|string|max:50',
        ]);

        // Opcional: usar una transacción para que si falla algo, se revierta todo
        DB::beginTransaction();

        try {
            // 1. Crear el usuario
            $user = User::create([
                'name' => $request->name,
                'usuario' => $request->usuario,
                'dni' => $request->dni,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'password' => Hash::make($request->password),
                'status' => $request->status,
            ]);

            // 2. Crear el registro docente con el ID del usuario recién creado
            $docente = Docente::create([
                'user_id' => $user->id,
                'codigo_modular' => $request->codigo_modular,
                'especialidad' => $request->especialidad,
                'condicion' => $request->condicion,
                'escala_magisterial' => $request->escala_magisterial,
                'rd_nombramiento' => $request->rd_nombramiento,
            ]);

            // 3. (Opcional) asignar rol docente
            $user->roles()->attach(Role::where('name', 'docente')->first()->id);
            // 🔹 Registrar actividad
            $this->registrarActividad(
                "Creó el docente '{$user->name} {$user->apellido_paterno} {$user->apellido_materno}' con DNI: {$user->dni}",
                "Creado"
            );
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
        // 1. Buscar el usuario
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // 2. Buscar el docente por user_id
        $docente = Docente::where('user_id', $user->id)->first();

        // 3. Si no existe, crearlo
        if (!$docente) {
            $docente = Docente::create([
                'user_id' => $user->id,
                'codigo_modular' => '',
                'especialidad' => '',
                'condicion' => '',
                'escala_magisterial' => '',
                'rd_nombramiento' => '',
            ]);
        }

        DB::beginTransaction();

        try {
            // 4. Validación
            $request->validate([
                'name' => 'required|string|max:255',
                'usuario' => 'required|string|max:50|unique:users,usuario,' . $user->id,
                'dni' => 'required|string|max:8|unique:users,dni,' . $user->id,
                'apellido_paterno' => 'required|string|max:100',
                'apellido_materno' => 'required|string|max:100',
                'fecha_nacimiento' => 'required|date',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'telefono' => 'nullable|string|max:15',
                'direccion' => 'nullable|string|max:255',
                'status' => 'required|integer|in:0,1,2,3',

                'codigo_modular' => 'required|string|max:20',
                'especialidad' => 'required|string|max:100',
                'condicion' => 'required|string|max:50',
                'escala_magisterial' => 'nullable|string|max:50',
                'rd_nombramiento' => 'nullable|string|max:50',
            ]);

            // 5. Actualizar usuario
            $user->update([
                'name' => $request->name,
                'usuario' => $request->usuario,
                'dni' => $request->dni,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'status' => $request->status,
            ]);

            // 6. Actualizar docente
            $docente->update([
                'codigo_modular' => $request->codigo_modular,
                'especialidad' => $request->especialidad,
                'condicion' => $request->condicion,
                'escala_magisterial' => $request->escala_magisterial,
                'rd_nombramiento' => $request->rd_nombramiento,
            ]);
            $this->registrarActividad(
                "Actualizó los datos del docente '{$user->name} {$user->apellido_paterno} {$user->apellido_materno}'",
                "Actualizado"
            );
            DB::commit();

            return response()->json([
                'message' => 'Docente actualizado correctamente',
                'user' => $user,
                'docente' => $docente
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar docente',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Eliminar un docente
    public function destroy($id)
    {
        $docente = User::find($id);

        if (!$docente) {
            return response()->json(['message' => 'Docente no encontrado'], 404);
        }

        $nombre = $docente->name . " " .
            $docente->apellido_paterno . " " .
            $docente->apellido_materno;

        // $docente->delete();
        $docente->is_deleted = 1;
        $docente->save();

        // 🔹 Registrar actividad
        $this->registrarActividad(
            "Eliminó al docente '{$nombre}'",
            "Eliminado"
        );

        return response()->json(['message' => 'Docente eliminado correctamente'], 204);
    }


    public function getModulosAsignados()
    {
        $userId = auth()->id();

        return DB::table('grupo as g')
            ->join('especialidad_programa as ep', 'g.id_especialidad', '=', 'ep.id')
            ->join('especialidad_madre as em', 'ep.id_especialidad', '=', 'em.id')
            ->join('modulos as m', 'g.id_modulo', '=', 'm.id')
            ->leftJoin('docente as d', 'g.id_docente', '=', 'd.id')
            ->leftJoin('users as u', 'd.user_id', '=', 'u.id')
            ->leftJoin('matricula as ma', function ($join) {
                $join->on('ma.id_grupo', '=', 'g.id')
                    ->where('ma.reserva', 0); // <-- SOLO matriculados reales
            })
            ->where('d.user_id', $userId)
            ->select(
                'g.id as id_grupo',
                'em.nombre_especialidad as especialidad',
                'm.descripcion as modulo',
                DB::raw("CONCAT(u.name, ' ', u.apellido_paterno, ' ', u.apellido_materno) as docente"),
                'g.fecha_inicio',
                'g.fecha_fin',
                'g.seccion',
                'g.turno',
                DB::raw('COUNT(ma.id) as matriculados')
            )
            ->groupBy(
                'g.id',
                'em.nombre_especialidad',
                'm.descripcion',
                'u.name',
                'u.apellido_paterno',
                'u.apellido_materno',
                'g.fecha_inicio',
                'g.fecha_fin',
                'g.seccion',
                'g.turno'
            )
            ->get();
    }
}

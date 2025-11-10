<?php

namespace App\Http\Controllers;

use App\Models\Comisiones;
use App\Models\User;
use App\Traits\Error;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComisionesController extends Controller
{
    use Error;

    public function index(Request $request)
    {
        $comisiones = Comisiones::with(['usuarios:id,name,apellido_paterno,apellido_materno'])->get();

        $data = $comisiones->map(function ($comision) {
            return [
                'id' => $comision->id,
                'titulo' => $comision->titulo,
                'descripcion' => $comision->descripcion,
                'usuarios' => $comision->usuarios->map(function ($usuario) {
                    return [
                        'id' => $usuario->id,
                        'nameCompleto' => $usuario->name . ' ' . $usuario->apellido_paterno . ' ' . $usuario->apellido_materno,
                    ];
                }),
            ];
        });

        return response()->json($data);
    }
    public function index_filter()
    {
        $usuariosSinComision = User::doesntHave('comisiones')
        ->where('status', 1) // 👈 Solo activos
        ->select('id', 'name', 'apellido_paterno', 'apellido_materno')
        ->get();

        $data = $usuariosSinComision->map(function ($usuario) {
            return [
                'id' => $usuario->id,
                'nameCompleto' => $usuario->name . ' ' . $usuario->apellido_paterno . ' ' . $usuario->apellido_materno,
            ];
        });

        return response()->json($data);
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'titulo' => ['required', 'string', 'max:255'],
                'descripcion' => ['nullable', 'string'],
            ]);

            $comision = Comisiones::create($validated);

            if (isset($request->usuarios) && is_array($request->usuarios)) {
                $comision->usuarios()->sync($request->usuarios);
            }

            // Cargar solo lo necesario de los usuarios
            $comision->load([
                'usuarios' => function ($q) {
                    $q->select('users.id', 'users.name', 'users.apellido_paterno', 'users.apellido_materno');
                }
            ]);


            // Formatear usuarios para mostrar nombre completo
            $usuarios = $comision->usuarios->map(function ($usuario) {
                return [
                    'id' => $usuario->id,
                    'nameCompleto' => $usuario->name . ' ' . $usuario->apellido_paterno . ' ' . $usuario->apellido_materno,
                ];
            });

            return response()->json([
                'id' => $comision->id,
                'titulo' => $comision->titulo,
                'descripcion' => $comision->descripcion,
                'usuarios' => $usuarios,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'titulo' => ['required', 'string', 'max:255'],
                'descripcion' => ['nullable', 'string'],
            ]);

            $comision = Comisiones::find($id);
            if (!$comision) {
                throw new \Exception('Error|Comisión no encontrada--404', 13333);
            }

            $comision->titulo = $validated['titulo'];
            $comision->descripcion = $validated['descripcion'] ?? null;
            $comision->save();

            if (isset($request->usuarios) && is_array($request->usuarios)) {
                $comision->usuarios()->sync($request->usuarios);
            }

            return response()->json($comision);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }
    public function comision_docente($idUsuario){
         
        // Buscamos al usuario con sus comisiones
        $usuario = User::with('comisiones')->find($idUsuario);

        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no encontrado.'
            ], 404);
        }
        // Retornar datos del usuario y sus comisiones
        return response()->json([
            'usuario' => [
                'nombre_completo' => trim("{$usuario->apellido_paterno} {$usuario->apellido_materno} {$usuario->name}"),
            ],
            'comisiones' => $usuario->comisiones->map(function ($comision) {
                return [
                    'id' => $comision->id,
                    'titulo' => $comision->titulo,
                    'descripcion' => $comision->descripcion,
                ];
            }),
        ]);
    }


    public function destroy($id)
    {
        try {
            $comision = Comisiones::find($id);
            if (!$comision) {
                throw new \Exception('Error|Comisión no encontrada--404', 13333);
            }

            $comision->usuarios()->detach();
            $comision->delete();

            return response()->json([], 204);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }
}

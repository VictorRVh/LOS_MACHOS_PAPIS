<?php

namespace App\Http\Controllers;

use App\Models\Comisiones;
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
            $comision->load(['usuarios' => function ($q) {
                $q->select('users.id', 'users.name', 'users.apellido_paterno', 'users.apellido_materno');
            }]);


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


    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'comisionId' => ['required', Rule::exists('comisiones', 'id')],
                'titulo' => ['required', 'string', 'max:255'],
                'descripcion' => ['nullable', 'string'],
            ]);

            $comision = Comisiones::find($request->comisionId);
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

    public function destroy(Request $request)
    {
        try {
            $comision = Comisiones::find($request->comisionId);
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

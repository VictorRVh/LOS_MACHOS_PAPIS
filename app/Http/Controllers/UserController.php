<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Traits\Error;
use App\Traits\Helpers;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class UserController extends Controller
{
    use Error, Helpers;
    public function index(Request $request)
    {
        $users = User::with('roles.permissions')
            ->where('is_deleted', 0)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'super-directora');
            })
            ->get();

        $users = $users->map(
            fn($user) => $this->extractPermissionsFromUser($user)
        );

        return response()->json($users);
    }
    public function indexUserData($idUser)
    {
        try {
            $user = User::where('is_deleted', 0)
                ->where('id', $idUser)
                ->first();

            if (!$user) {
                throw new \Exception('Error|Usuario no encontrado--404', 13333);
            }

            // Bloquear si el usuario es super-directora
            if ($user->roles()->where('name', 'super-directora')->exists()) {
                throw new \Exception('Error|No autorizado para ver este usuario--401', 13333);
            }

            // Datos limpios del usuario SIN permisos pero con estado de cuenta
            $userData = [
                'id' => $user->id,
                'usuario' => $user->usuario, // login
                'name' => $user->name,
                'apellido_paterno' => $user->apellido_paterno,
                'apellido_materno' => $user->apellido_materno,
                'dni' => $user->dni,
                'email' => $user->email,
                'telefono' => $user->telefono,
                'direccion' => $user->direccion,
                'fecha_nacimiento' => $user->fecha_nacimiento,

                // 🔥 ESTADO DE LA CUENTA
                'password_cambiada' => $user->password_cambiada, // 0 = nunca cambió, 1 = ya cambió
                'status' => $user->status,                       // activo / inactivo

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];

            return response()->json($userData);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }


    public function index_filter_status()
    {
        $usuariosActivos = User::where('status', 1)
            ->where('is_deleted', 0)
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'super-directora');
            })
            ->select('id', 'name', 'apellido_paterno', 'apellido_materno')
            ->get();

        $users = $usuariosActivos->map(function ($usuario) {
            return [
                'id' => $usuario->id,
                'nameCompleto' => $usuario->name . ' ' . $usuario->apellido_paterno . ' ' . $usuario->apellido_materno,
            ];
        });

        return response()->json($users);
    }




    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required'],
                'usuario' => ['required'],
                'dni' => ['required', 'unique:users,dni'],
                'apellido_paterno' => ['required'],
                'apellido_materno' => ['required'],
                'fecha_nacimiento' => ['required'],
                'email' => ['required', 'email', 'unique:users,email'],
                'telefono' => ['required'],
                'direccion' => ['required'],
                'password' => ['required'],
                'status' => ['required'],
            ]);

            $validated['password'] = Hash::make($validated['password']);

            $user = new User();
            foreach ($validated as $key => $val) {
                $user->{$key} = $val;
            }
            $user->save();

            $rolesAsignadosNombres = [];

            if (isset($request->roles) && is_array($request->roles)) {

                // remover super-directora si está
                $superAdminRole = Role::where('name', 'super-directora')->first();
                $roles = array_filter(
                    $request->roles,
                    fn($roleId) => $roleId !== ($superAdminRole->id ?? null)
                );

                // sincronizar
                $user->roles()->sync($roles);

                // obtener nombres de roles asignados para el registro
                $rolesAsignadosNombres = Role::whereIn('id', $roles)->pluck('name')->toArray();
            }

            // Convertir roles a texto elegante
            $rolesTexto = empty($rolesAsignadosNombres)
                ? 'sin roles asignados'
                : implode(', ', $rolesAsignadosNombres);

            // REGISTRAR ACTIVIDAD
            $this->registrarActividad(
                "Creó un nuevo usuario: {$user->name} {$user->apellido_paterno} (DNI: {$user->dni}) con los roles: {$rolesTexto}",
                "Creado"
            );

            return response()->json($user);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }


    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required'],
                'usuario' => ['required'],
                'dni' => [
                    'required',
                    Rule::unique('users', 'dni')->ignore($request->userId),
                ],
                'apellido_paterno' => ['required'],
                'apellido_materno' => ['required'],
                'fecha_nacimiento' => ['required'],
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users', 'email')->ignore($request->userId),
                ],
                'telefono' => ['required'],
                'direccion' => ['required'],
                // 'password' => ['nullable'], // Puede ser opcional en update
                'status' => ['required'],
            ]);

            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $user = User::with('roles')->find($request->userId);
            if (!$user) {
                throw new \Exception('Error|Usuario no encontrado --404', 13333);
            }

            $usersSuperAdminRole = $user->roles->firstWhere(
                'name',
                'super-directora'
            );
            if ($usersSuperAdminRole) {
                throw new \Exception(
                    'Error|Usuario super-directora\'t no se puede editar--401',
                    13333
                );
            }
            $nombreAnterior = $user->name . ' ' . $user->apellido_paterno . ' ' . $user->apellido_materno;
            foreach ($validated as $key => $val) {
                $user->{$key} = $val;
            }
            $user->save();

            if (isset($request->roles) && is_array($request->roles)) {
                // removing super admin role if it is exists on the role list;
                // so there is no possibility to add super-directora role for updated users
                $superAdminRole = Role::where('name', 'super-directora')->first();

                $roles = [];
                if ($superAdminRole) {
                    $roles = array_filter(
                        $request->roles,
                        fn($roleId) => $roleId !== $superAdminRole->id
                    );
                } else {
                    $roles = $request->roles;
                }

                $user->roles()->sync($roles);
            }
            $this->registrarActividad(
                "Actualizó el usuario '{$nombreAnterior}'",
                "Actualizado"
            );

            return response()->json($user);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }

    public function updateProfile(Request $request, $id)
    {
        try {
            if ((int) Auth::id() !== (int) $id) {
                return response()->json(['message' => 'No autorizado para editar este perfil'], 403);
            }

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'apellido_paterno' => ['required', 'string', 'max:255'],
                'apellido_materno' => ['nullable', 'string', 'max:255'],
                'dni' => [
                    'nullable',
                    'string',
                    Rule::unique('users', 'dni')->ignore($id),
                ],
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users', 'email')->ignore($id),
                ],
                'telefono' => ['nullable', 'string', 'max:20'],
                'direccion' => ['nullable', 'string', 'max:255'],
                'fecha_nacimiento' => ['nullable', 'date'],
                'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            ]);

            $user = User::find($id);
            if (!$user) {
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }

            unset($validated['avatar']);

            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $mimeType = $avatar->getMimeType();

                if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
                    return response()->json(['message' => 'Formato de imagen no permitido'], 422);
                }

                $relativeDirectory = 'uploads/avatars';
                $absoluteDirectory = public_path($relativeDirectory);

                if (!File::exists($absoluteDirectory)) {
                    File::makeDirectory($absoluteDirectory, 0755, true);
                }

                if ($user->avatar_path) {
                    $previousAvatar = public_path($user->avatar_path);
                    if (File::exists($previousAvatar)) {
                        File::delete($previousAvatar);
                    }
                }

                $canConvertToWebp =
                    function_exists('imagewebp') &&
                    (
                        ($mimeType === 'image/jpeg' && function_exists('imagecreatefromjpeg')) ||
                        ($mimeType === 'image/png' && function_exists('imagecreatefrompng'))
                    );

                if ($canConvertToWebp) {
                    $avatarRelativePath = "{$relativeDirectory}/user-{$user->id}.webp";
                    $avatarAbsolutePath = public_path($avatarRelativePath);

                    $imageResource = match ($mimeType) {
                        'image/jpeg' => imagecreatefromjpeg($avatar->getRealPath()),
                        'image/png' => imagecreatefrompng($avatar->getRealPath()),
                        default => null,
                    };

                    if (!$imageResource) {
                        return response()->json(['message' => 'No se pudo procesar la imagen seleccionada'], 422);
                    }

                    if ($mimeType === 'image/png') {
                        imagepalettetotruecolor($imageResource);
                        imagealphablending($imageResource, true);
                        imagesavealpha($imageResource, true);
                    }

                    $webpGenerated = imagewebp($imageResource, $avatarAbsolutePath, 82);
                    imagedestroy($imageResource);

                    if (!$webpGenerated) {
                        return response()->json(['message' => 'No se pudo convertir la imagen a webp'], 422);
                    }

                    $validated['avatar_path'] = $avatarRelativePath;
                } else {
                    $extension = $mimeType === 'image/png' ? 'png' : 'jpg';
                    $avatarFileName = "user-{$user->id}.{$extension}";
                    $avatar->move($absoluteDirectory, $avatarFileName);
                    $validated['avatar_path'] = "{$relativeDirectory}/{$avatarFileName}";
                }
            }

            $user->update($validated);
            $user->refresh();

            return response()->json($this->extractPermissionsFromUser($user));


            // return response()->json($user);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }


    public function destroy(Request $request)
    {
        try {
            $user = User::with('roles')->find($request->userId);
            if (!$user) {
                throw new \Exception('Error|User not found--404', 13333);
            }

            $superAdminRole = $user->roles->firstWhere('name', 'super-directora');
            if ($superAdminRole) {
                throw new \Exception(
                    'Error|Super admin user can\'t be deleted--401',
                    13333
                );
            }
            $nombre = $user->name . ' ' . $user->apellido_paterno . ' ' . $user->apellido_materno;
            $user->is_deleted = 1;
            $user->save();
            $this->registrarActividad(
                "Eliminó al usuario '{$nombre}'",
                "Eliminado"
            );
            return response()->json([], 204);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }

    public function updatePassword(Request $request, $id)
    {
        try {
            if ((int) Auth::id() !== (int) $id) {
                return response()->json(['message' => 'No autorizado para actualizar esta contraseña'], 403);
            }

            $validated = $request->validate([
                'current_password' => ['required'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $user = User::find($id);
            if (!$user) {
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }

            // Validar contraseña actual
            if (!Hash::check($validated['current_password'], $user->password)) {
                throw new \Exception('Error|La contraseña actual no coincide.--403', 13333);
            }

            // Actualizar contraseña
            $user->password = Hash::make($validated['password']);
            $user->save();

            return response()->json(['message' => 'Contraseña actualizada correctamente']);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }

    public function restaurarPassword(Request $request, $id)
    {
        try {

            $user = User::find($id);

            if (!$user) {
                throw new \Exception('Error|Usuario no encontrado --404', 13333);
            }

            // 🔥 No permitir restaurar a super-directora
            if ($user->roles()->where('name', 'super-directora')->exists()) {
                throw new \Exception('Error|No autorizado para restaurar contraseña de este usuario--401', 13333);
            }

            // 🔥 NUEVA CONTRASEÑA (elige una)
            // 1) Contraseña por defecto fija
            // $newPassword = "12345678";

            // 2) Contraseña igual al DNI del usuario
            $newPassword = $request->password;

            // 3) Contraseña aleatoria
            // $newPassword = Str::random(10);

            // Guardar contraseña restaurada
            $user->password = Hash::make($newPassword);

            // 🔥 MUY IMPORTANTE: marcar como que NO ha cambiado su contraseña
            $user->password_cambiada = 0;

            $user->save();

            // Registrar actividad en tu sistema
            $this->registrarActividad(
                "Restauró la contraseña del usuario {$user->name} {$user->apellido_paterno} (DNI: {$user->dni})",
                "Restaurado"
            );

             return response()->json([
            'message' => 'Contraseña restaurada correctamente',
            'user' => [
                'id' => $user->id,
                'nombre' => $user->name . ' ' . $user->apellido_paterno . ' ' . $user->apellido_materno,
            ]
        ]);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }
}

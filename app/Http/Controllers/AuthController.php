<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\Error;
use App\Traits\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use Error, Helpers;

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'usuario' => ['required'],
                'password' => ['required'],
            ]);

            $user = User::with('roles.permissions')
                ->where('usuario', $credentials['usuario'])
                ->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                throw new \Exception('Error|Las credenciales no coinciden--403', 13333);
            }

            if ($user->status == 0) {
                throw new \Exception('Error|Este usuario está desactivado--403', 13333);
            }

            // Si no ha cambiado su contraseña, no iniciar sesión aún
            if ($user->password_cambiada) {
                return response()->json([
                    'requiereCambioPassword' => true,
                    'user_id' => $user->id,
                ]);
            }

            // ✅ Si ya cambió su contraseña, iniciar sesión normalmente
            $request->session()->regenerate();
            Auth::loginUsingId($user->id, true);

            return response()->json([
                'requiereCambioPassword' => false,
                'user' => $this->extractPermissionsFromUser($user),
            ]);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }



    public function verify()
    {
        if (!($id = Auth::id())) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        $user = User::with('roles.permissions')->find($id);

        return response()->json($this->extractPermissionsFromUser($user));
    }

    public function logout(Request $request)
    {
        try {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json(true);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }

    public function cambiarPasswordPrimeraVez(Request $request)
    {
        $request->validate([
            'nueva_password' => 'required|min:6|confirmed',
            'user_id' => 'required|exists:users,id', // <-- validar que el user_id exista
        ]);

        // Buscar usuario por el ID enviado
        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $user->password = Hash::make($request->nueva_password);
        $user->password_cambiada = false;
        $user->save();

        return response()->json(['message' => 'Contraseña actualizada con éxito']);
    }

}

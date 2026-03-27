<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\Error;
use App\Traits\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

            $throttleKey = $this->getLoginThrottleKey(
                $request,
                $credentials['usuario'],
            );

            $this->ensureLoginIsNotRateLimited($throttleKey);

            $user = User::with('roles.permissions')
                ->where('usuario', $credentials['usuario'])
                ->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                $lockoutSeconds = $this->registerFailedLoginAttempt($throttleKey);

                if ($lockoutSeconds !== null) {
                    throw new \Exception(
                        "Error|Demasiados intentos fallidos. Intenta nuevamente en {$lockoutSeconds} segundos.--403",
                        13333,
                    );
                }

                throw new \Exception('Error|Las credenciales no coinciden--403', 13333);
            }

            $this->clearLoginThrottleState($throttleKey);

            if ($user->status == 0) {
                throw new \Exception('Error|Este usuario está desactivado--403', 13333);
            }

            // ACA ERA PE VICTOR PTMRE
            if (!$user->password_cambiada) {
                return response()->json([
                    'requiereCambioPassword' => true,
                    'user_id' => $user->id,
                ]);
            }

            // YA CAMBIÓ SU CONTRASEÑA E INICIA SESION
            $request->session()->regenerate();
            Auth::loginUsingId($user->id, true);
            $this->markUserAsOnline($user->id);

            return response()->json([
                'requiereCambioPassword' => false,
                'user' => $this->extractPermissionsFromUser($user),
            ]);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }

    protected function getLoginThrottleKey(Request $request, string $usuario): string
    {
        return 'login_attempts:' . sha1(
            mb_strtolower(trim($usuario)) . '|' . $request->ip(),
        );
    }

    protected function ensureLoginIsNotRateLimited(string $throttleKey): void
    {
        $state = $this->getLoginThrottleState($throttleKey);
        $lockedUntil = $state['locked_until'] ?? null;

        if (!$lockedUntil) {
            return;
        }

        $retryAfter = $lockedUntil - now()->timestamp;

        if ($retryAfter > 0) {
            throw new \Exception(
                "Error|Demasiados intentos fallidos. Intenta nuevamente en {$retryAfter} segundos.--403",
                13333,
            );
        }

        $state['locked_until'] = null;
        $this->storeLoginThrottleState($throttleKey, $state);
    }

    protected function registerFailedLoginAttempt(string $throttleKey): ?int
    {
        $state = $this->getLoginThrottleState($throttleKey);
        $state['failed_attempts'] = ($state['failed_attempts'] ?? 0) + 1;

        if ($this->shouldApplyLockout($state['failed_attempts'])) {
            $lockoutSeconds = $this->calculateLockoutSeconds(
                $state['failed_attempts'],
            );

            $state['locked_until'] = now()->addSeconds($lockoutSeconds)->timestamp;
            $this->storeLoginThrottleState($throttleKey, $state);

            return $lockoutSeconds;
        }

        $state['locked_until'] = null;
        $this->storeLoginThrottleState($throttleKey, $state);

        return null;
    }

    protected function shouldApplyLockout(int $failedAttempts): bool
    {
        return $failedAttempts === 5 ||
            ($failedAttempts > 5 && ($failedAttempts - 5) % 2 === 0);
    }

    protected function calculateLockoutSeconds(int $failedAttempts): int
    {
        if ($failedAttempts <= 5) {
            return 15;
        }

        return (((int) floor(($failedAttempts - 5) / 2)) + 1) * 15;
    }

    protected function getLoginThrottleState(string $throttleKey): array
    {
        return Cache::get($throttleKey, [
            'failed_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    protected function storeLoginThrottleState(
        string $throttleKey,
        array $state,
    ): void {
        Cache::put($throttleKey, $state, now()->addHours(12));
    }

    protected function clearLoginThrottleState(string $throttleKey): void
    {
        Cache::forget($throttleKey);
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
            $userId = Auth::id();

            Auth::guard('web')->logout();

            if ($userId) {
                $this->markUserAsOffline($userId);
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // return response()->json(true);
            return response()
                ->json(true);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }

    public function cambiarPasswordPrimeraVez(Request $request)
    {
        $request->validate([
            'nueva_password' => 'required|min:6|confirmed',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        if (Hash::check($request->nueva_password, $user->password)) {
            return response()->json([
                'message' => 'La nueva contraseña no puede ser igual a la anterior',
            ], 422);
        }

        $user->password = Hash::make($request->nueva_password);
        $user->password_cambiada = true; // PASSWORD CAMBIADA
        $user->save();

        $request->session()->regenerate();
        Auth::loginUsingId($user->id, true);
        $this->markUserAsOnline($user->id);

        return response()->json([
            'message' => 'Contraseña actualizada con éxito',
            'requiereCambioPassword' => false, // PARA MOSTRAR EN EL FRONT
            'user' => $this->extractPermissionsFromUser($user),
        ]);
    }

    protected function markUserAsOnline(int $userId): void
    {
        Cache::put(
            $this->getUserOnlineCacheKey($userId),
            now()->timestamp,
            now()->addMinutes(2),
        );
    }

    protected function markUserAsOffline(int $userId): void
    {
        Cache::forget($this->getUserOnlineCacheKey($userId));
    }

    protected function getUserOnlineCacheKey(int $userId): string
    {
        return "user-online:{$userId}";
    }
}

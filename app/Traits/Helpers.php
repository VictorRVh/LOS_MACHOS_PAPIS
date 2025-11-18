<?php

namespace App\Traits;

use App\Models\ActividadesRecientes;
use App\Models\User;

trait Helpers
{
    // public function extractPermissionsFromUser(User $user): User
    // {
    //     $permissions = collect();

    //     foreach ($user->roles as $role) {
    //         $permissions = $permissions->merge($role->permissions);
    //     }

    //     $permissions = $permissions
    //         ->flatten()
    //         ->unique('id')
    //         ->values();

    //     $user->permissions = $permissions;

    //     return $user;
    // }

    public function extractPermissionsFromUser(User $user): User
    {
        $permissions = collect();

        foreach ($user->roles as $role) {
            // Limpiar permisos de cada rol
            $role->permissions->each(function ($permission) {
                $permission->makeHidden(['created_at', 'updated_at', 'pivot']);
            });

            $permissions = $permissions->merge($role->permissions);

            // Limpiar atributos del rol
            $role->makeHidden(['created_at', 'updated_at', 'pivot']);
        }

        // Quitar duplicados y reindexar
        $permissions = $permissions
            ->flatten()
            ->unique('id')
            ->values();

        $user->permissions = $permissions;

        // Limpiar el propio user si lo deseas
        $user->makeHidden(['updated_at', 'email_verified_at']);

        return $user;
    }


    // insertar actividades .. leo csmr ni me lo toques 
    public function registrarActividad($descripcion,$action)
    {
        $user = auth()->user();

        // Tomamos el primer rol del usuario (si tiene)
        $rol = $user->roles()->first();
        $idRole = $rol ? $rol->id : null;

        ActividadesRecientes::create([
            'id_role'    => $idRole,       // puede ser null
            'id_usuario' => $user->id,
            'descripcion' => $descripcion,
            'accion' => $action,
            // no necesitamos 'fecha', usamos created_at
        ]);
    }

    // public function extractPermissionsFromUser(User $user, $includePermissions = true): User
    // {
    //     if ($includePermissions) {
    //         $permissions = collect();

    //         foreach ($user->roles as $role) {
    //             $role->permissions->each(function ($permission) {
    //                 $permission->makeHidden(['created_at', 'updated_at', 'pivot']);
    //             });

    //             $permissions = $permissions->merge($role->permissions);

    //             $role->makeHidden(['created_at', 'updated_at', 'pivot']);
    //         }

    //         $permissions = $permissions
    //             ->flatten()
    //             ->unique('id')
    //             ->values();

    //         $user->permissions = $permissions;
    //     } else {
    //         // Aun así limpiamos los roles
    //         foreach ($user->roles as $role) {
    //             $role->makeHidden(['created_at', 'updated_at', 'pivot']);
    //         }
    //     }

    //     $user->makeHidden(['updated_at', 'email_verified_at']);

    //     return $user;
    // }
}

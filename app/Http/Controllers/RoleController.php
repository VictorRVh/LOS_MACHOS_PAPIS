<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Traits\Error;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Traits\Helpers;

class RoleController extends Controller
{
    use Error, Helpers;
    public function index(Request $request)
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'unique:roles,name'],
            ]);

            $role = new Role();
            foreach ($validated as $key => $val) {
                $role->{$key} = $val;
            }
            $role->save();

            if (isset($request->permissions) && is_array($request->permissions)) {
                $role->permissions()->sync($request->permissions);
            }

            // Registrar actividad
            $this->registrarActividad("Creó el rol '{$role->name}'", "Creado");

            return response()->json($role);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }

    public function update(Request $request)
    {
        try {
            // Validar nombre del rol
            $validated = $request->validate([
                'name' => [
                    'required',
                    Rule::unique('roles')->ignore($request->roleId),
                ],
            ], [
                'name.required' => 'El nombre del rol es obligatorio.',
                'name.unique' => 'Este nombre de rol ya existe.',
            ]);

            // Buscar el rol
            $role = Role::find($request->roleId);
            if (!$role) {
                throw new \Exception('Error|Rol no encontrado--404', 13333);
            }

            // Evitar modificar el rol super-directora
            if ($role->name === 'super-directora') {
                throw new \Exception(
                    'Error|No se puede modificar el rol de super administradora--403',
                    13334
                );
            }

            $nombreAnterior = $role->name; // Guardamos nombre anterior
            $nombreCambiado = false;

            // Actualizar nombre solo si es diferente
            if (isset($validated['name']) && $validated['name'] !== $nombreAnterior) {
                $role->name = $validated['name'];
                $nombreCambiado = true;
            }

            $role->save();

            // Actualizar permisos si vienen en el request
            if (isset($request->permissions) && is_array($request->permissions)) {
                $role->permissions()->sync($request->permissions);

                // Registrar actividad de permisos
                $this->registrarActividad(
                    "Actualizó los permisos del rol '{$role->name}'",
                    "Actualizado"
                );
            }

            // Registrar actividad de nombre solo si cambió
            if ($nombreCambiado) {
                $this->registrarActividad(
                    "Actualizó el rol '{$nombreAnterior}' → '{$role->name}'",
                    "Actualizado"
                );
            }

            return response()->json($role);
        } catch (\Exception $error) {
            // Si quieres, puedes personalizar también la función errorResponse para que los mensajes sean más claros
            return $this->errorResponse($error);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $role = Role::find($request->roleId);
            if (!$role) {
                throw new \Exception('Error|Role not found--404', 13333);
            }
            if ($role->name === 'super-directora') {
                throw new \Exception(
                    'Error|Super admin role can\'t be deleted--404',
                    13333
                );
            }
            $nombreRol = $role->name; // Guardamos el nombre para el registro
            $role->delete();
            // Registrar actividad
            $this->registrarActividad("Eliminó el rol '{$nombreRol}'", "Eliminado");

            return response()->json([], 204);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }
}

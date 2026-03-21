<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionRoleTableSeeder extends Seeder
{
    public function run(): void
    {
        $permissionRole = [];

        $docentePermissions = [
            'ver-perfil-docente',
            'editar-perfil-docente',
            'ver-mis-módulos',
            'ver-estudiantes-asignados',
            'todo-acceso-unidad-didáctica-docente',
            'ver-unidad-didáctica-docente',
            'crear-unidad-didáctica-docente',
            'editar-unidad-didáctica-docente',
            'eliminar-unidad-didáctica-docente',
            'todo-acceso-sesiones-docente',
            'ver-sesiones-docente',
            'crear-sesiones-docente',
            'editar-sesiones-docente',
            'eliminar-sesiones-docente',
            'todo-acceso-unidad-didáctica-notas-docente',
            'ver-unidad-didáctica-notas-docente',
            'crear-unidad-didáctica-notas-docente',
            'editar-unidad-didáctica-notas-docente',
            'eliminar-unidad-didáctica-notas-docente',
            'todo-acceso-alumnos-docente',
            'ver-alumnos-docente',
            'editar-alumnos-docente',
            'ver-comisión-docente',
            'todo-acceso-capacidades-docente',
            'ver-capacidades-docente',
            'crear-capacidades-docente',
            'editar-capacidades-docente',
            'eliminar-capacidades-docente',
        ];

        $docentePermissionIds = DB::table('permissions')
            ->whereIn('name', $docentePermissions)
            ->pluck('id')
            ->toArray();

        // Con esta webada filtramos los permisos del admin 
        $adminPermissionIds = DB::table('permissions')
            ->whereNotIn('id', $docentePermissionIds)
            ->pluck('id');

        foreach ($adminPermissionIds as $pid) {
            foreach ([1, 2, 4] as $roleId) {
                $permissionRole[] = [
                    'role_id'       => $roleId,
                    'permission_id' => $pid,
                    'created_at'    => now(),
                ];
            }
        }

        // Rol docente: solo permisos de docente
        foreach ($docentePermissionIds as $pid) {
            $permissionRole[] = [
                'role_id'       => 6,
                'permission_id' => $pid,
                'created_at'    => now(),
            ];
        }

        DB::table('permission_role')->insert($permissionRole);
    }
}

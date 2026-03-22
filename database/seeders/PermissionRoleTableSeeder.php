<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionRoleTableSeeder extends Seeder
{
    public function run(): void
    {
        $permissionRole = [];

        // =========================
        // PERMISOS SOLO DOCENTE
        // =========================
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

            // compartidos PERO solo para docente
            'editar-perfil',
            'editar-password',
        ];

        // =========================
        // IDS
        // =========================
        $allPermissionIds = DB::table('permissions')->pluck('id');

        $docentePermissionIds = DB::table('permissions')
            ->whereIn('name', $docentePermissions)
            ->pluck('id');

        // =========================
        // ADMIN (1,2,4) → TODOS
        // =========================
        foreach ($allPermissionIds as $permissionId) {
            foreach ([1, 2, 4] as $roleId) {
                $permissionRole[] = [
                    'role_id'       => $roleId,
                    'permission_id' => $permissionId,
                    'created_at'    => now(),
                ];
            }
        }

        // =========================
        // DOCENTE (6) → SOLO DOCENTE
        // =========================
        foreach ($docentePermissionIds as $permissionId) {
            $permissionRole[] = [
                'role_id'       => 6,
                'permission_id' => $permissionId,
                'created_at'    => now(),
            ];
        }

        // =========================
        // INSERT SEGURO
        // =========================
        DB::table('permission_role')->insertOrIgnore($permissionRole);
    }
}
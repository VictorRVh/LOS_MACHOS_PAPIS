<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionRole = [];

        for ($i = 1; $i <= 106; $i++) {
            $permissionRole[] = [
                'role_id' => 1,
                'permission_id' => $i,
                'created_at' => now(),
            ];

            $permissionRole[] = [
                'role_id' => 2,
                'permission_id' => $i,
                'created_at' => now(),
            ];

            $permissionRole[] = [
                'role_id' => 4,
                'permission_id' => $i,
                'created_at' => now(),
            ];
        }

        $docentePermissions = [
            'ver-perfil-docente',
            'editar-perfil-docente',
            'ver-mis-módulos',
            'ver-estudiantes-asignados',

            //Permisos para capacida terminal
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
            // 'crear-alumnos-docente',
            'editar-alumnos-docente',
            // 'eliminar-alumnos-docente',

            // comsiones ...  
            'ver-comisión-docente',

            //Competencias 
            'todo-acceso-capacidades-docente',
            'ver-capacidades-docente',
            'crear-capacidades-docente',
            'editar-capacidades-docente',
            'eliminar-capacidades-docente',

        ];

        // buscar IDs de los permisos creados en la tabla permissions
        $permissionIds = DB::table('permissions')
            ->whereIn('name', $docentePermissions)
            ->pluck('id');

        foreach ($permissionIds as $pid) {
            $permissionRole[] = [
                'role_id' => 6, // docente
                'permission_id' => $pid,
                'created_at' => now(),
            ];
        }

        DB::table('permission_role')->insert($permissionRole);
    }
}

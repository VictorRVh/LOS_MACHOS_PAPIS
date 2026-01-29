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

        for ($i = 1; $i <= 96; $i++) {
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
            'ver-mis-modulos',
            'ver-estudiantes-asignados',

            //Permisos para capacida terminal
            'todo-acceso-capacidad-terminal-docente',
            'ver-capacidad-terminal-docente',
            'crear-capacidad-terminal-docente',
            'editar-capacidad-terminal-docente',
            'eliminar-capacidad-terminal-docente',

            'todo-acceso-sesiones-docente',
            'ver-sesiones-docente',
            'crear-sesiones-docente',
            'editar-sesiones-docente',
            'eliminar-sesiones-docente',

            'todo-acceso-capacidad-terminal-notas-docente',
            'ver-capacidad-terminal-notas-docente',
            'crear-capacidad-terminal-notas-docente',
            'editar-capacidad-terminal-notas-docente',
            'eliminar-capacidad-terminal-notas-docente',

            'todo-acceso-alumnos-docente',
            'ver-alumnos-docente',
            // 'crear-alumnos-docente',
            'editar-alumnos-docente',
            // 'eliminar-alumnos-docente',

            // comsiones ...  
            'ver-comision-docente',

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

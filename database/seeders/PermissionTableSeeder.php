<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'todo-acceso-usuarios',
            'ver-usuarios',
            'crear-usuarios',
            'editar-usuarios',
            'eliminar-usuarios',
            'icono-usuario',

            'todo-acceso-roles',
            'ver-roles',
            'crear-roles',
            'editar-roles',
            'eliminar-roles',
            'icono-roles',

            'todo-acceso-permisos',
            'ver-permisos',
            'crear-permisos',
            'editar-permisos',
            'eliminar-permisos',
            'icono-permisos',


            // PERMISOS DE LOS DOCENTES
            'ver-perfil-docente',
            'editar-perfil-docente',
            'ver-mis-modulos',
            'ver-estudiantes-asignados',

        ];

        $permissions = array_map(function ($name) {
            return [
                'name' => $name,
                'created_at' => now(),
            ];
        }, $permissions);

        DB::table('permissions')->insert($permissions);
    }
}

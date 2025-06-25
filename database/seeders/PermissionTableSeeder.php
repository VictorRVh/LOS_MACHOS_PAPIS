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
            'todo-acceso-roles',
            'ver-roles',
            'crear-roles',
            'editar-roles',
            'eliminar-roles',
            'todo-acceso-permisos',
            'ver-permisos',
            'crear-permisos',
            'editar-permisos',
            'eliminar-permisos',
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

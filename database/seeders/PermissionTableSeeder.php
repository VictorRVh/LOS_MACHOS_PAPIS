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

            'ver-permisos',
            'icono-permisos',

           //
            // PERMISOS DE LOS DOCENTES
            'todo-acceso-docentes',
            'ver-docentes',
            'crear-docentes',
            'editar-docentes',
            'eliminar-docentes',
            'icono-docentes',

           // PERMISOS DE COMVENIOS
            'todo-acceso-convenios',
            'ver-convenios',
            'crear-convenios',
            'editar-convenios',
            'eliminar-convenios',
            'icono-convenios',

            // PERMISOS DE PERIODO
            'todo-acceso-periodos',
            'ver-periodos',
            'crear-periodos',
            'editar-periodos',
            'eliminar-periodos',
            'icono-periodos',

            // PERMISOS DE PERIODO
            'todo-acceso-administrativos',
            'ver-administrativos',
            //'crear-administrativos',
            'editar-administrativos',
            'eliminar-administrativos',
            'icono-administrativos',

            // PERMISOS DE ESPECIALIDADES
            'todo-acceso-especialidades',
            'ver-especialidades',
            'crear-especialidades',
            'editar-especialidades',
            'eliminar-especialidades',
            'icono-especialidades',

            // PERMISOS DE ESPECIALIDADES
            'todo-acceso-comisiones',
            'ver-comisiones',
            'crear-comisiones',
            'editar-comisiones',
            'eliminar-comisiones',
            'icono-comisiones',
            
/////////////////////////////////////////////PROGRAMA DE ESTUDIOS//////////////////////////////////////////
            // PERMISOS DE MODULOS
            'todo-acceso-modulos',
            'ver-modulos',
            'crear-modulos',
            'editar-modulos',
            'eliminar-modulos',
            'icono-modulos',

            // PERMISOS DE PROGRAMA ESPECIALIDAD
            'todo-acceso-programa-especialidades',
            'ver-programa-especialidades',
            'crear-programa-especialidades',
            'editar-programa-especialidades',
            'eliminar-programa-especialidades',
            'icono-programa-especialidades',

            // PERMISOS DE PROGRAMAS
            'todo-acceso-programas',
            'ver-programas',
            'crear-programas',
            'editar-programas',
            'eliminar-programas',
            'icono-programas',

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

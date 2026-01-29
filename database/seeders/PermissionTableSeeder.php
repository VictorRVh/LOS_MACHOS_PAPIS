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
            'ver-actividades',
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
            'todo-acceso-modalidades',
            'ver-modalidades',
            'crear-modalidades',
            'editar-modalidades',
            'eliminar-modalidades',
            'icono-modalidades',

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
            'todo-acceso-programas-de-estudio',
            'ver-programas-de-estudio',
            'crear-programas-de-estudio',
            'editar-programas-de-estudio',
            'eliminar-programas-de-estudio',
            'icono-programas-de-estudio',

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
            'todo-acceso-ciclo-programa',
            'ver-ciclo-programa',
            'crear-ciclo-programa',
            'editar-ciclo-programa',
            'eliminar-ciclo-programa',
            'icono-ciclo-programa',

            //
            
            // PERMISOS DE PROGRAMAS
            'todo-acceso-ciclo-academico',
            'ver-ciclo-academico',
            'crear-ciclo-academico',
            'editar-ciclo-academico',
            'eliminar-ciclo-academico',
            'icono-ciclo-academico',

            'todo-acceso-grupos',
            'ver-grupos',
            'crear-grupos',
            'editar-grupos',
            'eliminar-grupos',
            'icono-grupos',

            'todo-acceso-matriculas',
            'ver-matriculas',
            'crear-matriculas',
            'editar-matriculas',
            'eliminar-matriculas',
            'icono-matriculas',
            
            // PERMISOS DE DE PROGRAMACION ADMIN 
            'todo-acceso-documento-programado',
            'ver-documento-programado',
            'crear-documento-programado',
            'editar-documento-programado',
            'eliminar-documento-programado',
            'icono-documento-programado',
            
             // PERMISOS DE DE PROGRAMACION PARA DOCENTES
            'todo-acceso-programacion-documentos-subidos',
            'ver-programacion-documentos-subidos',
            'crear-programacion-documentos-subidos',
            'editar-programacion-documentos-subidos',
            'eliminar-programacion-documentos-subidos',
            'icono-programacion-documentos-subidos',


            ///////////////////////////////////////
            ///////////////////////////////////////
            ///////////////////////////////////////
            ///////////////////////////////////////

            // PERMISOSPARA ELADMIN..PARA QUE PUEDA VISUALIZAR LOS DATOS DEL DOCENTE
             // (CALENDARIZACION,CAPACIDADDES TERMINALES, NOTAS, ASISTENCIA)
             'ver-sesiones-docente',
             'ver-capacidad-terminal-docente',
             'ver-capacidad-terminal-notas-docente',

             ///////////////////////////////////////
             ///////////////////////////////////////
             ///////////////////////////////////////
             ///////////////////////////////////////
             

                         // PERMISOS DE DE PROGRAMACION PARA DOCENTES
            'todo-acceso-capacidad-terminal-docente',
        /// 'ver-capacidad-terminal-docente',
            'crear-capacidad-terminal-docente',
            'editar-capacidad-terminal-docente',
            'eliminar-capacidad-terminal-docente',

            'todo-acceso-capacidad-terminal-notas-docente',
            //'ver-capacidad-terminal-notas-docente',
            'crear-capacidad-terminal-notas-docente',
            'editar-capacidad-terminal-notas-docente',
            'eliminar-capacidad-terminal-notas-docente',

            'todo-acceso-alumnos-docente',
            'ver-alumnos-docente',
           // 'crear-alumnos-docente',
            'editar-alumnos-docente',
           // 'eliminar-alumnos-docente',

            'todo-acceso-sesiones-docente',
            //'ver-sesiones-docente',
            'crear-sesiones-docente',
            'editar-sesiones-docente',
            'eliminar-sesiones-docente',

            // ver comsiones 
            'ver-comision-docente',
                

            'ver-perfil-docente',
            'editar-perfil-docente',
            'ver-mis-modulos',
            'ver-estudiantes-asignados',

                 //Competencias
            'todo-acceso-capacidades-docente',
            'ver-capacidades-docente',
            'crear-capacidades-docente',
            'editar-capacidades-docente',
            'eliminar-capacidades-docente',

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

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
            'todo-acceso-módulos',
            'ver-módulos',
            'crear-módulos',
            'editar-módulos',
            'eliminar-módulos',
            'icono-módulos',

            // PERMISOS DE PROGRAMA ESPECIALIDAD
            'todo-acceso-ciclo-programa',
            'ver-ciclo-programa',
            'crear-ciclo-programa',
            'editar-ciclo-programa',
            'eliminar-ciclo-programa',
            'icono-ciclo-programa',

            //

            // PERMISOS DE PROGRAMAS
            'todo-acceso-ciclo-académico',
            'ver-ciclo-académico',
            'crear-ciclo-académico',
            'editar-ciclo-académico',
            'eliminar-ciclo-académico',
            'icono-ciclo-académico',

            'todo-acceso-grupos',
            'ver-grupos',
            'crear-grupos',
            'editar-grupos',
            'eliminar-grupos',
            'icono-grupos',

            'todo-acceso-matrículas',
            'ver-matrículas',
            'crear-matrículas',
            'editar-matrículas',
            'eliminar-matrículas',
            'trasladar-estudiante-matrículas',
            'reservar-estudiante-matrículas',
            'icono-matrículas',

            // PERMISOS DE DE PROGRAMACION ADMIN 
            'todo-acceso-documento-programado',
            'ver-documento-programado',
            'crear-documento-programado',
            'editar-documento-programado',
            'eliminar-documento-programado',
            'icono-documento-programado',

            // PERMISOS DE DE PROGRAMACION PARA DOCENTES
            'todo-acceso-programación-documentos-subidos',
            'ver-programación-documentos-subidos',
            'crear-programación-documentos-subidos',
            'editar-programación-documentos-subidos',
            'eliminar-programación-documentos-subidos',
            'icono-programación-documentos-subidos',

            // Datos cetpro
            'ver-actividades',
            'ver-información-cetpro',
            'editar-información-cetpro',

            // ESTADISTICAS

            'todo-acceso-estadísticas',
            'ver-estadísticas',
            // 'crear-documento-estadísticas',
            // 'editar-documento-estadísticas',
            // 'eliminar-documento-estadísticas',
            'icono-estadísticas',

            ///////////////////////////////////////
            ///////////////////////////////////////
            ///////////////////////////////////////
            ///////////////////////////////////////

            // PERMISOSPARA ELADMIN..PARA QUE PUEDA VISUALIZAR LOS DATOS DEL DOCENTE
            // (CALENDARIZACION,CAPACIDADDES TERMINALES, NOTAS, ASISTENCIA)
            'ver-sesiones-docente',
            'ver-unidad-didáctica-docente',
            'ver-unidad-didáctica-notas-docente',

            ///////////////////////////////////////
            ///////////////////////////////////////
            ///////////////////////////////////////
            ///////////////////////////////////////


            // PERMISOS DE DE PROGRAMACION PARA DOCENTES
            'todo-acceso-unidad-didáctica-docente',
            /// 'ver-unidad-didáctica-docente',
            'crear-unidad-didáctica-docente',
            'editar-unidad-didáctica-docente',
            'eliminar-unidad-didáctica-docente',

            'todo-acceso-unidad-didáctica-notas-docente',
            //'ver-unidad-didáctica-notas-docente',
            'crear-unidad-didáctica-notas-docente',
            'editar-unidad-didáctica-notas-docente',
            'eliminar-unidad-didáctica-notas-docente',

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
            'ver-comisión-docente',


            'ver-perfil-docente',
            'editar-perfil-docente',
            'ver-mis-módulos',
            'ver-estudiantes-asignados',

            //Competencias
            'todo-acceso-capacidades-docente',
            'ver-capacidades-docente',
            'crear-capacidades-docente',
            'editar-capacidades-docente',
            'eliminar-capacidades-docente',
            
            //PARA VER LAS ACTIVIDADES RECIENTES
            'todo-actividadaes-recientes',
            'ver-actividadaes-recientes',
            'crear-actividadaes-recientes',
            'editar-actividadaes-recientes',
            'eliminar-actividadaes-recientes'

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

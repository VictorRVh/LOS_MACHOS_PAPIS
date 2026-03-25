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
        // PERMISOS ADMIN (1, 2, 4)
        // =========================
        $adminPermissions = [
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
            'todo-acceso-docentes',
            'ver-docentes',
            'crear-docentes',
            'editar-docentes',
            'eliminar-docentes',
            'icono-docentes',
            'todo-acceso-modalidades',
            'ver-modalidades',
            'crear-modalidades',
            'editar-modalidades',
            'eliminar-modalidades',
            'icono-modalidades',
            'todo-acceso-periodos',
            'ver-periodos',
            'crear-periodos',
            'editar-periodos',
            'eliminar-periodos',
            'icono-periodos',
            'todo-acceso-administrativos',
            'ver-administrativos',
            'editar-administrativos',
            'eliminar-administrativos',
            'icono-administrativos',
            'todo-acceso-programas-de-estudio',
            'ver-programas-de-estudio',
            'crear-programas-de-estudio',
            'editar-programas-de-estudio',
            'eliminar-programas-de-estudio',
            'icono-programas-de-estudio',
            'todo-acceso-comisiones',
            'ver-comisiones',
            'crear-comisiones',
            'editar-comisiones',
            'eliminar-comisiones',
            'icono-comisiones',
            'todo-acceso-módulos',
            'ver-módulos',
            'crear-módulos',
            'editar-módulos',
            'eliminar-módulos',
            'icono-módulos',
            'todo-acceso-ciclo-programa',
            'ver-ciclo-programa',
            'crear-ciclo-programa',
            'editar-ciclo-programa',
            'eliminar-ciclo-programa',
            'icono-ciclo-programa',
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
            'todo-acceso-documento-programado',
            'ver-documento-programado',
            'crear-documento-programado',
            'editar-documento-programado',
            'eliminar-documento-programado',
            'icono-documento-programado',
            'todo-acceso-programación-documentos-subidos',
            'ver-programación-documentos-subidos',
            'crear-programación-documentos-subidos',
            'editar-programación-documentos-subidos',
            'eliminar-programación-documentos-subidos',
            'icono-programación-documentos-subidos',
            'ver-actividades',
            'ver-información-cetpro',
            'editar-información-cetpro',
            'todo-acceso-estadísticas',
            'ver-estadísticas',
            'icono-estadísticas',
            // Admin puede VER datos de docentes (solo lectura)
            'ver-sesiones-docente',
            'ver-unidad-didáctica-docente',
            'ver-unidad-didáctica-notas-docente',
            'todo-actividades-recientes',
            'ver-actividades-recientes',
            'crear-actividades-recientes',
            'editar-actividades-recientes',
            'eliminar-actividades-recientes',
            'editar-perfil',
            'editar-password',
        ];

        // =========================
        // PERMISOS DOCENTE (6)
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
            'editar-perfil',
            'editar-password',
        ];

        // =========================
        // OBTENER IDS
        // =========================
        $adminPermissionIds = DB::table('permissions')
            ->whereIn('name', $adminPermissions)
            ->pluck('id');

        $docentePermissionIds = DB::table('permissions')
            ->whereIn('name', $docentePermissions)
            ->pluck('id');

        // =========================
        // ADMIN (1, 2, 4)
        // =========================
        foreach ($adminPermissionIds as $permissionId) {
            foreach ([1, 2, 4] as $roleId) {
                $permissionRole[] = [
                    'role_id'       => $roleId,
                    'permission_id' => $permissionId,
                    'created_at'    => now(),
                ];
            }
        }

        // =========================
        // DOCENTE (6)
        // =========================
        foreach ($docentePermissionIds as $permissionId) {
            $permissionRole[] = [
                'role_id'       => 6,
                'permission_id' => $permissionId,
                'created_at'    => now(),
            ];
        }

        DB::table('permission_role')->insert($permissionRole);
    }
}

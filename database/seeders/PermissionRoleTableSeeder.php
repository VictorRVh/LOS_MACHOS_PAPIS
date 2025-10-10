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
        }

        $docentePermissions = [
            'ver-perfil-docente',
            'editar-perfil-docente',
            'ver-mis-modulos',
            'ver-estudiantes-asignados',
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

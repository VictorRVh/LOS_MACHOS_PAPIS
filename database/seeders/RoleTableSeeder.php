<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super-directora',
                'created_at' => now(),
            ],
            [
                'name' => 'directora',
                'created_at' => now(),
            ],
            [
                'name' => 'administrativo',
                'created_at' => now(),
            ],
            [
                'name' => 'coordinador',
                'created_at' => now(),
            ],
            [
                'name' => 'secretaria',
                'created_at' => now(),
            ],
            [
                'name' => 'docente',
                'created_at' => now(),
            ],
        ];

        DB::table('roles')->insert($roles);
    }
}

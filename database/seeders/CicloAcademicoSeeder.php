<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CicloAcademicoSeeder extends Seeder
{
    public function run()
    {
        DB::table('ciclo_academico')->insert([
            [
                'id' => (string) Str::uuid(),
                'nombre_ciclo' => 'Ciclo Técnico',
                'descripcion' => 'Descripcion de ciclo tecnico',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'nombre_ciclo' => 'Ciclo Auxiliar Técnico',
                'descripcion' => 'Descripcion de ciclo auxiliar tecnico',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

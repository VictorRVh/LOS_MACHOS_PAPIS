<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call(RoleTableSeeder::class);          // 1. Roles
        $this->call(PermissionTableSeeder::class);   // 2. Permisos
        $this->call(PermissionRoleTableSeeder::class); // 3. Asignar permisos
        $this->call(UserTableSeeder::class);          // 4. Usuarios
        $this->call(RoleUserTableSeeder::class);      // 5. Asignar roles a usuarios
        $this->call(CicloAcademicoSeeder::class);
    }
}

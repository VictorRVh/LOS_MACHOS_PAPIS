<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        $users = [
            [
                'name' => 'Mr. Super Admin',
                'usuario' => 'superadmin',
                'dni' => '12345678',
                'apellido_paterno' => 'Super',
                'apellido_materno' => 'Admin',
                'fecha_nacimiento' => '1990-01-01',
                'telefono' => '987654321',
                'direccion' => 'Av. Principal 123',
                'email' => 'sadmin@sadmin.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'Mr. Admin',
                'usuario' => 'admin',
                'dni' => '87654321',
                'apellido_paterno' => 'Admin',
                'apellido_materno' => 'User',
                'fecha_nacimiento' => '1991-02-02',
                'telefono' => '912345678',
                'direccion' => 'Calle Secundaria 456',
                'email' => 'admin@admin.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'Mr. Author',
                'usuario' => 'author',
                'dni' => '11223344',
                'apellido_paterno' => 'Author',
                'apellido_materno' => 'User',
                'fecha_nacimiento' => '1992-03-03',
                'telefono' => '911223344',
                'direccion' => 'Jr. Escritor 789',
                'email' => 'author@author.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'Mr. Editor',
                'usuario' => 'editor',
                'dni' => '55667788',
                'apellido_paterno' => 'Editor',
                'apellido_materno' => 'User',
                'fecha_nacimiento' => '1993-04-04',
                'telefono' => '933112233',
                'direccion' => 'Av. Ediciones 321',
                'email' => 'editor@editor.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
            // Puedes seguir el mismo patrón para los demás:
            [
                'name' => 'Mr. User 1',
                'usuario' => 'user1',
                'dni' => '10000001',
                'apellido_paterno' => 'User',
                'apellido_materno' => 'Uno',
                'fecha_nacimiento' => '1994-05-05',
                'telefono' => '900000001',
                'direccion' => 'Calle Uno',
                'email' => 'user1@user.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'Mr. User 2',
                'usuario' => 'user2',
                'dni' => '10000002',
                'apellido_paterno' => 'User',
                'apellido_materno' => 'Dos',
                'fecha_nacimiento' => '1995-06-06',
                'telefono' => '900000002',
                'direccion' => 'Calle Dos',
                'email' => 'user2@user.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'Mr. User 3',
                'usuario' => 'user3',
                'dni' => '10000003',
                'apellido_paterno' => 'User',
                'apellido_materno' => 'Tres',
                'fecha_nacimiento' => '1996-07-07',
                'telefono' => '900000003',
                'direccion' => 'Calle Tres',
                'email' => 'user3@user.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'Mr. User 4',
                'usuario' => 'user4',
                'dni' => '10000004',
                'apellido_paterno' => 'User',
                'apellido_materno' => 'Cuatro',
                'fecha_nacimiento' => '1997-08-08',
                'telefono' => '900000004',
                'direccion' => 'Calle Cuatro',
                'email' => 'user4@user.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'Mr. User',
                'usuario' => 'user',
                'dni' => '10000000',
                'apellido_paterno' => 'User',
                'apellido_materno' => 'General',
                'fecha_nacimiento' => '1998-09-09',
                'telefono' => '900000000',
                'direccion' => 'Calle Central',
                'email' => 'user@user.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
    }
}

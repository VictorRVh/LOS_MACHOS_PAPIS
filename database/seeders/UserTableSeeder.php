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
                'name' => 'Monica-pro',
                'usuario' => 'super-directora',
                'dni' => '12345633',
                'apellido_paterno' => 'Calderon',
                'apellido_materno' => 'Muñoz',
                'fecha_nacimiento' => '1990-01-01',
                'telefono' => '987654321',
                'direccion' => 'Av. Principal 123',
                'email' => 'monicapro@gmail.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'Monica',
                'usuario' => 'directora',
                'dni' => '12345678',
                'apellido_paterno' => 'Calderon',
                'apellido_materno' => 'Muñoz',
                'fecha_nacimiento' => '1990-01-01',
                'telefono' => '987654321',
                'direccion' => 'Av. Principal 123',
                'email' => 'monica@gmail.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'Roger',
                'usuario' => 'administrativo',
                'dni' => '87654321',
                'apellido_paterno' => 'Quispe',
                'apellido_materno' => 'Chala',
                'fecha_nacimiento' => '1991-02-02',
                'telefono' => '912345678',
                'direccion' => 'Calle Secundaria 456',
                'email' => 'RogerAdministrativo@gmail.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'José',
                'usuario' => 'coordinador',
                'dni' => '11223344',
                'apellido_paterno' => 'Tapia',
                'apellido_materno' => 'Coaquira',
                'fecha_nacimiento' => '1992-03-03',
                'telefono' => '911223344',
                'direccion' => 'Jr. Escritor 789',
                'email' => 'josetapia@gmail.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'Nohemy',
                'usuario' => 'secretaria',
                'dni' => '55667788',
                'apellido_paterno' => 'Velazco',
                'apellido_materno' => 'Tevez',
                'fecha_nacimiento' => '1993-04-04',
                'telefono' => '933112233',
                'direccion' => 'Av. Ediciones 321',
                'email' => 'noehmy@gmai.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
            // Docentes
            [
                'name' => 'Juan',
                'usuario' => 'docente-uno',
                'dni' => '10000001',
                'apellido_paterno' => 'Perez',
                'apellido_materno' => 'Mamani',
                'fecha_nacimiento' => '1994-05-05',
                'telefono' => '900000001',
                'direccion' => 'Calle Uno',
                'email' => 'juan@gmail.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'Pedro',
                'usuario' => 'docente-dos',
                'dni' => '10000002',
                'apellido_paterno' => 'Vilca',
                'apellido_materno' => 'Uturunco',
                'fecha_nacimiento' => '1995-06-06',
                'telefono' => '900000002',
                'direccion' => 'Calle Dos',
                'email' => 'pedro@gmail.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'Johan',
                'usuario' => 'docente-tres',
                'dni' => '10000003',
                'apellido_paterno' => 'Quispe',
                'apellido_materno' => 'Lopez',
                'fecha_nacimiento' => '1996-07-07',
                'telefono' => '900000003',
                'direccion' => 'Calle Tres',
                'email' => 'johan@gmail.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'Harol',
                'usuario' => 'docente-cuatro',
                'dni' => '10000004',
                'apellido_paterno' => 'Flores',
                'apellido_materno' => 'Cutimbo',
                'fecha_nacimiento' => '1997-08-08',
                'telefono' => '900000004',
                'direccion' => 'Calle Cuatro',
                'email' => 'Harol@gmail.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'James',
                'usuario' => 'docente-cinco',
                'dni' => '10000000',
                'apellido_paterno' => 'Florez',
                'apellido_materno' => 'Flores',
                'fecha_nacimiento' => '1998-09-09',
                'telefono' => '900000000',
                'direccion' => 'Calle Central',
                'email' => 'james@gmail.com',
                'password' => $password,
                'status' => 1,
                'created_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
    }
}

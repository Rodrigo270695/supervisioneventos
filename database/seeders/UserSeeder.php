<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si el usuario administrador ya existe
        $adminUser = User::where('dni', '77344506')->first();

        if (!$adminUser) {
            // Crear usuario administrador si no existe
            $adminUser = User::create([
                'name' => 'Rodrigo Granja Requejo',
                'dni' => '77344506',
                'email' => 'admin@ejemplo.com', // Opcional, puedes cambiar este email
                'password' => Hash::make('*Rodrigo95*'),
            ]);
        }

        // Asignar rol de administrador (siempre se asigna, incluso si el usuario ya existía)
        $adminUser->assignRole('admin');

        // Puedes crear más usuarios aquí si lo necesitas
        // Ejemplo de usuario normal:
        /*
        $user = User::firstOrCreate(
            ['dni' => '12345678'],
            [
                'name' => 'Usuario Normal',
                'email' => 'usuario@ejemplo.com',
                'password' => Hash::make('contraseña'),
            ]
        );
        $user->assignRole('user');
        */
    }
}

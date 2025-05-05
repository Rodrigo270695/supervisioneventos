<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llamar al seeder de roles primero (para que los roles existan antes de crear usuarios)
        $this->call(RoleSeeder::class);

        // Luego llamar al seeder de usuarios
        $this->call(UserSeeder::class);

        // El código comentado de abajo se puede eliminar o mantener según prefieras
        /*
        User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        */
    }
}

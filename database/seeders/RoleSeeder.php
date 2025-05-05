<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear rol de administrador si no existe
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Crear rol de usuario si no existe
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Aquí puedes agregar permisos específicos y asignarlos a los roles
        // Por ejemplo:
        // $permission = Permission::firstOrCreate(['name' => 'crear eventos']);
        // $adminRole->givePermissionTo($permission);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutar seeders de configuración
        $this->call([
            RoleSeeder::class,
            DireccionSeeder::class,
            AseguradoraSeeder::class,
        ]);

        // Crear Super Admin de prueba
        $admin = User::factory()->create([
            'name' => 'Administrador del Sistema',
            'email' => 'admin@polizas.com',
            'password' => bcrypt('admin123'),
            'direccion_id' => 1, // DTI
        ]);

        $admin->assignRole('Super Admin');
    }
}

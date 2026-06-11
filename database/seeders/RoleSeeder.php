<?php

namespace Database\Seeders;

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
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            // Pólizas
            'ver-polizas',
            'crear-polizas',
            'editar-polizas',
            'eliminar-polizas',
            'exportar-polizas',

            // Aseguradoras
            'ver-aseguradoras',
            'gestionar-aseguradoras',

            // Usuarios
            'ver-usuarios',
            'gestionar-usuarios',

            // Direcciones
            'ver-direcciones',
            'gestionar-direcciones',

            // Reportes
            'ver-reportes',
            'ver-dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles y asignar permisos

        // Super Admin - Acceso total
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Administrador - Gestión completa excepto usuarios
        $admin = Role::firstOrCreate(['name' => 'Administrador']);
        $admin->givePermissionTo([
            'ver-polizas',
            'crear-polizas',
            'editar-polizas',
            'eliminar-polizas',
            'exportar-polizas',
            'ver-aseguradoras',
            'gestionar-aseguradoras',
            'ver-direcciones',
            'ver-reportes',
            'ver-dashboard',
        ]);

        // Director - Ver y editar pólizas de su dirección
        $director = Role::firstOrCreate(['name' => 'Director']);
        $director->givePermissionTo([
            'ver-polizas',
            'crear-polizas',
            'editar-polizas',
            'ver-aseguradoras',
            'ver-dashboard',
        ]);

        // Usuario - Solo consulta
        $usuario = Role::firstOrCreate(['name' => 'Usuario']);
        $usuario->givePermissionTo([
            'ver-polizas',
            'ver-aseguradoras',
            'ver-dashboard',
        ]);

        // Asesor Prefectura - Consulta y Firma de Renovaciones
        $asesor = Role::firstOrCreate(['name' => 'Asesor Prefectura']);
        $asesor->givePermissionTo([
            'ver-polizas',
            'ver-dashboard',
        ]);

        // Gestor Tesorería Ambiente - Acceso exclusivo a pólizas ambientales
        $gestorAmb = Role::firstOrCreate(['name' => 'Gestor Tesorería Ambiente']);
        $gestorAmb->givePermissionTo([
            'ver-polizas',
            'crear-polizas',
            'editar-polizas',
            'ver-dashboard',
            'ver-aseguradoras',
        ]);
    }
}

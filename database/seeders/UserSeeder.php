<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure there is an administrator role to attach to the seeded user
        $adminRole = Rol::firstOrCreate(
            ['nombre' => 'Administrador']
        );

        // Admin user (use DB to avoid Eloquent timestamps if table lacks them)
        DB::table('usuario')->updateOrInsert(
            ['email' => 'admin@example.com'],
            [
                'nombre' => 'Admin',
                'apellido' => 'Local',
                'telefono' => '000000000',
                'password' => Hash::make('password'),
                'estado' => true,
                'disponibilidad' => true,
                'cuenta_no_expirada' => true,
                'cuenta_no_bloqueada' => true,
                'credenciales_no_expiradas' => true,
                'rol_id' => $adminRole->id,
            ]
        );

        // Example test user (keeps old test@example.com for compatibility)
        DB::table('usuario')->updateOrInsert(
            ['email' => 'test@example.com'],
            [
                'nombre' => 'Test',
                'apellido' => 'User',
                'telefono' => '000000001',
                'password' => Hash::make('password'),
                'estado' => true,
                'disponibilidad' => true,
                'cuenta_no_expirada' => true,
                'cuenta_no_bloqueada' => true,
                'credenciales_no_expiradas' => true,
                'rol_id' => $adminRole->id,
            ]
        );
    }
}

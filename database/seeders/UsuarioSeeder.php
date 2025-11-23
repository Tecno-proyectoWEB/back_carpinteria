<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $propietario = Rol::where('nombre', 'PROPIETARIO')->first();
        $secretaria = Rol::where('nombre', 'SECRETARIA')->first();
        $carpintero = Rol::where('nombre', 'CARPINTERO')->first();
        $cliente = Rol::where('nombre', 'CLIENTE')->first();

        // Propietario
        if ($propietario) {
            Usuario::firstOrCreate(
                ['email' => 'propietario@carpinteria.com'],
                [
                    'nombre' => 'Juan',
                    'apellido' => 'Pérez',
                    'email' => 'propietario@carpinteria.com',
                    'password' => Hash::make('password123'),
                    'telefono' => '0987654321',
                    'rol_id' => $propietario->id,
                    'estado' => true,
                    'disponibilidad' => true,
                    'cuenta_no_expirada' => true,
                    'cuenta_no_bloqueada' => true,
                    'credenciales_no_expiradas' => true,
                ]
            );
        }

        // Secretaria
        if ($secretaria) {
            Usuario::firstOrCreate(
                ['email' => 'secretaria@carpinteria.com'],
                [
                    'nombre' => 'María',
                    'apellido' => 'González',
                    'email' => 'secretaria@carpinteria.com',
                    'password' => Hash::make('password123'),
                    'telefono' => '0987654322',
                    'rol_id' => $secretaria->id,
                    'estado' => true,
                    'disponibilidad' => true,
                    'cuenta_no_expirada' => true,
                    'cuenta_no_bloqueada' => true,
                    'credenciales_no_expiradas' => true,
                ]
            );
        }

        // Carpintero
        if ($carpintero) {
            Usuario::firstOrCreate(
                ['email' => 'carpintero@carpinteria.com'],
                [
                    'nombre' => 'Carlos',
                    'apellido' => 'Rodríguez',
                    'email' => 'carpintero@carpinteria.com',
                    'password' => Hash::make('password123'),
                    'telefono' => '0987654323',
                    'rol_id' => $carpintero->id,
                    'estado' => true,
                    'disponibilidad' => true,
                    'cuenta_no_expirada' => true,
                    'cuenta_no_bloqueada' => true,
                    'credenciales_no_expiradas' => true,
                ]
            );
        }

        // Cliente de ejemplo
        if ($cliente) {
            Usuario::firstOrCreate(
                ['email' => 'cliente@example.com'],
                [
                    'nombre' => 'Pedro',
                    'apellido' => 'Martínez',
                    'email' => 'cliente@example.com',
                    'password' => Hash::make('password123'),
                    'telefono' => '0987654324',
                    'rol_id' => $cliente->id,
                    'estado' => true,
                    'disponibilidad' => true,
                    'cuenta_no_expirada' => true,
                    'cuenta_no_bloqueada' => true,
                    'credenciales_no_expiradas' => true,
                ]
            );
        }
    }
}

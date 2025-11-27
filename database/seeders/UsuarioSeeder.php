<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Rol;

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
            $usuario = Usuario::firstOrNew(['email' => 'propietario@carpinteria.com']);
            if (!$usuario->exists) {
                $usuario->fill([
                    'nombre' => 'Juan',
                    'apellido' => 'Pérez',
                    'email' => 'propietario@carpinteria.com',
                    'password' => 'password123', // El cast 'hashed' lo hasheará automáticamente
                    'telefono' => '0987654321',
                    'rol_id' => $propietario->id,
                    'estado' => true,
                    'disponibilidad' => true,
                    'cuenta_no_expirada' => true,
                    'cuenta_no_bloqueada' => true,
                    'credenciales_no_expiradas' => true,
                ]);
                $usuario->save();
            }
        }

        // Secretaria
        if ($secretaria) {
            $usuario = Usuario::firstOrNew(['email' => 'secretaria@carpinteria.com']);
            if (!$usuario->exists) {
                $usuario->fill([
                    'nombre' => 'María',
                    'apellido' => 'González',
                    'email' => 'secretaria@carpinteria.com',
                    'password' => 'password123', // El cast 'hashed' lo hasheará automáticamente
                    'telefono' => '0987654322',
                    'rol_id' => $secretaria->id,
                    'estado' => true,
                    'disponibilidad' => true,
                    'cuenta_no_expirada' => true,
                    'cuenta_no_bloqueada' => true,
                    'credenciales_no_expiradas' => true,
                ]);
                $usuario->save();
            }
        }

        // Carpintero
        if ($carpintero) {
            $usuario = Usuario::firstOrNew(['email' => 'carpintero@carpinteria.com']);
            if (!$usuario->exists) {
                $usuario->fill([
                    'nombre' => 'Carlos',
                    'apellido' => 'Rodríguez',
                    'email' => 'carpintero@carpinteria.com',
                    'password' => 'password123', // El cast 'hashed' lo hasheará automáticamente
                    'telefono' => '0987654323',
                    'rol_id' => $carpintero->id,
                    'estado' => true,
                    'disponibilidad' => true,
                    'cuenta_no_expirada' => true,
                    'cuenta_no_bloqueada' => true,
                    'credenciales_no_expiradas' => true,
                ]);
                $usuario->save();
            }
        }

        // Cliente de ejemplo
        if ($cliente) {
            $usuario = Usuario::firstOrNew(['email' => 'cliente@example.com']);
            if (!$usuario->exists) {
                $usuario->fill([
                    'nombre' => 'Pedro',
                    'apellido' => 'Martínez',
                    'email' => 'cliente@example.com',
                    'password' => 'password123', // El cast 'hashed' lo hasheará automáticamente
                    'telefono' => '0987654324',
                    'rol_id' => $cliente->id,
                    'estado' => true,
                    'disponibilidad' => true,
                    'cuenta_no_expirada' => true,
                    'cuenta_no_bloqueada' => true,
                    'credenciales_no_expiradas' => true,
                ]);
                $usuario->save();
            }
        }
    }
}

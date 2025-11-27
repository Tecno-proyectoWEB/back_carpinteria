<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Rol;
<<<<<<< Updated upstream
use Illuminate\Support\Facades\Hash;
=======
use App\Models\Permiso;
>>>>>>> Stashed changes

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
<<<<<<< Updated upstream
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
=======
            $usuario = Usuario::firstOrNew(['email' => 'propietario@carpinteria.com']);
            $usuario->fill([
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'email' => 'propietario@carpinteria.com',
                'password' => Hash::make('password123'), // Hashear explícitamente la contraseña
                'telefono' => '0987654321',
                'rol_id' => $propietario->id,
                'estado' => true,
                'disponibilidad' => true,
                'cuenta_no_expirada' => true,
                'cuenta_no_bloqueada' => true,
                'credenciales_no_expiradas' => true,
            ]);
            $usuario->save();
>>>>>>> Stashed changes
        }

        // Secretaria
        if ($secretaria) {
<<<<<<< Updated upstream
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
=======
            $usuario = Usuario::firstOrNew(['email' => 'secretaria@carpinteria.com']);
            $usuario->fill([
                'nombre' => 'María',
                'apellido' => 'González',
                'email' => 'secretaria@carpinteria.com',
                'password' => Hash::make('password123'), // Hashear explícitamente la contraseña
                'telefono' => '0987654322',
                'rol_id' => $secretaria->id,
                'estado' => true,
                'disponibilidad' => true,
                'cuenta_no_expirada' => true,
                'cuenta_no_bloqueada' => true,
                'credenciales_no_expiradas' => true,
            ]);
            $usuario->save();
>>>>>>> Stashed changes
        }

        // Carpintero
        if ($carpintero) {
<<<<<<< Updated upstream
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
=======
            $usuario = Usuario::firstOrNew(['email' => 'carpintero@carpinteria.com']);
            $usuario->fill([
                'nombre' => 'Carlos',
                'apellido' => 'Rodríguez',
                'email' => 'carpintero@carpinteria.com',
                'password' => Hash::make('password123'), // Hashear explícitamente la contraseña
                'telefono' => '0987654323',
                'rol_id' => $carpintero->id,
                'estado' => true,
                'disponibilidad' => true,
                'cuenta_no_expirada' => true,
                'cuenta_no_bloqueada' => true,
                'credenciales_no_expiradas' => true,
            ]);
            $usuario->save();
>>>>>>> Stashed changes
        }

        // Cliente de ejemplo
        if ($cliente) {
<<<<<<< Updated upstream
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
=======
            $usuario = Usuario::firstOrNew(['email' => 'cliente@example.com']);
            $usuario->fill([
                'nombre' => 'Pedro',
                'apellido' => 'Martínez',
                'email' => 'cliente@example.com',
                'password' => Hash::make('password123'), // Hashear explícitamente la contraseña
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

        // Asegurar que el rol PROPIETARIO tenga TODOS los permisos
        // Esto se ejecuta siempre, incluso si el usuario ya existía
        if ($propietario) {
            $todosPermisos = Permiso::all();

            if ($todosPermisos->isNotEmpty()) {
                // Sincronizar todos los permisos con el rol PROPIETARIO
                // sync() reemplaza todos los permisos existentes con los nuevos
                $propietario->permisos()->sync($todosPermisos->pluck('id'));

                $this->command->info('✅ Todos los permisos asignados al rol PROPIETARIO (' . $todosPermisos->count() . ' permisos)');

                // Verificar que se guardaron correctamente
                $permisosAsignados = $propietario->permisos()->count();
                $this->command->info("   Verificado: {$permisosAsignados} permisos asignados al rol PROPIETARIO");
            } else {
                $this->command->warn('⚠️  No se encontraron permisos en la base de datos. Ejecuta primero PermisoSeeder.');
            }
        } else {
            $this->command->error('❌ No se encontró el rol PROPIETARIO. Ejecuta primero RolSeeder.');
>>>>>>> Stashed changes
        }
    }
}

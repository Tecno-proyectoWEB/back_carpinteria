<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['nombre' => 'PROPIETARIO'],
            ['nombre' => 'PROVEEDOR'],
            ['nombre' => 'CARPINTERO'],
            ['nombre' => 'SECRETARIA'],
            ['nombre' => 'CLIENTE'],
        ];

        foreach ($roles as $rol) {
            Rol::firstOrCreate(['nombre' => $rol['nombre']], $rol);
        }
    }
}

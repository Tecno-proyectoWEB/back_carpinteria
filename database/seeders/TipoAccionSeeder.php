<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoAccion;

class TipoAccionSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            [
                'codigo' => 'CREATE',
                'descripcion' => 'Creación de registro',
                'modulo' => 'GENERAL',
            ],
            [
                'codigo' => 'UPDATE',
                'descripcion' => 'Actualización de registro',
                'modulo' => 'GENERAL',
            ],
            [
                'codigo' => 'DELETE',
                'descripcion' => 'Eliminación de registro',
                'modulo' => 'GENERAL',
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoAccion::firstOrCreate(['codigo' => $tipo['codigo']], $tipo);
        }
    }
}

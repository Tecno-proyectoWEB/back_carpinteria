<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Almacen;

class AlmacenSeeder extends Seeder
{
    public function run(): void
    {
        $almacenes = [
            [
                'nombre' => 'Almacén Principal',
                'capacidad' => 5000.00,
            ],
            [
                'nombre' => 'Almacén de Materiales',
                'capacidad' => 3000.00,
            ],
            [
                'nombre' => 'Almacén de Productos Terminados',
                'capacidad' => 2000.00,
            ],
        ];

        foreach ($almacenes as $almacen) {
            Almacen::firstOrCreate(['nombre' => $almacen['nombre']], $almacen);
        }
    }
}

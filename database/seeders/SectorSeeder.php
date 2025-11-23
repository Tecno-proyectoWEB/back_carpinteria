<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sector;
use App\Models\Almacen;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        $almacenPrincipal = Almacen::where('nombre', 'Almacén Principal')->first();
        $almacenMateriales = Almacen::where('nombre', 'Almacén de Materiales')->first();
        $almacenProductos = Almacen::where('nombre', 'Almacén de Productos Terminados')->first();

        $sectores = [
            [
                'nombre' => 'Sector A - Maderas',
                'stock' => 0,
                'capacidad_maxima' => 1500.00,
                'tipo' => 'MADERA',
                'descripcion' => 'Sector para almacenamiento de maderas',
                'almacen_id' => $almacenPrincipal?->id,
            ],
            [
                'nombre' => 'Sector B - Herrajes',
                'stock' => 0,
                'capacidad_maxima' => 800.00,
                'tipo' => 'HERRAMIENTAS',
                'descripcion' => 'Sector para herrajes y herramientas',
                'almacen_id' => $almacenMateriales?->id,
            ],
            [
                'nombre' => 'Sector C - Productos Finales',
                'stock' => 0,
                'capacidad_maxima' => 1000.00,
                'tipo' => 'PRODUCTOS',
                'descripcion' => 'Sector para productos terminados',
                'almacen_id' => $almacenProductos?->id,
            ],
        ];

        foreach ($sectores as $sector) {
            Sector::firstOrCreate(['nombre' => $sector['nombre']], $sector);
        }
    }
}

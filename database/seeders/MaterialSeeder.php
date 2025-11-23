<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Material;
use App\Models\Categoria;
use App\Models\Sector;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $categoriaAlacenas = Categoria::where('nombre', 'Alacenas')->first();
        $sectorMaderas = Sector::where('nombre', 'Sector A - Maderas')->first();
        $sectorHerrajes = Sector::where('nombre', 'Sector B - Herrajes')->first();

        $materiales = [
            [
                'nombre' => 'Tablero MDF 18mm',
                'descripcion' => 'Tablero de fibra de densidad media de 18mm',
                'unidad_medida' => 'm²',
                'precio' => 45.50,
                'stock_actual' => 150,
                'stock_minimo' => 50,
                'punto_reorden' => 75,
                'categoria_text' => 'Maderas',
                'activo' => true,
                'imagen' => null,
                'categoria_id' => $categoriaAlacenas?->id,
                'sector_id' => $sectorMaderas?->id,
            ],
            [
                'nombre' => 'Bisagras de Cocina',
                'descripcion' => 'Bisagras cónceas para puertas de cocina',
                'unidad_medida' => 'unidad',
                'precio' => 3.50,
                'stock_actual' => 200,
                'stock_minimo' => 100,
                'punto_reorden' => 150,
                'categoria_text' => 'Herrajes',
                'activo' => true,
                'imagen' => null,
                'categoria_id' => $categoriaAlacenas?->id,
                'sector_id' => $sectorHerrajes?->id,
            ],
            [
                'nombre' => 'Barniz Poliuretánico',
                'descripcion' => 'Barniz transparente de alta resistencia',
                'unidad_medida' => 'litro',
                'precio' => 28.00,
                'stock_actual' => 80,
                'stock_minimo' => 30,
                'punto_reorden' => 50,
                'categoria_text' => 'Acabados',
                'activo' => true,
                'imagen' => null,
                'categoria_id' => $categoriaAlacenas?->id,
                'sector_id' => $sectorMaderas?->id,
            ],
        ];

        foreach ($materiales as $material) {
            Material::firstOrCreate(['nombre' => $material['nombre']], $material);
        }
    }
}

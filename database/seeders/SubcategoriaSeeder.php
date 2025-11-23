<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subcategoria;

class SubcategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $subcategorias = [
            [
                'nombre' => 'Muebles de Cocina',
                'descripcion' => 'Muebles y accesorios para cocina',
            ],
            [
                'nombre' => 'Muebles de Dormitorio',
                'descripcion' => 'Muebles para habitaciones',
            ],
            [
                'nombre' => 'Muebles de Oficina',
                'descripcion' => 'Muebles para espacios de trabajo',
            ],
        ];

        foreach ($subcategorias as $subcategoria) {
            Subcategoria::firstOrCreate(['nombre' => $subcategoria['nombre']], $subcategoria);
        }
    }
}

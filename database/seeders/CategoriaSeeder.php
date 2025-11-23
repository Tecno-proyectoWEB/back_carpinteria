<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Subcategoria;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $subcategoriaCocina = Subcategoria::where('nombre', 'Muebles de Cocina')->first();
        $subcategoriaDormitorio = Subcategoria::where('nombre', 'Muebles de Dormitorio')->first();
        $subcategoriaOficina = Subcategoria::where('nombre', 'Muebles de Oficina')->first();

        $categorias = [
            [
                'nombre' => 'Alacenas',
                'descripcion' => 'Alacenas y gabinetes de cocina',
                'activo' => true,
                'subcategoria_id' => $subcategoriaCocina?->id,
            ],
            [
                'nombre' => 'Camas',
                'descripcion' => 'Camas y bases de cama',
                'activo' => true,
                'subcategoria_id' => $subcategoriaDormitorio?->id,
            ],
            [
                'nombre' => 'Escritorios',
                'descripcion' => 'Escritorios y mesas de trabajo',
                'activo' => true,
                'subcategoria_id' => $subcategoriaOficina?->id,
            ],
        ];

        foreach ($categorias as $categoria) {
            Categoria::firstOrCreate(['nombre' => $categoria['nombre']], $categoria);
        }
    }
}

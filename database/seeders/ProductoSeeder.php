<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Categoria;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $categoriaAlacenas = Categoria::where('nombre', 'Alacenas')->first();
        $categoriaCamas = Categoria::where('nombre', 'Camas')->first();
        $categoriaEscritorios = Categoria::where('nombre', 'Escritorios')->first();

        $productos = [
            [
                'nombre' => 'Alacena de Cocina 2 Puertas',
                'descripcion' => 'Alacena moderna de 2 puertas con estantes internos',
                'stock' => 5,
                'stock_minimo' => 2,
                'imagen' => null,
                'tiempo' => '5 días',
                'precio_unitario' => 450.00,
                'categoria_id' => $categoriaAlacenas?->id,
            ],
            [
                'nombre' => 'Cama King Size con Base',
                'descripcion' => 'Cama king size con base de madera sólida',
                'stock' => 3,
                'stock_minimo' => 1,
                'imagen' => null,
                'tiempo' => '7 días',
                'precio_unitario' => 850.00,
                'categoria_id' => $categoriaCamas?->id,
            ],
            [
                'nombre' => 'Escritorio Ejecutivo',
                'descripcion' => 'Escritorio ejecutivo con cajones y gavetas',
                'stock' => 4,
                'stock_minimo' => 2,
                'imagen' => null,
                'tiempo' => '6 días',
                'precio_unitario' => 650.00,
                'categoria_id' => $categoriaEscritorios?->id,
            ],
        ];

        foreach ($productos as $producto) {
            Producto::firstOrCreate(['nombre' => $producto['nombre']], $producto);
        }
    }
}

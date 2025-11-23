<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Servicio;
use App\Models\Categoria;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        $categoriaAlacenas = Categoria::where('nombre', 'Alacenas')->first();
        $categoriaCamas = Categoria::where('nombre', 'Camas')->first();
        $categoriaEscritorios = Categoria::where('nombre', 'Escritorios')->first();

        $servicios = [
            [
                'nombre' => 'Instalación de Muebles',
                'descripcion' => 'Servicio de instalación profesional de muebles',
                'precio_base' => 120.00,
                'tiempo_estimado' => 4,
                'activo' => true,
                'categoria_id' => $categoriaAlacenas?->id,
            ],
            [
                'nombre' => 'Reparación de Muebles',
                'descripcion' => 'Servicio de reparación y restauración de muebles',
                'precio_base' => 80.00,
                'tiempo_estimado' => 3,
                'activo' => true,
                'categoria_id' => $categoriaCamas?->id,
            ],
            [
                'nombre' => 'Diseño Personalizado',
                'descripcion' => 'Servicio de diseño de muebles a medida',
                'precio_base' => 200.00,
                'tiempo_estimado' => 8,
                'activo' => true,
                'categoria_id' => $categoriaEscritorios?->id,
            ],
        ];

        foreach ($servicios as $servicio) {
            Servicio::firstOrCreate(['nombre' => $servicio['nombre']], $servicio);
        }
    }
}

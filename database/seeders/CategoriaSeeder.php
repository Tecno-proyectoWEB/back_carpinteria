<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Alacenas',
                'descripcion' => 'Alacenas y gabinetes de cocina',
                'activo' => true,
            ],
            [
                'nombre' => 'Camas',
                'descripcion' => 'Camas y bases de cama',
                'activo' => true,
            ],
            [
                'nombre' => 'Escritorios',
                'descripcion' => 'Escritorios y mesas de trabajo',
                'activo' => true,
            ],
            [
                'nombre' => 'Muebles de Cocina',
                'descripcion' => 'Muebles y accesorios para cocina',
                'activo' => true,
            ],
            [
                'nombre' => 'Muebles de Dormitorio',
                'descripcion' => 'Muebles para habitaciones',
                'activo' => true,
            ],
            [
                'nombre' => 'Muebles de Oficina',
                'descripcion' => 'Muebles para espacios de trabajo',
                'activo' => true,
            ],
            [
                'nombre' => 'Maderas',
                'descripcion' => 'Materiales de madera',
                'activo' => true,
            ],
            [
                'nombre' => 'Herrajes',
                'descripcion' => 'Herrajes y accesorios',
                'activo' => true,
            ],
            [
                'nombre' => 'Barnices y Pinturas',
                'descripcion' => 'Barnices, pinturas y acabados',
                'activo' => true,
            ],
        ];

        foreach ($categorias as $categoria) {
            Categoria::firstOrCreate(['nombre' => $categoria['nombre']], $categoria);
        }
    }
}

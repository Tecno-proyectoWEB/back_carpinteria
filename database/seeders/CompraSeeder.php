<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Compra;
use App\Models\DetallePedidoCompra;
use App\Models\Proveedor;
use App\Models\Usuario;
use App\Models\Material;

class CompraSeeder extends Seeder
{
    public function run(): void
    {
        $proveedor1 = Proveedor::first();
        $proveedor2 = Proveedor::skip(1)->first();
        $usuario = Usuario::where('email', 'propietario@carpinteria.com')->first();
        $material1 = Material::first();
        $material2 = Material::skip(1)->first();
        $material3 = Material::skip(2)->first();

        if (!$proveedor1 || !$usuario || !$material1) {
            return;
        }

        $compras = [
            [
                'estado' => 'COMPLETADA',
                'fecha' => now()->subDays(5),
                'importe_total' => 6825.00,
                'importe_descuento' => 0,
                'proveedor_id' => $proveedor1->id,
                'usuario_id' => $usuario->id,
            ],
            [
                'estado' => 'COMPLETADA',
                'fecha' => now()->subDays(3),
                'importe_total' => 350.00,
                'importe_descuento' => 0,
                'proveedor_id' => $proveedor2?->id ?? $proveedor1->id,
                'usuario_id' => $usuario->id,
            ],
            [
                'estado' => 'PENDIENTE',
                'fecha' => now(),
                'importe_total' => 2240.00,
                'importe_descuento' => 0,
                'proveedor_id' => $proveedor1->id,
                'usuario_id' => $usuario->id,
            ],
        ];

        foreach ($compras as $index => $compraData) {
            $compra = Compra::create($compraData);

            // Crear detalles de compra
            if ($index === 0) {
                // Compra 1: 150 unidades de MDF
                DetallePedidoCompra::create([
                    'cantidad' => 150,
                    'estado' => 'RECIBIDO',
                    'importe' => 6825.00,
                    'importe_desc' => 0,
                    'precio' => 45.50,
                    'compra_id' => $compra->id,
                    'material_id' => $material1->id,
                ]);
            } elseif ($index === 1) {
                // Compra 2: 100 bisagras
                DetallePedidoCompra::create([
                    'cantidad' => 100,
                    'estado' => 'RECIBIDO',
                    'importe' => 350.00,
                    'importe_desc' => 0,
                    'precio' => 3.50,
                    'compra_id' => $compra->id,
                    'material_id' => $material2?->id ?? $material1->id,
                ]);
            } else {
                // Compra 3: 80 litros de barniz
                DetallePedidoCompra::create([
                    'cantidad' => 80,
                    'estado' => 'PENDIENTE',
                    'importe' => 2240.00,
                    'importe_desc' => 0,
                    'precio' => 28.00,
                    'compra_id' => $compra->id,
                    'material_id' => $material3?->id ?? $material1->id,
                ]);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Proveedor;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            [
                'nombre' => 'Maderas del Sur S.A.',
                'ruc' => '20123456789',
                'direccion' => 'Av. Principal 123, Lima',
                'telefono' => '01-2345678',
                'email' => 'ventas@maderasdelsur.com',
                'persona_contacto' => 'Juan García',
                'activo' => true,
            ],
            [
                'nombre' => 'Herrajes y Accesorios S.R.L.',
                'ruc' => '20198765432',
                'direccion' => 'Jr. Comercio 456, Lima',
                'telefono' => '01-9876543',
                'email' => 'contacto@herrajes.com',
                'persona_contacto' => 'María López',
                'activo' => true,
            ],
            [
                'nombre' => 'Barnices y Pinturas Premium',
                'ruc' => '20345678901',
                'direccion' => 'Av. Industrial 789, Lima',
                'telefono' => '01-3456789',
                'email' => 'info@barnicespremium.com',
                'persona_contacto' => 'Carlos Ramírez',
                'activo' => true,
            ],
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::firstOrCreate(['ruc' => $proveedor['ruc']], $proveedor);
        }
    }
}

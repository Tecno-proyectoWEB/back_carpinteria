<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MetodoPago;

class MetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        $metodos = [
            [
                'nombre' => 'EFECTIVO',
                'descripcion' => 'Pago en efectivo',
                'es_electronico' => false,
                'tipo_electronico' => null,
                'numero_cuenta' => null,
                'entidad_financiera' => null,
                'activo' => true,
            ],
            [
                'nombre' => 'QR PagoFácil',
                'descripcion' => 'Pago mediante código QR usando PagoFácil',
                'es_electronico' => true,
                'tipo_electronico' => 'QR',
                'numero_cuenta' => null,
                'entidad_financiera' => 'PagoFácil',
                'activo' => true,
            ],
        ];

        foreach ($metodos as $metodo) {
            MetodoPago::updateOrCreate(
                ['nombre' => $metodo['nombre']],
                $metodo
            );
        }
    }
}

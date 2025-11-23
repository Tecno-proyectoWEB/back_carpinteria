<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MetodoPago;

class MetodoPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $metodos = [
            [
                'nombre' => 'EFECTIVO',
                'descripcion' => 'Pago en efectivo',
            ],
            [
                'nombre' => 'TRANSFERENCIA BANCARIA',
                'descripcion' => 'Transferencia bancaria',
            ],
            [
                'nombre' => 'TARJETA DE CRÉDITO',
                'descripcion' => 'Pago con tarjeta de crédito',
            ],
            [
                'nombre' => 'TARJETA DE DÉBITO',
                'descripcion' => 'Pago con tarjeta de débito',
            ],
            [
                'nombre' => 'QR',
                'descripcion' => 'Pago mediante código QR',
            ],
            [
                'nombre' => 'CHEQUE',
                'descripcion' => 'Pago con cheque',
            ],
        ];

        foreach ($metodos as $metodo) {
            MetodoPago::firstOrCreate(['nombre' => $metodo['nombre']], $metodo);
        }
    }
}

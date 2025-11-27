<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Pago;
use App\Models\Usuario;
use App\Models\MetodoPago;
use App\Models\Producto;
use App\Models\Servicio;

class VentaSeeder extends Seeder
{
    public function run(): void
    {
        $cliente = Usuario::where('email', 'cliente@example.com')->first();
        $secretaria = Usuario::where('email', 'secretaria@carpinteria.com')->first();
        $metodoEfectivo = MetodoPago::where('nombre', 'EFECTIVO')->first();
        $metodoCredito = MetodoPago::where('nombre', 'QR PagoFácil')->first();
        $producto1 = Producto::first();
        $producto2 = Producto::skip(1)->first();
        $servicio1 = Servicio::first();

        if (!$cliente || !$metodoEfectivo || !$producto1) {
            return;
        }

        $ventas = [
            [
                'fecha' => now()->subDays(5),
                'descripcion' => 'Venta de alacena y servicio de instalación',
                'importe_total' => 570.00,
                'importe_total_desc' => 570.00,
                'estado' => true,
                'usuario_id' => $cliente->id,
                'metodo_pago_id' => $metodoEfectivo->id,
            ],
            [
                'fecha' => now()->subDays(3),
                'descripcion' => 'Venta de cama king size',
                'importe_total' => 1200.00,
                'importe_total_desc' => 1200.00,
                'estado' => true,
                'usuario_id' => $cliente->id,
            ],
            [
                'fecha' => now()->subDays(1),
                'descripcion' => 'Venta de escritorio ejecutivo',
                'importe_total' => 850.00,
                'importe_total_desc' => 850.00,
                'estado' => false,
                'usuario_id' => $cliente->id,
            ],
        ];

        foreach ($ventas as $index => $ventaData) {
            if (!isset($ventaData['metodo_pago_id'])) {
                $ventaData['metodo_pago_id'] = $metodoEfectivo->id;
            }
            $venta = Venta::create($ventaData);

            // Crear detalles de venta
            if ($index === 0) {
                // Venta 1: Alacena + Instalación
                DetalleVenta::create([
                    'producto_id' => $producto1->id,
                    'servicio_id' => null,
                    'venta_id' => $venta->id,
                    'cantidad' => 1,
                    'estado' => true,
                    'importe_total' => 450.00,
                    'importe_total_desc' => 0,
                    'precio_unitario' => 450.00,
                ]);
                DetalleVenta::create([
                    'producto_id' => null,
                    'servicio_id' => $servicio1?->id,
                    'venta_id' => $venta->id,
                    'cantidad' => 1,
                    'estado' => true,
                    'importe_total' => 120.00,
                    'importe_total_desc' => 0,
                    'precio_unitario' => 120.00,
                ]);

                // Pago al contado
                Pago::create([
                    'monto' => 570.00,
                    'fecha_pago' => $venta->fecha,
                    'fecha_vencimiento' => null,
                    'estado' => 'PAGADO',
                    'tipo' => 'CONTADO',
                    'numero_cuota' => null,
                    'observaciones' => 'Pago completo al contado',
                    'venta_id' => $venta->id,
                    'metodo_pago_id' => $metodoEfectivo->id,
                    'usuario_id' => $secretaria?->id ?? $cliente->id,
                ]);
            } elseif ($index === 1) {
                // Venta 2: Cama
                DetalleVenta::create([
                    'producto_id' => $producto2?->id ?? $producto1->id,
                    'servicio_id' => null,
                    'venta_id' => $venta->id,
                    'cantidad' => 1,
                    'estado' => true,
                    'importe_total' => 1200.00,
                    'importe_total_desc' => 0,
                    'precio_unitario' => 1200.00,
                ]);

                // Pago al contado
                Pago::create([
                    'monto' => 1200.00,
                    'fecha_pago' => $venta->fecha,
                    'fecha_vencimiento' => null,
                    'estado' => 'PAGADO',
                    'tipo' => 'CONTADO',
                    'numero_cuota' => null,
                    'observaciones' => 'Pago completo al contado',
                    'venta_id' => $venta->id,
                    'metodo_pago_id' => $metodoEfectivo->id,
                    'usuario_id' => $secretaria?->id ?? $cliente->id,
                ]);
            } elseif ($index === 2) {
                // Venta 3: Escritorio (pendiente)
                DetalleVenta::create([
                    'producto_id' => $producto1->id,
                    'servicio_id' => null,
                    'venta_id' => $venta->id,
                    'cantidad' => 1,
                    'estado' => false,
                    'importe_total' => 850.00,
                    'importe_total_desc' => 0,
                    'precio_unitario' => 850.00,
                ]);

                // Crear cuotas para venta a crédito
                $monto_cuota = 850.00 / 3;
                $fecha_cuota = now()->addMonth();

                for ($i = 1; $i <= 3; $i++) {
                    Pago::create([
                        'monto' => $i === 3 ? 850.00 - ($monto_cuota * 2) : $monto_cuota,
                        'fecha_pago' => null,
                        'fecha_vencimiento' => $fecha_cuota->format('Y-m-d'),
                        'estado' => 'PENDIENTE',
                        'tipo' => 'CUOTA',
                        'numero_cuota' => $i,
                        'observaciones' => "Cuota {$i} de 3",
                        'venta_id' => $venta->id,
                        'metodo_pago_id' => $metodoCredito?->id ?? $metodoEfectivo->id,
                        'usuario_id' => null,
                    ]);

                    $fecha_cuota->modify('+1 month');
                }
            }
        }
    }
}


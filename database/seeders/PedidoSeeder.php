<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Pago;
use App\Models\Usuario;
use App\Models\MetodoPago;
use App\Models\Producto;
use App\Models\Servicio;

class PedidoSeeder extends Seeder
{
    public function run(): void
    {
        $cliente = Usuario::where('email', 'cliente@example.com')->first();
        $secretaria = Usuario::where('email', 'secretaria@carpinteria.com')->first();
        $metodoEfectivo = MetodoPago::where('nombre', 'EFECTIVO')->first();
        $metodoCredito = MetodoPago::where('nombre', 'TRANSFERENCIA BANCARIA')->first();
        $producto1 = Producto::first();
        $producto2 = Producto::skip(1)->first();
        $servicio1 = Servicio::first();

        if (!$cliente || !$metodoEfectivo || !$producto1) {
            return;
        }

        $pedidos = [
            [
                'fecha' => now()->subDays(2),
                'descripcion' => 'Pedido de alacena y servicio de instalación',
                'importe_total' => 570.00,
                'importe_total_desc' => 0,
                'estado' => true,
                'metodo_pago_id' => $metodoEfectivo->id,
                'usuario_id' => $cliente->id,
            ],
            [
                'fecha' => now()->subDays(1),
                'descripcion' => 'Pedido de cama king size',
                'importe_total' => 850.00,
                'importe_total_desc' => 0,
                'estado' => true,
                'metodo_pago_id' => $metodoCredito?->id ?? $metodoEfectivo->id,
                'usuario_id' => $cliente->id,
            ],
            [
                'fecha' => now(),
                'descripcion' => 'Pedido de escritorio ejecutivo',
                'importe_total' => 650.00,
                'importe_total_desc' => 0,
                'estado' => false,
                'metodo_pago_id' => $metodoEfectivo->id,
                'usuario_id' => $cliente->id,
            ],
        ];

        foreach ($pedidos as $index => $pedidoData) {
            $pedido = Pedido::create($pedidoData);

            // Crear detalles de pedido
            if ($index === 0) {
                // Pedido 1: Alacena + Instalación
                DetallePedido::create([
                    'producto_id' => $producto1->id,
                    'servicio_id' => null,
                    'pedido_id' => $pedido->id,
                    'cantidad' => 1,
                    'estado' => true,
                    'importe_total' => 450.00,
                    'importe_total_desc' => 0,
                    'precio_unitario' => 450.00,
                ]);
                DetallePedido::create([
                    'producto_id' => null,
                    'servicio_id' => $servicio1?->id,
                    'pedido_id' => $pedido->id,
                    'cantidad' => 1,
                    'estado' => true,
                    'importe_total' => 120.00,
                    'importe_total_desc' => 0,
                    'precio_unitario' => 120.00,
                ]);

                // Pago al contado
                Pago::create([
                    'monto' => 570.00,
                    'fecha_pago' => $pedido->fecha,
                    'fecha_vencimiento' => null,
                    'estado' => 'PAGADO',
                    'tipo' => 'CONTADO',
                    'numero_cuota' => null,
                    'observaciones' => 'Pago completo al contado',
                    'pedido_id' => $pedido->id,
                    'metodo_pago_id' => $metodoEfectivo->id,
                    'usuario_id' => $secretaria?->id ?? $cliente->id,
                ]);
            } elseif ($index === 1) {
                // Pedido 2: Cama
                DetallePedido::create([
                    'producto_id' => $producto2?->id ?? $producto1->id,
                    'servicio_id' => null,
                    'pedido_id' => $pedido->id,
                    'cantidad' => 1,
                    'estado' => true,
                    'importe_total' => 850.00,
                    'importe_total_desc' => 0,
                    'precio_unitario' => 850.00,
                ]);

                // Pago a crédito (2 cuotas)
                Pago::create([
                    'monto' => 425.00,
                    'fecha_pago' => $pedido->fecha,
                    'fecha_vencimiento' => now()->addDays(30),
                    'estado' => 'PAGADO',
                    'tipo' => 'CUOTA',
                    'numero_cuota' => 1,
                    'observaciones' => 'Primera cuota',
                    'pedido_id' => $pedido->id,
                    'metodo_pago_id' => $metodoCredito?->id ?? $metodoEfectivo->id,
                    'usuario_id' => $secretaria?->id ?? $cliente->id,
                ]);
                Pago::create([
                    'monto' => 425.00,
                    'fecha_pago' => null,
                    'fecha_vencimiento' => now()->addDays(60),
                    'estado' => 'PENDIENTE',
                    'tipo' => 'CUOTA',
                    'numero_cuota' => 2,
                    'observaciones' => 'Segunda cuota',
                    'pedido_id' => $pedido->id,
                    'metodo_pago_id' => $metodoCredito?->id ?? $metodoEfectivo->id,
                    'usuario_id' => null,
                ]);
            } else {
                // Pedido 3: Escritorio (pendiente)
                DetallePedido::create([
                    'producto_id' => $producto2?->id ?? $producto1->id,
                    'servicio_id' => null,
                    'pedido_id' => $pedido->id,
                    'cantidad' => 1,
                    'estado' => false,
                    'importe_total' => 650.00,
                    'importe_total_desc' => 0,
                    'precio_unitario' => 650.00,
                ]);

                // Pago pendiente
                Pago::create([
                    'monto' => 650.00,
                    'fecha_pago' => null,
                    'fecha_vencimiento' => now()->addDays(7),
                    'estado' => 'PENDIENTE',
                    'tipo' => 'CREDITO',
                    'numero_cuota' => null,
                    'observaciones' => 'Pago pendiente',
                    'pedido_id' => $pedido->id,
                    'metodo_pago_id' => $metodoEfectivo->id,
                    'usuario_id' => null,
                ]);
            }
        }
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Producto;
use App\Models\MetodoPago;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PedidoSalidaTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $metodoPago;
    protected $producto;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuario para autenticación
        $this->user = Usuario::factory()->create();

        // Crear método de pago
        $this->metodoPago = MetodoPago::factory()->create();

        // Crear producto con stock suficiente
        $this->producto = Producto::factory()->create([
            'stock' => 100,
            'precio_unitario' => 5000,
        ]);
    }

    public function testSalidaProductoSuccess()
    {
        $payload = [
            'usuario_id' => $this->user->id,
            'metodo_pago_id' => $this->metodoPago->id,
            'detalles' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 10,
                    'precio_unitario' => 5000,
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/pedidos/salida-producto', $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'usuario',
            'metodoPago',
            'detalles' => [
                [
                    'producto',
                    'cantidad',
                    'precio_unitario',
                    'importe_total',
                ]
            ],
        ]);

        // Verificar que el stock se actualizó correctamente
        $this->assertDatabaseHas('productos', [
            'id' => $this->producto->id,
            'stock' => 90,
        ]);
    }

    public function testSalidaProductoStockInsuficiente()
    {
        $payload = [
            'usuario_id' => $this->user->id,
            'metodo_pago_id' => $this->metodoPago->id,
            'detalles' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 110,
                    'precio_unitario' => 5000,
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/pedidos/salida-producto', $payload);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => "Stock insuficiente para producto {$this->producto->nombre}"
        ]);

        // Verificar que el stock no fue modificado
        $this->assertDatabaseHas('productos', [
            'id' => $this->producto->id,
            'stock' => 100,
        ]);
    }

    public function testSalidaProductoProductoNoExistente()
    {
        $payload = [
            'usuario_id' => $this->user->id,
            'metodo_pago_id' => $this->metodoPago->id,
            'detalles' => [
                [
                    'producto_id' => 999999, // id no existente
                    'cantidad' => 10,
                    'precio_unitario' => 5000,
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/pedidos/salida-producto', $payload);

        $response->assertStatus(404);
        $response->assertJson([
            'error' => "Producto con id 999999 no encontrado",
        ]);
    }
}

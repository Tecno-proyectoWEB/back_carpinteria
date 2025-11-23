<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReporteTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = Usuario::factory()->create();
    }

    public function testReporteCompras()
    {
        $payload = [
            'fecha_inicio' => '2023-01-01',
            'fecha_fin' => '2023-12-31',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/reportes/compras', $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['fecha', 'importe_total', 'proveedor'],
        ]);
    }

    public function testResumenCompras()
    {
        $payload = [
            'fecha_inicio' => '2023-01-01',
            'fecha_fin' => '2023-12-31',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/reportes/compras/resumen', $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure(['total_compras', 'compras_por_proveedor', 'materiales_mas_comprados']);
    }

    public function testReporteVentas()
    {
        $payload = [
            'fecha_inicio' => '2023-01-01',
            'fecha_fin' => '2023-12-31',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/reportes/ventas', $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['fecha', 'importe_total', 'vendedor'],
        ]);
    }

    public function testResumenVentas()
    {
        $payload = [
            'fecha_inicio' => '2023-01-01',
            'fecha_fin' => '2023-12-31',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/reportes/ventas/resumen', $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure(['total_ventas', 'ventas_por_vendedor', 'productos_mas_vendidos']);
    }
}

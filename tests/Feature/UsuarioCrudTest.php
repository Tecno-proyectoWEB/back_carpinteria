<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UsuarioCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $rol;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rol = Rol::factory()->create();
        $this->user = Usuario::factory()->create();
    }

    public function testCreateUsuario()
    {
        $payload = [
            'nombre' => 'Juan',
            'apellido' => 'Perez',
            'email' => 'juan.perez@example.com',
            'telefono' => '123456789',
            'password' => 'password123',
            'rol_id' => $this->rol->id,
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/usuarios', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('usuario', [
            'email' => 'juan.perez@example.com',
            'nombre' => 'Juan',
        ]);
    }

    public function testReadUsuarios()
    {
        Usuario::factory()->create(['rol_id' => $this->rol->id]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/usuarios');

        $response->assertStatus(200)
                 ->assertJsonStructure([['id', 'nombre', 'apellido', 'email', 'rol']]);
    }

    public function testUpdateUsuario()
    {
        $usuario = Usuario::factory()->create(['rol_id' => $this->rol->id]);

        $response = $this->actingAs($this->user, 'sanctum')->putJson("/api/usuarios/{$usuario->id}", [
            'nombre' => 'Updated',
            'rol_id' => $this->rol->id,
            'password' => 'newpassword123',
            'email' => $usuario->email,
            'apellido' => $usuario->apellido,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('usuario', [
            'id' => $usuario->id,
            'nombre' => 'Updated',
        ]);
    }

    public function testDeleteUsuario()
    {
        $usuario = Usuario::factory()->create(['rol_id' => $this->rol->id]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson("/api/usuarios/{$usuario->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('usuario', [
            'id' => $usuario->id,
        ]);
    }
}

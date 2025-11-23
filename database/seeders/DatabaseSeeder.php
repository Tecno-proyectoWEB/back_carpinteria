<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            PermisoSeeder::class,
            RolPermisoSeeder::class,
            MetodoPagoSeeder::class,
            UsuarioSeeder::class,
            TipoAccionSeeder::class,
            SubcategoriaSeeder::class,
            CategoriaSeeder::class,
            AlmacenSeeder::class,
            SectorSeeder::class,
            ProveedorSeeder::class,
            MaterialSeeder::class,
            ProductoSeeder::class,
            ServicioSeeder::class,
            CompraSeeder::class,
            PedidoSeeder::class,
        ]);
    }
}

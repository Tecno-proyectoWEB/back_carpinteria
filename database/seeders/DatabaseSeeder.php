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
            // TipoAccionSeeder eliminado - modelo TipoAccion fue eliminado
            // SubcategoriaSeeder eliminado - modelo Subcategoria fue eliminado
            CategoriaSeeder::class,
            // AlmacenSeeder eliminado - modelo Almacen fue eliminado
            // SectorSeeder eliminado - modelo Sector fue eliminado
            // ProveedorSeeder eliminado - modelo Proveedor fue eliminado (los proveedores están en Usuario)
            MaterialSeeder::class,
            ProductoSeeder::class,
            ServicioSeeder::class,
            // CompraSeeder eliminado - modelo Compra fue eliminado
            PedidoSeeder::class,
        ]);
    }
}

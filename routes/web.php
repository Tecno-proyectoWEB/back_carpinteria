<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;

// Rutas públicas
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Ruta de búsqueda (pública pero mejor con auth)
Route::get('/buscar', [\App\Http\Controllers\SearchController::class, 'buscar'])->name('buscar');

// Rutas protegidas
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Productos
    Route::resource('productos', \App\Http\Controllers\Web\ProductoController::class);
    
    // Servicios
    Route::resource('servicios', \App\Http\Controllers\Web\ServicioController::class);
    
    // Pedidos
    Route::resource('pedidos', \App\Http\Controllers\Web\PedidoController::class)->only(['index', 'create', 'show']);
    Route::post('pedidos/store-contado', [\App\Http\Controllers\Web\PedidoController::class, 'storeContado'])->name('pedidos.store-contado');
    Route::post('pedidos/store-credito', [\App\Http\Controllers\Web\PedidoController::class, 'storeCredito'])->name('pedidos.store-credito');
    
    // Materiales
    Route::resource('materiales', \App\Http\Controllers\Web\MaterialController::class);
    
    // Compras
    Route::resource('compras', \App\Http\Controllers\Web\CompraController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('compras/{compra}/confirmar', [\App\Http\Controllers\Web\CompraController::class, 'confirmar'])->name('compras.confirmar');
    
    // Usuarios
    Route::resource('usuarios', \App\Http\Controllers\Web\UsuarioController::class);
    
    // Roles
    Route::resource('roles', \App\Http\Controllers\Web\RolController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    
    // Inventario
    Route::get('inventario', [\App\Http\Controllers\Web\InventarioController::class, 'index'])->name('inventario.index');
    Route::get('inventario/stock', [\App\Http\Controllers\Web\InventarioController::class, 'stock'])->name('inventario.stock');
    Route::get('inventario/create', [\App\Http\Controllers\Web\InventarioController::class, 'create'])->name('inventario.create');
    Route::post('inventario', [\App\Http\Controllers\Web\InventarioController::class, 'store'])->name('inventario.store');
    Route::get('inventario/{movimientoInventario}', [\App\Http\Controllers\Web\InventarioController::class, 'show'])->name('inventario.show');
    
    // Reportes
    Route::get('reportes', [\App\Http\Controllers\Web\ReporteController::class, 'index'])->name('reportes.index');
    Route::get('reportes/ventas', [\App\Http\Controllers\Web\ReporteController::class, 'ventas'])->name('reportes.ventas');
    Route::get('reportes/compras', [\App\Http\Controllers\Web\ReporteController::class, 'compras'])->name('reportes.compras');
    Route::get('reportes/inventario', [\App\Http\Controllers\Web\ReporteController::class, 'inventario'])->name('reportes.inventario');
    
    // Proveedores
    Route::resource('proveedores', \App\Http\Controllers\Web\ProveedorController::class);
});

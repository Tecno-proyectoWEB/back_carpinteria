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
    Route::get('roles', [\App\Http\Controllers\Web\RolController::class, 'index'])->name('roles.index');
    Route::get('roles/{rol}/edit', [\App\Http\Controllers\Web\RolController::class, 'edit'])->name('roles.edit');
    Route::put('roles/{rol}', [\App\Http\Controllers\Web\RolController::class, 'update'])->name('roles.update');
    
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
    
    // Bitácora
    Route::get('bitacora', [\App\Http\Controllers\Web\BitacoraController::class, 'index'])->name('bitacora.index');
    Route::get('bitacora/{bitacora}', [\App\Http\Controllers\Web\BitacoraController::class, 'show'])->name('bitacora.show');
    
    // Categorías
    Route::resource('categorias', \App\Http\Controllers\Web\CategoriaController::class);
    
    // Sectores
    Route::resource('sectores', \App\Http\Controllers\Web\SectorController::class);
    
    // Pagos
    Route::get('pagos', [\App\Http\Controllers\Web\PagoController::class, 'index'])->name('pagos.index');
    Route::get('pagos/{pago}', [\App\Http\Controllers\Web\PagoController::class, 'show'])->name('pagos.show');
    Route::post('pagos/{pago}/registrar', [\App\Http\Controllers\Web\PagoController::class, 'registrarPago'])->name('pagos.registrar');
    
    // Métodos de Pago
    Route::resource('metodos-pago', \App\Http\Controllers\Web\MetodoPagoController::class);
    
    // Pagofacil
    Route::post('pagofacil/crear-cupon', [\App\Http\Controllers\Web\PagoFacilController::class, 'crearCupon'])->name('pagofacil.crear-cupon');
    Route::get('pagofacil/plan-pagos/{pedido}', [\App\Http\Controllers\Web\PagoFacilController::class, 'showPlanPagos'])->name('pagofacil.plan-pagos');
    Route::post('pagofacil/crear-plan-pagos', [\App\Http\Controllers\Web\PagoFacilController::class, 'crearPlanPagos'])->name('pagofacil.crear-plan-pagos');
});

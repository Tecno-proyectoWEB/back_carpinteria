<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;

// Rutas públicas
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Ruta para cerrar sesión forzadamente (GET para acceso directo)
Route::get('/logout', [AuthController::class, 'forceLogout'])->name('logout.get');

// Ruta de búsqueda (pública pero mejor con auth)
Route::get('/buscar', [\App\Http\Controllers\SearchController::class, 'buscar'])->name('buscar');

// Rutas protegidas
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Productos - Usando controlador API
    Route::resource('productos', \App\Http\Controllers\ProductoController::class);
    
    // Servicios - Usando controlador API
    Route::resource('servicios', \App\Http\Controllers\ServicioController::class);
    
    // Pedidos - Usando controlador API
    Route::resource('pedidos', \App\Http\Controllers\PedidoController::class)->only(['index', 'create', 'show']);
    Route::post('pedidos/store-contado', [\App\Http\Controllers\PedidoController::class, 'storeContado'])->name('pedidos.store-contado');
    Route::post('pedidos/store-credito', [\App\Http\Controllers\PedidoController::class, 'storeCredito'])->name('pedidos.store-credito');
    
    // Materiales - Usando controlador API
    Route::resource('materiales', \App\Http\Controllers\MaterialController::class);
    
    // Compras - Usando controlador API
    Route::resource('compras', \App\Http\Controllers\CompraController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('compras/{compra}/confirmar', [\App\Http\Controllers\CompraController::class, 'confirmar'])->name('compras.confirmar');
    
    // Usuarios - Usando controlador API
    Route::resource('usuarios', \App\Http\Controllers\UsuarioController::class);
    
    // Roles - Mantener controlador Web (específico para gestión de permisos)
    Route::get('roles', [\App\Http\Controllers\Web\RolController::class, 'index'])->name('roles.index');
    Route::get('roles/{rol}/edit', [\App\Http\Controllers\Web\RolController::class, 'edit'])->name('roles.edit');
    Route::put('roles/{rol}', [\App\Http\Controllers\Web\RolController::class, 'update'])->name('roles.update');
    
    // Permisos - CRUD completo
    Route::resource('permisos', \App\Http\Controllers\PermisoController::class);
    
    // Inventario - Usando controlador API
    Route::get('inventario', [\App\Http\Controllers\MovimientoInventarioController::class, 'index'])->name('inventario.index');
    Route::get('inventario/stock', [\App\Http\Controllers\MovimientoInventarioController::class, 'stock'])->name('inventario.stock');
    Route::get('inventario/create', [\App\Http\Controllers\MovimientoInventarioController::class, 'create'])->name('inventario.create');
    Route::post('inventario', [\App\Http\Controllers\MovimientoInventarioController::class, 'store'])->name('inventario.store');
    Route::get('inventario/{movimientoInventario}', [\App\Http\Controllers\MovimientoInventarioController::class, 'show'])->name('inventario.show');
    
    // Reportes - Mantener controlador Web (específico para vistas de reportes)
    Route::get('reportes', [\App\Http\Controllers\Web\ReporteController::class, 'index'])->name('reportes.index');
    Route::get('reportes/ventas', [\App\Http\Controllers\Web\ReporteController::class, 'ventas'])->name('reportes.ventas');
    Route::get('reportes/compras', [\App\Http\Controllers\Web\ReporteController::class, 'compras'])->name('reportes.compras');
    Route::get('reportes/inventario', [\App\Http\Controllers\Web\ReporteController::class, 'inventario'])->name('reportes.inventario');
    
    // Proveedores - Usando controlador API
    Route::resource('proveedores', \App\Http\Controllers\ProveedorController::class);
    
    // Bitácora - Usando controlador API
    Route::get('bitacora', [\App\Http\Controllers\BitacoraController::class, 'index'])->name('bitacora.index');
    Route::get('bitacora/{bitacora}', [\App\Http\Controllers\BitacoraController::class, 'show'])->name('bitacora.show');
    
    // Categorías - Usando controlador API
    Route::resource('categorias', \App\Http\Controllers\CategoriaController::class);
    
    // Sectores - Usando controlador API
    Route::resource('sectores', \App\Http\Controllers\SectorController::class);
    
    // Pagos - Usando controlador API
    Route::get('pagos', [\App\Http\Controllers\PagoController::class, 'index'])->name('pagos.index');
    Route::get('pagos/{pago}', [\App\Http\Controllers\PagoController::class, 'show'])->name('pagos.show');
    Route::post('pagos/{pago}/registrar', [\App\Http\Controllers\PagoController::class, 'registrarPago'])->name('pagos.registrar');
    
    // Métodos de Pago - Usando controlador API
    Route::resource('metodos-pago', \App\Http\Controllers\MetodoPagoController::class);
    
    // Pagofacil - Usando controlador API
    Route::post('pagofacil/crear-cupon', [\App\Http\Controllers\PagoFacilController::class, 'crearCupon'])->name('pagofacil.crear-cupon');
    Route::get('pagofacil/plan-pagos/{pedido}', [\App\Http\Controllers\PagoFacilController::class, 'showPlanPagos'])->name('pagofacil.plan-pagos');
    Route::post('pagofacil/crear-plan-pagos', [\App\Http\Controllers\PagoFacilController::class, 'crearPlanPagos'])->name('pagofacil.crear-plan-pagos');
});

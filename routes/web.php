<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CU1: Gestión de Usuarios y Roles
    Route::resource('usuarios', UsuarioController::class);
    Route::resource('roles', RolController::class);

    // CU2: Gestión de Productos
    Route::resource('productos', ProductoController::class);

    // CU3: Gestión de Servicios
    Route::resource('servicios', ServicioController::class);

    // CU4: Gestión de Insumos (Materiales)
    Route::resource('materiales', MaterialController::class);

    // CU5: Gestión de Inventarios
    Route::resource('inventarios', MovimientoInventarioController::class)->only(['index', 'create', 'store', 'show']);

    // CU6: Gestión de Ventas (Pedidos)
    Route::resource('pedidos', PedidoController::class);
    Route::post('/pedidos/contado', [PedidoController::class, 'storeContado'])->name('pedidos.storeContado');
    Route::post('/pedidos/credito', [PedidoController::class, 'storeCredito'])->name('pedidos.storeCredito');
    Route::post('/pedidos/{pedido}/confirmar-credito', [PedidoController::class, 'confirmarCredito'])->name('pedidos.confirmarCredito');

    // CU7: Gestión de Pagos
    Route::resource('pagos', PagoController::class);
    Route::post('/pagos/{pago}/registrar', [PagoController::class, 'registrarPago'])->name('pagos.registrar');

    // CU8: Reportes y Estadísticas
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('index');
        Route::get('/ventas', [ReporteController::class, 'ventas'])->name('ventas');
        Route::get('/estadisticas', [ReporteController::class, 'estadisticas'])->name('estadisticas');
        Route::get('/inventario', [ReporteController::class, 'inventario'])->name('inventario');
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Login
Route::get('/login', function () {
    return \Inertia\Inertia::render('Auth/Login');
})->name('login')->middleware('guest');

Route::post('/login', [AuthController::class, 'login'])->middleware('guest');

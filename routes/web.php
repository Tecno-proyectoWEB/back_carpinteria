<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\PaymentController;

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

    // CU6: Gestión de Ventas
    Route::resource('ventas', VentaController::class);
    Route::post('/ventas/contado', [VentaController::class, 'storeContado'])->name('ventas.storeContado');
    Route::post('/ventas/credito', [VentaController::class, 'storeCredito'])->name('ventas.storeCredito');
    Route::post('/ventas/{venta}/confirmar-credito', [VentaController::class, 'confirmarCredito'])->name('ventas.confirmarCredito');

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

    // Búsqueda
    Route::get('/buscar', [BusquedaController::class, 'buscar'])->name('busqueda.buscar');

    // Consultar estado de pago
    Route::get('/payment/{id}/status', [PaymentController::class, 'checkStatus'])->name('payment.status');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Callback de PagoFácil (sin autenticación - debe ser público)
Route::post('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');

// Login
Route::get('/login', function () {
    return \Inertia\Inertia::render('Auth/Login');
})->name('login')->middleware('guest');

Route::post('/login', [AuthController::class, 'login'])->middleware('guest');

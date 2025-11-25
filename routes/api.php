<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\DetallePedidoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\DetallePedidoCompraController;
use App\Http\Controllers\DetalleDevolucionController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\RolPermisoController;
use App\Http\Controllers\TipoAccionController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\PagoController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Ruta de búsqueda para API
Route::get('/buscar', [\App\Http\Controllers\SearchController::class, 'buscar']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('almacenes', AlmacenController::class);
    Route::apiResource('sectores', SectorController::class);
    Route::apiResource('categorias', CategoriaController::class);
    Route::apiResource('materiales', MaterialController::class);
    Route::apiResource('pedidos', PedidoController::class);
    Route::post('pedidos/storeContado', [\App\Http\Controllers\PedidoController::class, 'storeContado']);
    Route::post('pedidos/storeCredito', [\App\Http\Controllers\PedidoController::class, 'storeCredito']);
    Route::post('pedidos/{pedido}/confirmar-credito', [\App\Http\Controllers\PedidoController::class, 'confirmarCredito']);
    Route::apiResource('metodos-pago', MetodoPagoController::class);
    Route::apiResource('detalles-pedido', DetallePedidoController::class);
    Route::apiResource('productos', ProductoController::class);
    Route::apiResource('devoluciones', DevolucionController::class);
    Route::apiResource('proveedores', ProveedorController::class);
    Route::apiResource('compras', CompraController::class);
    Route::post('compras/{compra}/confirmar', [CompraController::class, 'confirmar']);
    Route::apiResource('detalles-pedido-compra', DetallePedidoCompraController::class);
    Route::apiResource('detalles-devolucion', DetalleDevolucionController::class);
    Route::apiResource('bitacoras', BitacoraController::class);
    Route::apiResource('roles', RolController::class);
    Route::apiResource('permisos', PermisoController::class);
    Route::apiResource('rol-permisos', RolPermisoController::class);
    Route::apiResource('tipos-accion', TipoAccionController::class);
    Route::apiResource('stripe-payments', StripePaymentController::class);
    
    // Rutas de Pagofacil
    Route::post('pagofacil/crear-cupon', [\App\Http\Controllers\PagoFacilController::class, 'crearCupon']);
    Route::post('pagofacil/verificar-pago', [\App\Http\Controllers\PagoFacilController::class, 'verificarPago']);
    Route::post('pagofacil/crear-plan-pagos', [\App\Http\Controllers\PagoFacilController::class, 'crearPlanPagos']);
    Route::post('pagofacil/webhook', [\App\Http\Controllers\PagoFacilController::class, 'webhook']);
    Route::apiResource('servicios', ServicioController::class);
    Route::apiResource('movimientos-inventario', MovimientoInventarioController::class);
    Route::apiResource('pagos', PagoController::class);
    Route::post('pagos/{pago}/registrar', [PagoController::class, 'registrarPago']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('pedidos/{pedido}/pago-transferencia', [\App\Http\Controllers\PedidoPagoController::class, 'pagoTransferencia']);
        Route::post('pedidos/{pedido}/pago-qr', [\App\Http\Controllers\PedidoPagoController::class, 'pagoQR']);
        Route::post('devoluciones/{devolucion}/agregar-detalle', [\App\Http\Controllers\DevolucionController::class, 'agregarDetalle']);
        Route::post('devoluciones/{devolucion}/aprobar', [\App\Http\Controllers\DevolucionController::class, 'aprobarDevolucion']);
        Route::post('reportes/compras', [\App\Http\Controllers\ReporteCompraController::class, 'reporteCompras']);
        Route::post('reportes/compras/resumen', [\App\Http\Controllers\ReporteCompraController::class, 'resumenCompras']);
        Route::post('reportes/compras/exportar-pdf', [\App\Http\Controllers\ReporteCompraController::class, 'exportarPdf']);
        Route::post('reportes/ventas', [\App\Http\Controllers\ReporteVentaController::class, 'reporteVentas']);
        Route::post('reportes/ventas/resumen', [\App\Http\Controllers\ReporteVentaController::class, 'resumenVentas']);
        Route::post('reportes/ventas/exportar-pdf', [\App\Http\Controllers\ReporteVentaController::class, 'exportarPdf']);
        Route::post('pedidos/salida-producto', [\App\Http\Controllers\PedidoController::class, 'salidaProducto']);
    });
});

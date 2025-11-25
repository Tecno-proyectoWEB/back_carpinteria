<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PageVisit;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo contar visitas para rutas web (no API)
        if ($request->is('api/*')) {
            return $response;
        }

        // Obtener la ruta actual
        $ruta = $request->path();
        
        // Omitir rutas de assets y archivos estáticos
        if (str_contains($ruta, '.') || $ruta === '') {
            return $response;
        }

        // Nombre descriptivo de la página
        $nombrePagina = $this->getPageName($ruta);

        // Incrementar contador de visitas
        PageVisit::incrementar($ruta, $nombrePagina);

        return $response;
    }

    /**
     * Obtener nombre descriptivo de la página según la ruta
     */
    private function getPageName($ruta)
    {
        $nombres = [
            '/' => 'Dashboard',
            'login' => 'Login',
            'productos' => 'Productos',
            'servicios' => 'Servicios',
            'materiales' => 'Materiales',
            'pedidos' => 'Pedidos',
            'compras' => 'Compras',
            'usuarios' => 'Usuarios',
            'reportes' => 'Reportes',
            'inventario' => 'Inventario',
        ];

        // Extraer la primera parte de la ruta
        $partes = explode('/', $ruta);
        $primeraParte = $partes[0] ?? $ruta;

        return $nombres[$primeraParte] ?? ucfirst(str_replace('-', ' ', $primeraParte));
    }
}


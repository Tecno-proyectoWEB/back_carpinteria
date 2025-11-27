<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\MenuController;

abstract class BaseController extends Controller
{
    /**
     * Determina si la petición es para API o Web
     */
    protected function isApiRequest(Request $request): bool
    {
        return $request->wantsJson() || $request->is('api/*') || $request->expectsJson();
    }

    /**
     * Comparte el usuario autenticado con Inertia
     * NOTA: Esto es un backup por si el middleware HandleInertiaRequests no se ejecuta
     * El middleware debería manejar esto automáticamente
     */
    protected function shareAuthUser(Request $request): void
    {
        // Solo compartir si no está ya compartido (el middleware debería hacerlo)
        // Esto es un fallback de seguridad
        $user = null;
        if ($request->user()) {
            $authUser = $request->user();
            if (!$authUser->relationLoaded('rol')) {
                $authUser->load('rol');
            }
            if ($authUser->rol && !$authUser->rol->relationLoaded('permisos')) {
                $authUser->rol->load('permisos');
            }
            
            $user = [
                'id' => $authUser->id,
                'nombre' => $authUser->nombre ?? '',
                'apellido' => $authUser->apellido ?? '',
                'email' => $authUser->email ?? '',
                'rol' => $authUser->rol ? [
                    'id' => $authUser->rol->id,
                    'nombre' => $authUser->rol->nombre ?? 'Sin rol',
                ] : null,
            ];
        }
        
        // Compartir usuario directamente con Inertia (solo si el middleware no lo hizo)
        Inertia::share([
            'auth' => [
                'user' => $user,
            ],
        ]);
    }

    /**
     * Retorna respuesta JSON o Inertia según el tipo de request
     */
    protected function respond($data, $view = null, $props = [])
    {
        $request = request();
        
        if ($this->isApiRequest($request)) {
            return response()->json($data);
        }

        if ($view) {
            // Compartir usuario autenticado
            $this->shareAuthUser($request);
            
            // Agregar menú y visitas para web
            $menuController = new MenuController();
            $menuItems = $menuController->getMenuForUser($request->user());
            $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());
            
            return Inertia::render($view, array_merge($props, [
                'menuItems' => $menuItems,
                'pageVisits' => $pageVisits,
            ]));
        }

        return response()->json($data);
    }

    /**
     * Retorna error según el tipo de request
     */
    protected function respondError($message, $code = 403)
    {
        $request = request();
        
        if ($this->isApiRequest($request)) {
            return response()->json(['message' => $message], $code);
        }

        abort($code, $message);
    }
}


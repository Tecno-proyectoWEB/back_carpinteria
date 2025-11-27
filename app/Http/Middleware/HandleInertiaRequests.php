<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Obtener contador de visitas para la página actual
        $pageVisits = null;
        if ($request->path() !== '' && !$request->is('api/*')) {
            $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());
        }

        // Obtener usuario autenticado UNA SOLA VEZ
        $user = $request->user();
        $menuItems = [];
        $permisos = [];
        
        if ($user) {
            try {
                // Asegurar que el usuario tenga el rol cargado
                if (!$user->relationLoaded('rol')) {
                    $user->load('rol');
                }
                
                // Solo cargar menú y permisos si el usuario tiene un rol
                if ($user->rol) {
                    // Cargar menú
                    $menuController = new \App\Http\Controllers\MenuController();
                    $menuItems = $menuController->getMenuForUser($user);
                    
                    // Asegurar que sea un array
                    if (!is_array($menuItems)) {
                        $menuItems = [];
                    }
                    
                    // Cargar permisos
                    if (!$user->rol->relationLoaded('permisos')) {
                        $user->rol->load('permisos');
                    }
                    $permisos = $user->permisos()->pluck('nombre')->toArray();
                    
                    // Debug en desarrollo
                    if (config('app.debug')) {
                        if (empty($menuItems)) {
                            \Log::warning('Menú vacío para usuario', [
                                'user_id' => $user->id,
                                'rol_id' => $user->rol->id ?? null,
                                'rol_nombre' => $user->rol->nombre ?? null,
                                'path' => $request->path(),
                                'method' => $request->method(),
                                'timestamp' => now()->toISOString(),
                            ]);
                        } else {
                            \Log::info('Menú cargado correctamente', [
                                'user_id' => $user->id,
                                'rol_nombre' => $user->rol->nombre ?? null,
                                'menu_items_count' => count($menuItems),
                                'menu_items' => array_map(function($item) { return $item['nombre']; }, $menuItems),
                                'path' => $request->path(),
                                'method' => $request->method(),
                                'timestamp' => now()->toISOString(),
                            ]);
                        }
                    }
                } else {
                    \Log::warning('Usuario sin rol asignado', [
                        'user_id' => $user->id,
                        'path' => $request->path(),
                    ]);
                }
            } catch (\Exception $e) {
                // En caso de error, usar array vacío y loguear
                \Log::error('Error al cargar menú: ' . $e->getMessage(), [
                    'user_id' => $user->id ?? null,
                    'path' => $request->path(),
                    'trace' => $e->getTraceAsString()
                ]);
                $menuItems = [];
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'apellido' => $user->apellido,
                    'email' => $user->email,
                    'rol' => $user->rol ? [
                        'id' => $user->rol->id,
                        'nombre' => $user->rol->nombre,
                    ] : null,
                    'permisos' => $permisos,
                ] : null,
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success'),
            ],
            'pageVisits' => $pageVisits,
            // Asegurar que menuItems siempre sea un array, incluso si está vacío
            'menuItems' => is_array($menuItems) ? $menuItems : [],
        ];
    }
}


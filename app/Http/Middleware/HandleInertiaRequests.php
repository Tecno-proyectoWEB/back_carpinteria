<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * Handle the incoming request.
     */
    public function handle($request, \Closure $next)
    {
        try {
            // Log con error_log para asegurar que se vea
            error_log('🔴🔴🔴 HandleInertiaRequests::handle() EJECUTÁNDOSE - INICIO - PATH: ' . $request->path());
            \Log::error('🔴🔴🔴 HandleInertiaRequests::handle() EJECUTÁNDOSE - INICIO', [
                'path' => $request->path(),
                'method' => $request->method(),
                'is_inertia_request' => $request->header('X-Inertia'),
                'wants_json' => $request->wantsJson(),
                'auth_check' => \Illuminate\Support\Facades\Auth::check(),
                'user_id' => $request->user() ? $request->user()->id : null,
                'class' => get_class($this),
            ]);
            
            $response = parent::handle($request, $next);
            
            error_log('🔴🔴🔴 HandleInertiaRequests::handle() COMPLETADO - PATH: ' . $request->path());
            \Log::error('🔴🔴🔴 HandleInertiaRequests::handle() COMPLETADO', [
                'path' => $request->path(),
                'response_type' => get_class($response),
            ]);
            
            return $response;
        } catch (\Exception $e) {
            error_log('❌❌❌ ERROR EN HandleInertiaRequests::handle(): ' . $e->getMessage());
            \Log::error('❌❌❌ ERROR EN HandleInertiaRequests::handle()', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Log para verificar que el middleware se ejecuta - PRIMERO
        error_log('🔵🔵🔵 HandleInertiaRequests::share() ejecutándose - PRIMERO - PATH: ' . $request->path());
        \Log::error('🔵 HandleInertiaRequests::share() ejecutándose - PRIMERO', [
            'path' => $request->path(),
            'method' => $request->method(),
            'session_id' => $request->session()->getId(),
            'auth_check' => \Illuminate\Support\Facades\Auth::check(),
            'user_id' => $request->user() ? $request->user()->id : null,
        ]);
        
        // Obtener contador de visitas para la página actual
        $pageVisits = null;
        if ($request->path() !== '' && !$request->is('api/*')) {
            $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());
        }

        // Cargar usuario con rol y permisos si está autenticado
        $user = null;
        
        // Verificar autenticación de múltiples formas
        $authCheck = Auth::check();
        $requestUser = $request->user();
        $isAuthenticated = $authCheck || $requestUser;
        
        \Log::debug('Verificando autenticación en HandleInertiaRequests', [
            'Auth::check()' => $authCheck,
            '$request->user()' => $requestUser ? $requestUser->id : null,
            'isAuthenticated' => $isAuthenticated,
            'path' => $request->path(),
        ]);
        
        if ($isAuthenticated) {
            try {
                $authUser = $request->user() ?? Auth::user();
                
                if (!$authUser) {
                    \Log::warning('Auth::check() es true pero $request->user() es null', [
                        'session_id' => $request->session()->getId(),
                    ]);
                    $user = null;
                } else {
                    // Asegurar que el rol esté cargado
                    if (!$authUser->relationLoaded('rol')) {
                        $authUser->load('rol');
                    }
                    // Cargar permisos del rol si existe
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
                    
                    \Log::info('Usuario compartido en Inertia', [
                        'user_id' => $user['id'],
                        'email' => $user['email'],
                        'rol' => $user['rol']['nombre'] ?? 'Sin rol',
                        'path' => $request->path(),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Error al cargar usuario en Inertia', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        } else {
            \Log::debug('No hay usuario autenticado en Inertia', [
                'path' => $request->path(),
                'session_id' => $request->session()->getId(),
            ]);
        }

        // Obtener datos compartidos del parent primero
        $parentShared = parent::share($request);
        
        // Construir nuestros datos compartidos - estructura simple y directa
        $ourShared = [
            'auth' => [
                'user' => $user, // null si no está autenticado, array con datos si lo está
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success'),
            ],
            'pageVisits' => $pageVisits,
        ];
        
        // Combinar: nuestros datos sobrescriben los del parent
        $shared = array_merge($parentShared, $ourShared);
        
        // Log detallado para debugging ANTES de retornar
        \Log::debug('Props compartidos en Inertia - ANTES DE RETORNAR', [
            'has_auth' => isset($shared['auth']),
            'auth_type' => gettype($shared['auth'] ?? null),
            'auth_is_array' => is_array($shared['auth'] ?? null),
            'has_user' => isset($shared['auth']['user']) && $shared['auth']['user'] !== null,
            'user_id' => $shared['auth']['user']['id'] ?? 'NO USER',
            'user_data' => $shared['auth']['user'] ?? 'NO USER DATA',
            'auth_structure' => json_encode($shared['auth'] ?? 'NO AUTH'),
            'path' => $request->path(),
            'parent_keys' => array_keys($parentShared),
            'final_keys' => array_keys($shared),
            'final_auth_keys' => isset($shared['auth']) && is_array($shared['auth']) ? array_keys($shared['auth']) : 'NOT ARRAY',
        ]);
        
        // Asegurar que auth siempre esté presente como array
        if (!isset($shared['auth']) || !is_array($shared['auth'])) {
            \Log::error('ERROR CRÍTICO: auth no está presente o no es array!', [
                'shared_keys' => array_keys($shared),
                'auth_exists' => isset($shared['auth']),
                'auth_type' => gettype($shared['auth'] ?? null),
            ]);
            $shared['auth'] = ['user' => $user];
        }
        
        // Verificación final
        if (!isset($shared['auth']['user'])) {
            \Log::warning('WARNING: auth.user no está presente, agregando null', [
                'auth_structure' => $shared['auth'],
            ]);
            $shared['auth']['user'] = $user;
        }
        
        return $shared;
    }
}


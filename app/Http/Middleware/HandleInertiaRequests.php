<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Contar visitas de la página actual
        // IMPORTANTE: ContarVisitas ahora registra la visita ANTES de procesar la request
        // Por lo tanto, podemos contar directamente las visitas registradas
        $visitasPagina = 0;
        $ruta = $request->path();
        $rutaNormalizada = parse_url($ruta, PHP_URL_PATH) ?? $ruta;

        // Contar visitas de la página actual (el registro se hace en el middleware ContarVisitas)
        if ($rutaNormalizada !== 'login' &&
            $rutaNormalizada !== '/' &&
            !str_starts_with($rutaNormalizada, '_') &&
            !str_starts_with($rutaNormalizada, 'api/') &&
            !str_starts_with($rutaNormalizada, 'payment/')) {
            try {
                // Usar DB directamente para evitar problemas con el modelo
                if (\Illuminate\Support\Facades\DB::getSchemaBuilder()->hasTable('visita')) {
                    // Contar todas las visitas de esta ruta (ya incluye la visita actual registrada por ContarVisitas)
                    $visitasPagina = \Illuminate\Support\Facades\DB::table('visita')
                        ->where('ruta', $rutaNormalizada)
                        ->count();
                } else {
                    $visitasPagina = 0;
                    \Illuminate\Support\Facades\Log::warning('La tabla visita no existe para contar visitas');
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error al contar visitas: ' . $e->getMessage(), [
                    'ruta' => $rutaNormalizada,
                    'error' => $e->getTraceAsString()
                ]);
                $visitasPagina = 0;
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'nombre' => $request->user()->nombre,
                    'apellido' => $request->user()->apellido,
                    'email' => $request->user()->email,
                    'rol' => $request->user()->load('rol')->rol ? [
                        'id' => $request->user()->rol->id,
                        'nombre' => $request->user()->rol->nombre,
                    ] : null,
                ] : null,
            ],
            'visitasPagina' => $visitasPagina,
            'ziggy' => function () {
                return (new \Tighten\Ziggy\Ziggy)->toArray();
            },
        ];
    }
}

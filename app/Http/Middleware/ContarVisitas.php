<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ContarVisitas
{
    /**
     * Handle an incoming request.
     * Corrección 5.5: Registrar visita ANTES de procesar la request
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Registrar la visita ANTES de procesar la request para que el contador funcione correctamente
        // Solo contar visitas en rutas GET (incluyendo peticiones de Inertia)
        if ($request->isMethod('GET')) {
            $ruta = $request->path();
            $rutaNormalizada = parse_url($ruta, PHP_URL_PATH) ?? $ruta;

            $excluir = [
                'login',
                '/',
                'buscar',
            ];

            $excluirPrefijos = ['_', 'api/', 'payment/'];

            $debeExcluir = in_array($rutaNormalizada, $excluir) ||
                collect($excluirPrefijos)->contains(function($prefijo) use ($rutaNormalizada) {
                    return str_starts_with($rutaNormalizada, $prefijo);
                });

            if (!$debeExcluir) {
                try {
                    if (!DB::getSchemaBuilder()->hasTable('visita')) {
                        Log::warning('La tabla visita no existe en la base de datos');
                        // No retornar aquí, permitir que la request continúe
                    } else {
                        $usuarioId = null;
                        $user = $request->user();
                        if ($user) {
                            $usuarioId = $user->id ?? null;
                        }

                        $insertado = DB::table('visita')->insert([
                            'ruta' => $rutaNormalizada,
                            'ip' => $request->ip() ?? '127.0.0.1',
                            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                            'usuario_id' => $usuarioId,
                            'created_at' => now(),
                        ]);

                        if (config('app.debug')) {
                            Log::info('Visita registrada', [
                                'ruta' => $rutaNormalizada,
                                'usuario_id' => $usuarioId,
                                'insertado' => $insertado
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error al registrar visita en middleware: ' . $e->getMessage(), [
                        'ruta' => $rutaNormalizada,
                        'method' => $request->method(),
                        'ajax' => $request->ajax(),
                        'wantsJson' => $request->wantsJson(),
                        'inertia' => $request->header('X-Inertia'),
                        'usuario_id' => $request->user()?->id,
                        'error_code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                }
            }
        }

        $response = $next($request);
        return $response;
    }
}


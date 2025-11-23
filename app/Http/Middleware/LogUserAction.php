<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Bitacora;

class LogUserAction
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (auth()->check()) {
            $user = auth()->user();

            Bitacora::create([
                'accion' => $request->method() . ' ' . $request->path(),
                'modulo' => 'API',
                'tabla_afectada' => null,
                'registro_id' => null,
                'usuario_id' => $user->id,
                'direccion_ip' => $request->ip(),
                'navegador' => $request->header('User-Agent'),
                'tipo_accion_id' => null,
            ]);
        }

        return $response;
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // Usar sanctum como guard padrão se nenhum for especificado
        $guards = empty($guards) ? ['sanctum'] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Autenticação bem-sucedida, continuar
                return $next($request);
            }
        }

        // Para APIs, retorna JSON em vez de redirecionar
        return response()->json([
            'message' => 'Unauthenticated.',
            'error' => 'Token de autenticação inválido ou ausente'
        ], 401);
    }
}

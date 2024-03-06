<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class dygavHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->header('DYGAV-TOKEN')) {
            return response()->json(['error' => 'Falta el header requerido'], 400);
        }

        // Agrega aquí más lógica de validación si es necesario
        $request->header('DYGAV-TOKEN', 'dygav1920');
        return $next($request);
    }
}

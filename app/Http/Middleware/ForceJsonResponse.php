<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Принудительно устанавливаем JSON заголовки для API запросов
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
            $request->headers->set('Content-Type', 'application/json');
        }

        $response = $next($request);

        // Устанавливаем заголовки для API ответов
        if ($request->is('api/*') && $response instanceof \Illuminate\Http\JsonResponse) {
            $response->headers->set('Content-Type', 'application/json; charset=utf-8');
            $response->headers->set('Cache-Control', 'no-cache, private');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
        }

        return $response;
    }
}

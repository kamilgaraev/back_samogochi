<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется авторизация'
            ], 401);
        }

        if (!auth()->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Недостаточно прав доступа'
            ], 403);
        }

        return $next($request);
    }
}

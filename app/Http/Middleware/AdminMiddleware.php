<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request with role-based access control.
     * Provides backward compatibility with is_admin flag.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $this->unauthorized($request, 'Необходима авторизация');
        }

        $user = auth()->user();

        // Check if user is admin using new role system or legacy is_admin flag
        if (!$user->isAdmin()) {
            return $this->forbidden($request, 'Недостаточно прав доступа для панели администратора');
        }

        return $next($request);
    }

    /**
     * Handle unauthorized access
     */
    protected function unauthorized(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'code' => 'UNAUTHORIZED'
            ], 401);
        }

        return redirect()->route('admin.login')->with('error', $message);
    }

    /**
     * Handle forbidden access
     */
    protected function forbidden(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'code' => 'FORBIDDEN'
            ], 403);
        }

        abort(403, $message);
    }
}
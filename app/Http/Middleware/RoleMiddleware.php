<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $roles  Comma-separated roles (e.g., 'super-admin,admin')
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return $this->unauthorized($request, 'Authentication required');
        }

        $user = auth()->user();

        // Convert comma-separated string to array if needed
        if (count($roles) === 1 && str_contains($roles[0], ',')) {
            $roles = array_map('trim', explode(',', $roles[0]));
        }

        // Check if user has any of the required roles
        if (!$user->hasAnyRole($roles)) {
            return $this->forbidden($request, 'Insufficient role privileges', $roles);
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
    protected function forbidden(Request $request, string $message, array $requiredRoles = []): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'required_roles' => $requiredRoles,
                'user_roles' => auth()->user()->roles->pluck('name'),
                'code' => 'FORBIDDEN'
            ], 403);
        }

        abort(403, $message . ' Required roles: ' . implode(', ', $requiredRoles));
    }
}

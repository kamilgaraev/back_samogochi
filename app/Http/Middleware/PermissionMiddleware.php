<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permissions  Comma-separated permissions (e.g., 'users.view,users.edit')
     * @param  string|null  $guard  Authentication guard to use (default: web)
     * @param  string  $operator  'and' or 'or' - how to check multiple permissions (default: 'or')
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $permissions, ?string $guard = null, string $operator = 'or'): Response
    {
        $authGuard = $guard ? auth($guard) : auth();

        if (!$authGuard->check()) {
            return $this->unauthorized($request, 'Authentication required');
        }

        $user = $authGuard->user();

        // Convert comma-separated string to array
        $permissionsArray = array_map('trim', explode(',', $permissions));

        // Check permissions based on operator
        $hasPermission = match (strtolower($operator)) {
            'and' => $user->hasAllPermissions($permissionsArray),
            'or' => $user->hasAnyPermission($permissionsArray),
            default => $user->hasAnyPermission($permissionsArray),
        };

        if (!$hasPermission) {
            return $this->forbidden($request, 'Insufficient permissions', $permissionsArray, $operator);
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
    protected function forbidden(Request $request, string $message, array $requiredPermissions = [], string $operator = 'or'): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'required_permissions' => $requiredPermissions,
                'operator' => $operator,
                'user_permissions' => auth()->user()->getAllPermissions()->pluck('name'),
                'code' => 'FORBIDDEN'
            ], 403);
        }

        $operatorText = $operator === 'and' ? 'ALL' : 'ANY';
        abort(403, $message . " Required permissions ({$operatorText}): " . implode(', ', $requiredPermissions));
    }
}

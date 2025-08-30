<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class RateLimitMiddleware
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next, ...$guards): BaseResponse
    {
        $key = $this->resolveRequestSignature($request);
        
        $maxAttempts = $this->getMaxAttempts($guards);
        $decayMinutes = $this->getDecayMinutes($guards);
        
        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildResponse($key, $maxAttempts);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response, 
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    protected function resolveRequestSignature(Request $request): string
    {
        if ($user = $request->user('api')) {
            return sha1('user:'.$user->id.'|'.$request->ip());
        }

        return sha1($request->ip());
    }

    protected function getMaxAttempts(array $guards): int
    {
        return isset($guards[0]) ? (int) $guards[0] : 60;
    }

    protected function getDecayMinutes(array $guards): int
    {
        return isset($guards[1]) ? (int) $guards[1] : 1;
    }

    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        return $maxAttempts - $this->limiter->attempts($key);
    }

    protected function buildResponse(string $key, int $maxAttempts)
    {
        $retryAfter = $this->limiter->availableIn($key);

        return response()->json([
            'success' => false,
            'message' => 'Слишком много запросов. Попробуйте позже.',
            'retry_after' => $retryAfter
        ], 429)->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
            'Retry-After' => $retryAfter,
        ]);
    }

    protected function addHeaders($response, int $maxAttempts, int $remainingAttempts)
    {
        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ]);
    }
}

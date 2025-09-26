<?php

namespace App\Http\Middleware;

use App\Services\RealtimeMetricsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiMetricsMiddleware
{
    protected RealtimeMetricsService $metricsService;

    public function __construct(RealtimeMetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $responseTime = (microtime(true) - $startTime) * 1000;

        $this->metricsService->recordApiResponseTime($responseTime);

        if ($response->getStatusCode() >= 400) {
            $this->recordApiError($request, $response);
        }

        return $response;
    }

    private function recordApiError(Request $request, Response $response): void
    {
        $errorData = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'user_id' => auth()->id() ?? null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
        ];

        Log::error('API Error Recorded', $errorData);
    }
}

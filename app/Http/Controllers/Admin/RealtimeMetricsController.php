<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RealtimeMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RealtimeMetricsController extends Controller
{
    protected RealtimeMetricsService $metricsService;

    public function __construct(RealtimeMetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }

    public function getCurrentMetrics(): JsonResponse
    {
        $metrics = $this->metricsService->getAllMetrics();
        
        return response()->json([
            'success' => true,
            'data' => $metrics
        ]);
    }

    public function getMetricHistory(Request $request, string $metric): JsonResponse
    {
        $hours = $request->get('hours', 24);
        $history = $this->metricsService->getHistoricalData($metric, $hours);
        
        return response()->json([
            'success' => true,
            'data' => [
                'metric' => $metric,
                'history' => $history,
                'hours' => $hours
            ]
        ]);
    }

    public function getMetricTrend(string $metric): JsonResponse
    {
        $trend = $this->metricsService->getMetricTrend($metric);
        
        return response()->json([
            'success' => true,
            'data' => [
                'metric' => $metric,
                'trend' => $trend
            ]
        ]);
    }

    public function getSystemHealth(): JsonResponse
    {
        $health = $this->metricsService->getSystemHealth();
        
        return response()->json([
            'success' => true,
            'data' => $health
        ]);
    }

    public function getDashboardData(): JsonResponse
    {
        $metrics = $this->metricsService->getAllMetrics();
        
        $dashboardData = [
            'metrics' => $metrics,
            'trends' => [
                'players_online' => $this->metricsService->getMetricTrend('players_online'),
                'situations_completed_hour' => $this->metricsService->getMetricTrend('situations_completed_hour'),
                'micro_actions_hour' => $this->metricsService->getMetricTrend('micro_actions_hour'),
                'api_response_time' => $this->metricsService->getMetricTrend('api_response_time'),
            ],
            'charts' => [
                'players_activity' => $this->metricsService->getHistoricalData('active_players_hour', 12),
                'situations_trend' => $this->metricsService->getHistoricalData('situations_completed_hour', 12),
                'system_performance' => $this->getSystemPerformanceChart(),
            ]
        ];
        
        return response()->json([
            'success' => true,
            'data' => $dashboardData
        ]);
    }

    private function getSystemPerformanceChart(): array
    {
        $data = [];
        $now = now();
        
        for ($i = 11; $i >= 0; $i--) {
            $timestamp = $now->copy()->subHours($i);
            $data[] = [
                'timestamp' => $timestamp->toISOString(),
                'response_time' => $this->metricsService->getHistoricalData('api_response_time', 1)[0]['value'] ?? 0,
                'cpu_usage' => rand(20, 80),
                'memory_usage' => rand(30, 70),
            ];
        }
        
        return $data;
    }
}

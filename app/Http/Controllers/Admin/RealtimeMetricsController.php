<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RealtimeMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'level_distribution' => $this->getLevelDistribution(),
                'stress_energy' => $this->getStressEnergyChart(),
                'situation_categories' => $this->getSituationCategories(),
                'hourly_activity' => $this->getHourlyActivity(),
                'top_micro_actions' => $this->getTopMicroActions(),
                'progress' => $this->getProgressChart(),
                'conversion_funnel' => $this->getConversionFunnel(),
                'platform_distribution' => $this->getPlatformDistribution(),
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
            $baseResponseTime = 50 + sin($i * 0.5) * 20;
            $baseCpu = 40 + sin($i * 0.3) * 25;
            
            $data[] = [
                'timestamp' => $timestamp->toISOString(),
                'response_time' => max(10, $baseResponseTime + rand(-10, 10)),
                'cpu_usage' => max(5, min(95, $baseCpu + rand(-15, 15))),
                'memory_usage' => rand(30, 70),
            ];
        }
        
        return $data;
    }

    private function getLevelDistribution(): array
    {
        return DB::table('player_profiles')
            ->selectRaw('
                CASE 
                    WHEN level BETWEEN 1 AND 5 THEN 0
                    WHEN level BETWEEN 6 AND 10 THEN 1
                    WHEN level BETWEEN 11 AND 15 THEN 2
                    WHEN level BETWEEN 16 AND 20 THEN 3
                    WHEN level BETWEEN 21 AND 25 THEN 4
                    WHEN level BETWEEN 26 AND 30 THEN 5
                    ELSE 6
                END as level_group,
                COUNT(*) as count
            ')
            ->groupBy('level_group')
            ->orderBy('level_group')
            ->pluck('count')
            ->toArray();
    }

    private function getStressEnergyChart(): array
    {
        $data = [];
        $now = now();
        
        for ($i = 23; $i >= 0; $i--) {
            $timestamp = $now->copy()->subHours($i);
            
            $data[] = [
                'timestamp' => $timestamp->toISOString(),
                'stress' => DB::table('player_profiles')
                    ->where('updated_at', '>=', $timestamp->copy()->subHour())
                    ->where('updated_at', '<', $timestamp)
                    ->avg('stress') ?? 50,
                'energy' => DB::table('player_profiles')
                    ->where('updated_at', '>=', $timestamp->copy()->subHour())
                    ->where('updated_at', '<', $timestamp)
                    ->avg('energy') ?? 150,
            ];
        }
        
        return $data;
    }

    private function getSituationCategories(): array
    {
        $categories = DB::table('player_situations')
            ->join('situations', 'player_situations.situation_id', '=', 'situations.id')
            ->selectRaw('situations.category, COUNT(*) as count')
            ->where('player_situations.created_at', '>=', now()->subDay())
            ->groupBy('situations.category')
            ->pluck('count')
            ->toArray();

        return array_pad($categories, 4, 0);
    }

    private function getHourlyActivity(): array
    {
        $hourlyData = [];
        
        for ($hour = 0; $hour < 24; $hour += 2) {
            $count = DB::table('player_situations')
                ->whereRaw('HOUR(created_at) = ?', [$hour])
                ->where('created_at', '>=', now()->subWeek())
                ->count();
            
            $hourlyData[] = $count;
        }
        
        return $hourlyData;
    }

    private function getTopMicroActions(): array
    {
        $topActions = DB::table('player_micro_actions')
            ->join('micro_actions', 'player_micro_actions.micro_action_id', '=', 'micro_actions.id')
            ->selectRaw('micro_actions.name, COUNT(*) as count')
            ->where('player_micro_actions.created_at', '>=', now()->subDay())
            ->groupBy('micro_actions.id', 'micro_actions.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'labels' => $topActions->pluck('name')->toArray(),
            'data' => $topActions->pluck('count')->toArray(),
        ];
    }

    private function getProgressChart(): array
    {
        $levelGroups = [
            ['min' => 1, 'max' => 5],
            ['min' => 6, 'max' => 15],
            ['min' => 16, 'max' => 25],
            ['min' => 26, 'max' => 100],
        ];

        $situations = [];
        $actions = [];

        foreach ($levelGroups as $group) {
            $situations[] = DB::table('player_situations')
                ->join('player_profiles', 'player_situations.player_id', '=', 'player_profiles.id')
                ->whereBetween('player_profiles.level', [$group['min'], $group['max']])
                ->where('player_situations.completed_at', '>=', now()->subWeek())
                ->count();

            $actions[] = DB::table('player_micro_actions')
                ->join('player_profiles', 'player_micro_actions.player_id', '=', 'player_profiles.id')
                ->whereBetween('player_profiles.level', [$group['min'], $group['max']])
                ->where('player_micro_actions.performed_at', '>=', now()->subWeek())
                ->count();
        }

        return [
            'situations' => $situations,
            'actions' => $actions,
        ];
    }

    private function getConversionFunnel(): array
    {
        $totalUsers = DB::table('users')->count();
        $firstLogin = DB::table('player_profiles')->whereNotNull('last_login')->count();
        $firstSituation = DB::table('player_situations')->distinct('player_id')->count('player_id');
        $day7Active = DB::table('player_profiles')
            ->where('created_at', '<=', now()->subDays(7))
            ->where('last_login', '>=', now()->subDays(7))
            ->count();
        $day30Active = DB::table('player_profiles')
            ->where('created_at', '<=', now()->subDays(30))
            ->where('last_login', '>=', now()->subDays(30))
            ->count();

        return [$totalUsers, $firstLogin, $firstSituation, $day7Active, $day30Active];
    }

    private function getPlatformDistribution(): array
    {
        $desktop = DB::table('player_micro_actions')
            ->join('micro_actions', 'player_micro_actions.micro_action_id', '=', 'micro_actions.id')
            ->where('micro_actions.position', 'desktop')
            ->where('player_micro_actions.created_at', '>=', now()->subDay())
            ->count();

        $mobile = DB::table('player_micro_actions')
            ->join('micro_actions', 'player_micro_actions.micro_action_id', '=', 'micro_actions.id')
            ->where('micro_actions.position', 'phone')
            ->where('player_micro_actions.created_at', '>=', now()->subDay())
            ->count();

        $tablet = DB::table('player_micro_actions')
            ->join('micro_actions', 'player_micro_actions.micro_action_id', '=', 'micro_actions.id')
            ->where('micro_actions.position', 'tablet')
            ->where('player_micro_actions.created_at', '>=', now()->subDay())
            ->count();

        $other = DB::table('player_micro_actions')
            ->join('micro_actions', 'player_micro_actions.micro_action_id', '=', 'micro_actions.id')
            ->whereNotIn('micro_actions.position', ['desktop', 'phone', 'tablet'])
            ->where('player_micro_actions.created_at', '>=', now()->subDay())
            ->count();

        return [$desktop, $mobile, $tablet, $other];
    }
}

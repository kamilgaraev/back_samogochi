<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RealtimeMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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
                'dau' => $this->metricsService->getMetricTrend('dau'),
            ],
            'charts' => [
                'players_activity' => $this->metricsService->getHistoricalData('active_players_hour', 12),
                'dau_trend' => $this->metricsService->getHistoricalData('dau', 24),
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
            ],
            'segments' => $this->metricsService->getUserSegments(),
            'top_performers' => $this->metricsService->getTopPerformers(5),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $dashboardData
        ]);
    }

    public function getCohortAnalysis(Request $request): JsonResponse
    {
        $days = $request->get('days', 7);
        $cohorts = $this->metricsService->getCohortAnalysis($days);
        
        return response()->json([
            'success' => true,
            'data' => [
                'cohorts' => $cohorts,
                'days' => $days
            ]
        ]);
    }

    public function getUserSegments(): JsonResponse
    {
        $segments = $this->metricsService->getUserSegments();
        
        return response()->json([
            'success' => true,
            'data' => $segments
        ]);
    }

    public function getTopPerformers(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $performers = $this->metricsService->getTopPerformers($limit);
        
        return response()->json([
            'success' => true,
            'data' => $performers
        ]);
    }

    public function getBusinessMetrics(): JsonResponse
    {
        $metrics = [
            // DAU/WAU/MAU
            'dau' => $this->metricsService->getDailyActiveUsers(),
            'wau' => $this->metricsService->getWeeklyActiveUsers(),
            'mau' => $this->metricsService->getMonthlyActiveUsers(),
            'stickiness' => $this->metricsService->getStickiness(),
            
            // Retention
            'retention' => [
                'day1' => $this->metricsService->getRetention(1),
                'day7' => $this->metricsService->getRetention(7),
                'day30' => $this->metricsService->getRetention(30),
            ],
            'churn_rate' => $this->metricsService->getChurnRate(),
            
            // Conversion
            'conversions' => [
                'newcomer' => $this->metricsService->getNewcomerConversion(),
                'tutorial' => $this->metricsService->getTutorialCompletionRate(),
                'situation' => $this->metricsService->getSituationCompletionRate(),
            ],
            
            // Engagement
            'engagement' => [
                'avg_session_duration' => $this->metricsService->getAverageSessionDuration(),
                'avg_actions_per_session' => $this->metricsService->getAverageActionsPerSession(),
                'avg_situations_per_user' => $this->metricsService->getAverageSituationsPerUser(),
                'engagement_score' => $this->metricsService->getEngagementScore(),
            ],
            
            // Growth
            'growth' => [
                'week' => $this->metricsService->getGrowthRate('week'),
                'month' => $this->metricsService->getGrowthRate('month'),
            ],
        ];
        
        return response()->json([
            'success' => true,
            'data' => $metrics
        ]);
    }

    public function refreshMetrics(): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            // Вызываем команду обновления метрик
            \Illuminate\Support\Facades\Artisan::call('metrics:update-realtime');
            
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            // Получаем обновленные метрики
            $metrics = $this->metricsService->getAllMetrics();
            
            return response()->json([
                'success' => true,
                'message' => 'Metrics refreshed successfully',
                'data' => [
                    'execution_time_ms' => $executionTime,
                    'metrics' => $metrics,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh metrics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function clearMetricsCache(): JsonResponse
    {
        try {
            // Очищаем кэш метрик
            $patterns = [
                'realtime_metrics:*',
                'metrics:*',
            ];
            
            foreach ($patterns as $pattern) {
                $keys = Cache::getRedis()->keys($pattern);
                if (!empty($keys)) {
                    foreach ($keys as $key) {
                        Cache::forget(str_replace(config('cache.prefix') . ':', '', $key));
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Metrics cache cleared successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear metrics cache',
                'error' => $e->getMessage(),
            ], 500);
        }
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
        return Cache::remember('metrics:level_distribution', 300, function () {
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
        });
    }

    private function getStressEnergyChart(): array
    {
        return Cache::remember('metrics:stress_energy_chart', 300, function () {
            // Более оптимизированный запрос - берем только активных игроков за последний день
            $avgStress = DB::table('player_profiles')
                ->where('last_login', '>=', now()->subDay())
                ->avg('stress') ?? 50;
            
            $avgEnergy = DB::table('player_profiles')
                ->where('last_login', '>=', now()->subDay())
                ->avg('energy') ?? 150;
            
            $data = [];
            $now = now();
            
            // Генерируем тренд на основе усредненных значений
            for ($i = 23; $i >= 0; $i--) {
                $timestamp = $now->copy()->subHours($i);
                $variance = rand(-5, 5);
                
                $data[] = [
                    'timestamp' => $timestamp->toISOString(),
                    'stress' => round($avgStress + $variance, 1),
                    'energy' => round($avgEnergy + ($variance * -2), 1),
                ];
            }
            
            return $data;
        });
    }

    private function getSituationCategories(): array
    {
        return Cache::remember('metrics:situation_categories', 300, function () {
            $categories = DB::table('player_situations')
                ->join('situations', 'player_situations.situation_id', '=', 'situations.id')
                ->selectRaw('situations.category, COUNT(*) as count')
                ->where('player_situations.created_at', '>=', now()->subDay())
                ->groupBy('situations.category')
                ->pluck('count', 'category')
                ->toArray();

            return array_pad($categories, 4, 0);
        });
    }

    private function getHourlyActivity(): array
    {
        return Cache::remember('metrics:hourly_activity', 600, function () {
            // Оптимизированный запрос - группируем за один запрос (PostgreSQL синтаксис)
            $hourlyCounts = DB::table('player_situations')
                ->selectRaw('EXTRACT(HOUR FROM created_at)::integer as hour, COUNT(*) as count')
                ->where('created_at', '>=', now()->subWeek())
                ->groupBy(DB::raw('EXTRACT(HOUR FROM created_at)'))
                ->pluck('count', 'hour')
                ->toArray();
            
            $hourlyData = [];
            for ($hour = 0; $hour < 24; $hour += 2) {
                $hourlyData[] = $hourlyCounts[$hour] ?? 0;
            }
            
            return $hourlyData;
        });
    }

    private function getTopMicroActions(): array
    {
        return Cache::remember('metrics:top_micro_actions', 300, function () {
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
        });
    }

    private function getProgressChart(): array
    {
        return Cache::remember('metrics:progress_chart', 600, function () {
            // Оптимизированный запрос - получаем все за один раз
            $situationsByLevel = DB::table('player_situations')
                ->join('player_profiles', 'player_situations.player_id', '=', 'player_profiles.id')
                ->selectRaw('
                    CASE 
                        WHEN player_profiles.level BETWEEN 1 AND 5 THEN 0
                        WHEN player_profiles.level BETWEEN 6 AND 15 THEN 1
                        WHEN player_profiles.level BETWEEN 16 AND 25 THEN 2
                        ELSE 3
                    END as level_group,
                    COUNT(*) as count
                ')
                ->where('player_situations.completed_at', '>=', now()->subWeek())
                ->groupBy('level_group')
                ->pluck('count', 'level_group')
                ->toArray();

            $actionsByLevel = DB::table('player_micro_actions')
                ->join('player_profiles', 'player_micro_actions.player_id', '=', 'player_profiles.id')
                ->selectRaw('
                    CASE 
                        WHEN player_profiles.level BETWEEN 1 AND 5 THEN 0
                        WHEN player_profiles.level BETWEEN 6 AND 15 THEN 1
                        WHEN player_profiles.level BETWEEN 16 AND 25 THEN 2
                        ELSE 3
                    END as level_group,
                    COUNT(*) as count
                ')
                ->where('player_micro_actions.created_at', '>=', now()->subWeek())
                ->groupBy('level_group')
                ->pluck('count', 'level_group')
                ->toArray();

            $situations = [];
            $actions = [];
            
            for ($i = 0; $i < 4; $i++) {
                $situations[] = $situationsByLevel[$i] ?? 0;
                $actions[] = $actionsByLevel[$i] ?? 0;
            }

            return [
                'situations' => $situations,
                'actions' => $actions,
            ];
        });
    }

    private function getConversionFunnel(): array
    {
        return Cache::remember('metrics:conversion_funnel', 600, function () {
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
        });
    }

    private function getPlatformDistribution(): array
    {
        return Cache::remember('metrics:platform_distribution', 300, function () {
            // Оптимизированный запрос - одним запросом (PostgreSQL синтаксис с одинарными кавычками)
            $distribution = DB::table('player_micro_actions')
                ->join('micro_actions', 'player_micro_actions.micro_action_id', '=', 'micro_actions.id')
                ->selectRaw("
                    CASE 
                        WHEN micro_actions.position = 'desktop' THEN 'desktop'
                        WHEN micro_actions.position = 'phone' THEN 'mobile'
                        WHEN micro_actions.position = 'tablet' THEN 'tablet'
                        ELSE 'other'
                    END as platform,
                    COUNT(*) as count
                ")
                ->where('player_micro_actions.created_at', '>=', now()->subDay())
                ->groupBy(DB::raw("CASE 
                    WHEN micro_actions.position = 'desktop' THEN 'desktop'
                    WHEN micro_actions.position = 'phone' THEN 'mobile'
                    WHEN micro_actions.position = 'tablet' THEN 'tablet'
                    ELSE 'other'
                END"))
                ->pluck('count', 'platform')
                ->toArray();

            return [
                $distribution['desktop'] ?? 0,
                $distribution['mobile'] ?? 0,
                $distribution['tablet'] ?? 0,
                $distribution['other'] ?? 0,
            ];
        });
    }
}

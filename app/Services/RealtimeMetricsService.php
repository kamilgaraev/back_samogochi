<?php

namespace App\Services;

use App\Models\User;
use App\Models\PlayerProfile;
use App\Models\PlayerSituation;
use App\Models\PlayerMicroAction;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class RealtimeMetricsService
{
    private const CACHE_PREFIX = 'realtime_metrics:';
    private const CACHE_TTL = 60; // 1 minute

    public function getAllMetrics(): array
    {
        return [
            // Реал-тайм метрики
            'players_online' => $this->getPlayersOnline(),
            'active_players_hour' => $this->getActivePlayersLastHour(),
            'situations_completed_hour' => $this->getSituationsCompletedLastHour(),
            'micro_actions_hour' => $this->getMicroActionsLastHour(),
            'avg_stress_level' => $this->getAverageStressLevel(),
            'avg_energy_level' => $this->getAverageEnergyLevel(),
            'new_registrations_hour' => $this->getNewRegistrationsLastHour(),
            'api_errors_hour' => $this->getApiErrorsLastHour(),
            'api_response_time' => $this->getApiResponseTime(),
            'active_sessions' => $this->getActiveSessions(),
            
            // Бизнес-метрики
            'dau' => $this->getDailyActiveUsers(),
            'wau' => $this->getWeeklyActiveUsers(),
            'mau' => $this->getMonthlyActiveUsers(),
            'stickiness' => $this->getStickiness(),
            
            // Retention метрики
            'retention_day1' => $this->getRetention(1),
            'retention_day7' => $this->getRetention(7),
            'retention_day30' => $this->getRetention(30),
            'churn_rate' => $this->getChurnRate(),
            
            // Конверсия
            'newcomer_conversion' => $this->getNewcomerConversion(),
            'tutorial_completion_rate' => $this->getTutorialCompletionRate(),
            'situation_completion_rate' => $this->getSituationCompletionRate(),
            
            // Engagement
            'avg_session_duration' => $this->getAverageSessionDuration(),
            'avg_actions_per_session' => $this->getAverageActionsPerSession(),
            'avg_situations_per_user' => $this->getAverageSituationsPerUser(),
            'engagement_score' => $this->getEngagementScore(),
            
            // Рост
            'growth_rate_week' => $this->getGrowthRate('week'),
            'growth_rate_month' => $this->getGrowthRate('month'),
            
            'system_health' => $this->getSystemHealth(),
            'timestamp' => now()->toISOString(),
        ];
    }

    public function getPlayersOnline(): int
    {
        return Cache::remember(self::CACHE_PREFIX . 'players_online', 30, function () {
            return PlayerProfile::where('last_login', '>=', now()->subMinutes(15))->count();
        });
    }

    public function getActivePlayersLastHour(): int
    {
        return Cache::remember(self::CACHE_PREFIX . 'active_hour', self::CACHE_TTL, function () {
            return ActivityLog::where('created_at', '>=', now()->subHour())
                ->distinct('user_id')
                ->count();
        });
    }

    public function getSituationsCompletedLastHour(): int
    {
        return Cache::remember(self::CACHE_PREFIX . 'situations_hour', self::CACHE_TTL, function () {
            return PlayerSituation::whereNotNull('completed_at')
                ->where('completed_at', '>=', now()->subHour())
                ->count();
        });
    }

    public function getMicroActionsLastHour(): int
    {
        return Cache::remember(self::CACHE_PREFIX . 'micro_actions_hour', self::CACHE_TTL, function () {
            return PlayerMicroAction::where('created_at', '>=', now()->subHour())->count();
        });
    }

    public function getAverageStressLevel(): float
    {
        return Cache::remember(self::CACHE_PREFIX . 'avg_stress', self::CACHE_TTL, function () {
            return round(PlayerProfile::where('last_login', '>=', now()->subDay())->avg('stress') ?? 0, 1);
        });
    }

    public function getAverageEnergyLevel(): float
    {
        return Cache::remember(self::CACHE_PREFIX . 'avg_energy', self::CACHE_TTL, function () {
            return round(PlayerProfile::where('last_login', '>=', now()->subDay())->avg('energy') ?? 0, 1);
        });
    }

    public function getNewRegistrationsLastHour(): int
    {
        return Cache::remember(self::CACHE_PREFIX . 'new_registrations', self::CACHE_TTL, function () {
            return User::where('created_at', '>=', now()->subHour())->count();
        });
    }

    public function getApiErrorsLastHour(): int
    {
        return Cache::remember(self::CACHE_PREFIX . 'api_errors', self::CACHE_TTL, function () {
            $logFile = storage_path('logs/laravel.log');
            if (!file_exists($logFile)) {
                return 0;
            }

            $command = "tail -n 1000 {$logFile} | grep -E '\\[" . now()->subHour()->format('Y-m-d H') . "' | grep -E 'ERROR|CRITICAL' | wc -l";
            return (int) shell_exec($command) ?: 0;
        });
    }

    public function getApiResponseTime(): float
    {
        return Cache::remember(self::CACHE_PREFIX . 'response_time', 30, function () {
            $metrics = Redis::lrange('api_response_times', 0, 99);
            if (empty($metrics)) {
                return 0.0;
            }
            
            return round(array_sum($metrics) / count($metrics), 2);
        });
    }

    public function getActiveSessions(): int
    {
        return Cache::remember(self::CACHE_PREFIX . 'active_sessions', 30, function () {
            try {
                $sessionFiles = glob(storage_path('framework/sessions/*'));
                $activeSessions = 0;
                $threshold = now()->subMinutes(30)->timestamp;

                foreach ($sessionFiles as $file) {
                    if (filemtime($file) > $threshold) {
                        $activeSessions++;
                    }
                }

                return $activeSessions;
            } catch (\Exception $e) {
                return 0;
            }
        });
    }

    public function getNewcomerConversion(): float
    {
        return Cache::remember(self::CACHE_PREFIX . 'newcomer_conversion', self::CACHE_TTL * 5, function () {
            $newUsersThisWeek = User::where('created_at', '>=', now()->subWeek())->count();
            
            if ($newUsersThisWeek === 0) {
                return 0.0;
            }

            $completedTutorial = User::where('created_at', '>=', now()->subWeek())
                ->whereHas('playerProfile', function ($query) {
                    $query->where('level', '>', 1);
                })
                ->count();

            return round(($completedTutorial / $newUsersThisWeek) * 100, 1);
        });
    }

    public function getSystemHealth(): array
    {
        return Cache::remember(self::CACHE_PREFIX . 'system_health', 30, function () {
            $health = [
                'status' => 'healthy',
                'cpu_usage' => 0,
                'memory_usage' => 0,
                'db_connections' => 0,
                'redis_status' => true,
            ];

            try {
                if (function_exists('sys_getloadavg')) {
                    $load = sys_getloadavg();
                    $health['cpu_usage'] = round($load[0] * 100, 1);
                }

                $memoryUsage = memory_get_usage(true);
                $memoryLimit = $this->parseBytes(ini_get('memory_limit'));
                if ($memoryLimit > 0) {
                    $health['memory_usage'] = round(($memoryUsage / $memoryLimit) * 100, 1);
                } else {
                    $health['memory_usage'] = 0;
                }

                try {
                    $result = DB::select("SHOW STATUS LIKE 'Threads_connected'");
                    $health['db_connections'] = $result[0]->Value ?? 0;
                } catch (\Exception $e) {
                    $health['db_connections'] = 'N/A';
                }

                try {
                    Redis::ping();
                    $health['redis_status'] = true;
                } catch (\Exception $e) {
                    $health['redis_status'] = false;
                }

                if ($health['cpu_usage'] > 80 || $health['memory_usage'] > 85 || !$health['redis_status']) {
                    $health['status'] = 'warning';
                }

                if ($health['cpu_usage'] > 95 || $health['memory_usage'] > 95) {
                    $health['status'] = 'critical';
                }

            } catch (\Exception $e) {
                $health['status'] = 'error';
                $health['redis_status'] = false;
            }

            return $health;
        });
    }

    public function getHistoricalData(string $metric, int $hours = 24): array
    {
        $data = [];
        $now = now();
        
        for ($i = $hours - 1; $i >= 0; $i--) {
            $timestamp = $now->copy()->subHours($i);
            $key = self::CACHE_PREFIX . "history:{$metric}:" . $timestamp->format('Y-m-d-H');
            
            $value = Cache::get($key);
            if ($value === null) {
                $value = $this->getCurrentMetricValue($metric);
            }
            
            $data[] = [
                'timestamp' => $timestamp->toISOString(),
                'value' => $value,
            ];
        }

        return $data;
    }

    private function getCurrentMetricValue(string $metric)
    {
        switch ($metric) {
            case 'players_online':
                return $this->getPlayersOnline();
            case 'active_players_hour':
                return $this->getActivePlayersLastHour();
            case 'situations_completed_hour':
                return $this->getSituationsCompletedLastHour();
            case 'micro_actions_hour':
                return $this->getMicroActionsLastHour();
            case 'api_response_time':
                return $this->getApiResponseTime();
            default:
                return rand(10, 100); // Заглушка для тестирования
        }
    }

    public function storeHistoricalMetric(string $metric, $value): void
    {
        $key = self::CACHE_PREFIX . "history:{$metric}:" . now()->format('Y-m-d-H');
        Cache::put($key, $value, 60 * 60 * 25); // 25 hours
        
        // Также сохраняем с минутной точностью для более плавных графиков
        $minuteKey = self::CACHE_PREFIX . "minute:{$metric}:" . now()->format('Y-m-d-H-i');
        Cache::put($minuteKey, $value, 60 * 2); // 2 hours
    }

    public function recordApiResponseTime(float $responseTime): void
    {
        Redis::lpush('api_response_times', $responseTime);
        Redis::ltrim('api_response_times', 0, 99);
        Redis::expire('api_response_times', 300);
    }

    private function parseBytes(string $val): int
    {
        $val = trim($val);
        if ($val === '-1') {
            return PHP_INT_MAX; // Unlimited memory
        }
        
        $unit = strtolower(substr($val, -1));
        $val = (int) $val;

        switch ($unit) {
            case 'g':
                $val *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $val *= 1024 * 1024;
                break;
            case 'k':
                $val *= 1024;
                break;
        }

        return max(1, $val); // Avoid division by zero
    }

    public function getMetricTrend(string $metric, int $periods = 5): array
    {
        $values = [];
        
        for ($i = $periods - 1; $i >= 0; $i--) {
            $timestamp = now()->subMinutes($i * 15);
            $key = self::CACHE_PREFIX . "trend:{$metric}:" . $timestamp->format('H:i');
            $values[] = Cache::get($key, 0);
        }

        if (count($values) < 2) {
            return ['direction' => 'stable', 'change' => 0];
        }

        $current = end($values);
        $previous = $values[count($values) - 2];
        
        if ($previous == 0) {
            return ['direction' => 'stable', 'change' => 0];
        }

        $change = (($current - $previous) / $previous) * 100;
        
        $direction = 'stable';
        if ($change > 5) {
            $direction = 'up';
        } elseif ($change < -5) {
            $direction = 'down';
        }

        return [
            'direction' => $direction,
            'change' => round($change, 1)
        ];
    }

    // ===== БИЗНЕС-МЕТРИКИ =====
    
    public function getDailyActiveUsers(): int
    {
        return Cache::remember(self::CACHE_PREFIX . 'dau', self::CACHE_TTL, function () {
            return PlayerProfile::where('last_login', '>=', now()->subDay())->count();
        });
    }

    public function getWeeklyActiveUsers(): int
    {
        return Cache::remember(self::CACHE_PREFIX . 'wau', self::CACHE_TTL * 5, function () {
            return PlayerProfile::where('last_login', '>=', now()->subWeek())->count();
        });
    }

    public function getMonthlyActiveUsers(): int
    {
        return Cache::remember(self::CACHE_PREFIX . 'mau', self::CACHE_TTL * 10, function () {
            return PlayerProfile::where('last_login', '>=', now()->subMonth())->count();
        });
    }

    public function getStickiness(): float
    {
        return Cache::remember(self::CACHE_PREFIX . 'stickiness', self::CACHE_TTL * 5, function () {
            $dau = $this->getDailyActiveUsers();
            $mau = $this->getMonthlyActiveUsers();
            
            if ($mau === 0) {
                return 0.0;
            }
            
            return round(($dau / $mau) * 100, 1);
        });
    }

    // ===== RETENTION МЕТРИКИ =====
    
    public function getRetention(int $days): float
    {
        return Cache::remember(self::CACHE_PREFIX . "retention_day{$days}", self::CACHE_TTL * 10, function () use ($days) {
            $cohortDate = now()->subDays($days);
            
            // Пользователи зарегистрированные N дней назад
            $cohortUsers = User::whereDate('created_at', $cohortDate->toDateString())->count();
            
            if ($cohortUsers === 0) {
                return 0.0;
            }
            
            // Сколько из них вернулись сегодня
            $returnedUsers = User::whereDate('created_at', $cohortDate->toDateString())
                ->whereHas('playerProfile', function ($query) {
                    $query->where('last_login', '>=', now()->subDay());
                })
                ->count();
            
            return round(($returnedUsers / $cohortUsers) * 100, 1);
        });
    }

    public function getChurnRate(): float
    {
        return Cache::remember(self::CACHE_PREFIX . 'churn_rate', self::CACHE_TTL * 10, function () {
            // Пользователи которые были активны месяц назад
            $activeLastMonth = PlayerProfile::whereBetween('last_login', [
                now()->subMonths(2),
                now()->subMonth()
            ])->count();
            
            if ($activeLastMonth === 0) {
                return 0.0;
            }
            
            // Из них сколько не вернулись в этом месяце
            $churned = PlayerProfile::whereBetween('last_login', [
                now()->subMonths(2),
                now()->subMonth()
            ])
            ->where('last_login', '<', now()->subMonth())
            ->count();
            
            return round(($churned / $activeLastMonth) * 100, 1);
        });
    }

    // ===== КОНВЕРСИЯ =====
    
    public function getTutorialCompletionRate(): float
    {
        return Cache::remember(self::CACHE_PREFIX . 'tutorial_completion', self::CACHE_TTL * 5, function () {
            $totalUsers = User::where('created_at', '>=', now()->subWeek())->count();
            
            if ($totalUsers === 0) {
                return 0.0;
            }
            
            $completedTutorial = User::where('created_at', '>=', now()->subWeek())
                ->whereHas('playerProfile', function ($query) {
                    $query->where('level', '>=', 2);
                })
                ->count();
            
            return round(($completedTutorial / $totalUsers) * 100, 1);
        });
    }

    public function getSituationCompletionRate(): float
    {
        return Cache::remember(self::CACHE_PREFIX . 'situation_completion', self::CACHE_TTL, function () {
            $started = PlayerSituation::where('created_at', '>=', now()->subDay())->count();
            
            if ($started === 0) {
                return 0.0;
            }
            
            $completed = PlayerSituation::whereNotNull('completed_at')
                ->where('created_at', '>=', now()->subDay())
                ->count();
            
            return round(($completed / $started) * 100, 1);
        });
    }

    // ===== ENGAGEMENT =====
    
    public function getAverageSessionDuration(): float
    {
        return Cache::remember(self::CACHE_PREFIX . 'avg_session_duration', self::CACHE_TTL * 2, function () {
            // Примерная оценка на основе активности
            $sessions = ActivityLog::where('created_at', '>=', now()->subDay())
                ->select('user_id', DB::raw('MIN(created_at) as session_start'), DB::raw('MAX(created_at) as session_end'))
                ->groupBy('user_id')
                ->get();
            
            if ($sessions->isEmpty()) {
                return 0.0;
            }
            
            $totalDuration = 0;
            foreach ($sessions as $session) {
                $duration = Carbon::parse($session->session_end)->diffInMinutes(Carbon::parse($session->session_start));
                $totalDuration += min($duration, 180); // Ограничим 3 часами на сессию
            }
            
            return round($totalDuration / $sessions->count(), 1);
        });
    }

    public function getAverageActionsPerSession(): float
    {
        return Cache::remember(self::CACHE_PREFIX . 'avg_actions_per_session', self::CACHE_TTL * 2, function () {
            $activeUsersToday = $this->getDailyActiveUsers();
            
            if ($activeUsersToday === 0) {
                return 0.0;
            }
            
            $totalActions = PlayerMicroAction::where('created_at', '>=', now()->subDay())->count();
            
            return round($totalActions / $activeUsersToday, 1);
        });
    }

    public function getAverageSituationsPerUser(): float
    {
        return Cache::remember(self::CACHE_PREFIX . 'avg_situations_per_user', self::CACHE_TTL * 5, function () {
            $totalUsers = User::count();
            
            if ($totalUsers === 0) {
                return 0.0;
            }
            
            $totalSituations = PlayerSituation::whereNotNull('completed_at')->count();
            
            return round($totalSituations / $totalUsers, 1);
        });
    }

    public function getEngagementScore(): float
    {
        return Cache::remember(self::CACHE_PREFIX . 'engagement_score', self::CACHE_TTL * 5, function () {
            // Комплексный скор на основе нескольких факторов
            $dau = $this->getDailyActiveUsers();
            $mau = $this->getMonthlyActiveUsers();
            
            if ($mau === 0) {
                return 0.0;
            }
            
            $stickiness = ($dau / $mau) * 100;
            $situationRate = $this->getSituationCompletionRate();
            $avgActions = $this->getAverageActionsPerSession();
            
            // Взвешенный скор
            $score = ($stickiness * 0.4) + ($situationRate * 0.3) + (min($avgActions / 10, 10) * 0.3);
            
            return round($score, 1);
        });
    }

    // ===== РОСТ =====
    
    public function getGrowthRate(string $period): float
    {
        return Cache::remember(self::CACHE_PREFIX . "growth_rate_{$period}", self::CACHE_TTL * 10, function () use ($period) {
            $days = $period === 'week' ? 7 : 30;
            $previousDays = $days * 2;
            
            $currentPeriod = User::where('created_at', '>=', now()->subDays($days))->count();
            $previousPeriod = User::whereBetween('created_at', [
                now()->subDays($previousDays),
                now()->subDays($days)
            ])->count();
            
            if ($previousPeriod === 0) {
                return $currentPeriod > 0 ? 100.0 : 0.0;
            }
            
            return round((($currentPeriod - $previousPeriod) / $previousPeriod) * 100, 1);
        });
    }

    // ===== ДОПОЛНИТЕЛЬНЫЕ АНАЛИТИЧЕСКИЕ МЕТОДЫ =====
    
    public function getCohortAnalysis(int $cohortDays = 7): array
    {
        return Cache::remember(self::CACHE_PREFIX . "cohort_analysis_{$cohortDays}", self::CACHE_TTL * 30, function () use ($cohortDays) {
            $cohorts = [];
            
            for ($i = 0; $i < $cohortDays; $i++) {
                $cohortDate = now()->subDays($i);
                $cohortUsers = User::whereDate('created_at', $cohortDate->toDateString())
                    ->pluck('id');
                
                if ($cohortUsers->isEmpty()) {
                    continue;
                }
                
                $cohorts[] = [
                    'date' => $cohortDate->toDateString(),
                    'users' => $cohortUsers->count(),
                    'day0_retention' => 100,
                    'day1_retention' => $this->calculateDayRetention($cohortUsers, $cohortDate, 1),
                    'day3_retention' => $this->calculateDayRetention($cohortUsers, $cohortDate, 3),
                    'day7_retention' => $this->calculateDayRetention($cohortUsers, $cohortDate, 7),
                ];
            }
            
            return $cohorts;
        });
    }

    private function calculateDayRetention($userIds, Carbon $cohortDate, int $day): float
    {
        if ($cohortDate->addDays($day)->isFuture()) {
            return 0.0;
        }
        
        $targetDate = $cohortDate->copy()->addDays($day);
        $returnedUsers = PlayerProfile::whereIn('id', $userIds)
            ->whereDate('last_login', '>=', $targetDate->toDateString())
            ->count();
        
        return $userIds->count() > 0 ? round(($returnedUsers / $userIds->count()) * 100, 1) : 0.0;
    }

    public function getTopPerformers(int $limit = 10): array
    {
        return Cache::remember(self::CACHE_PREFIX . 'top_performers', self::CACHE_TTL * 10, function () use ($limit) {
            return PlayerProfile::with('user')
                ->orderByDesc('level')
                ->orderByDesc('total_experience')
                ->limit($limit)
                ->get()
                ->map(function ($profile) {
                    return [
                        'id' => $profile->id,
                        'username' => $profile->user->username ?? 'Unknown',
                        'level' => $profile->level,
                        'experience' => $profile->total_experience,
                        'situations_completed' => PlayerSituation::where('player_id', $profile->id)
                            ->whereNotNull('completed_at')
                            ->count(),
                    ];
                })
                ->toArray();
        });
    }

    public function getUserSegments(): array
    {
        return Cache::remember(self::CACHE_PREFIX . 'user_segments', self::CACHE_TTL * 10, function () {
            $totalUsers = User::count();
            
            if ($totalUsers === 0) {
                return [
                    'new_users' => 0,
                    'active_users' => 0,
                    'at_risk' => 0,
                    'churned' => 0,
                ];
            }
            
            return [
                'new_users' => User::where('created_at', '>=', now()->subWeek())->count(),
                'active_users' => PlayerProfile::where('last_login', '>=', now()->subWeek())->count(),
                'at_risk' => PlayerProfile::whereBetween('last_login', [
                    now()->subMonth(),
                    now()->subWeeks(2)
                ])->count(),
                'churned' => PlayerProfile::where('last_login', '<', now()->subMonth())->count(),
            ];
        });
    }
}

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
            'newcomer_conversion' => $this->getNewcomerConversion(),
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
                $memoryLimit = ini_get('memory_limit');
                $health['memory_usage'] = round(($memoryUsage / $this->parseBytes($memoryLimit)) * 100, 1);

                $health['db_connections'] = DB::select("SHOW STATUS LIKE 'Threads_connected'")[0]->Value ?? 0;

                Redis::ping();
                $health['redis_status'] = true;

                if ($health['cpu_usage'] > 80 || $health['memory_usage'] > 85) {
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
        $last = strtolower($val[strlen($val) - 1]);
        $val = (int) $val;

        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
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
}

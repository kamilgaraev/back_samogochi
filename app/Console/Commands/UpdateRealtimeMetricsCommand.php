<?php

namespace App\Console\Commands;

use App\Services\RealtimeMetricsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateRealtimeMetricsCommand extends Command
{
    protected $signature = 'metrics:update-realtime {--force : Force update even if recently updated}';
    
    protected $description = 'Update realtime metrics cache for admin dashboard';

    public function __construct(
        private RealtimeMetricsService $metricsService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🔄 Updating realtime metrics...');

        try {
            $startTime = microtime(true);
            
            // Получаем все метрики (они автоматически кэшируются)
            $metrics = $this->metricsService->getAllMetrics();

            // Сохраняем исторические данные для ключевых метрик
            $historicalMetrics = [
                'players_online',
                'active_players_hour',
                'situations_completed_hour',
                'micro_actions_hour',
                'dau',
                'wau',
                'mau',
                'api_response_time',
            ];

            foreach ($historicalMetrics as $metric) {
                if (isset($metrics[$metric])) {
                    $this->metricsService->storeHistoricalMetric($metric, $metrics[$metric]);
                }
            }

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            // Выводим краткую статистику
            $this->newLine();
            $this->info('📊 Metrics Summary:');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Players Online', $metrics['players_online'] ?? 0],
                    ['DAU', $metrics['dau'] ?? 0],
                    ['WAU', $metrics['wau'] ?? 0],
                    ['MAU', $metrics['mau'] ?? 0],
                    ['Situations/hour', $metrics['situations_completed_hour'] ?? 0],
                    ['Actions/hour', $metrics['micro_actions_hour'] ?? 0],
                    ['Stickiness', ($metrics['stickiness'] ?? 0) . '%'],
                    ['Engagement Score', $metrics['engagement_score'] ?? 0],
                ]
            );

            $this->newLine();
            $this->info("✅ Realtime metrics updated successfully in {$executionTime}ms");
            
            Log::info('Realtime metrics updated via command', [
                'players_online' => $metrics['players_online'] ?? 0,
                'dau' => $metrics['dau'] ?? 0,
                'execution_time_ms' => $executionTime,
            ]);

            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to update metrics: ' . $e->getMessage());
            
            if ($this->option('verbose')) {
                $this->error($e->getTraceAsString());
            }
            
            Log::error('Failed to update realtime metrics via command', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return self::FAILURE;
        }
    }
}

<?php

namespace App\Jobs;

use App\Events\MetricsUpdated;
use App\Services\RealtimeMetricsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateRealtimeMetrics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(RealtimeMetricsService $metricsService): void
    {
        $metrics = $metricsService->getAllMetrics();

        foreach (['players_online', 'active_players_hour', 'situations_completed_hour', 'micro_actions_hour'] as $metric) {
            if (isset($metrics[$metric])) {
                $metricsService->storeHistoricalMetric($metric, $metrics[$metric]);
            }
        }

        broadcast(new MetricsUpdated($metrics));
    }
}

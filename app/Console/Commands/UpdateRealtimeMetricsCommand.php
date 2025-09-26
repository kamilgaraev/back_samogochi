<?php

namespace App\Console\Commands;

use App\Jobs\UpdateRealtimeMetrics;
use Illuminate\Console\Command;

class UpdateRealtimeMetricsCommand extends Command
{
    protected $signature = 'metrics:update-realtime {--force : Force update even if recently updated}';
    
    protected $description = 'Update realtime metrics and broadcast to admin dashboard';

    public function handle(): int
    {
        $this->info('Updating realtime metrics...');

        try {
            UpdateRealtimeMetrics::dispatch();
            
            $this->info('✅ Realtime metrics updated successfully');
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to update metrics: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}

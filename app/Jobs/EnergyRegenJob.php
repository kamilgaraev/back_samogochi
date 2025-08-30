<?php

namespace App\Jobs;

use App\Models\PlayerProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EnergyRegenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        Log::info('EnergyRegenJob started');

        $processed = 0;
        $updated = 0;

        PlayerProfile::chunk(1000, function ($players) use (&$processed, &$updated) {
            foreach ($players as $player) {
                $processed++;
                
                $hoursSinceUpdate = $player->updated_at->diffInHours(now());
                
                if ($hoursSinceUpdate >= 1 && $player->energy < 200) {
                    $energyToAdd = min(
                        1 * $hoursSinceUpdate, 
                        200 - $player->energy
                    );
                    
                    if ($energyToAdd > 0) {
                        $player->increment('energy', $energyToAdd);
                        $player->touch();
                        $updated++;
                    }
                }
            }
        });

        Log::info("EnergyRegenJob completed. Processed: {$processed}, Updated: {$updated}");
    }
}

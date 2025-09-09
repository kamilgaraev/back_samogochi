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

        // Получаем конфигурации энергии
        $gameBalance = \App\Models\GameConfig::getGameBalance();
        $maxEnergy = $gameBalance['max_energy'] ?? 200;
        $energyRegenPerHour = $gameBalance['energy_regen_per_hour'] ?? 1;

        PlayerProfile::chunk(1000, function ($players) use (&$processed, &$updated, $maxEnergy, $energyRegenPerHour) {
            foreach ($players as $player) {
                $processed++;
                
                $hoursSinceUpdate = $player->updated_at->diffInHours(now());
                
                if ($hoursSinceUpdate >= 1 && $player->energy < $maxEnergy) {
                    $energyToAdd = min(
                        $energyRegenPerHour * $hoursSinceUpdate, 
                        $maxEnergy - $player->energy
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

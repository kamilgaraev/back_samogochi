<?php

namespace App\Jobs;

use App\Models\PlayerProfile;
use App\Models\ActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyRewardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        Log::info('DailyRewardJob started');

        $processed = 0;
        $rewarded = 0;

        PlayerProfile::where('last_login', '>=', now()->startOfDay())
            ->where(function($query) {
                $query->whereNull('last_daily_reward')
                      ->orWhere('last_daily_reward', '<', now()->startOfDay());
            })
            ->chunk(1000, function ($players) use (&$processed, &$rewarded) {
                foreach ($players as $player) {
                    $processed++;
                    
                    try {
                        DB::beginTransaction();

                        $baseExperience = config('game.daily_login_experience', 10);
                        $bonusExperience = min($player->consecutive_days, 10) * 2;
                        $totalExperience = $baseExperience + $bonusExperience;

                        $oldLevel = $player->level;

                        $player->increment('total_experience', $totalExperience);
                        $player->increment('consecutive_days', 1);
                        $player->last_daily_reward = now();
                        $player->save();

                        $player = $player->fresh();
                        $newLevel = $player->level;

                        ActivityLog::logEvent('player.daily_reward_auto', [
                            'experience_gained' => $baseExperience,
                            'bonus_experience' => $bonusExperience,
                            'consecutive_days' => $player->consecutive_days,
                            'level_up' => $newLevel > $oldLevel
                        ], $player->user_id);

                        if ($newLevel > $oldLevel) {
                            ActivityLog::logEvent('player.level_up', [
                                'old_level' => $oldLevel,
                                'new_level' => $newLevel,
                                'trigger' => 'daily_reward'
                            ], $player->user_id);
                        }

                        DB::commit();
                        $rewarded++;

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Error processing daily reward for player {$player->id}: " . $e->getMessage());
                    }
                }
            });

        Log::info("DailyRewardJob completed. Processed: {$processed}, Rewarded: {$rewarded}");
    }
}

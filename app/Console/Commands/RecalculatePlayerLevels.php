<?php

namespace App\Console\Commands;

use App\Models\PlayerProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculatePlayerLevels extends Command
{
    protected $signature = 'players:recalculate-levels 
                            {--dry-run : Показать что будет изменено без применения изменений}';

    protected $description = 'Пересчитывает уровни игроков на основе их опыта';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 Режим проверки (изменения не будут применены)');
        } else {
            $this->info('🔄 Начинаю пересчет уровней игроков...');
        }

        $players = PlayerProfile::all();
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        $this->output->progressStart($players->count());

        foreach ($players as $player) {
            $calculatedLevel = floor($player->total_experience / 100) + 1;
            $currentLevel = $player->level;

            if ($calculatedLevel !== $currentLevel) {
                $this->line('');
                $this->warn("👤 Игрок #{$player->id} (User #{$player->user_id}): Уровень {$currentLevel} → {$calculatedLevel} (Опыт: {$player->total_experience})");

                if (!$isDryRun) {
                    try {
                        DB::beginTransaction();
                        
                        $player->update(['level' => $calculatedLevel]);
                        
                        if ($calculatedLevel > $currentLevel) {
                            $customizationService = app(\App\Services\CustomizationService::class);
                            $customizationService->unlockItemsForLevel($player->id, $calculatedLevel);
                        }
                        
                        DB::commit();
                        $updated++;
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error("   ❌ Ошибка обновления: {$e->getMessage()}");
                        $errors++;
                    }
                } else {
                    $updated++;
                }
            } else {
                $skipped++;
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->line('');
        
        if ($isDryRun) {
            $this->info('📊 Результаты проверки:');
            $this->table(
                ['Статус', 'Количество'],
                [
                    ['Требуют обновления', $updated],
                    ['Корректные', $skipped],
                    ['Всего проверено', $players->count()],
                ]
            );
            $this->line('');
            $this->info('💡 Запустите команду без флага --dry-run для применения изменений');
        } else {
            $this->info('✅ Пересчет завершен!');
            $this->table(
                ['Статус', 'Количество'],
                [
                    ['Обновлено', $updated],
                    ['Пропущено (корректные)', $skipped],
                    ['Ошибки', $errors],
                    ['Всего обработано', $players->count()],
                ]
            );
        }

        return Command::SUCCESS;
    }
}


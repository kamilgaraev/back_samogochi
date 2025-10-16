<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\EnergyRegenJob;
use App\Jobs\DailyRewardJob;
use App\Jobs\UpdateRealtimeMetrics;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('game:energy-regen', function () {
    $this->info('Запуск восстановления энергии игроков...');
    EnergyRegenJob::dispatch();
    $this->info('Задача восстановления энергии запущена!');
})->purpose('Restore player energy manually');

Artisan::command('game:daily-rewards', function () {
    $this->info('Запуск выдачи ежедневных наград...');
    DailyRewardJob::dispatch();
    $this->info('Задача ежедневных наград запущена!');
})->purpose('Process daily rewards manually');


Schedule::job(new EnergyRegenJob)->hourly();

Schedule::job(new DailyRewardJob)->daily();

Schedule::job(new UpdateRealtimeMetrics)->everyMinute();

Schedule::command('game:send-end-emails')
    ->dailyAt('12:00')
    ->when(function () {
        $targetDate = \Carbon\Carbon::create(2026, 4, 15);
        return now()->isSameDay($targetDate);
    });

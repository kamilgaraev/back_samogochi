<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\GameConfig;
use App\Models\ActivityLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SendGameEndEmailsCommand extends Command
{
    protected $signature = 'game:send-end-emails {--test-email= : Send test email to specific user by email}';

    protected $description = 'Send game end notification emails to all users (runs once)';

    public function handle()
    {
        $testEmail = $this->option('test-email');
        
        if ($testEmail) {
            return $this->sendTestEmail($testEmail);
        }

        $configKey = 'game_end_emails_sent';
        
        $alreadySent = GameConfig::where('key', $configKey)
            ->where('value', 'true')
            ->exists();
        
        if ($alreadySent) {
            $this->warn('⚠️  Письма об окончании игры уже были отправлены ранее.');
            Log::info('Game end emails already sent, skipping.');
            return 0;
        }

        $this->info('📧 Начинаем отправку писем об окончании игры...');

        $users = User::whereNotNull('email_verified_at')
            ->with('playerProfile')
            ->get();

        if ($users->isEmpty()) {
            $this->warn('⚠️  Нет пользователей для отправки писем.');
            return 0;
        }

        $successCount = 0;
        $failCount = 0;

        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            try {
                $stats = $this->getUserStats($user);

                Mail::send('emails.game-end', [
                    'userName' => $user->name,
                    'stats' => $stats
                ], function ($message) use ($user) {
                    $message->from('noreply@stressapi.ru', 'Самогочи')
                            ->to($user->email, $user->name)
                            ->subject('Спасибо за игру в Самогочи');
                });

                ActivityLog::logEvent('game.end_email_sent', [
                    'email' => $user->email
                ], $user->id);

                $successCount++;
                
            } catch (\Exception $e) {
                Log::error('Failed to send game end email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
                $failCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        GameConfig::updateOrCreate(
            ['key' => $configKey],
            ['value' => 'true', 'description' => 'Флаг отправки писем об окончании игры']
        );

        $this->info("✅ Отправка завершена!");
        $this->info("   Успешно: {$successCount}");
        if ($failCount > 0) {
            $this->error("   Ошибок: {$failCount}");
        }

        Log::info('Game end emails sent', [
            'success' => $successCount,
            'failed' => $failCount
        ]);

        return 0;
    }

    private function getUserStats($user)
    {
        $profile = $user->playerProfile;
        
        if (!$profile) {
            return [
                'days_played' => 0,
                'total_actions' => 0
            ];
        }

        $createdAt = $user->created_at;
        $daysPlayed = $createdAt ? (int) $createdAt->diffInDays(now()) : 0;

        $totalActions = DB::table('activity_logs')
            ->where('user_id', $user->id)
            ->whereIn('event_type', ['micro_action.completed', 'situation.selected'])
            ->count();

        return [
            'days_played' => (int) min($daysPlayed, 180),
            'total_actions' => (int) $totalActions
        ];
    }

    private function sendTestEmail(string $email)
    {
        $this->info("🧪 Отправка тестового письма на {$email}...");

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ Пользователь с email '{$email}' не найден.");
            return 1;
        }

        try {
            $stats = $this->getUserStats($user);

            Mail::send('emails.game-end', [
                'userName' => $user->name,
                'stats' => $stats
            ], function ($message) use ($user) {
                $message->from('noreply@stressapi.ru', 'Самогочи')
                        ->to($user->email, $user->name)
                        ->subject('Спасибо за игру в Самогочи');
            });

            $this->info("✅ Тестовое письмо успешно отправлено!");
            $this->info("   Получатель: {$user->name} ({$user->email})");
            $this->info("   Дней в игре: {$stats['days_played']}");
            $this->info("   Действий выполнено: {$stats['total_actions']}");

            Log::info('Test game end email sent', [
                'email' => $email,
                'user_id' => $user->id
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Ошибка при отправке: {$e->getMessage()}");
            Log::error('Failed to send test game end email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }
}


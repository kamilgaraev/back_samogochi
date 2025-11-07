<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\PlayerProfile;
use App\Models\ActivityLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendSleepEndEmailsCommand extends Command
{
    protected $signature = 'game:send-sleep-end-emails {--test-email= : Send test email to specific user by email}';

    protected $description = 'Send wake-up notification emails to users whose sleep has ended';

    public function handle()
    {
        $testEmail = $this->option('test-email');
        
        if ($testEmail) {
            return $this->sendTestEmail($testEmail);
        }

        $this->info('😴 Проверяем пользователей, которые проснулись...');

        // Находим профили, у которых сон закончился (sleeping_until прошло)
        $profiles = PlayerProfile::whereNotNull('sleeping_until')
            ->where('sleeping_until', '<=', now())
            ->with(['user' => function ($query) {
                $query->whereNotNull('email_verified_at');
            }])
            ->get()
            ->filter(function ($profile) {
                return $profile->user !== null;
            });

        if ($profiles->isEmpty()) {
            $this->info('✅ Нет пользователей, которым нужно отправить письмо о пробуждении.');
            return 0;
        }

        $this->info("📧 Найдено пользователей для отправки: {$profiles->count()}");

        $successCount = 0;
        $failCount = 0;

        $progressBar = $this->output->createProgressBar($profiles->count());
        $progressBar->start();

        foreach ($profiles as $profile) {
            try {
                $user = $profile->user;
                
                Mail::send('emails.sleep-end', [
                    'userName' => $user->name,
                    'gameUrl' => config('app.url', 'https://game.stresshelp.ru')
                ], function ($message) use ($user) {
                    $message->from('noreply@stressapi.ru', 'Самогочи')
                            ->to($user->email, $user->name)
                            ->subject('Время возвращаться в игру! ☀️');
                });

                // Будим персонажа (обнуляем sleeping_until)
                $profile->wakeUp();

                ActivityLog::logEvent('game.sleep_end_email_sent', [
                    'email' => $user->email
                ], $user->id);

                $successCount++;
                
            } catch (\Exception $e) {
                Log::error('Failed to send sleep end email', [
                    'user_id' => $profile->user_id,
                    'email' => $profile->user->email ?? 'N/A',
                    'error' => $e->getMessage()
                ]);
                $failCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ Отправка завершена!");
        $this->info("   Успешно: {$successCount}");
        if ($failCount > 0) {
            $this->error("   Ошибок: {$failCount}");
        }

        Log::info('Sleep end emails sent', [
            'success' => $successCount,
            'failed' => $failCount
        ]);

        return 0;
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
            Mail::send('emails.sleep-end', [
                'userName' => $user->name,
                'gameUrl' => config('app.url', 'https://game.stresshelp.ru')
            ], function ($message) use ($user) {
                $message->from('noreply@stressapi.ru', 'Самогочи')
                        ->to($user->email, $user->name)
                        ->subject('Время возвращаться в игру! ☀️');
            });

            $this->info("✅ Тестовое письмо успешно отправлено!");
            $this->info("   Получатель: {$user->name} ({$user->email})");

            Log::info('Test sleep end email sent', [
                'email' => $email,
                'user_id' => $user->id
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Ошибка при отправке: {$e->getMessage()}");
            Log::error('Failed to send test sleep end email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }
}


<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    protected $token;
    protected $email;

    public function __construct(string $token, string $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $resetUrl = config('app.frontend_url') . '/reset-password?token=' . $this->token . '&email=' . urlencode($this->email);

        return (new MailMessage)
            ->subject('Восстановление пароля')
            ->greeting('Здравствуйте, ' . $notifiable->name . '!')
            ->line('Мы получили запрос на восстановление пароля для вашей учетной записи.')
            ->line('Для сброса пароля нажмите на кнопку ниже:')
            ->action('Сбросить пароль', $resetUrl)
            ->line('Эта ссылка действительна в течение 60 минут.')
            ->line('Если вы не запрашивали восстановление пароля, просто проигнорируйте это письмо.')
            ->salutation('С уважением, команда ' . config('app.name'));
    }
}


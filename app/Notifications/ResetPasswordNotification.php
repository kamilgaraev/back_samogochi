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
        return (new MailMessage)
            ->subject('Восстановление пароля')
            ->greeting('Здравствуйте, ' . $notifiable->name . '!')
            ->line('Мы получили запрос на восстановление пароля для вашей учетной записи.')
            ->line('Используйте следующий код для сброса пароля:')
            ->line('**Код: ' . $this->token . '**')
            ->line('Этот код действителен в течение 60 минут.')
            ->line('Если вы не запрашивали восстановление пароля, просто проигнорируйте это письмо.')
            ->salutation('С уважением, команда ' . config('app.name'));
    }
}


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
            ->subject('Новый пароль')
            ->greeting('Здравствуйте, ' . $notifiable->name . '!')
            ->line('Вы запросили восстановление пароля.')
            ->line('Ваш новый пароль: **' . $this->token . '**')
            ->line('Рекомендуем сменить его после входа в систему.')
            ->line('Если вы не запрашивали восстановление пароля, срочно свяжитесь с поддержкой.')
            ->salutation('С уважением, команда ' . config('app.name'));
    }
}


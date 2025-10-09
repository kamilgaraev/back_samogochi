<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification
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
        $verificationUrl = config('app.url') . '/api/auth/verify-email/' . $this->token . '?email=' . urlencode($this->email);

        return (new MailMessage)
            ->subject('Подтверждение email адреса')
            ->greeting('Здравствуйте, ' . $notifiable->name . '!')
            ->line('Благодарим вас за регистрацию в ' . config('app.name') . '!')
            ->line('Для подтверждения вашего email адреса нажмите на кнопку ниже:')
            ->action('Подтвердить Email', $verificationUrl)
            ->line('Эта ссылка действительна в течение 24 часов.')
            ->line('Если вы не регистрировались, просто проигнорируйте это письмо.')
            ->salutation('С уважением, команда ' . config('app.name'));
    }
}


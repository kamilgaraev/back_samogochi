<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    protected $newPassword;
    protected $email;

    public function __construct(string $newPassword, string $email)
    {
        $this->newPassword = $newPassword;
        $this->email = $email;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return new ResetPasswordMailable($this->newPassword, $notifiable->name);
    }
}

class ResetPasswordMailable extends Mailable
{
    use Queueable;

    public $newPassword;
    public $userName;

    public function __construct($newPassword, $userName)
    {
        $this->newPassword = $newPassword;
        $this->userName = $userName;
    }

    public function build()
    {
        return $this->subject('Новый пароль')
                    ->view('emails.reset-password');
    }
}


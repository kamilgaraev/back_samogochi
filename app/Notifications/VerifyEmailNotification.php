<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
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

    public function toMail($notifiable)
    {
        $verificationUrl = config('app.url') . '/api/auth/verify-email/' . $this->token . '?email=' . urlencode($this->email);

        return new VerifyEmailMailable($verificationUrl, $notifiable->name);
    }
}

class VerifyEmailMailable extends Mailable
{
    use Queueable;

    public $verificationUrl;
    public $userName;

    public function __construct($verificationUrl, $userName)
    {
        $this->verificationUrl = $verificationUrl;
        $this->userName = $userName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Подтверждение email адреса',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-email',
        );
    }
}


<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public $token;

    /**
     * Create a notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // $domain = 'https://quanlytruonghoc.id.vn/reset-password';
        $domain = 'http://localhost:5173/reset-password';
        $url = url($domain . '?token=' . $this->token . '&email=' . $notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('Lấy lại mật khẩu')
            ->view('emails.reset_password', ['url' => $url]);
    }
}

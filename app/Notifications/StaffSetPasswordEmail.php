<?php

namespace App\Notifications;

use App\Models\NotificationSetting;
use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffSetPasswordEmail extends BaseNotification
{

    public $restaurant;
    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(Restaurant $restaurant, $token)
    {
        $this->restaurant = $restaurant;
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $build = parent::build($notifiable);

        return $build
            ->subject(__('email.staffSetPassword.subject'))
            ->greeting(__('app.hello') . ' ' . $notifiable->name . ',')
            ->line(__('email.staffSetPassword.text1'))
            ->action(__('email.staffSetPassword.action'), url(route('password.reset', ['token' => $this->token, 'email' => $notifiable->email], false)))
            ->line(__('app.thankYou'));
    }

}

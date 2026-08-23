<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class DummyBackupNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [];
    }
}

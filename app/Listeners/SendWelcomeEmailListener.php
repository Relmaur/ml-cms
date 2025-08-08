<?php

namespace App\Listeners;

use App\Events\UserRegistered;

class SendWelcomeEmailListener
{

    public function handle(UserRegistered $event)
    {

        // For now, we'll just log that the "email" was sent.
        // Later on, we'll implement this with a real mailer
        $logMessage = sprintf(
            "[%s] Welcome email sent to: %s (%s)\n",
            date('Y-m-d H:i:s'),
            $event->user->name,
            $event->user->email
        );
        // Temp: Log to a file in our storage directory
        file_put_contents(__DIR__ . '/../../storage/logs/mail.log', $logMessage, FILE_APPEND);
    }
}

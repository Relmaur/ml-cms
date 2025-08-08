<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Core\Mailer;

class SendWelcomeEmailListener
{
    private $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function handle(UserRegistered $event)
    {

        $to = $event->user->email;
        $subject = 'Welcome to Our Awesome CMS!';
        $htmlBody = "<h1>Hello {$event->user->name}!</h1><p>Thank you for registering. We're excited to have you</p>";

        $this->mailer->send($to, $subject, $htmlBody);

        // For now, we'll just log that the "email" was sent.
        // Later on, we'll implement this with a real mailer
        // $logMessage = sprintf(
        //     "[%s] Welcome email sent to: %s (%s)\n",
        //     date('Y-m-d H:i:s'),
        //     $event->user->name,
        //     $event->user->email
        // );
        // // Temp: Log to a file in our storage directory
        // file_put_contents(__DIR__ . '/../../storage/logs/mail.log', $logMessage, FILE_APPEND);
    }
}

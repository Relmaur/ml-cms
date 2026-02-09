<?php

namespace Core;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mime\Email;

class Mailer
{

    private $mailer;

    public function __construct()
    {
        $dsn = $_ENV['MAILER_DSN'] ?? '';
        $transport = Transport::fromDsn($dsn);
        $this->mailer = new SymfonyMailer($transport);
    }

    public function send(string $to, string $subject, string $htmlBody)
    {
        $email = (new Email())
            ->from('no-reply@riven.com')
            ->to($to)
            ->subject($subject)
            ->html($htmlBody);

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // On production one woudl log this error more gracefully
            error_log('Mail Error: ' . $e->getMessage());
        }
    }
}

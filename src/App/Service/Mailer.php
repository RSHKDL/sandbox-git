<?php

namespace App\App\Service;

use App\App\Service\Interfaces\MailerInterface;

/**
 * Class Mailer
 * @package App\Service
 */
final class Mailer implements MailerInterface
{

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * Mailer constructor.
     * @param \Swift_Mailer $mailer
     */
    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param string $subject
     * @param string|array $from
     * @param string $to
     * @param string $body
     */
    public function sendMail($subject, $from = [], $to, $body)
    {
        $message = (new \Swift_Message($subject))
            ->setFrom($from)
            ->setTo($to)
            ->setBody($body,'text/html');

        $this->mailer->send($message);
    }
}

<?php

namespace App\Mail;

use Symfony\Component\Mailer\Bridge\Amazon\Smtp\SesTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

class MailService
{
    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var string
     */
    protected $sender;

    /**
     * @var string
     */
    protected $frontendUrl;

    /**
     * @param string $username
     * @param string $password
     * @param string $region
     * @param string $sender
     */
    public function __construct(string $username, string $password, string $region, string $sender, string $frontendUrl)
    {
        $smtp = new SesTransport($username, $password, $region);
        $this->mailer = new Mailer($smtp);

        $this->sender = $sender;
        $this->frontendUrl = $frontendUrl;
    }

    /**
     * @param string $to
     * @param string $name
     * @param string $token
     */
    public function sendRecoveryPassword(string $to, string $name, string $token)
    {
        $resetUrl = $this->frontendUrl . "/reset-password?email={$to}&token=$token";

        $email = (new Email())
            ->from($this->sender)
            ->to($to)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Password reset request')
            ->text('Password reset link: '.$resetUrl)
            ->html("Hi {$name}, <br>
we received a password reset request for your account. <br>We're hoping it was you who requested it - if so click on the link below to get started.<br>
<a href='{$resetUrl}'>Reset your password now</a><br> 
If you didn't request a password reset and have no idea why you're getting this email then please ignore it. Your account is perfectly safe.
<br><br>Kind regards,<br>AK fintech team
");

        $this->mailer->send($email);
    }

}
<?php

namespace App\Service;


use App\Entity\User;
use App\Entity\UserChangePasswordRequest;
use App\Mail\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class PasswordRecoveryService
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var string
     */
    protected $frontendUrl;

    /**
     * PasswordRecoveryService constructor.
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $em
     * @param MailService $mailService
     */
    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, MailService $mailService)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->mailService = $mailService;
    }

    /**
     * @param User $user
     */
    public function recoverPassword(User $user)
    {
        $token = md5(uniqid());

        $changeRequest = (new UserChangePasswordRequest())
            ->setEmail($user->getEmail())
            ->setUser($user)
            ->setToken($token)
        ;

        $this->em->persist($changeRequest);
        $this->em->flush();

        $this->mailService->sendRecoveryPassword($user->getEmail(), $user->getFirstName() ?? '', $token);

        $this->logger->info("Password change requested for user {$user->getEmail()}. Email sent with token $token");
    }

}
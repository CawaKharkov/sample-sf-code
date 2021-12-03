<?php

namespace App\Verification\UAS;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UASTokenStorageDatabase implements UASTokenStorageInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function setSessId(User $user, string $sessId)
    {
        $user->setUasSessId($sessId);

        $this->em->flush();
    }

    public function getSessId(User $user): string
    {
        return $user->getUasSessId();
    }

    public function setToken(User $user, string $token, \DateTimeInterface $expirationAt)
    {
        $user->setUasToken($token);
        $user->setUasTokenExpirationAt($expirationAt);

        $this->em->flush();
    }

    public function getToken(User $user): string
    {
        $token = $user->getUasToken();

        if (!$token) {
            throw new BadRequestHttpException("User {$user->getEmail()} is not authorized on UAS. Sms-verification needed.");
        }

        return $token;
    }

    public function hasToken(User $user): bool
    {
        $token = $user->getUasToken();

        return !empty($token);
    }
}
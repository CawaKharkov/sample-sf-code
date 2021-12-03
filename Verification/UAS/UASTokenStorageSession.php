<?php

namespace App\Verification\UAS;


use App\Entity\User;
use App\Verification\VerificationException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UASTokenStorageSession implements UASTokenStorageInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        $this->session->start();
    }

    public function setSessId(User $user, string $sessId)
    {
        if (empty($sessId)) {
            throw new VerificationException("SessId can not be empty");
        }

        $this->session->set('sessId', $sessId);
    }

    public function getSessId(User $user): string
    {
        if (!$this->session->has('sessId')) {
            throw new VerificationException("No sessId for phone {$user->getPhone()}");
        }

        return $this->session->get('sessId');
    }

    public function setToken(User $user, string $token, \DateTimeInterface $expirationAt)
    {
        $this->session->set('uasToken', $token);
    }

    public function getToken(User $user): string
    {
        return $this->session->get('uasToken');
    }

    public function hasToken(User $user): bool
    {
        // TODO: Implement hasToken() method.
    }
}
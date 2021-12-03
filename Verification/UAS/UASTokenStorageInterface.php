<?php

namespace App\Verification\UAS;


use App\Entity\User;

interface UASTokenStorageInterface
{
    public function setSessId(User $user, string $sessId);

    public function getSessId(User $user): string;

    public function setToken(User $user, string $token, \DateTimeInterface $expirationAt);

    public function getToken(User $user): string;

    public function hasToken(User $user): bool;
}
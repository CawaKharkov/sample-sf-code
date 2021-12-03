<?php

namespace App\Verification;

use App\Entity\SmsCode;
use App\Repository\SmsCodeRepository;
use Doctrine\ORM\EntityManagerInterface;

class SmsServiceStub implements SmsServiceInterface
{
    /**
     * @see SmsServiceInterface::sendCode()
     */
    public function sendCode(SmsCode $smsCode): void
    {
        $smsCode->setCode(1010);
    }

    /**
     * @see SmsServiceInterface::checkCode()
     */
    public function checkCode(SmsCode $smsCode): bool
    {
        if (1010 == $smsCode->getCode()) {
            return true;
        }

        return false;
    }
}
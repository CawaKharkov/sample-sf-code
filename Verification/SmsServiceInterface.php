<?php

namespace App\Verification;


use App\Entity\SmsCode;

interface SmsServiceInterface
{
    /**
     * @param SmsCode $code
     */
    public function sendCode(SmsCode $code): void;

    /**
     * @param SmsCode $code
     * @return bool
     */
    public function checkCode(SmsCode $code): bool;
}
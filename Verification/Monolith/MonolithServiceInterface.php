<?php

namespace App\Verification\Monolith;


use App\Entity\CryptoWithdrawal;
use App\Entity\FiatWithdrawal;
use App\Entity\User;
use App\Entity\Withdrawal;
use GuzzleHttp\Psr7\Stream;

interface MonolithServiceInterface
{
    /**
     * Creates new user
     */
    public function createUser(User $user): void;

    /**
     * Checks user status
     */
    public function getUserStatus(User $user);

    /**
     * Loads and processes new deposit transactions
     */
    public function loadDepositTransactions(User $user): void;

    /**
     * Gets Monolith config
     */
    public function getConfig(User $user): Stream;

    /**
     * Gets User's organizations
     */
    public function getOrganizations(User $user): Stream;

    /**
     * @param User $user
     * @return string
     */
    public function getAccountId(User $user): string;

    /**
     * @param Withdrawal $withdrawal
     * @return string Confirmation token
     */
    public function paymentInitialization(Withdrawal $withdrawal): string;

    /**
     * @param Withdrawal $withdrawal
     * @return mixed
     */
    public function paymentConfirmation(Withdrawal $withdrawal, string $confirmationToken, string $code): string;

    /**
     * @param CryptoWithdrawal $cryptoWithdrawal
     * @return bool
     */
    public function paymentCrypto(CryptoWithdrawal $cryptoWithdrawal) : bool;

}
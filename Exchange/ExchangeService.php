<?php

namespace App\Exchange;

use App\Entity\Currency;
use App\Entity\ExchangeDirection;
use App\Entity\ExchangeOrder;
use App\Entity\ExchangeRate;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\UserAccount;
use App\Repository\CurrencyRepository;
use App\Repository\ExchangeDirectionRepository;
use App\Repository\ExchangeRateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class ExchangeService
{
    const EXCHANGER_EMAIL = 'exchanger@test.com';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ExchangeRateRepository $exchangeRateRepository;
     */
    private $exchangeRateRepository;

    /**
     * @var ExchangeDirectionRepository $exchangeDirectionRepository;
     */
    private $exchangeDirectionRepository;

    /**
     * @var CurrencyRepository $currencyRepository
     */
    private $currencyRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;


    /**
     * @param LoggerInterface $logger
     * @param ExchangeRateRepository $exchangeRateRepository
     * @param ExchangeDirectionRepository $exchangeDirectionRepository
     * @param CurrencyRepository $currencyRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(LoggerInterface $logger, ExchangeRateRepository $exchangeRateRepository,
                                ExchangeDirectionRepository $exchangeDirectionRepository,
                                CurrencyRepository $currencyRepository, EntityManagerInterface $em)
    {
        $this->logger = $logger;
        $this->exchangeRateRepository  = $exchangeRateRepository;
        $this->exchangeDirectionRepository = $exchangeDirectionRepository;
        $this->currencyRepository = $currencyRepository;
        $this->em = $em;
    }

    /**
     * @param ExchangeOrder $exchangeOrder
     * @throws ExchangeException
     */
    public function exchange(ExchangeOrder $exchangeOrder)
    {
        $exchanger = $this->em->getRepository(User::class)
            ->findOneByEmail(self::EXCHANGER_EMAIL);

        if (is_null($exchanger)) {
            throw new ExchangeException("Exchanger user " . self::EXCHANGER_EMAIL . " does not exist");
        }

        $latestRate = $this->exchangeRateRepository->findLatest(
            $exchangeOrder->getDirection()->getCurrencyBase(),
            $exchangeOrder->getDirection()->getCurrencyQuote()
        );

        if (is_null($latestRate)) {
            throw new ExchangeException("No exchange rate for direction " . $exchangeOrder->getDirection()->getCode());
        }

        $exchangeOrderRate = $exchangeOrder->getPrice() / $exchangeOrder->getAmount();

        $latestRateValue = $exchangeOrder->getSide() == ExchangeOrder::SIDE_SELLER
            ? $latestRate->getPriceBid()
            : $latestRate->getPriceAsk();

        $sides = [ExchangeOrder::SIDE_SELLER => 'SELLER', ExchangeOrder::SIDE_BUYER => 'BUYER'];

        $this->logger->info("Current exhcange rate for direction {$exchangeOrder->getDirection()->getCode()}, side {$sides[$exchangeOrder->getSide()]} is: {$latestRateValue}");

        if (abs($latestRateValue - $exchangeOrderRate) > $latestRateValue * 0.01) {
            throw new ExchangeException("Current exchange rate $latestRateValue does not match incoming order rate $exchangeOrderRate ±1%");
        }

        $this->logger->info("Current exchange rate $latestRateValue matchs incoming order rate $exchangeOrderRate ±1%");

        // finance: create exchange transactions for both users
        {
            $currentUser = $exchangeOrder->getUser();

            $userSide = $exchangeOrder->getType() == ExchangeOrder::TYPE_BUY
                ? ExchangeOrder::SIDE_BUYER
                : ExchangeOrder::SIDE_SELLER;

            $exchangerSide = $exchangeOrder->getType() == ExchangeOrder::TYPE_BUY
                ? ExchangeOrder::SIDE_SELLER
                : ExchangeOrder::SIDE_BUYER;

            /** @var Transaction $transaction */
            foreach ($this->createTransactions($exchangeOrder, $currentUser, $userSide) as $transaction) {
                $this->em->persist($transaction);
            }

            /** @var Transaction $transaction */
            foreach ($this->createTransactions($exchangeOrder, $exchanger, $exchangerSide) as $transaction) {
                $this->em->persist($transaction);
            }

            $this->em->flush();
        }
    }

    /**
     * @param ExchangeOrder $exchangeOrder
     * @param User $user
     * @param int $side
     * @return array
     * @throws ExchangeException
     */
    private function createTransactions(ExchangeOrder $exchangeOrder, User $user, int $side): array
    {
        $transactions = [];

        // transaction 1: buy (increase)
        {
            $currencySide = $side == ExchangeOrder::SIDE_SELLER
                ? ExchangeOrder::SIDE_SELLER
                : ExchangeOrder::SIDE_BUYER;

            $currencyIncrease = $exchangeOrder->getCurrencyIncreaseForSide($currencySide);

            $this->logger->info("Increasing currency for user {$user->getEmail()}: {$currencyIncrease->getCode()}");

            /** @var UserAccount $userAccount */
            $userAccount = $this->getUserPrimaryAccount(
                $currencyIncrease,
                $user
            );

            $amount = $side == ExchangeOrder::SIDE_BUYER
                ? $exchangeOrder->getAmount()
                : $exchangeOrder->getPrice()
            ;

            $transactions[] = (new Transaction())
                ->setUserAccount($userAccount)
                ->setType(Transaction::TYPE_EXCHANGE_BUY)
                ->setAmount($amount)
                ->setExchangeOrder($exchangeOrder)
                ->setDescription("Exchange order: buy")
            ;

            $this->logger->info("Increasing {$userAccount->getCurrency()->getCode()}: +{$amount} (side={$exchangeOrder->getSide()})");
        }

        // transaction 2: sell (decrease)
        {
            $currencySide = $side == ExchangeOrder::SIDE_SELLER
                ? ExchangeOrder::SIDE_BUYER
                : ExchangeOrder::SIDE_SELLER;

            $currencyDecrease = $exchangeOrder->getCurrencyDecreaseForSide($currencySide);

            $this->logger->info("Decreasing currency for user {$user->getEmail()}: {$currencyDecrease->getCode()}");


            /** @var UserAccount $userAccount */
            $userAccount = $this->getUserPrimaryAccount(
                $currencyDecrease,
                $user
            );

            $amount = $side == ExchangeOrder::SIDE_BUYER
                ? $exchangeOrder->getPrice()
                : $exchangeOrder->getAmount();

            $transactions[] = (new Transaction())
                ->setUserAccount($userAccount)
                ->setType(Transaction::TYPE_EXCHANGE_SELL)
                ->setAmount($amount)
                ->setExchangeOrder($exchangeOrder)
                ->setDescription("Exchange order: pay")
            ;

            $this->logger->info("Decreasing {$userAccount->getCurrency()->getCode()}: -{$amount} (side={$exchangeOrder->getSide()})");
        }

        return $transactions;
    }

    public function eraseToday()
    {
        $this->exchangeRateRepository->eraseToday();
    }

    /**
     * @param array $prices
     * @return ExchangeRate[]|array
     * @throws ExchangeException
     */
    public function parsePrices(array $prices): array
    {
        /** @var ExchangeRate[] $rates */
        $rates = [];

        foreach ($prices as $price) {
            /** @var ExchangeDirection|null $direction */
            $direction = $this->exchangeDirectionRepository->findOneByCode($price->pair);

            if (is_null($direction)) {
                throw new ExchangeException("Exchange direction {$price->pair} does not exist");
            }

            $rate = (new ExchangeRate())
                ->setCurrencyFrom($direction->getCurrencyBase())
                ->setCurrencyTo($direction->getCurrencyQuote())
                ->setPriceAsk($price->ask)
                ->setPriceBid($price->bid)
            ;

            $rates[] = $rate;
        }

        return $rates;
    }

    /**
     * @param Currency $currency
     * @param User $user
     * @return UserAccount
     */
    private function getUserPrimaryAccount(Currency $currency, User $user) : UserAccount
    {
        $userAccount = $user->getPrimaryAccountByCurrency($currency);

        $this->logger->info("Detected user {$user->getEmail()} primary account for {$currency->getCode()}: {$userAccount->getId()} ({$userAccount->getCurrency()->getCode()})");

        if (! $userAccount instanceof UserAccount) {
            throw new \LogicException("User {$user->getEmail()} has no primary account in {$currency->getCode()}");
        }

        return $userAccount;
    }




}
<?php

namespace App\Controller;

use App\Entity\CryptoWithdrawal;
use App\Entity\Currency;
use App\Entity\Direction;
use App\Entity\ExchangeBonus;
use App\Entity\ExchangeDirection;
use App\Entity\ExchangeRate;
use App\Entity\Rate;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\UserAccount;
use App\Entity\UserDepositMethod;
use App\Finance\RateService;
use App\Integration\BilderlingsService;
use App\Integration\KrakenAPI;
use App\Repository\CryptoDepositRepository;
use App\Repository\CryptoWithdrawalRepository;
use App\Repository\CurrencyRepository;
use App\Repository\DirectionRepository;
use App\Repository\ExchangeDirectionRepository;
use App\Repository\ExchangeRateRepository;
use App\Repository\OrderRepository;
use App\Repository\RateRepository;
use App\Repository\UserAccountRepository;
use App\Repository\UserDepositMethodRepository;
use App\Repository\UserRepository;
use Binance;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use OldSound\RabbitMqBundle\RabbitMq\Consumer;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Log\LoggerInterface;
use Ronte\UASAuthenticationBundle\UAS\UASService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\RestBundle\Controller\Annotations as Rest;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(LoggerInterface $logger)
    {
        return $this->json([
            'message' => 'Welcome!',
            'path' => 'src/Controller/DefaultController.php',
        ]);
    }

    /**
     * @Route("/add_test_money", name="add_test_money")
     */
    public function addTestMoney(UserAccountRepository $repository, EntityManagerInterface $em, KernelInterface $kernel)
    {
        if ('prod' == $kernel->getEnvironment()) {
            throw new AccessDeniedException("This can not be run on production environment!");
        }

        $limits = [
            'USD' => 10000,
            'EUR' => 10000,
        ];

        foreach ($repository->findByType(UserAccount::TYPE_PRIMARY) as $userAccount) {
            if ($userAccount->getBalance() == 0) {
                $code = $userAccount->getCurrency()->getCode();
                if (array_key_exists($code, $limits)) {
                    $userAccount->setBalance($limits[$code]);
                }
            }
        }

        $em->flush();

        return $this->json([
            'message' => "Test money added",
        ]);
    }

    /**
     * @Route("/erase_hold_balance", name="erase_hold_balance")
     */
    public function eraseHoldBalance(UserAccountRepository $repository, EntityManagerInterface $em, KernelInterface $kernel)
    {
        if ('prod' == $kernel->getEnvironment()) {
            throw new AccessDeniedException("This can not be run on production environment!");
        }

        $i = 0;

        /** @var UserAccount $holdAccount */
        foreach ($repository->findByType(UserAccount::TYPE_HOLD) as $holdAccount) {
            if ($holdAccount->getBalance() > 0) {
                $holdAccount->setBalance(0);
                $i++;
            }
        }

        $em->flush();

        return $this->json([
            'message' => "$i test hold balances erased",
        ]);
    }

    /**
     * @Route("/create_hold_accounts", name="create_hold_accounts")
     */
    public function createHoldAccounts(CurrencyRepository $currencyRepository,
                                       UserRepository $userRepository, EntityManagerInterface $em, UserAccountRepository $accountRepository)
    {
        $i = 0;
        foreach ($userRepository->findAll() as $user) {
                foreach ($currencyRepository->findAll() as $currency) {
                    $existed = $accountRepository->findOneBy([
                        'type' => UserAccount::TYPE_HOLD,
                        'user' => $user,
                        'currency' => $currency
                    ]);

                    if (!$existed) {
                        $holdAccount = (new UserAccount())
                            ->setUser($user)
                            ->setCurrency($currency)
                            ->setType(UserAccount::TYPE_HOLD)
                            ->setBalance(0);

                        $em->persist($holdAccount);

                        $i++;
                    }
                }
        }

        $em->flush();

        return $this->json([
            'message' => "$i hold accounts created",
        ]);
    }

    /**
     * @Route("/create_deposit_methods", name="create_deposit_methods")
     */
    public function createDepositMethods(CurrencyRepository $currencyRepository, UserRepository $userRepository,
                                         EntityManagerInterface $em, UserDepositMethodRepository $depositMethodRepository)
    {
        $i = 0;
        foreach ($userRepository->findAll() as $user) {
            foreach ($currencyRepository->findByType(Currency::TYPE_FIAT) as $currency) {
                $existed = $depositMethodRepository->findOneBy([
                    'user' => $user,
                    'currency' => $currency
                ]);

                if (!$existed) {
                    $method = (new UserDepositMethod())
                        ->setUser($user)
                        ->setCurrency($currency)
                        ->setReference(uniqid(null, true));

                    $em->persist($method);

                    $i++;
                }
            }
        }

        $em->flush();

        return $this->json([
            'message' => "$i deposit methods created",
        ]);
    }

    /**
     * @Route("/update_rates", name="update_rates", methods={"GET"})
     */
    public function updateRates(RateService $rateService, EntityManagerInterface $em)
    {
        $i = 0;

        foreach ($rateService->getRatesFromOrderapi() as $rate) {
            $em->persist($rate);
            $i++;

            $invertedRate = (new Rate())
                ->setCurrency($rate->getCurrencyTo())
                ->setCurrencyTo($rate->getCurrencyFrom())
                ->setCurrencyFrom($rate->getCurrencyTo())
                ->setCode("{$rate->getCurrencyTo()->getCode()}{$rate->getCurrencyFrom()->getCode()}")
                ->setValue(bcdiv(1, $rate->getValue(), 8));

            $em->persist($invertedRate);
            $i++;
        }

        $em->flush();

        return $this->json([
            'message' => "$i rates saved"
        ]);
    }

    /**
     * @Route("/check_holds/{amount}", name="check_holds", methods={"GET"})
     *
     * Displays hold/unhold sum for last N order (must be 0 on closing)
     */
    public function checkHolds(int $amount, OrderRepository $orderRepository)
    {
        foreach ($orderRepository->getLast($amount) as $order) {
            $holdLeft = 0;
            $mathHoldLeft = 0.0;
            foreach ($order->getTransactions() as $transaction) {
                switch ($transaction->getType()) {
                    case Transaction::TYPE_HOLD:
                        $holdLeft += $transaction->getAmount();
                        bcadd($mathHoldLeft, $transaction->getAmount(), 8);
                        break;
                    case Transaction::TYPE_UNHOLD:
                        $holdLeft -= $transaction->getAmount();
                        bcsub($mathHoldLeft, $transaction->getAmount(), 8);
                        break;
                }
            }
            echo "{$order->getId()}: ($mathHoldLeft) : $holdLeft <br>";
        }

        return new Response('ok');
    }


    /**
     * @Route("/references/remove_dot", name="references_remove_dots", methods={"GET"})
     *
     * Removes dots from user deposit method references
     */
    public function removeDots(UserDepositMethodRepository $repository, EntityManagerInterface $em)
    {
        /** @var UserDepositMethod $method */
        foreach ($repository->findAll() as $method) {
            $reference = $method->getReference();
            $reference = preg_replace('/\./', '', $reference);
            $method->setReference($reference);
        }

        $em->flush();

        return new Response('ok');
    }

    /**
     * @Route("/qr", name="qr", methods={"GET"})
     */
    public function qr()
    {
        $qrCode = new QrCode('Life is too short to be generating QR codes');

        return new Response($qrCode->writeString(), 200, [
            'Content-Type' => $qrCode->getContentType(),
        ]);
    }

    /**
     * @Route("/binance", name="binance", methods={"GET"})
     */
    public function binance()
    {
        $api = new Binance\API(
            "TBFfgqNuMquOSy19juQyXs9CFDMVi6j5A0GOz4Qh0z4N6QWB7QPAHDUPJ1laen0Y",
            "EuTtI7ye25hm7KpB90iWy14eH80hMS3dZA5gKr7pRbjyFkoqaJSRTb32jtUEre7f"
        );

        /*$asset = "BTC";
        $address = "1C5gqLRs96Xq4V2ZZAR1347yUCpHie7sa";
        $amount = 0.00002;
        $response = $api->withdraw($asset, $address, $amount);
        print_r($response);*/

        $api->useServerTime();

        $ticker = $api->prices(); // Make sure you have an updated ticker object for this to work
        $balances = $api->balances($ticker);

        echo '<pre>' ;print_r($balances);

        /*$depositAddress = $api->depositAddress("VEN");
        print_r($depositAddress);*/


        return new Response('ok');
    }

    /**
     * @Route("/kraken", name="kraken", methods={"GET"})
     */
    public function kraken()
    {
        $kraken = new KrakenAPI(
            'DUxL1fJ4bakIoUeN+AbCbDDxGqj1EoiN3G5ssLIbUv7c8z7p/LydKjvd',
            'VHsytDu8GEV+Xsjj+6jdrrHDYGoBVc0wnttH9hjGpx2DYKd44BCzBFj8gg+T90H7RynDys4R9Cr2WKu0CW5mfQ=='
        );

        $res = $kraken->QueryPrivate('Balance');
        echo '<pre>'; print_r($res);

        die;


    }


    /**
     * @Route("/crypto/transactions", name="crypto_transactions", methods={"GET"})
     */
    public function cryptoTransasctionsWithdrawal(CryptoWithdrawalRepository $withdrawalRepo, CryptoDepositRepository $depositRepo)
    {
        $withdrawals = $withdrawalRepo->findAll();

        $deposits = $depositRepo->findAll();

        $transacions = new ArrayCollection(
            array_merge($withdrawals, $deposits)
        );

        $iterator = $transacions->getIterator();

        $iterator->uasort(function ($a, $b) {
            if ($a->getCreatedAt() == $b->getCreatedAt()) {
                return 0;
            }
            return ($a->getCreatedAt() > $b->getCreatedAt()) ? -1 : 1;
        });

        $sorted = new ArrayCollection(iterator_to_array($iterator));

        $result = '
        <h3>Withdrawals & deposits</h3>
        <table border="1">
            <tr>
            <th>type</th>
            <th>date</th>
            <th>user</th>
            <th>TxId</th>
            <th>TxDbId</th>
            <th>address</th>
            <th>amount</th>
            <th>confirmed</th>
            </tr>';

        foreach ($sorted as $transaction) {
            $result .= sprintf("
            <tr>
               <td>%s</td>
               <td>%s</td>
               <td>%s</td>
               <td style='word-break: break-all'>%s</td>
               <td>%s</td>
               <td>%s</td>
               <td style='word-break: break-all'>%s %s</td>
               <td>%s</td>
            </tr>",
                $transaction instanceof CryptoWithdrawal ? 'Withdrawal' : 'Deposit',
                $transaction->getCreatedAt()->format('D, d M Y H:i:s'),
                $transaction->getUser()->getEmail(),
                $transaction->getTxId(),
                $transaction->getTxDbId(),
                $transaction->getCryptoaddress()->getAddress(),
                $transaction->getCryptoaddress()->getCurrency()->getCode(), $transaction->getAmount(),
                $transaction->getChainConfirmedAt() ? $transaction->getChainConfirmedAt()->format('D, d M Y H:i:s') : ''
            );
        }

        $result .= '</table>';

        return new Response($result);
    }

    /**
     * @Route("/erase_bot_orders/days/{days}", name="erase_bot_orders", methods={"GET"})
     *
     * Erases bots' orders
     */
    public function eraseBotOrders(int $days, UserRepository $userRepository, EntityManagerInterface $em)
    {
        /** @var User $user */
        foreach ($userRepository->findAll() as $user) {
            preg_match('/bot\w+@test\.com/', $user->getEmail(), $matches);
            if ($matches) {
                $user->setIsBot(true);
            }
        }
        $em->flush();

        $bots = $userRepository->findByIsBot(true);

        $userIds = [];
        /** @var User $bot */
        foreach ($bots as $bot) {
            $userIds[] = "'{$bot->getId()}'";
        }

        $query = 'DELETE FROM `order` 
                      WHERE `user_id` IN ('.implode(',', $userIds).')
                      AND created_at < DATE_SUB(NOW(), INTERVAL :days DAY)';

        $statement = $em->getConnection()->prepare($query);
        $statement->execute(['days' => 30]);

        return new Response('Query executed: '. $query);
    }

    /**
     * @Route("/create_exchange_directions", name="create_exchange_directions", methods={"GET"})
     */
    public function createExchangeDirections(DirectionRepository $directionRepository, ExchangeDirectionRepository $exchangeDirectionRepository, EntityManagerInterface $em)
    {
        $directions = $directionRepository->findAll();

        $created = [];

        /** @var Direction $direction */
        foreach ($directions as $direction) {
            $existedExchangeDirection = $exchangeDirectionRepository->findOneByCode($direction->getCode());

            if (is_null($existedExchangeDirection)) {
                $exchangeDirection = (new ExchangeDirection())
                    ->setCode($direction->getCode())
                    ->setMinimumAmount($direction->getMinimumAmount())
                    ->setMaximumAmount($direction->getMaximumAmount())
                    ->setPrecision($direction->getPrecision())
                    ->setCurrencyQuote($direction->getCurrencyQuote())
                    ->setCurrencyBase($direction->getCurrencyBase())
                ;

                $em->persist($exchangeDirection);

                $created[] = $exchangeDirection;
            }
        }

        $em->flush();

        return new Response('Created exchange directions: ' . count($created));
    }

    /**
     * @Route("/prolong_user_uas_token/{userId}", name="prolong_user_uas_token", methods={"GET"})
     */
    public function prolongUserUasToken($userId, UserRepository $userRepository, UASService $uasService)
    {
        $user = $userRepository->find($userId);

        if (!$user) {
            throw $this->createNotFoundException("User $userId not found");
        }

        $uasService->introspect($user->getUasToken());

        return new Response('User token prolonged till ' . $user->getUasTokenExpirationAtAsString());
    }

    /**
     * @Route("/process_bilderlings_payments", name="process_bilderlings_payments", methods={"GET"})
     * @Rest\View()
     */
    public function processBilderlingsPayments(BilderlingsService $bilderlingsService)
    {
        $transactions = $bilderlingsService->loadPayments();

        echo '<pre>';
        print_r($transactions); die;

        return $transactions;
    }

    /**
     * @Route("/add_mock_bonuses", name="add_mock_bonuses", methods={"GET"})
     * @Rest\View()
     */
    public function addMockBonuses(EntityManagerInterface $em)
    {
        $user = $em->getRepository(User::class)
            ->findOneByEmail('user1@test.com');

        $currency = $em->getRepository(Currency::class)
            ->findOneByCode('USDT');

        $userAccount = $em->getRepository(UserAccount::class)
            ->findOneBy([
                'user' => $user,
                'type' => UserAccount::TYPE_PRIMARY,
                'currency' => $currency
            ]);

        if (! $userAccount instanceof UserAccount) {
            throw new \Exception("Not found USDT test account");
        }

        for ($i=0; $i < 20; $i++) {
            $bonus = (new ExchangeBonus())
                ->setAccount($userAccount)
                ->setAmount(rand(0, 9) / 10);

            $em->persist($bonus);
            $em->flush();
        }

        return new Response('OK!');
    }

    /**
     * @Route("/fill_bots_accounts", name="fill_bots_accounts", methods={"GET"})
     *
     * Fill bots' accounts
     */
    public function fillBotsAccounts(UserRepository $userRepository, EntityManagerInterface $em)
    {
        $bots = $userRepository->findByIsBot(true);

        foreach ($bots as $bot) {
            foreach ($bot->getUserAccounts(UserAccount::TYPE_PRIMARY) as $account) {
                if ($account->getBalance() < 1) {
                    $deposit = (new Transaction())
                        ->setType(Transaction::TYPE_DEPOSIT)
                        ->setAmount(1000)
                        ->setUserAccount($account);

                    $em->persist($deposit);

                    echo "Filled up {$bot->getEmail()}'s {$account->getCurrency()->getCode()} account <br>";
                }
            }
        }
        $em->flush();

        return new Response('OK');
    }

}

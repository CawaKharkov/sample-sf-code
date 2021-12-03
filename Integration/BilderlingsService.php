<?php

namespace App\Integration;

use App\Entity\AMLInspection;
use App\Entity\Currency;
use App\Entity\DepositTransaction;
use App\Entity\FiatWithdrawal;
use App\Entity\User;
use App\Entity\UserDepositMethod;
use App\Entity\UserWithdrawAccount;
use App\Verification\Monolith\AMLService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;


class BilderlingsService
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $url;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $xProfile;

    /**
     * @var string
     */
    private $xUser;

    /**
     * @var string
     */
    private $xToken;

    /**
     * @var AMLService
     */
    private $amlService;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param Client $client
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $em
     * @param string $url
     * @param string $xProfile
     * @param string $xUser
     * @param string $xToken
     */
    public function __construct(Client $client, LoggerInterface $logger, EntityManagerInterface $em,
                                string $url, string $xProfile, string $xUser, string $xToken, AMLService $amlService,
                                SerializerInterface $serializer)
    {
        $this->client = $client;
        $this->url = $url;
        $this->logger = $logger;
        $this->em = $em;
        $this->xProfile = $xProfile;
        $this->xUser = $xUser;
        $this->xToken = $xToken;
        $this->amlService = $amlService;
        $this->serializer = $serializer;
    }

    /**
     * @throws IntegrationException
     */
    public function loadPayments()
    {
        $headers = [
            'Content-Type'  => 'application/json;charset=UTF-8',
            'Accept' => 'application/json',
            'X-Profile' => $this->xProfile,
            'X-User' => $this->xUser,
            'X-Token' => $this->xToken,
        ];

        // Grab the client's handler instance.
        $clientHandler = $this->client->getConfig('handler');
        // Create a middleware that echoes parts of the request.
        $tapMiddleware = Middleware::tap(function ($request) {
            echo $request->getHeaderLine('Content-Type');
            // application/json
            echo $request->getBody();
            // {"foo":"bar"}
        });

        $res = $this->client->get($this->url . '/bilder-account/api/v1/payments', [
            'headers' => $headers,
            //'handler' => $tapMiddleware($clientHandler)
        ]);

        $this->logger->info('Response from Bilderlings: ' . $res->getBody());

        /** @var \stdClass $payment */
        foreach (\json_decode($res->getBody())->payload as $payment) {
            /** @var Currency|null $currency */
            $currency = $this->em->getRepository(Currency::class)
                ->findOneByCode($payment->senderCurrency);

            if (!$currency) {
                throw new IntegrationException("Got unknown currency {$payment->senderCurrency} from Bilderlings");
            }

            $depositMethod = $this->em->getRepository(UserDepositMethod::class)
                ->findOneBy([
                    'reference' => $payment->comment,
                    'currency' => $currency,
                ]);

            if (is_null($depositMethod)) {
                $this->logger->info("Omitted deposit transaction: no deposit method found with reference='{$payment->comment}'', currency={$currency->getCode()}");
                continue;
            }

            $existed = $this->em->getRepository(DepositTransaction::class)
                ->findOneByExternalId($payment->id);

            if ($existed instanceof DepositTransaction) {
                $this->logger->info("Transaction with external id '{$payment->id}' already exists, omitted");
                continue;
            }

            $depositTransaction = (new DepositTransaction())
                ->setExternalId($payment->id)
                ->setCurrency($currency)
                ->setUserDepositMethod($depositMethod)
                ->setReferenceNumber($payment->comment)
                ->setAmount($payment->recipientAmount)
                //->setFee($object->fee)
            ;

            $this->checkForAml($depositTransaction);

            $this->em->persist($depositTransaction);
            $this->em->flush();
        }
    }

    /**
     * @param DepositTransaction $depositTransaction
     */
    public function checkForAml(DepositTransaction $depositTransaction)
    {
        $user = new User();

        $amlInspection = new AMLInspection();
        $amlInspection->setDepositTransaction($depositTransaction);
        $this->em->persist($amlInspection);
        $this->em->flush();

        $account = (new UserWithdrawAccount())
            ->setUser($user)
            ->setNameOnAccount('some akcrypto user')
            ->setBankName('some akcrypto user bank name')
            ->setIban('some user iban');

        $withdrawal = (new FiatWithdrawal())
            ->setAccount($account)
            ->setUser($depositTransaction->getUserDepositMethod()->getUser())
            ->setAmount($depositTransaction->getAmount());

        $confirmationToken = $this->amlService->paymentInitialization($withdrawal);

        $this->amlService->paymentConfirmation($withdrawal, $confirmationToken, '0000');

        $depositTransaction->setConfirmedAt(new \DateTime());
    }

    /**
     * @param FiatWithdrawal $withdrawal
     */
    public function createPayment(FiatWithdrawal $withdrawal)
    {
        $headers = [
            'Content-Type'  => 'application/json;charset=UTF-8',
            'Accept' => 'application/json',
            'X-Profile' => $this->xProfile,
            'X-User' => $this->xUser,
            'X-Token' => $this->xToken,
        ];

        // Grab the client's handler instance.
        $clientHandler = $this->client->getConfig('handler');
        // Create a middleware that echoes parts of the request.
        $tapMiddleware = Middleware::tap(function ($request) {
            echo $request->getHeaderLine('Content-Type');
            // application/json
            echo $request->getBody();
            // {"foo":"bar"}
        });

        $data = '{
            "comment": "Rent for ...",
            "feeCurrency": "'.$withdrawal->getAccount()->getCurrency()->getCode().'",
            "recipient": "'.$withdrawal->getAccount()->getNameOnAccount().'",
            "recipientAddress": "'.$withdrawal->getAccount()->getAddress().'",
            "recipientBankAddress": "'.$withdrawal->getAccount()->getBankName().'",
            "recipientBankBic": "'.$withdrawal->getAccount()->getBic().'",
            "recipientBankCountry": "UK",
            "recipientBankName": "'.$withdrawal->getAccount()->getBankName().'",
            "recipientBankSortCode": "040002",
            "recipientBankSwift": "'.$withdrawal->getAccount()->getIban().'",
            "recipientBankVoCode": "20300",
            "recipientCountry": "UK",
            "recipientName": "'.$withdrawal->getAccount()->getNameOnAccount().'",
            "recipientRegNumber": "GB123345200",
            "senderAmount": '.$withdrawal->getAmount().',
            "senderCurrency": "'.$withdrawal->getAccount()->getCurrency()->getCode().'",
            "urgency": "URGENT"
        }';

        $res = $this->client->post($this->url . '/bilder-account/api/v1/payments', [
            'body' => $data,
            'headers' => $headers,
            //'handler' => $tapMiddleware($clientHandler)
        ]);

        $this->logger->info('Response from Bilderlings: ' . $res->getBody());
    }


}
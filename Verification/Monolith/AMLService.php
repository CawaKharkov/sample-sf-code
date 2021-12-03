<?php

namespace App\Verification\Monolith;

use App\Entity\AMLInspection;
use App\Entity\CryptoWithdrawal;
use App\Entity\Currency;
use App\Entity\FiatWithdrawal;
use App\Entity\User;
use App\Entity\Withdrawal;
use App\Finance\RateService;
use App\Integration\IntegrationException;
use App\Verification\SmsServiceInterface;
use App\Verification\UAS\UASTokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Stream;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;

class AMLService
{
    const RECONNECT_ATTEMPTS = 3;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $url;

    /**
     * @var MonolithUserConverter
     */
    private $userConverter;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UASTokenStorageInterface
     */
    private $uasTokenStorage;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SmsServiceInterface
     */
    private $smsService;

    private $rateService;

    public function __construct(Client $client, string $url, MonolithUserConverter $userConverter, SerializerInterface $serializer,
                                LoggerInterface $logger, UASTokenStorageInterface $uasTokenStorage, EntityManagerInterface $em, SmsServiceInterface $smsService,
                                RateService $rateService)
    {
        $this->client = $client;
        $this->url = $url;
        $this->userConverter = $userConverter;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->uasTokenStorage = $uasTokenStorage;
        $this->em = $em;
        $this->smsService = $smsService;
        $this->rateService = $rateService;
    }

    /**
     * Gets User's organizations
     */
    public function getOrganizations(User $user): Stream
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $this->convertPhoneToCanonical($user->getPhone()),
            'token' => $this->uasTokenStorage->getToken($user),
            'applicationId' => 'AKCRYPTO',
            'productId' => 'AKCRYPTO',
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

        $this->logger->info("Getting user organizations from Monolith...");

        $res = $this->client->get($this->url . '/api/v2/organizations', [
            'headers' => $headers,
            //'handler' => $tapMiddleware($clientHandler)
        ]);

        $this->logger->info('Response from Monolith: ' . $res->getBody());

        return $res->getBody();
    }

    /**
     * @param User $user
     * @return string
     * @throws IntegrationException
     */
    public function getAccountId(User $user): string
    {
        $organizationsResponse = $this->getOrganizations($user);

        /** @var array $response */
        $response = \json_decode($organizationsResponse);

        if (!is_array($response) || !isset($response[0])) {
            throw new IntegrationException("Got unexpected organizations response for user {$user->getEmail()} from Monolith");
        }

        if (!isset($response[0]->accounts) || !is_array($response[0]->accounts) || empty($response[0]->accounts)) {
            throw new IntegrationException("Error retrieving accounts for user {$user->getEmail()} from Monolith");
        }

        return $response[0]->accounts[0]->id;
    }

    /**
     * @param Withdrawal $withdrawal
     * @return string The confirmation token
     * @throws IntegrationException
     * @throws \Exception
     */
    public function paymentInitialization(Withdrawal $withdrawal): string
    {
        $accountId = $this->getAccountId($withdrawal->getUser());

        $amlInspection = new AMLInspection();

        if ($withdrawal instanceof FiatWithdrawal) {
            $amlInspection->setFiatWithdrawal($withdrawal);
        } elseif ($withdrawal instanceof CryptoWithdrawal) {
            $amlInspection->setCryptoWithdrawal($withdrawal);
        }

        $this->em->persist($amlInspection);
        $this->em->flush();

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $this->convertPhoneToCanonical($withdrawal->getUser()->getPhone()),
            'token' => $this->uasTokenStorage->getToken($withdrawal->getUser()),
            'applicationId' => 'AKCRYPTO',
            'productId' => 'AKCRYPTO',
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

        $body = $this->createPaymentBody($withdrawal, $accountId);

        $this->logger->info("1. Payment initialization in Monolith: " . $this->url . '/api/v1/payment_sepa', $headers);

        $this->logger->info('Payment initialization body: ' . $body);

        $res = $this->client->post($this->url . '/api/v1/payment_sepa', [
            'headers' => $headers,
            'body' => $body
            //'handler' => $tapMiddleware($clientHandler)
        ]);

        $this->logger->info('Initialization response from Monolith: ' . $res->getBody());

        /** @var \stdClass $response */
        $response = \json_decode($res->getBody());

        if ($response->errors[0]->code == 449) {
            $confirmationToken = $response->errors[0]->properties->confirmationToken;
            return $confirmationToken;
        } else {
            $this->logger->warning("Got error initialization response from Monolith: " . $res->getBody());
            throw new IntegrationException("Error creating SEPA Payment: code {$response->errors[0]->code}, message: {{$response->errors[0]->message}}.");
        }
    }

    /**
     * @param Withdrawal $withdrawal
     * @param string $confirmationToken
     * @param string $code
     * @return string
     * @throws IntegrationException
     */
    public function paymentConfirmation(Withdrawal $withdrawal, string $confirmationToken, string $code): string
    {
        $accountId = $this->getAccountId($withdrawal->getUser());

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $this->convertPhoneToCanonical($withdrawal->getUser()->getPhone()),
            'token' => $this->uasTokenStorage->getToken($withdrawal->getUser()),
            'applicationId' => 'AKCRYPTO',
            'productId' => 'AKCRYPTO',
            'X-Confirmation-Code' => $code,
            'X-Confirmation-Token' => $confirmationToken,
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

        $body = $this->createPaymentBody($withdrawal, $accountId);

        $this->logger->info("2. Payment confirmation in Monolith: " . $this->url . '/api/v1/payment_sepa', $headers);

        $this->logger->info('Payment confirmation body: ' . $body);

        $res = $this->client->post($this->url . '/api/v1/payment_sepa', [
            'headers' => $headers,
            'body' => $body
            //'handler' => $tapMiddleware($clientHandler)
        ]);

        $this->logger->info('Confirmation response from Monolith: ' . $res->getBody());

        /** @var \stdClass $response */
        $response = \json_decode($res->getBody());

        if (isset($response->status) && $response->status == 'ok') {
            $referenceNumber = $response->referenceNumber;
            return $referenceNumber;
        } else {
            $this->logger->warning("Got error confirmation response from Monolith: " . $res->getBody());
            throw new IntegrationException("Error creating SEPA Payment: code {$response->errors[0]->code}, message: {{$response->errors[0]->message}}.");
        }
    }


    /**
     * @param Withdrawal $withdrawal
     * @param $accountId
     * @return string
     * @throws \Exception
     */
    private function createPaymentBody(Withdrawal $withdrawal, $accountId): string
    {
        $iban = $withdrawal->getAccount()->getIban();
        $amount = $withdrawal->getAmount();
        $currencyCode = $withdrawal->getAccount()->getCurrency()->getCode();
        $beneficiaryName = $withdrawal->getAccount()->getNameOnAccount();
        $purpose = $withdrawal->getAccount()->getDescription();

        /** @var Currency $currency */
        $currency = $this->em->getRepository(Currency::class)
            ->findOneByCode($currencyCode);

        if ($currency->getType() == Currency::TYPE_CRYPTO) {
            throw new \Exception("Got crypto currency $currencyCode, but fiat expected");
        }

        /** @var Currency $eurCurrency */
        $eurCurrency = $this->em->getRepository(Currency::class)->findOneByCode('EUR');

        $rate = $this->rateService->getCurrentMarketRate($currency, $eurCurrency);

        $amountInEur = round($amount * $rate->getValue(), 2);

        $this->logger->info("Got current rate for {$currencyCode}: {$eurCurrency->getCode()} {$rate->getValue()}. Amount = {$eurCurrency->getCode()} {$amountInEur}");

        $body = '{
                "payment_sepa": {
                "account": "' . $accountId . '",
                "beneficiaryName": "' . $beneficiaryName . '",
                "iban": "' . $iban . '",
                "amount": {
                  "sum": {
                    "currency": {
                      "code": "' . $eurCurrency->getCode() . '"
                    },
                    "value": "' . $amountInEur . '"
                  }
                },
                "purpose": "' . $purpose . '",
                "transferDetails": "test"
                }
            }';

        return $body;
    }

    /**
     * @param string $phone
     * @return mixed
     */
    protected function convertPhoneToCanonical(string $phone)
    {
        return preg_replace('/\D/', '', $phone);
    }

}
<?php

namespace App\Verification\Monolith;

use App\Entity\CryptoWithdrawal;
use App\Entity\Currency;
use App\Entity\DepositTransaction;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\UserDepositMethod;
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

class MonolithService implements MonolithServiceInterface
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
     * @inheritdoc
     */
    public function createUser(User $user): void
    {
        $headers = [
            'Content-Type'  => 'application/json',
            'token'         => $this->uasTokenStorage->getToken($user),
            'applicationId' => 'AKCRYPTO',
            'productId'     => 'AKCRYPTO',
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

        $convertedUser = $this->getConvertedUser($user);

        $this->logger->info("Creating user {$user->getPhone()} in Monolith (POST {$this->url}/api/v2/clients): "
            . json_encode($convertedUser)
            . ', headers: ' . print_r($headers,1 )
        );

        $res = $this->client->post($this->url . '/api/v2/clients', [
            'body' => json_encode($convertedUser),
            'headers' => $headers,
            //'handler' => $tapMiddleware($clientHandler)
        ]);

        $this->logger->info('Response from Monolith: ' . $res->getBody());
    }

    /**
     * @inheritdoc
     */
    public function loadDepositTransactions(User $user, $page = 0, $size = 100): void
    {
        $phoneCanonical = preg_replace('/\D/', '', $user->getPhone());

        if (!$this->uasTokenStorage->hasToken($user)) {
            throw new \Exception("Need to obtain token for user with phone {$user->getPhone()} first");
        }

        $accountId = $this->getAccountId($user);

        //$accounts = ['PPY5107', 'PPY5112', 'PPY5114', 'PPY5115', 'PPY5110', 'PPY5119'];

        $accounts = [$accountId];

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => $phoneCanonical,
            'token'         => $this->uasTokenStorage->getToken($user),
            'applicationId' => 'AKCRYPTO',
            'productId'     => 'AKCRYPTO',
        ];

        do {
            $url = $this->url . "/api/v2/operations?accounts=".implode(',', $accounts)."&page=$page&size=$size";

            $this->logger->info("Requesting deposit transactions from Monolith: $url...");

            try {
                $res = $this->client->get($url, [
                    'headers' => $headers,
                    //'handler' => $tapMiddleware($clientHandler)
                ]);

                $response = $res->getBody()->getContents();

                $body = json_decode($response);

                if (isset($body->errors)) {
                    $errorMessage = '';
                    /** @var \stdClass $error */
                    foreach ($body->errors as $error) {
                        $errorMessage .= \json_encode($error);
                    }
                    throw new IntegrationException("Error getting transactions from Monolith: $errorMessage");
                }

                if (is_null($body->content) || is_null($body->maxPageNumber)) {
                    throw new IntegrationException("Invalid response from Monolith: $response");
                }

                $transactions = $body->content;
                $maxPageNumber = $body->maxPageNumber;

                $this->logger->info("Got ".count($transactions)." transactions from Monolith (page=$page, size=$size)");

                //$pageSize = $body->pageSize;
                //$pageNumber = $body->pageNumber;

                $this->processDepositTransactions($transactions);

            } catch (\Exception $e) {
                $this->logger->error($e);
                if (!isset($maxPageNumber)) {
                    $maxPageNumber = self::RECONNECT_ATTEMPTS;
                }
                continue;
            }


        } while (++$page <= $maxPageNumber);
    }

    protected function processDepositTransactions(array $objects): void
    {
        /** @var \stdClass $object */
        foreach ($objects as $object) {
            if (! $object instanceof \stdClass) {
                throw new IntegrationException("Invalid response from Monolith: expected object, got " . gettype($object));
            }

            if ($object->status != 'COMPLETED') {
                $this->logger->info("Omitted deposit transaction {$object->referenceNumber} with status={$object->status}");
                continue;
            }

            $existed = $this->em->getRepository(DepositTransaction::class)
                ->findOneByReferenceNumber($object->referenceNumber);

            if ($existed instanceof DepositTransaction) {
                $this->logger->info("Omitted deposit transaction {$object->referenceNumber}: already exists");
                continue;
            }

            /** @var Currency $currency */
            $currency = $this->em->getRepository(Currency::class)
                ->findOneByCode($object->currency->code);

            if (is_null($currency)) {
                $this->logger->info("Omitted unknown deposit transaction currency from Monolith: {$object->currency->code}");
                continue;
                //throw new IntegrationException("Got unknown deposit transaction currency from Monolith: {$object->currency->code}");
            }

            $depositMethod = $this->em->getRepository(UserDepositMethod::class)
                ->findOneBy([
                    'reference' => $object->description,
                    'currency' => $currency,
                ]);

            if (is_null($depositMethod)) {
                $this->logger->info("Omitted deposit transaction: no deposit method found with reference={$object->description}, currency={$currency->getCode()}");
                continue;
            }

            $depositTransaction = (new DepositTransaction())
                ->setCurrency($currency)
                ->setUserDepositMethod($depositMethod)
                ->setReferenceNumber($object->referenceNumber)
                ->setAmount($object->amount)
                ->setFee($object->fee);

            $this->em->persist($depositTransaction);
            $this->em->flush();

            $this->logger->info("Saved new deposit transaction with referenceNumber={$depositTransaction->getReferenceNumber()}");

            $userAccount = $depositMethod->getUser()->getPrimaryAccountByCurrency($currency);

            if ($depositTransaction->getAmount() > 0) {
                $amount = $depositTransaction->getAmount();
                $type = Transaction::TYPE_DEPOSIT;
            } else {
                $amount = abs($depositTransaction->getAmount());
                $type = Transaction::TYPE_WITHDRAW;
            }

            // Main transaction
            {
                $transaction = (new Transaction())
                    ->setType($type)
                    ->setAmount($amount)
                    ->setDescription(sprintf("reference=%s, referenceNumber=%s, id=%s",
                        $depositMethod->getReference(),
                        $depositTransaction->getReferenceNumber(),
                        $object->id)
                    )
                    ->setUserAccount($userAccount);

                $this->em->persist($transaction);
                $this->em->flush();
            }

            // Fee transaction
            {
                if ($object->fee > 0) {
                    $transaction = (new Transaction())
                        ->setType(Transaction::TYPE_FEE)
                        ->setAmount($object->fee)
                        ->setDescription("fee for transaction, reference: {$depositMethod->getReference()}, referenceNumber: {$depositTransaction->getReferenceNumber()}")
                        ->setUserAccount($userAccount);

                    $this->em->persist($transaction);
                    $this->em->flush();
                }
            }
        }
    }

    /**
     * @param User $user
     * @return \stdClass
     */
    protected function getConvertedUser(User $user): \stdClass
    {
        return $this->userConverter->convert($user);
    }

    /**
     * @param User $user
     * @return Stream
     */
    public function getConfig(User $user): Stream
    {
        $headers = [
            'Content-Type'  => 'application/json',
            'token'         => $this->uasTokenStorage->getToken($user),
            'applicationId' => 'K2COIN',
            'productId'     => 'K2COIN',
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

        $this->logger->info("Getting config from Monolith...");

        $res = $this->client->get($this->url . '/api/v1/config', [
            'headers' => $headers,
            //'handler' => $tapMiddleware($clientHandler)
        ]);

        $this->logger->info('Response from Monolith: ' . $res->getBody());

        return $res->getBody();
    }

    /**
     * Gets User's organizations
     */
    public function getOrganizations(User $user): Stream
    {
        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => $this->convertPhoneToCanonical($user->getPhone()),
            'token'         => $this->uasTokenStorage->getToken($user),
            'applicationId' => 'AKCRYPTO',
            'productId'     => 'AKCRYPTO',
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

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => $this->convertPhoneToCanonical($withdrawal->getUser()->getPhone()),
            'token'         => $this->uasTokenStorage->getToken($withdrawal->getUser()),
            'applicationId' => 'AKCRYPTO',
            'productId'     => 'AKCRYPTO',
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
            'Content-Type'  => 'application/json',
            'Authorization' => $this->convertPhoneToCanonical($withdrawal->getUser()->getPhone()),
            'token'         => $this->uasTokenStorage->getToken($withdrawal->getUser()),
            'applicationId' => 'AKCRYPTO',
            'productId'     => 'AKCRYPTO',
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
                "account": "'.$accountId.'",
                "beneficiaryName": "'.$beneficiaryName.'",
                "iban": "'.$iban.'",
                "amount": {
                  "sum": {
                    "currency": {
                      "code": "'.$eurCurrency->getCode().'"
                    },
                    "value": "'.$amountInEur.'"
                  }
                },
                "purpose": "'.$purpose.'",
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

    /**
     * Checks user status
     */
    public function getUserStatus(User $user)
    {
        $headers = [
            'Content-Type'  => 'application/json',
            'token'         => $this->uasTokenStorage->getToken($user),
            'applicationId' => 'AKCRYPTO',
            'productId'     => 'AKCRYPTO',
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

        $this->logger->info("Getting user {$user->getEmail()} status in Monolith (GET {$this->url}/api/v2/clients/status): "
            . ', headers: ' . print_r($headers,1 )
        );

        $res = $this->client->get($this->url . '/api/v2/clients/status', [
            'headers' => $headers,
            //'handler' => $tapMiddleware($clientHandler)
        ]);

        $body = $res->getBody();

        $this->logger->info('Response from Monolith: ' . $body);

        /*
         * {
  "registrationStatus": "PROCESSING",
  "uuid": null,
  "additionalInfoUrl": null,
  "phoneNumber": "37120170066"
}
         */

        /** @var \stdClass $response */
        $response = \json_decode($body);

        return $response->registrationStatus;
    }

    /**
     * @inheritDoc
     */
    public function paymentCrypto(CryptoWithdrawal $cryptoWithdrawal): bool
    {
        $headers = [
            'Content-Type'  => 'application/json',
            'ApiKey' => 'VvdVZdDnYb',
            'X-Application-Id' => 'AKCRYPTO',
            'X-Product-Id' => 'AKCRYPTO',
            'X-Client-Id' => $this->convertPhoneToCanonical($cryptoWithdrawal->getUser()->getPhone()),
        ];

        $clientHandler = $this->client->getConfig('handler');
        $tapMiddleware = Middleware::tap(function ($request) {
            echo $request->getHeaderLine('Content-Type');
            echo $request->getBody();
        });

        $url = $this->url . '/service-api/v1/ak-crypto/exchange/payments/crypto';
        $body = '{
  "type": "INTERNAL",
  "fromAccount": "'.$cryptoWithdrawal->getCryptoaddress()->getAddress().'",
  "fromName": "Name of payment sender",
  "amount": 0.23660001,
  "currencyCode": "'.$cryptoWithdrawal->getCryptoaddress()->getCurrency()->getCode().'",
  "feeType": "SENDER",
  "priority": "NORMAL",
  "description": "'.$cryptoWithdrawal->getCryptoaddress()->getDescription().'",
  "toName": "'.$cryptoWithdrawal->getCryptoaddress()->getAddress().'",
  "toAccount": "'.$cryptoWithdrawal->getCryptoaddress()->getAddress().'",
  "toTheSameCustomer": 0,
  "externalOwnerId": 2,
  "subType": "CRYPTO",
  "externalID": "8475f-fe4-545e4-53"
}';

        $this->logger->info('Sending request to Monolith ' . $url .' with body: ' . $body);

        $res = $this->client->post($url, [
            'headers' => $headers,
            'body' => $body,
            //'handler' => $tapMiddleware($clientHandler)
        ]);

        $this->logger->info("Got {$res->getStatusCode()} response from Monolith :" . $res->getBody());

        $response = \json_decode($res->getBody());

        //echo $res->getBody(); die;

        if ($response->status == 'ok') {
            $referenceNumber = $response->referenceNumber;
            return true;
        } elseif (!empty($response->errors)) {
            $this->logger->warning("Got error initialization response from Monolith: " . $res->getBody());
            return false;
        } else {
            $this->logger->warning("Unknown response from Monolith: " . $res->getBody());
        }
    }
}
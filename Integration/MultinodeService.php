<?php

namespace App\Integration;

use App\AML\AMLInspectionInterface;
use App\Entity\CryptoDeposit;
use App\Entity\Currency;
use App\Entity\MultinodeRequestUpdateItemResults;
use App\Entity\User;
use App\Entity\UserDepositCryptoaddress;
use App\Entity\CryptoWithdrawal;
use App\Entity\UserWithdrawCryptoaddress;
use App\Verification\Monolith\AMLService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MultinodeService
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
     * @var string
     */
    private $apikey;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var AMLService
     */
    private $amlService;

    /**
     * @var AMLInspectionInterface
     */
    private $amlInspectionService;

    public function __construct(Client $client, string $url, string $apikey, LoggerInterface $logger,
                                EntityManagerInterface $em, AMLService $amlService, AMLInspectionInterface $amlInspectionService)
    {
        $this->client = $client;
        $this->url = $url;
        $this->apikey = $apikey;
        $this->logger = $logger;
        $this->em = $em;
        $this->amlService = $amlService;
        $this->amlInspectionService = $amlInspectionService;
    }

    /**
     * @param Currency $currency
     * @param User $user
     */
    public function generateCryptoAddress(Currency $currency, User $user): void
    {
        // Grab the client's handler instance.
        $clientHandler = $this->client->getConfig('handler');
        // Create a middleware that echoes parts of the request.
        $tapMiddleware = Middleware::tap(function ($request) {
            echo $request->getHeaderLine('Content-Type');
            echo $request->getBody();
        });

        $chain = strtolower($currency->getCode());

        $this->logger->info("Trying to generate new cryptoaddress: chain={$chain}, user={$user->getId()}");

        $res = $this->client->get($this->url . "/accounts/assign-new?chain={$chain}&user={$user->getId()}", [
            'headers' => [
                'apikey' => $this->apikey,
            ],
            //'handler' => $tapMiddleware($clientHandler)
        ]);

        $this->logger->info('Got response from multinode: ' . $res->getBody());

        /** @var \stdClass $response */
        $response = json_decode($res->getBody());

        $cryptoAddress = (new UserDepositCryptoaddress())
            ->setUser($user)
            ->setCurrency($currency)
            ->setAddress($response->data->account)
            ->setStatus(UserDepositCryptoaddress::STATUS_NEW)
        ;

        $this->em->persist($cryptoAddress);
        $this->em->flush();
    }

    /**
     * @param string $address
     * @param Currency $currency
     * @return bool
     * @throws IntegrationException
     */
    public function verifyCryptoAddress(string $address, Currency $currency): bool
    {
        // Grab the client's handler instance.
        $clientHandler = $this->client->getConfig('handler');
        // Create a middleware that echoes parts of the request.
        $tapMiddleware = Middleware::tap(function ($request) {
            echo $request->getHeaderLine('Content-Type');
            echo $request->getBody();
        });

        $chain = strtolower($currency->getCode());

        $this->logger->info("Trying to verify cryptoaddress {$address}, chain={$chain}");

        $res = $this->client->get($this->url . "/addresses/verify?chain={$chain}&address={$address}", [
            'headers' => [
                'apikey' => $this->apikey,
            ],
            //'handler' => $tapMiddleware($clientHandler)
        ]);

        $this->logger->info('Got response from multinode: ' . $res->getBody());

        /** @var \stdClass $response */
        $response = json_decode($res->getBody());

        if ('ok' == $response->status) {
            return true;
        } elseif ('addressNotVerified' == $response->status) {
            return false;
        } else {
            throw new IntegrationException("Unknown response from multinode: " . $res->getBody());
        }
    }

    /**
     * @param CryptoWithdrawal $cryptoWithdrawal
     */
    public function withdraw(CryptoWithdrawal $cryptoWithdrawal): void
    {
        // Grab the client's handler instance.
        $clientHandler = $this->client->getConfig('handler');
        // Create a middleware that echoes parts of the request.
        $tapMiddleware = Middleware::tap(function ($request) {
            echo $request->getHeaderLine('Content-Type');
            echo $request->getBody();
        });

        $chain = strtolower($cryptoWithdrawal->getCryptoaddress()->getCurrency()->getCode());

        $this->logger->info("Trying to check CryptoWithdrawal {$cryptoWithdrawal->getId()} for AML");
        $this->amlInspectionService->checkCryptoWithdrawal($cryptoWithdrawal);

        $this->logger->info("Trying to send withdraw transaction: cryptoaddress={$cryptoWithdrawal->getCryptoaddress()->getAddress()}, amount={$cryptoWithdrawal->getAmount()}, chain={$chain}, user={$cryptoWithdrawal->getUser()->getId()}");

        if (!$this->verifyCryptoAddress($cryptoWithdrawal->getCryptoaddress()->getAddress(), $cryptoWithdrawal->getCryptoaddress()->getCurrency())) {
            throw new BadRequestHttpException("Address {$cryptoWithdrawal->getCryptoaddress()->getAddress()} for {$chain} is invalid");
        }

        $res = $this->client->get($this->url . "/transactions/send?chain={$chain}&user={$cryptoWithdrawal->getUser()->getId()}&amount={$cryptoWithdrawal->getAmount()}&address={$cryptoWithdrawal->getCryptoaddress()->getAddress()}", [
            'headers' => [
                'apikey' => $this->apikey,
            ],
            //'handler' => $tapMiddleware($clientHandler)
        ]);

        $this->logger->info('Got response from multinode: ' . $res->getBody());

        /** @var \stdClass $response */
        $response = json_decode($res->getBody());

        $cryptoWithdrawal->setTxDbId($response->data->txDbId);
    }

    /**
     * @param CryptoDeposit $cryptoDeposit
     */
    public function deposit(CryptoDeposit $cryptoDeposit): void
    {
        // Grab the client's handler instance.
        $clientHandler = $this->client->getConfig('handler');
        // Create a middleware that echoes parts of the request.
        $tapMiddleware = Middleware::tap(function ($request) {
            echo $request->getHeaderLine('Content-Type');
            echo $request->getBody();
        });

        $chain = strtolower($cryptoDeposit->getCryptoaddress()->getCurrency()->getCode());

        $user = new User();

        $cryptoaddress = (new UserWithdrawCryptoaddress())
            ->setUser($user)
            ->setAddress('some_internal_user_address')
            ->setCurrency(new Currency());

        // todo: AMLService::checkAmlCryptoDeposit($cryptoDeposit);
        // $this->amlService->paymentInitialization($withdrawal);

        $this->logger->info("Trying to make deposit transaction: cryptoaddress={$cryptoaddress->getAddress()}, amount={$cryptoDeposit->getAmount()}, chain={$chain}, user={$cryptoDeposit->getUser()->getId()}");

        if (!$this->verifyCryptoAddress($cryptoaddress->getAddress(), $cryptoaddress->getCurrency())) {
            throw new BadRequestHttpException("Address {$cryptoaddress->getAddress()} for {$chain} is invalid");
        }

        $res = $this->client->get($this->url . "/transactions/send?chain={$chain}&user={$cryptoDeposit->getUser()->getId()}&amount={$cryptoDeposit->getAmount()}&address={$cryptoaddress->getAddress()}", [
            'headers' => [
                'apikey' => $this->apikey,
            ],
            //'handler' => $tapMiddleware($clientHandler)
        ]);

        $this->logger->info('Got response from multinode: ' . $res->getBody());

        /** @var \stdClass $response */
        $response = json_decode($res->getBody());
    }

    /**
     * @param CryptoWithdrawal $cryptoWithdrawal
     */
    public function checkForAml(CryptoWithdrawal $cryptoWithdrawal)
    {
        $this->amlService->paymentInitialization($cryptoWithdrawal);
    }

    public function processRequestCreate(MultinodeRequestUpdateItemResults $results)
    {

    }

    public function processRequestUpdates(MultinodeRequestUpdateItemResults $results)
    {

    }


}
<?php

namespace App\Integration;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use Psr\Log\LoggerInterface;


class BCCService
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
     * @var string|null
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
     * @param Client $client
     * @param string $url
     * @param string|null $apikey
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $em
     */
    public function __construct(Client $client, string $url, ?string $apikey, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->client = $client;
        $this->url = $url;
        $this->apikey = $apikey;
        $this->logger = $logger;
        $this->em = $em;
    }

    /**
     * @param User $user
     */
    public function createUser(User $user)
    {
        $data = new \stdClass();

        $data->birthCountryCode = $user->getResidenceCountry();
        $data->birthDate = $user->getBirthDate()->format('Y-m-d');
        $data->birthPlace = $user->getBirthPlace();
        $data->clientType = 'PRIVATE';
        $data->email = $user->getEmail();
        $data->gender = $user->getGender() == 1 ? 'MALE' : 'FEMALE';
        $data->identificationNumber = $user->getResidenceIdentificationNumber();

        $data->identityDocument = new \stdClass();
        $data->identityDocument->countryCode = $user->getIdentityCitizenship();
        $data->identityDocument->expiryDate = $user->getIdentityExpiryDate()->format("Y-m-d");
        $data->identityDocument->fileReferenceInStorage = '';
        $data->identityDocument->issueDate = $user->getIdentityIssueDate()->format("Y-m-d");
        $data->identityDocument->issuer = $user->getIdentityIssuer();
        $data->identityDocument->number = $user->getIdentityNumber();
        $data->identityDocument->type = $user->getIdentityType() == 1 ? 'PASSPORT' : 'IDCARD';

        $data->name = $user->getFirstName();
        $data->surname = $user->getLastName();
        $data->middleName = $user->getMiddleName();
        $data->nationalityCountryCode = '';
        $data->pep = $user->getAdditionalPoliticPerson();
        $data->pepInFamily = $user->getAdditionalPoliticFamily();

        $data->residenceAddress = new \stdClass();
        $data->residenceAddress->apartmentNumber = $user->getResidenceApartment();
        $data->residenceAddress->city = $user->getResidenceCity();
        $data->residenceAddress->countryCode = $user->getResidenceCountry();
        $data->residenceAddress->postalCode = $user->getResidencePostalCode();
        $data->residenceAddress->province = $user->getResidenceProvince();
        $data->residenceAddress->street = $user->getResidenceStreet();
        $data->residenceAddress->streetNumber = $user->getResidenceHouse();

        $headers = [
            'Content-Type'  => 'application/json',
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

        $this->logger->info("Creating user {$user->getPhone()} in BCC (POST {$this->url}/akcrypto/v1/clients): "
            . json_encode($data)
            . ', headers: ' . print_r($headers,1 )
        );

        $res = $this->client->post($this->url . '/akcrypto/v1/clients', [
            'body' => json_encode($data),
            'headers' => $headers,
            //'handler' => $tapMiddleware($clientHandler)
        ]);

        $this->logger->info('Response from BCC: ' . $res->getBody());
    }

    public function test()
    {
        $headers = [
            'Content-Type'  => 'application/json',
        ];

        $res = $this->client->post($this->url . '/akcrypto/v1/clients', [
            'body' => json_encode(new \stdClass()),
            'headers' => $headers,
            //'handler' => $tapMiddleware($clientHandler)
        ]);
    }



}
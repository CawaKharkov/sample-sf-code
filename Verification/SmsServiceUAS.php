<?php

namespace App\Verification;

use App\Entity\SmsCode;
use App\Verification\UAS\UASTokenStorageInterface;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Ronte\UASAuthenticationBundle\DataObject\LoginRequest;
use Ronte\UASAuthenticationBundle\DataObject\RegisterNewRequest;
use Ronte\UASAuthenticationBundle\DataObject\RequestCodeRequest;
use Ronte\UASAuthenticationBundle\DataObject\Token;
use Ronte\UASAuthenticationBundle\Exception\TooManyRequestsException;
use Ronte\UASAuthenticationBundle\Exception\UASRemoteErrorException;
use Ronte\UASAuthenticationBundle\Exception\ValidationException;
use Ronte\UASAuthenticationBundle\UAS\UASService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SmsServiceUAS implements SmsServiceInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    private $uasService;

    private $logger;

    private $storage;

    public function __construct(ValidatorInterface $validator, UASService $uasService, LoggerInterface $logger,
                                UASTokenStorageInterface $storage)
    {
        $this->validator = $validator;
        $this->uasService = $uasService;
        $this->logger = $logger;
        $this->storage = $storage;
    }

    /**
     * @see SmsServiceInterface::sendCode()
     */
    public function sendCode(SmsCode $smsCode): void
    {
        // регистрация юзера:
        // /signin/login, если 404, то register/new,
        // а затем отправка смс-кода: /signin/requestCode
        {
            $phoneCanonical = preg_replace('/\D/', '', $smsCode->getPhone());

            $req = new RequestCodeRequest($phoneCanonical);

            $errors = $this->validator->validate($req);

            if (count($errors) > 0) {
                throw new ValidationException($errors);
            }

            try {
                $this->logger->info("Trying to login with phone {$phoneCanonical}, request: " . print_r($req, 1));
                $requestedCodeData = $this->uasService->requestCode($req);
            } catch (TooManyRequestsException $e) {
                $this->logger->error($e);
                throw new TooManyRequestsHttpException(60, "Too many requests, please retry after 1 minute");
            } catch (ClientException $e) {
                $this->logger->info("Login/register failed: " . $e);
                if ($e->getCode() == 404) {
                    $this->logger->info("Got 404, phone {$phoneCanonical} not found. Trying to register manually");
                    $registerRequest = new RegisterNewRequest($phoneCanonical);

                    $this->logger->info("Trying to register with request: " . print_r($registerRequest, 1));
                    $requestedCodeData = $this->uasService->registerRequest($registerRequest);
                }
            } catch (\Exception $e) {
                $this->logger->error($e);
                throw new BadRequestHttpException("Error sending SMS-code");
            }

            if (empty($requestedCodeData->sessid)) {
                throw new SmsServiceException("Unable to get UAS sessid for phone {$phoneCanonical}. Response: " . print_r($requestedCodeData, 1));
            }

            $this->storage->setSessId($smsCode->getUser(), $requestedCodeData->sessid);

            if ($requestedCodeData->code !== null) {
                $smsCode->setCode($requestedCodeData->code);
            }

            $this->logger->info("Sms-code successfully sent to {$phoneCanonical}");
        }
    }

    /**
     * @see SmsServiceInterface::checkCode()
     */
    public function checkCode(SmsCode $smsCode): bool
    {
        // проверка смс-кода (логин): /signin

        $phoneCanonical = preg_replace('/\D/', '', $smsCode->getPhone());

        $loginRequest = new LoginRequest(
            $phoneCanonical,
            $smsCode->getCode(),
            $this->storage->getSessId($smsCode->getUser())
        );

        $errors = $this->validator->validate($loginRequest);

        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }

        try {
            $token = $this->uasService->login($loginRequest);
        } catch (UASRemoteErrorException $e) {
            throw new BadRequestHttpException('Wrong sms code');
        }

        if ($token instanceof Token) {
            $expirationAt = (new \DateTime())
                ->modify('+'.$token->expiresIn.' seconds');

            $this->storage->setToken($smsCode->getUser(), $token->value, $expirationAt);

            return true;
        }

        return false;
    }
}
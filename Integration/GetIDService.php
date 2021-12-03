<?php

namespace App\Integration;


use App\Entity\User;
use Psr\Log\LoggerInterface;

class GetIDService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GetIDService constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Compares GetID data with user's stored data
     *
     * @param User $user
     * @return array - List of non-matching fields
     */
    public function compare(User $user) : array
    {
        $this->logger->info("Starting comparing data for user " . $user->getEmail());

        // sometimes getidData is array, not object
        /** @var \stdClass $getIdData */
        $getIdData = \json_decode(\json_encode($user->getGetidData()));

        if (empty($getIdData)) {
            throw new \LogicException("No GetID data for user {$user->getEmail()}");
        }

        $ocr = [];
        foreach ($getIdData->servicesResults->docCheck->extracted->ocr as $value) {
            $ocr[$value->category] = $value->content;
        }

        $mrz = [];
        foreach ($getIdData->servicesResults->docCheck->extracted->mrz as $value) {
            $mrz[$value->category] = $value->content;
        }

        $nonMatchingKeys = [];

        $this->check('First name', 'first_name', $user->getFirstName(), $ocr['First name'] ?? '', $mrz['First name'] ?? '', $nonMatchingKeys);
        $this->check('Last name', 'last_name', $user->getLastName(), $ocr['Last name'] ?? '', $mrz['Last name'] ?? '', $nonMatchingKeys);
        $this->check('Date of birth', 'birth_date', $user->getBirthDate()->format('Y-m-d'), $ocr['Date of birth'] ?? '', $mrz['Date of birth'] ?? '', $nonMatchingKeys);
        $this->check('Document number', 'identity_number', $user->getIdentityNumber(), $ocr['Document number'] ?? '', $mrz['Document number'] ?? '', $nonMatchingKeys);
        if (!empty($ocr['Date of issue']) || !empty($mrz['Date of issue'])) {
            $this->check('Date of issue', 'identity_issue_date', $user->getIdentityIssueDate()->format('Y-m-d'), $ocr['Date of issue'] ?? '', $mrz['Date of issue'] ?? '', $nonMatchingKeys);
        }
        $this->check('Date of expiry', 'identity_expiry_date', $user->getIdentityExpiryDate()->format('Y-m-d'), $ocr['Date of expiry'] ?? '', $mrz['Date of expiry'] ?? '', $nonMatchingKeys);
        if (!empty($ocr['Place of birth']) || !empty($mrz['Place of birth'])) {
            $this->check('Place of birth', 'birth_place', $user->getBirthPlace(), $ocr['Place of birth'] ?? '', $mrz['Place of birth'] ?? '', $nonMatchingKeys);
        }
        $this->check('Gender', 'gender', $user->getGenderText(), $ocr['Gender'] ?? '', $mrz['Gender'] ?? '', $nonMatchingKeys);

        return $nonMatchingKeys;
    }

    /**
     * @param $getidKey
     * @param $userValue
     * @param $ocrValue
     * @param $mrzValue
     * @param $results
     */
    private function check($getidKey, $formKey, $userValue, $ocrValue, $mrzValue, &$results)
    {
        if (strtolower($userValue) != strtolower($ocrValue)) {
            if (strtolower($userValue) != strtolower($mrzValue)) {
                $message = (sprintf("$getidKey '%s' not matched with ocr: '%s' nor mrz: '%s'",
                    $userValue,
                    $ocrValue,
                    $mrzValue
                ));
                $this->logger->info($message);
                $results[$formKey] = $message;
            } else {
                $this->logger->info(sprintf("Gender '%s' matched with mrz: '%s'", $userValue, $mrzValue));
            }
        } else {
            $this->logger->info(sprintf("Gender '%s' matched with ocr: '%s'", $userValue, $ocrValue));
        }
    }

}
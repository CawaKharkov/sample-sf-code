<?php

namespace App\Verification\Monolith;

use App\Entity\User;

class MonolithUserConverter
{
    public function convert(User $user): \stdClass
    {
        $converted = new \stdClass();

        $converted->phoneNumber = $this->convertPhoneToCanonical($user->getPhone());
        $converted->sendDocumentsToEmail = false;
        //$converted->userPhoto = "c619b257-13ce-4fe2-b260-2234730c535f";
        //$converted->promoCode = "475RYE";

        $converted->cardOrder = new \stdClass();
        $converted->cardOrder->isExpressDelivery = true;
        $converted->cardOrder->deliveryAddress = new \stdClass();
        $converted->cardOrder->deliveryAddress->province = $user->getResidenceProvince();
        //$converted->cardOrder->deliveryAddress->streetNumber = $user->getResidenceStreet();
        $converted->cardOrder->deliveryAddress->street = $user->getResidenceStreet();
        $converted->cardOrder->deliveryAddress->apartmentNumber = $user->getResidenceApartment();
        $converted->cardOrder->deliveryAddress->city = $user->getResidenceCity();
        $converted->cardOrder->deliveryAddress->postalCode = $user->getResidencePostalCode();
        $converted->cardOrder->deliveryAddress->countryCode = $user->getResidenceCountry();
        $converted->cardOrder->nameOnCard = $user->getCardName();

        $converted->registrationForm = new \stdClass();
        $converted->registrationForm->addressProofDocument = null;
        //$converted->registrationForm->addressProofDocument->fileReferenceInStorage = "scanner=c619b257-13ce-4fe2-b260-2234730c535f";
        /*$converted->registrationForm->addressProofDocument->issueDate = !is_null($user->getIdentityIssueDate())
            ? $user->getIdentityIssueDate()->format('Y-m-d')
            : null;
        $converted->registrationForm->addressProofDocument->number = $user->getIdNumber();
        $converted->registrationForm->addressProofDocument->issueDate = $user->getIdentityType();*/
        $converted->registrationForm->birthCountryCode = $user->getBirthCountry();
        $converted->registrationForm->birthDate = !is_null($user->getBirthDate())
            ? $user->getBirthDate()->format('Y-m-d')
            : null;
        $converted->registrationForm->birthPlace = $user->getBirthPlace();
        $converted->registrationForm->clientType = 'PRIVATE';
        $converted->registrationForm->email = $user->getEmail();
        $converted->registrationForm->employmentType = 'EMPLOYEE';
        $converted->registrationForm->gender = $user->getGender() == 1 ? 'MALE' : 'FEMALE';
        $converted->registrationForm->identificationNumber = $user->getResidenceIdentificationNumber();
        $converted->registrationForm->identityDocument = new \stdClass();
        $converted->registrationForm->identityDocument->countryCode = $user->getIdentityCitizenship();
        $converted->registrationForm->identityDocument->expiryDate = !is_null($user->getIdentityExpiryDate())
            ? $user->getIdentityExpiryDate()->format('Y-m-d')
            : null;
        //$converted->registrationForm->identityDocument->fileReferenceInStorage = "scanner=c619b257-13ce-4fe2-b260-2234730c535f";
        $converted->registrationForm->identityDocument->issueDate = !is_null($user->getIdentityIssueDate())
            ? $user->getIdentityIssueDate()->format('Y-m-d')
            : null;
        $converted->registrationForm->identityDocument->issuer = $user->getIdentityIssuer();
        $converted->registrationForm->identityDocument->number = $user->getIdentityNumber();
        $converted->registrationForm->identityDocument->type = $user->getIdentityType() == 1
            ? 'PASSPORT'
            : 'ID_CARD';
        $converted->registrationForm->identityDocument->fileReferenceInStorage = 'getid';

        //$converted->registrationForm->institutionId = 37;
        $converted->registrationForm->name = $user->getFirstName();
        $converted->registrationForm->nationalityCountryCode = $user->getIdentityCitizenship();
        $converted->registrationForm->pep = false;
        $converted->registrationForm->pepInFamily = false;

        $converted->registrationForm->residenceAddress = new \stdClass();
        $converted->registrationForm->residenceAddress->province = '';
        //$converted->registrationForm->residenceAddress->streetNumber = 25;
        $converted->registrationForm->residenceAddress->street = $user->getResidenceStreet();
        $converted->registrationForm->residenceAddress->apartmentNumber = $user->getResidenceApartment();
        $converted->registrationForm->residenceAddress->city= $user->getResidenceCity();
        $converted->registrationForm->residenceAddress->postalCode = $user->getResidencePostalCode();
        $converted->registrationForm->residenceAddress->countryCode = $user->getResidenceCountry();

        $converted->registrationForm->surname = $user->getLastName();
        $converted->registrationForm->middleName = $user->getMiddleName();

        /*$converted->getIdData = new \stdClass();
        $converted->getIdData->documents = $user->getGetidData()->application->documents;
        $converted->getIdData->images = $user->getGetidData()->servicesResults->docCheck->extracted->images;*/

        $converted->getIdData = $user->getGetidData();

        return $converted;
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
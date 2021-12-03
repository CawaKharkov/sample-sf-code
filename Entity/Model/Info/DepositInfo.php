<?php

namespace App\Entity\Model\Info;

use Swagger\Annotations as SWG;


class DepositInfo
{
    /**
     * @SWG\Property(type="string")
     */
    public $depositMethod;

    /**
     * @SWG\Property(type="string")
     */
    public $accountName;

    /**
     * @SWG\Property(type="string")
     */
    public $address;

    /**
     * @SWG\Property(type="string")
     */
    public $iban;

    /**
     * @SWG\Property(type="string")
     */
    public $bankName;

    /**
     * @SWG\Property(type="string")
     */
    public $bic;

    /**
     * @SWG\Property(type="string")
     */
    public $bankAddress;

    /**
     * @SWG\Property(type="string")
     */
    public $reference;


}
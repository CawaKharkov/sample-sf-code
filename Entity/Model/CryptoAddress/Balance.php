<?php

namespace App\Entity\Model\CryptoAddress;

use Swagger\Annotations as SWG;

class Balance
{
    /**
     * @SWG\Property(type="string")
     */
    public $balance;

    /**
     * @SWG\Property(type="string")
     */
    public $withheld;

    /**
     * @SWG\Property(type="string")
     */
    public $withheldConverted;

    /**
     * @SWG\Property(type="string")
     */
    public $maximumDeposit;

}
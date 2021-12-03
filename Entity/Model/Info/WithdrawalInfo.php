<?php

namespace App\Entity\Model\Info;

use Swagger\Annotations as SWG;

class WithdrawalInfo
{
    /**
     * @SWG\Property(type="string")
     */
    public $minimum;

    /**
     * @SWG\Property(type="string")
     */
    public $maximum;

    /**
     * @SWG\Property(type="string")
     */
    public $balance;

    /**
     * @SWG\Property(type="string")
     */
    public $fee;

    /**
     * @SWG\Property(type="string")
     */
    public $inOrders;

}
<?php

namespace App\Controller;

use App\Entity\Transaction;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * @Rest\Route("/api/v1/transactions")
 */
class TransactionController extends AbstractFOSRestController
{

    /**
     * Transaction types list
     *
     * @Rest\Route("/types", name="api_transaction_types", methods={"GET"})
     * @Rest\View()
     *
     * @SWG\Response(response="200", description="List of available transaction types",
     *     @SWG\Schema(type="object", example={"1": "Deposit","2": "Withdraw"}))
     */
    public function transactionTypes()
    {
        return Transaction::$typesText;
    }

}
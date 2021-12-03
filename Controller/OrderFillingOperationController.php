<?php

namespace App\Controller;

use App\Repository\OrderFillingOperationRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;


/**
 * @Rest\Route("/api/v1/order_filling_operations")
 */
class OrderFillingOperationController extends AbstractFOSRestController
{
    /**
     * @Rest\Route("", name="api_filling_operation_list", methods={"GET"})
     * @Rest\View()
     */
    public function index(OrderFillingOperationRepository $repository)
    {
        return $repository->findAll();
    }
}

<?php

namespace App\Controller;

use App\Repository\FeeRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\QueryParam;



/**
 * @Rest\Route("/api/v1/fees")
 */
class FeeController extends AbstractFOSRestController
{
    /**
     * @Rest\Route("", name="api_fee_list", methods={"GET"})
     * @Rest\View()
     *
     * @QueryParam(name="type", description="Fee type")
     *
     */
    public function index(FeeRepository $repository, ParamFetcher $paramFetcher)
    {
        $type = $paramFetcher->get('type');

        return $type
            ? $repository->findByType($type)
            : $repository->findAll();
    }

}

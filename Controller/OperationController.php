<?php

namespace App\Controller;

use App\Finance\Operation\OperationServiceInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;



/**
 * @Rest\Route("/api/v1/operations")
 */
class OperationController extends AbstractFOSRestController
{
    /**
     * @Rest\Route("", name="api_operation_list", methods={"GET"})
     * @Rest\View()
     *
     * @QueryParam(name="type", description="Operation type")
     *
     * @SWG\Response(response="200", description="List of user operations",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=App\Entity\Model\Operation::class)))
     * )
     *
     */
    public function index(OperationServiceInterface $operationService)
    {
        return $operationService->getOperations($this->getUser());
    }

}

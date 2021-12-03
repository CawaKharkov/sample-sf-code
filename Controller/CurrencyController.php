<?php

namespace App\Controller;

use App\Entity\Currency;
use App\Repository\CurrencyRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;


/**
 * @Rest\Route("/api/v1/currencies")
 */
class CurrencyController extends AbstractFOSRestController
{
    /**
     * @Rest\Route("", name="api_currency_list", methods={"GET"})
     * @Rest\View()
     */
    public function index(CurrencyRepository $repository)
    {
        return $repository->findAll();
    }

    /**
     * Currency types list
     *
     * @Rest\Route("/types", name="api_currency_types", methods={"GET"})
     * @Rest\View()
     *
     * @SWG\Response(response="200", description="List of available order types",
     *     @SWG\Schema(type="object", example={"1": "Fiat","2": "Crypto"})
     * )
     */
    public function types()
    {
        return Currency::$typesText;
    }

}

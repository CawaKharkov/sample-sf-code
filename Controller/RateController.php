<?php

namespace App\Controller;

use App\Finance\AccountService;
use App\Finance\RateService;
use App\Repository\RateRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;


/**
 * @Rest\Route("/api/v1/rates")
 */
class RateController extends AbstractFOSRestController
{

    /**
     * Rates list
     *
     * @Rest\Route("", name="api_rates_list", methods={"GET"})
     * @Rest\View()
     *
     * @SWG\Response(response="200", description="List of current rates",
     *     @SWG\Schema(type="object", example={}))
     */
    public function list(RateService $rateService, RateRepository $rateRepository, AccountService $accountService)
    {
        $codes = ['BTCUSD', 'BTCEUR'];

        $rates = $rateRepository->findToday($codes);

        $ratesWithChange = [];
        foreach ($rates as $rate) {
            $ratesWithChange[] = [
                'currencyFrom' => $rate->getCurrencyFrom()->getCode(),
                'currencyTo'   => $rate->getCurrencyTo()->getCode(),
                'price'        => $rate->getValue(),
                'change'       => $accountService->getChange($rate->getCurrencyFrom(), $rate->getCurrencyTo()),
            ];
        }

        return $ratesWithChange;
    }
}

<?php

namespace App\Controller;

use App\Entity\Rate;
use App\Finance\RateService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Rest\Route("/api/v1/order-api")
 */
class OrderapiController extends AbstractFOSRestController
{

    /**
     * Rates
     *
     * @Rest\Route("/rates", name="api_orderapi_rates", methods={"POST"})
     * @Rest\View()
     *
     */
    public function rates(Request $request, LoggerInterface $logger, EntityManagerInterface $em, RateService $rateService)
    {
        //$logger->debug("Incoming OrderApi rates request: {$request->getContent()}");

        $prices = json_decode($request->getContent());

        if (!is_array($prices)) {
            throw new BadRequestHttpException("Unable to parse incoming OrderApi rates request: {$request->getContent()}");
        }

        $rateService->eraseToday();

        foreach ($rateService->getRates($prices) as $rate) {
            $em->persist($rate);

            $invertedRate = (new Rate())
                ->setCurrency($rate->getCurrencyTo())
                ->setCurrencyTo($rate->getCurrencyFrom())
                ->setCurrencyFrom($rate->getCurrencyTo())
                ->setCode("{$rate->getCurrencyTo()->getCode()}{$rate->getCurrencyFrom()->getCode()}")
                ->setValue(bcdiv(1, $rate->getValue(), 8));

            $em->persist($invertedRate);
        }

        $em->flush();

        return ['success' => true];
    }


}
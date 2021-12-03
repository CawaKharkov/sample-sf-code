<?php

namespace App\Controller;

use App\Entity\ExchangeOrder;
use App\Entity\ExchangeRate;
use App\Exchange\ExchangeService;
use App\Finance\AccountService;
use App\Finance\RateService;
use App\Form\ExchangeOrderType;
use App\Repository\ExchangeBonusRepository;
use App\Repository\ExchangeDirectionRepository;
use App\Repository\ExchangeRateRepository;
use App\Repository\OrderRepository;
use App\Repository\RateRepository;
use App\Repository\TopupAddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\Annotations\QueryParam;


/**
 * @Rest\Route("/api/v1/exchange")
 */
class ExchangeController extends AbstractFOSRestController
{

    /**
     * Exchange order directions list
     *
     * @Rest\Route("/directions", name="api_exchange_direction_list", methods={"GET"})
     * @Rest\View()
     *
     * @SWG\Response(response="200", description="List of available exchange directions",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=App\Entity\ExchangeDirection::class)))
     * )
     */
    public function directions(ExchangeDirectionRepository $repository)
    {
        return $repository->findAll();
    }

    /**
     * Create exchange order
     *
     * @Rest\Route("/orders", name="api_exchange_order_create", methods={"POST"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="Payload", description="Exchange order data", required=true, in="body", required=true,
     *      @SWG\Schema(type="array", @SWG\Items(@Model(type=App\Entity\ExchangeOrder::class)),
     *     example={"amount": 100, "price": 76.25, "type": 4, "direction": 2}),
     * )
     * @SWG\Response(response=200, description="Success response",@SWG\Schema(type="object",example={"success": true}))
     * @SWG\Response(response=400, description="Validation error", @SWG\Schema(type="object", example={"code": 400, "message": "Minimum amount is: UAH 1000"}))
     * @SWG\Response(response=409, description="Not enough balance", @SWG\Schema(type="object", example={"code": 409, "message": "Not enough balance on the USD user account"}))
     */
    public function createOrder(Request $request, EntityManagerInterface $em, LoggerInterface $logger, ExchangeService $exchangeService)
    {
        $logger->info("Incoming exchange order creation request: " . $request->getContent());

        $exchangeOrder = new ExchangeOrder();
        $exchangeOrder->setUser($this->getUser());

        $form = $this->createForm(ExchangeOrderType::class, $exchangeOrder);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($exchangeOrder);
            $em->flush();

            $logger->info(sprintf("User %s created '%s' %s exchange order %s. Amount1: %s, amount2: %s",
                $exchangeOrder->getUser()->getEmail(),
                $exchangeOrder->getTypeText(),
                $exchangeOrder->getDirection()->getCode(),
                $exchangeOrder->getId(),
                $exchangeOrder->getAmount(),
                $exchangeOrder->getPrice()
            ));

            // todo: remove try-catch
            try {
                $exchangeService->exchange($exchangeOrder);
            } catch (\Exception $e) {
                throw new BadRequestHttpException($e->getMessage(), $e);
            }

            return ['success' => true];
        }

        return $form;
    }

    /**
     * Rates
     *
     * @Rest\Route("/rates", name="api_exchange_rates", methods={"POST"})
     * @Rest\View()
     *
     */
    public function rates(Request $request, LoggerInterface $logger, EntityManagerInterface $em, ExchangeService $exchangeService)
    {
        $prices = json_decode($request->getContent());

        if (!is_array($prices)) {
            throw new BadRequestHttpException("Unable to parse incoming exchange rates request: {$request->getContent()}");
        }

        $exchangeService->eraseToday();

        foreach ($exchangeService->parsePrices($prices) as $rate) {
            $em->persist($rate);
        }

        $em->flush();

        return ['success' => true];
    }

    /**
     * Rates list
     *
     * @Rest\Route("/rates", name="api_exchange_rates_list", methods={"GET"})
     * @Rest\View()
     *
     * @SWG\Response(response="200", description="List of current rates",
     *     @SWG\Schema(type="object", example={}))
     */
    public function list(RateService $rateService, ExchangeRateRepository $rateRepository, AccountService $accountService)
    {
        $codes = ['BTCUSD', 'BTCEUR', 'ETHBTS', 'ZENBTC'];

        $rates = $rateRepository->findToday($codes);

        if (empty($rates)) {
            throw new BadRequestHttpException("No exchange rates found for directions: " . implode(', ', $codes));
        }

        $ratesWithChange = [];
        foreach ($rates as $rate) {
            $ratesWithChange[] = [
                'currencyFrom' => $rate->getCurrencyFrom()->getCode(),
                'currencyTo'   => $rate->getCurrencyTo()->getCode(),
                'price'        => $rate->getPriceAsk(),
                'change'       => $accountService->getChange($rate->getCurrencyFrom(), $rate->getCurrencyTo()),
            ];
        }

        return $ratesWithChange;
    }

    /**
     * Bonuses
     *
     * @Rest\Route("/bonuses", name="api_exchange_bonuses", methods={"GET"})
     * @Rest\View()
     *
     */
    public function bonuses(Request $request, LoggerInterface $logger,
                            EntityManagerInterface $em, ExchangeBonusRepository $bonusRepository)
    {
        return $bonusRepository->findAll();
    }

    /**
     * Bonuses total
     *
     * @Rest\Route("/bonuses/total", name="api_exchange_bonuses_total", methods={"GET"})
     * @Rest\View()
     *
     */
    public function bonusesTotal(Request $request, LoggerInterface $logger,
                            EntityManagerInterface $em, ExchangeBonusRepository $bonusRepository)
    {
        $bonuses = $bonusRepository->findAll();

        $sum = 0;
        foreach ($bonuses as $bonus) {
            $sum += $bonus->getAmount();
        }

        return ['total' => $sum];
    }

    /**
     * Bonuses
     *
     * @Rest\Route("/topup_addresses", name="api_topup_addresses", methods={"GET"})
     * @Rest\View()
     *
     */
    public function topupAddresses(Request $request, LoggerInterface $logger,
                            EntityManagerInterface $em, TopupAddressRepository $topupAddressRepository)
    {
        return $topupAddressRepository->findAll();
    }


}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MockController extends AbstractController
{
    /**
     * @Route("/mock/orders", name="mock_orders")
     * @Route("/mock/orders/{id}", name="mock_orders_id")
     */
    public function index()
    {
        return $this->json('OK');
    }

    /**
     * @Route("/mock/addresses/verify", name="mock_addresses_verify")
     */
    public function multinodeVerify()
    {
        return $this->json(['status' => 'ok']);
    }

    /**
     * @Route("/mock/prices", name="mock_prices")
     */
    public function prices()
    {
        return new JsonResponse(json_decode('[
            {
            "pair": "DASHUSD",
            "value": "69.44250000"
            },
            {
            "pair": "LTCBTC",
            "value": "0.00669150"
            },
            {
            "pair": "LTCUSD",
            "value": "59.96500000"
            },
            {
            "pair": "DASHBTC",
            "value": "0.00857000"
            },
            {
            "pair": "BCHEUR",
            "value": "202.45000000"
            },
            {
            "pair": "BTCUSD",
            "value": "8102.50000000"
            },
            {
            "pair": "ETHBTC",
            "value": "0.02199000"
            },
            {
            "pair": "USDTUSD",
            "value": "0.98835000"
            },
            {
            "pair": "BCHBTC",
            "value": "0.02680500"
            },
            {
            "pair": "BCHUSD",
            "value": "281.95000000"
            },
            {
            "pair": "BTCEUR",
            "value": "7348.70000000"
            },
            {
            "pair": "DASHEUR",
            "value": "63.55800000"
            },
            {
            "pair": "LTCEUR",
            "value": "49.99500000"
            },
            {
            "pair": "ETHUSD",
            "value": "180.16000000"
            },
            {
            "pair": "ETHEUR",
            "value": "166.38000000"
            }
        ]'));
    }
}

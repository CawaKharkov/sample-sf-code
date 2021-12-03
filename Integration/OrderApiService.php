<?php

namespace App\Integration;

use App\Entity\Order;
use \GuzzleHttp\Client;
use \GuzzleHttp\Middleware;
use \JMS\Serializer\Serializer;
use Psr\Log\LoggerInterface;

class OrderApiService
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var string
     */
    private $url;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Client $client, Serializer $serializer, string $url, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->url = $url;
        $this->logger = $logger;
    }

    /**
     * @param Order $order
     *
     * @return bool OrderApi responsed OK
     */
    public function pushOrder(Order $order): bool
    {
        /*// Grab the client's handler instance.
        $clientHandler = $this->client->getConfig('handler');
        // Create a middleware that echoes parts of the request.
        $tapMiddleware = Middleware::tap(function ($request) {
            echo $request->getHeaderLine('Content-Type');
            // application/json
            echo $request->getBody();
            // {"foo":"bar"}
        });*/

        $this->logger->debug("Creating order {$order->getId()} in OrderApi");

        $res = $this->client->post($this->url . '/orders', [
            'body' => $this->serializer->serialize($order, 'json'),
            'headers' => [
                'Content-Type'=> 'application/json',
                'Accept'      => 'application/json',
            ],
            //'handler' => $tapMiddleware($clientHandler)
        ]);

        $this->logger->debug("Response from OrderApi (create): " . $res->getBody());

        return 200 == $res->getStatusCode();
    }

    public function deleteOrder(Order $order)
    {
        $this->logger->debug("Deleting order {$order->getId()} in OrderApi");

        $res = $this->client->delete($this->url . '/orders/' . $order->getId());

        $this->logger->debug("Response from OrderApi (delete): " . $res->getBody());
    }

    /**
     * @return \stdClass[]
     *
     * returns the list of prices with following structure:
     * stdClass Object (
     *   [pair] => DASHEUR
     *   [value] => 63.22600000
     * )
     */
    public function getRates()
    {
        $response =  $this->client->get($this->url . '/prices')->getBody();

        $prices = json_decode($response);

        return $prices;
    }

    public function test(): bool
    {
        $res = $this->client->post($this->url . '/orders', [
            'body' => $this->serializer->serialize(new \stdClass(), 'json'),
            'headers' => [
                'Content-Type'=> 'application/json',
                'Accept'      => 'application/json',
            ],
            //'handler' => $tapMiddleware($clientHandler)
        ]);

        return 200 == $res->getStatusCode();
    }
}
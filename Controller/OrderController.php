<?php

namespace App\Controller;

use App\Entity\Model\OrderFilling;
use App\Entity\Model\Pagination\PaginationResponse;
use App\Entity\Order;
use App\Export\Order\ExportOrderService;
use App\Finance\HoldService;
use App\Finance\OrderFillingService;
use App\Form\OrderFillingType;
use App\Form\OrderType;
use App\Integration\OrderApiService;
use App\Repository\DirectionRepository;
use App\Repository\OrderRepository;
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
 * @Rest\Route("/api/v1/orders")
 */
class OrderController extends AbstractFOSRestController
{
    private $sortFields = [
        'created_at' => 'createdAt',
        'price'      => 'price',
        'amount'     => 'amount',
        'type'       => 'type',
        'status'     => 'status',
        'direction'  => 'direction',
        'closed_at'  => 'closedAt'
    ];

    /**
     * Acrive orders
     *
     * @Rest\Route("", name="api_order_list", methods={"GET"})
     * @Rest\View()
     *
     * @QueryParam(name="direction", description="Direction (pair) ID")
     * @QueryParam(name="sort_by", description="Field to sort by", default="created_at")
     * @QueryParam(name="order", description="Sort order: ASC, DESC", default="DESC")
     * @QueryParam(name="page", description="Page number", default=1)
     * @QueryParam(name="size", description="Page size", default=10)
     *
     * @SWG\Response(response="200", description="List of active orders",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=App\Entity\Order::class)))
     * )
     *
     */
    public function index(OrderRepository $repository, ParamFetcher $paramFetcher, PaginatorInterface $paginator)
    {
        $pagination = $paginator->paginate(
            $repository->getOpenQb(
                $this->getUser(),
                $this->sortFields[$paramFetcher->get('sort_by')],
                $paramFetcher->get('order'),
                $paramFetcher->get('direction')
            ),
            $paramFetcher->get('page'),
            $paramFetcher->get('size')
        );

        //return $pagination->getItems();
        return new PaginationResponse($pagination);
    }

    /**
     * Order history
     *
     * @Rest\Route("/history", name="api_order_history", methods={"GET"})
     * @Rest\View()
     *
     * @QueryParam(name="direction", description="Direction (pair) ID")
     * @QueryParam(name="sort_by", description="Field to sort by", default="created_at")
     * @QueryParam(name="order", description="Sort order: ASC, DESC", default="DESC")
     * @QueryParam(name="page", description="Page number", default=1)
     * @QueryParam(name="size", description="Page size", default=10)
     * @QueryParam(name="export", description="Export type")
     *
     * @SWG\Response(response="200", description="List of closed order (history)",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=App\Entity\Order::class)))
     * )
     *
     */
    public function history(OrderRepository $repository, ParamFetcher $paramFetcher, PaginatorInterface $paginator, ExportOrderService $exportService)
    {
        $pagination = $paginator->paginate(
            $repository->getHistoryQb(
                $this->getUser(),
                $this->sortFields[$paramFetcher->get('sort_by')],
                $paramFetcher->get('order'),
                $paramFetcher->get('direction')
            ),
            $paramFetcher->get('page'),
            $paramFetcher->get('size')
        );

        if ($exportType = $paramFetcher->get('export')) {
            return $exportService->getStreamedResponse($pagination, $exportType);
        }

        return new PaginationResponse($pagination);
    }

    /**
     * Order types list
     *
     * @Rest\Route("/types", name="api_ordertypes_list", methods={"GET"})
     * @Rest\View()
     *
     * @SWG\Response(response="200", description="List of available order types",
     *     @SWG\Schema(type="object", example={"1": "By market: sell","2": "By market: buy","3": "By limit: sell","4": "By limit: buy"})
     * )
     */
    public function types()
    {
        return Order::$typesText;
    }

    /**
     * Order status list
     *
     * @Rest\Route("/statuses", name="api_orderstatuses_list", methods={"GET"})
     * @Rest\View()
     *
     * @SWG\Response(response="200", description="List of available order statuses",
     *     @SWG\Schema(type="object", example={"1": "Open","2": "Filled","3": "Partial filled","4": "Cancelled"})
     * )
     */
    public function statuses()
    {
        return Order::$statusesText;
    }

    /**
     * Order directions list
     *
     * @Rest\Route("/directions", name="api_direction_list", methods={"GET"})
     * @Rest\View()
     *
     * @QueryParam(name="exchange", description="Only directions for exchange")
     *
     * @SWG\Response(response="200", description="List of available directions",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=App\Entity\Direction::class)))
     * )
     */
    public function directions(DirectionRepository $repository, ParamFetcher $paramFetcher)
    {
        if ($paramFetcher->get('exchange') == 'easy') {
            throw new BadRequestHttpException("use exchange routes instead!");
            //return $repository->findByIsExchange(true);
        }
        return $repository->findAll();
    }

    /**
     * Create / update order
     *
     * @Rest\Route("", name="api_order_create", methods={"POST"})
     * @Rest\Route("/{id}", name="api_order_update", methods={"PUT"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="Payload", description="Order data", required=true, in="body", required=true,
     *      @SWG\Schema(type="array", @SWG\Items(@Model(type=App\Entity\Order::class)),
     *     example={"amount": 1500.55,"price": 10.25,"type": 4,"direction": 2, "status": 3}),
     * )
     * @SWG\Response(response=200, description="Success response",@SWG\Schema(type="object",example={"success": true}))
     * @SWG\Response(response=400, description="Validation error", @SWG\Schema(type="object", example={"code": 400, "message": "Minimum amount is: UAH 1000"}))
     * @SWG\Response(response=409, description="Not enough balance", @SWG\Schema(type="object", example={"code": 409, "message": "Not enough balance on the USD user account"}))
     */
    public function create(Request $request, ?Order $order, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $logger->info("Incoming order creation request: " . $request->getContent());

        if (is_null($request->get('id'))) {
            // create order
            $order = new Order();
            $order->setUser($this->getUser());
        } else {
            // check if order exists
            if (! $order instanceof Order) {
                throw $this->createNotFoundException("Not found order: {$request->get('id')}");
            }

            // check if order belongs to current user
            if ($order->getUser() != $this->getUser()) {
                throw $this->createAccessDeniedException("Permission denied to update order: {$request->get('id')}");
            }
        }

        $form = $this->createForm(OrderType::class, $order);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($order);
            $em->flush();

            $logger->info(sprintf("User %s created the '%s' %s order %s. Amount: %s, price: %s",
                $order->getUser()->getEmail(),
                $order->getTypeText(),
                $order->getDirection()->getCode(),
                $order->getId(),
                $order->getAmount(),
                $order->getPrice()
            ));

            return ['success' => true];
        }

        return $form;
    }

    /**
     * Delete order
     *
     * @Rest\Route("/{id}", name="api_order_delete", methods={"DELETE"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="id", description="Order ID", required=true, in="path", type="integer")
     * @SWG\Response(response="200", description="Success", @SWG\Schema(type="object", example={"success": true}))
     * @SWG\Response(response="409", description="Can not delete", @SWG\Schema(type="object", example={"code": 409, "message": "Cannot delete"}))
     * @SWG\Response(response="404", description="Not found", @SWG\Schema(type="object", example={"code": 404, "message": "Not found"}))
     */
    public function delete(Request $request, ?Order $order, LoggerInterface $logger,
                           OrderApiService $orderApiService, HoldService $holdService)
    {
        if (null === $order) {
            throw new NotFoundHttpException("Order {$request->get('id')} not found");
        }

        // check if order belongs to current user
        if ($order->getUser() != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to delete order: {$request->get('id')}");
        }

        $logger->debug("Cancel order {$order->getId()} from OrderApi");

        try {
            $order->setStatus(Order::STATUS_CLOSED_PARTIAL_FILLED);
            $orderApiService->deleteOrder($order);
            $holdService->unholdByCancel($order);

            return ['success' => true];
        } catch (\Exception $e) {
            throw new ConflictHttpException("Can not cancel order {$request->get('id')}: " . $e->getMessage());
        }
    }

    /**
     * Fill the order
     *
     * @Rest\Route("/fill", name="api_order_fill", methods={"POST"})
     * @Rest\View()
     */
    public function fill(Request $request, OrderFillingService $service, LoggerInterface $logger)
    {
        $logger->info("Incoming filling request: " . $request->getContent());

        $orderFilling = new OrderFilling();

        $form = $this->createForm(OrderFillingType::class, $orderFilling);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $service->fill($orderFilling);
                } catch (\LogicException $e) {
                    throw new BadRequestHttpException($e->getMessage(), $e);
                }
                return ['success' => true];
            } else {
                $logger->warning('Not valid filling request form: ' . $request->getContent());
                return $form;
            }
        }
    }
}

<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderStatusType;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Psr\Log\LoggerInterface;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Rest\Route("/api/kostyl/orders")
 */
class KostylController extends AbstractFOSRestController
{
    /**
     * All orders
     *
     * @Rest\Route("", name="api_kostyl_order_list", methods={"GET"})
     * @Rest\View()
     *
     * @QueryParam(name="direction", description="Direction (pair)")
     *
     * @QueryParam(name="core_state", description="Core state")
     *
     * @SWG\Response(response="200", description="List of ALL active orders",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=App\Entity\Order::class)))
     * )
     *
     */
    public function index(OrderRepository $repository, ParamFetcher $paramFetcher)
    {
        $criteria = [
            'status' => [
                Order::STATUS_OPEN,
                //Order::STATUS_PARTIAL_FILLED
            ],
            'createdAt' => (new \DateTime())->modify('-1 week'),
        ];

        if ($paramFetcher->get('direction')) {
            $criteria['direction'] = $paramFetcher->get('direction');
        }

        if ($paramFetcher->get('core_state')) {
            $criteria['coreState'] = $paramFetcher->get('core_state');
        }

        return $repository->findBy($criteria, [
            'createdAt' => 'DESC'
        ]);
    }

    /**
     * Order core states list
     *
     * @Rest\Route("/core_states", name="api_ordercorestates_list", methods={"GET"})
     * @Rest\View()
     *
     * @SWG\Response(response="200", description="List of available order core states",
     *     @SWG\Schema(type="object", example={"1": "Created","2": "Creation error","3": "Deleted","4": "Delete error"})
     * )
     */
    public function coreStates()
    {
        return Order::$coreStatesText;
    }

    /**
     * Update order
     *
     * @Rest\Route("/{id}", name="api_kostyl_order_update", methods={"PUT"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="Payload", description="Order data", required=true, in="body", required=true,
     *      @SWG\Schema(type="array", @SWG\Items(@Model(type=App\Entity\Order::class)),
     *     example={"status": 3, "core_state": 1}),
     * )
     * @SWG\Response(response=200, description="Success response",@SWG\Schema(type="object",example={"success": true}))
     * @SWG\Response(response=400, description="Validation error", @SWG\Schema(type="object", example={"code": 400, "message": "Validation error message"}))
     */
    public function create(Request $request, ?Order $order, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $logger->info("Incoming order status changing request from OrderApi: " . $request->getContent());

        if (! $order instanceof Order) {
            throw $this->createNotFoundException("Not found order: {$request->get('id')}");
        }

        $form = $this->createForm(OrderStatusType::class, $order);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $logger->info(sprintf("Updated order %s: status=%s",
                $order->getId(),
                $order->getStatusText()
            ));

            return ['success' => true];
        }

        return $form;
    }

}

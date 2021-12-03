<?php

namespace App\EventListener;

use App\Entity\Order;
use App\Finance\OrderCreationService;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;


class OrderListener implements EventSubscriber
{
    /**
     * @var OrderCreationService
     */
    private $service;

    /**
     * @param OrderCreationService $service
     */
    public function __construct(OrderCreationService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove,
            Events::postPersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->create($args->getObject());
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        // todo: only check
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        if (($order = $args->getObject()) instanceof Order) {
            /** @var $order Order */
            $this->service->hold($order);

            /** @var bool $sent */
            $receivedByCore = $this->service->pushToOrderApi($order);

            $coreState = $receivedByCore
                ? Order::CORE_STATE_CREATED
                : Order::CORE_STATE_CREATION_ERROR;

            $order->setCoreState($coreState);
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        if (($order = $args->getObject()) instanceof Order) {
            /** @var Order $order */
            // only open orders can be deleted
            if ($order->getStatus() !== Order::STATUS_OPEN) {
                throw new ConflictHttpException("Unable to delete order in status {$order->getStatusText()}");
            }

            $this->service->deleteFromOrderApi($order);
        }
    }

    private function create($object)
    {
        if ($object instanceof Order) {
            $this->service->checkConstraints($object);
        }
    }
}

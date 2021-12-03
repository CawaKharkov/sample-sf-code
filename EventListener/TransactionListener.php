<?php

namespace App\EventListener;

use App\Entity\Transaction;
use App\Finance\TransactionService;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;


class TransactionListener implements EventSubscriber
{
    /**
     * @var TransactionService
     */
    private $transactionService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param TransactionService $transactionService
     */
    public function __construct(TransactionService $transactionService, LoggerInterface $logger)
    {
        $this->transactionService = $transactionService;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        /** @var Transaction $object */
        $object = $args->getObject();

        if ($object instanceof Transaction) {
            $this->transactionService->updateUserBalance($object);
            $this->logger->info("Transaction {$object->getTypeText()}: {$object->getUserAccount()->getCurrency()->getCode()} {$object->getAmount()} (user={$object->getUserAccount()->getUser()->getEmail()}) created. Balance updated.");
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        // todo
    }

}

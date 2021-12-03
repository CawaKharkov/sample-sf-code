<?php

namespace App\EventListener;

use App\Entity\User;
use App\Finance\AccountService;
use App\Service\WatchlistService;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;


class UserListener implements EventSubscriber
{

    /**
     * @var AccountService
     */
    private $accountService;

    /**
     * @var WatchlistService
     */
    private $watchlistService;

    /**
     * @param AccountService $accountService
     */
    public function __construct(AccountService $accountService, WatchlistService $watchlistService)
    {
        $this->accountService = $accountService;
        $this->watchlistService = $watchlistService;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        /** @var User $object */
        $object = $args->getObject();

        if ($object instanceof User) {
            $this->accountService->createAccounts($object);
            $this->accountService->createDepositMethods($object);
            $this->watchlistService->createDefaultWatchlist($object);
        }
    }


}

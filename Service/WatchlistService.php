<?php

namespace App\Service;

use App\Entity\Direction;
use App\Entity\User;
use App\Entity\UserWatchlist;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class WatchlistService
{
    const DEFAULT_WATCHLIST_DIRECTIONS = [
        'BTCEUR',
        'BTCUSD'
    ];

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param User $user
     */
    public function createDefaultWatchlist(User $user): void
    {
        foreach (self::DEFAULT_WATCHLIST_DIRECTIONS as $directionCode) {
            $direction = $this->em->getRepository(Direction::class)
                ->findOneByCode($directionCode);

            if (!$direction) {
                $this->logger->error("Default watchlist direction {$directionCode} not found");
                return;
            }

            $watchlist = (new UserWatchlist())
                ->setDirection($direction)
                ->setUser($user);

            $this->em->persist($watchlist);

            $this->logger->info("Default watchlist {$watchlist->getDirection()->getCode()} for user {$user->getEmail()} created");
        }

        $this->em->flush();
    }

}
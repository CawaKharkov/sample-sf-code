<?php

namespace App\Repository;

use App\Entity\ExchangeOrder;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method ExchangeOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExchangeOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExchangeOrder[]    findAll()
 * @method ExchangeOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExchangeOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExchangeOrder::class);
    }

    public function getQb(User $user, $sortBy, $order, $direction = null): ?QueryBuilder
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.user = :user')
            ->setParameter('user', $user)
            ->orderBy("o.$sortBy", $order)
        ;

        if ($direction) {
            $qb
                ->andWhere('o.direction = :direction')
                ->setParameter(':direction', $direction);
        }

        return $qb;
    }

    public function getOpenQb(User $user, $sortBy, $order, $direction = null): QueryBuilder
    {
        $qb = $this->getQb($user, $sortBy, $order, $direction)
            ->andWhere('o.status in (:statuses)')
            ->setParameter('statuses', [
                    ExchangeOrder::STATUS_OPEN,
                ]
            )
        ;

        return $qb;
    }

    public function getHistoryQb(User $user, $sortBy, $order, $direction = null): QueryBuilder
    {
        $qb = $this->getQb($user, $sortBy, $order, $direction)
            ->andWhere('o.status in (:statuses)')
            ->setParameter('statuses', [
                    ExchangeOrder::STATUS_CLOSED_PARTIAL_FILLED,
                    ExchangeOrder::STATUS_CLOSED_FILLED,
                    ExchangeOrder::STATUS_CLOSED_CANCELLED
                ]
            )
        ;

        return $qb;
    }

    /**
     * @param int $limit
     * @return ExchangeOrder[]
     */
    public function getLast(int $limit)
    {
        return $this->createQueryBuilder('o')
            ->where('o.status in (:statuses)')
            ->setParameter('statuses', [ExchangeOrder::STATUS_CLOSED_FILLED, ExchangeOrder::STATUS_CLOSED_PARTIAL_FILLED])
            ->setMaxResults($limit)
            ->orderBy("o.createdAt", 'desc')
            ->getQuery()
            ->getResult()
            ;
    }


}

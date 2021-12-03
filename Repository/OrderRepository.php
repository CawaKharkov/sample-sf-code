<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
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
                    Order::STATUS_OPEN,
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
                Order::STATUS_CLOSED_PARTIAL_FILLED,
                Order::STATUS_CLOSED_FILLED,
                Order::STATUS_CLOSED_CANCELLED
                ]
            )
        ;

        return $qb;
    }

    /**
     * @param int $limit
     * @return Order[]
     */
    public function getLast(int $limit)
    {
        return $this->createQueryBuilder('o')
            ->where('o.status in (:statuses)')
            ->setParameter('statuses', [Order::STATUS_CLOSED_FILLED, Order::STATUS_CLOSED_PARTIAL_FILLED])
            ->setMaxResults($limit)
            ->orderBy("o.createdAt", 'desc')
            ->getQuery()
            ->getResult()
            ;
    }


}

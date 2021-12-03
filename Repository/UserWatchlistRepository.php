<?php

namespace App\Repository;

use App\Entity\UserWatchlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserWatchlist|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserWatchlist|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserWatchlist[]    findAll()
 * @method UserWatchlist[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserWatchlistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserWatchlist::class);
    }

    // /**
    //  * @return UserWatchlist[] Returns an array of UserWatchlist objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserWatchlist
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

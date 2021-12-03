<?php

namespace App\Repository;

use App\Entity\ExchangeDirection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ExchangeDirection|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExchangeDirection|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExchangeDirection[]    findAll()
 * @method ExchangeDirection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExchangeDirectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExchangeDirection::class);
    }

    // /**
    //  * @return Direction[] Returns an array of Direction objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Direction
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

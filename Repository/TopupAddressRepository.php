<?php

namespace App\Repository;

use App\Entity\TopupAddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TopupAddress|null find($id, $lockMode = null, $lockVersion = null)
 * @method TopupAddress|null findOneBy(array $criteria, array $orderBy = null)
 * @method TopupAddress[]    findAll()
 * @method TopupAddress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TopupAddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TopupAddress::class);
    }

    // /**
    //  * @return TopupAddress[] Returns an array of TopupAddress objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TopupAddress
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

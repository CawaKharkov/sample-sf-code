<?php

namespace App\Repository;

use App\Entity\FiatWithdrawal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method FiatWithdrawal|null find($id, $lockMode = null, $lockVersion = null)
 * @method FiatWithdrawal|null findOneBy(array $criteria, array $orderBy = null)
 * @method FiatWithdrawal[]    findAll()
 * @method FiatWithdrawal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FiatWithdrawalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FiatWithdrawal::class);
    }

    // /**
    //  * @return FiatWithdrawal[] Returns an array of FiatWithdrawal objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FiatWithdrawal
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

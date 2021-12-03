<?php

namespace App\Repository;

use App\Entity\DepositTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method DepositTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method DepositTransaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method DepositTransaction[]    findAll()
 * @method DepositTransaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepositTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DepositTransaction::class);
    }

    // /**
    //  * @return DepositTransaction[] Returns an array of DepositTransaction objects
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
    public function findOneBySomeField($value): ?DepositTransaction
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

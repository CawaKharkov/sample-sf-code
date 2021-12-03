<?php

namespace App\Repository;

use App\Entity\OrderFillingOperation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method OrderFillingOperation|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderFillingOperation|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderFillingOperation[]    findAll()
 * @method OrderFillingOperation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderFillingOperationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderFillingOperation::class);
    }

    // /**
    //  * @return OrderFillingOperation[] Returns an array of OrderFillingOperation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OrderFillingOperation
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

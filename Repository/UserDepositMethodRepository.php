<?php

namespace App\Repository;

use App\Entity\UserDepositMethod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserDepositMethod|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserDepositMethod|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserDepositMethod[]    findAll()
 * @method UserDepositMethod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserDepositMethodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserDepositMethod::class);
    }

    // /**
    //  * @return UserDepositMethod[] Returns an array of UserDepositMethod objects
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
    public function findOneBySomeField($value): ?UserDepositMethod
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

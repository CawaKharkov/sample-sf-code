<?php

namespace App\Repository;

use App\Entity\UserWithdrawAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserWithdrawAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserWithdrawAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserWithdrawAccount[]    findAll()
 * @method UserWithdrawAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserWithdrawAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserWithdrawAccount::class);
    }

    // /**
    //  * @return UserBankAccount[] Returns an array of UserBankAccount objects
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
    public function findOneBySomeField($value): ?UserBankAccount
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

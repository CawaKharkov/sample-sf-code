<?php

namespace App\Repository;

use App\Entity\UserWithdrawCryptoaddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserWithdrawCryptoaddress|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserWithdrawCryptoaddress|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserWithdrawCryptoaddress[]    findAll()
 * @method UserWithdrawCryptoaddress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserWithdrawCryptoaddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserWithdrawCryptoaddress::class);
    }

    // /**
    //  * @return UserWithdrawCryptoaddress[] Returns an array of UserWithdrawCryptoaddress objects
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
    public function findOneBySomeField($value): ?UserWithdrawCryptoaddress
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

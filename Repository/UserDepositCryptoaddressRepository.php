<?php

namespace App\Repository;

use App\Entity\UserDepositCryptoaddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserDepositCryptoaddress|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserDepositCryptoaddress|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserDepositCryptoaddress[]    findAll()
 * @method UserDepositCryptoaddress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserDepositCryptoaddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserDepositCryptoaddress::class);
    }

    // /**
    //  * @return UserCryptoAddress[] Returns an array of UserCryptoAddress objects
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
    public function findOneBySomeField($value): ?UserCryptoAddress
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

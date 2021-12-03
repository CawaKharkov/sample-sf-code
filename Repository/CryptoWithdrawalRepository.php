<?php

namespace App\Repository;

use App\Entity\CryptoWithdrawal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CryptoWithdrawal|null find($id, $lockMode = null, $lockVersion = null)
 * @method CryptoWithdrawal|null findOneBy(array $criteria, array $orderBy = null)
 * @method CryptoWithdrawal[]    findAll()
 * @method CryptoWithdrawal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CryptoWithdrawalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CryptoWithdrawal::class);
    }

    // /**
    //  * @return Withdrawal[] Returns an array of Withdrawal objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Withdrawal
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

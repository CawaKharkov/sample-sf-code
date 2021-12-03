<?php

namespace App\Repository;

use App\Entity\CryptoDeposit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CryptoDeposit|null find($id, $lockMode = null, $lockVersion = null)
 * @method CryptoDeposit|null findOneBy(array $criteria, array $orderBy = null)
 * @method CryptoDeposit[]    findAll()
 * @method CryptoDeposit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CryptoDepositRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CryptoDeposit::class);
    }

    // /**
    //  * @return CryptoDeposit[] Returns an array of CryptoDeposit objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CryptoDeposit
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

<?php

namespace App\Repository;

use App\Entity\UserWhiteIp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserWhiteIp|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserWhiteIp|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserWhiteIp[]    findAll()
 * @method UserWhiteIp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserWhiteIpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserWhiteIp::class);
    }

    // /**
    //  * @return UserWhiteIp[] Returns an array of UserWhiteIp objects
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
    public function findOneBySomeField($value): ?UserWhiteIp
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

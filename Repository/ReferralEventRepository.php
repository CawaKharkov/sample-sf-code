<?php

namespace App\Repository;

use App\Entity\ReferralEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ReferralEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReferralEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReferralEvent[]    findAll()
 * @method ReferralEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReferralEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReferralEvent::class);
    }

    // /**
    //  * @return ReferralEvent[] Returns an array of ReferralEvent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ReferalEvent
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

<?php

namespace App\Repository;

use App\Entity\AMLInspection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AMLInspection|null find($id, $lockMode = null, $lockVersion = null)
 * @method AMLInspection|null findOneBy(array $criteria, array $orderBy = null)
 * @method AMLInspection[]    findAll()
 * @method AMLInspection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AMLInspectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AMLInspection::class);
    }

    // /**
    //  * @return AMLInspection[] Returns an array of AMLInspection objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AMLInspection
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

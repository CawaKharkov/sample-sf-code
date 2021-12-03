<?php

namespace App\Repository;

use App\Entity\MultinodeRequestUpdate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MultinodeRequestUpdate|null find($id, $lockMode = null, $lockVersion = null)
 * @method MultinodeRequestUpdate|null findOneBy(array $criteria, array $orderBy = null)
 * @method MultinodeRequestUpdate[]    findAll()
 * @method MultinodeRequestUpdate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MultinodeRequestUpdateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MultinodeRequestUpdate::class);
    }

    // /**
    //  * @return MultinodeRequestUpdate[] Returns an array of MultinodeRequestUpdate objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MultinodeRequestUpdate
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

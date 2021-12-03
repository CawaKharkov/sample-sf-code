<?php

namespace App\Repository;

use App\Entity\MultinodeRequestUpdateItemResults;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MultinodeRequestUpdateItemResults|null find($id, $lockMode = null, $lockVersion = null)
 * @method MultinodeRequestUpdateItemResults|null findOneBy(array $criteria, array $orderBy = null)
 * @method MultinodeRequestUpdateItemResults[]    findAll()
 * @method MultinodeRequestUpdateItemResults[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MultinodeRequestUpdateItemResultsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MultinodeRequestUpdateItemResults::class);
    }

    // /**
    //  * @return MultinodeRequestUpdateItem[] Returns an array of MultinodeRequestUpdateItem objects
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
    public function findOneBySomeField($value): ?MultinodeRequestUpdateItem
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

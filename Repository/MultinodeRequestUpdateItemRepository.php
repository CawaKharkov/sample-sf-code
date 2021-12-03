<?php

namespace App\Repository;

use App\Entity\MultinodeRequestUpdateItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MultinodeRequestUpdateItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method MultinodeRequestUpdateItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method MultinodeRequestUpdateItem[]    findAll()
 * @method MultinodeRequestUpdateItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MultinodeRequestUpdateItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MultinodeRequestUpdateItem::class);
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

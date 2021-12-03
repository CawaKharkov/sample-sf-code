<?php

namespace App\Repository;

use App\Entity\MultinodeRequestCreateItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MultinodeRequestCreateItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method MultinodeRequestCreateItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method MultinodeRequestCreateItem[]    findAll()
 * @method MultinodeRequestCreateItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MultinodeRequestCreateItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MultinodeRequestCreateItem::class);
    }

    // /**
    //  * @return MultinodeRequestCreateItem[] Returns an array of MultinodeRequestCreateItem objects
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
    public function findOneBySomeField($value): ?MultinodeRequestCreateItem
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

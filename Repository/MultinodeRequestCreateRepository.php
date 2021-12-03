<?php

namespace App\Repository;

use App\Entity\MultinodeRequestCreate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MultinodeRequestCreate|null find($id, $lockMode = null, $lockVersion = null)
 * @method MultinodeRequestCreate|null findOneBy(array $criteria, array $orderBy = null)
 * @method MultinodeRequestCreate[]    findAll()
 * @method MultinodeRequestCreate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MultinodeRequestCreateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MultinodeRequestCreate::class);
    }

    // /**
    //  * @return MultinodeRequestCreate[] Returns an array of MultinodeRequestCreate objects
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
    public function findOneBySomeField($value): ?MultinodeRequestCreate
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

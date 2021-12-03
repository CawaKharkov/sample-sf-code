<?php

namespace App\Repository;

use App\Entity\HelpArticle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method HelpArticle|null find($id, $lockMode = null, $lockVersion = null)
 * @method HelpArticle|null findOneBy(array $criteria, array $orderBy = null)
 * @method HelpArticle[]    findAll()
 * @method HelpArticle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HelpArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HelpArticle::class);
    }

    // /**
    //  * @return HelpArticle[] Returns an array of HelpArticle objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HelpArticle
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findAllLike($value)
    {
        return $this->createQueryBuilder('h')
            ->Where('h.text like :val')
            ->orWhere('h.chapter like :val')
            ->orWhere('h.topic like :val')
            ->setParameter('val', "%$value%")
            ->orderBy('h.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}

<?php

namespace App\Repository;

use App\Entity\SecretQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SecretQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method SecretQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method SecretQuestion[]    findAll()
 * @method SecretQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecretQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecretQuestion::class);
    }

    // /**
    //  * @return UserSecretQuestion[] Returns an array of UserSecretQuestion objects
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
    public function findOneBySomeField($value): ?UserSecretQuestion
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

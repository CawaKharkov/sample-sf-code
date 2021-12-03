<?php

namespace App\Repository;

use App\Entity\UserSecretQuestionAnswer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserSecretQuestionAnswer|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSecretQuestionAnswer|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSecretQuestionAnswer[]    findAll()
 * @method UserSecretQuestionAnswer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSecretQuestionAnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSecretQuestionAnswer::class);
    }

    // /**
    //  * @return UserSecretQuestionAnswer[] Returns an array of UserSecretQuestionAnswer objects
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
    public function findOneBySomeField($value): ?UserSecretQuestionAnswer
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

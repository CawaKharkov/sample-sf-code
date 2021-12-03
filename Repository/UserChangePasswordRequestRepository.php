<?php

namespace App\Repository;

use App\Entity\UserChangePasswordRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserChangePasswordRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserChangePasswordRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserChangePasswordRequest[]    findAll()
 * @method UserChangePasswordRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserChangePasswordRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserChangePasswordRequest::class);
    }

    // /**
    //  * @return UserChangePasswordRequest[] Returns an array of UserChangePasswordRequest objects
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
    public function findOneBySomeField($value): ?UserChangePasswordRequest
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

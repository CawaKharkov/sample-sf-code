<?php

namespace App\Repository;

use App\Entity\Referal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Referal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Referal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Referal[]    findAll()
 * @method Referal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReferalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Referal::class);
    }
}

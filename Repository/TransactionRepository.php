<?php

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\UserAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function getByUserQb(User $user, $sortBy, $order, ?string $search = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.userAccount', 'a')
            ->where('a.user = :user')
            ->andWhere('a.type = :accountType')
            ->setParameter('user', $user)
            ->setParameter('accountType', UserAccount::TYPE_PRIMARY)
            ->orderBy("t.$sortBy", $order)
        ;

        if ($search) {
            $qb
                ->leftJoin('a.currency', 'c')
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('t.description', ':search'),
                        $qb->expr()->like('t.amount',      ':search'),
                        $qb->expr()->like('c.code',        ':search'),
                        $qb->expr()->like('t.createdAt',   ':search')
                    )
                )
                ->setParameter('search', '%'.$search.'%');
        }

        return $qb;
    }
}

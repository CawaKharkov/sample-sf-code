<?php

namespace App\Repository;

use App\Entity\Currency;
use App\Entity\Rate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Rate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rate[]    findAll()
 * @method Rate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rate::class);
    }

    // /**
    //  * @return Rate[] Returns an array of Rate objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Rate
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findLatestByCurrency(Currency $currency) : ?Rate
    {
        // todo: remove when useless
        return $this->createQueryBuilder('r')
            ->andWhere('r.currency = :currency')
            ->setParameter('currency', $currency)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findLatest(Currency $currencyFrom, Currency $currencyTo) : ?Rate
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.currencyFrom = :currencyFrom')
            ->andWhere('r.currencyTo = :currencyTo')
            ->setParameter('currencyFrom', $currencyFrom)
            ->setParameter('currencyTo', $currencyTo)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findLatests(int $amount, Currency $currencyFrom, Currency $currencyTo) : array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.currencyFrom = :currencyFrom')
            ->andWhere('r.currencyTo = :currencyTo')
            ->setParameter('currencyFrom', $currencyFrom)
            ->setParameter('currencyTo', $currencyTo)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults($amount)
            ->getQuery()
            ->getResult()
            ;
    }

    public function eraseToday()
    {
        $this->getEntityManager()
            ->getConnection()
            ->prepare('DELETE FROM `rate` WHERE DAYOFYEAR(created_at) = DAYOFYEAR(NOW())')
            ->execute();
    }

    /**
     * @param array $codes
     * @return Rate[]
     */
    public function findToday(array $codes)
    {
        return $this->createQueryBuilder('r')
            ->where('r.createdAt >= :today')
            ->andWhere('r.code IN (:codes)')
            ->setParameter('today', (new \DateTime())->setTimestamp(strtotime('today midnight')))
            ->setParameter('codes', $codes)
            ->getQuery()
            ->getResult()
            ;
    }

}

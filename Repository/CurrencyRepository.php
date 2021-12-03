<?php

namespace App\Repository;

use App\Entity\Currency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @method Currency|null find($id, $lockMode = null, $lockVersion = null)
 * @method Currency|null findOneBy(array $criteria, array $orderBy = null)
 * @method Currency[]    findAll()
 * @method Currency[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CurrencyRepository extends ServiceEntityRepository
{
    const BASE_CURRENCY_CODE = 'USD';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Currency::class);
    }

    /**
     * @throws BadRequestHttpException
     * @return Currency
     */
    public function findBaseCurrency() : Currency
    {
        $currency = $this->createQueryBuilder('c')
            ->andWhere('c.code = :code')
            ->setParameter('code', self::BASE_CURRENCY_CODE)
            ->getQuery()
            ->getSingleResult()
            ;

        if (! $currency instanceof Currency) {
            throw new BadRequestHttpException("Base currency not found: " . self::BASE_CURRENCY_CODE);
        }

        return $currency;
    }
}

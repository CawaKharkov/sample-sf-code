<?php

namespace App\DataFixtures;

use App\Entity\Currency;
use App\Entity\Direction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class DirectionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $directions = [
            ['BTC', 'USD'],
            ['BTC', 'EUR'],
            ['Dash', 'BTC'],
            ['Dash', 'USD'],
            ['Dash', 'EUR'],
            ['ETH', 'USD'],
            ['ETH', 'EUR'],
            ['ETH', 'BTC'],
            ['LTC', 'USD'],
            ['LTC', 'EUR'],
            ['LTC', 'BTC'],
            ['BCH', 'USD'],
            ['BCH', 'EUR'],
            ['BCH', 'BTC'],
            ['USDT', 'USD'],
        ];

        foreach ($directions as $dir) {
            $codeFrom = $dir[0];
            $codeTo = $dir[1];

            $currencyFrom = $manager->getRepository(Currency::class)->findOneByCode($codeFrom);
            $currencyTo = $manager->getRepository(Currency::class)->findOneByCode($codeTo);

            $direction = (new Direction())
                ->setCurrencyBase($currencyFrom)
                ->setCurrencyQuote($currencyTo)
                ->setMinimumAmount(0)
                ->setMaximumAmount(1000000)
                ->setCode($currencyFrom->getCode() . $currencyTo->getCode())
                ->setPrecision($currencyTo->getPrecision())
            ;

            $manager->persist($direction);

        }

        $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return [CurrencyFixtures::class];
    }
}

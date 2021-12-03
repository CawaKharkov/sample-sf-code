<?php

namespace App\DataFixtures;

use App\Entity\Currency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CurrencyFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $data = [
            [
                'code' => 'USD',
                'name' => 'American Dollar',
                'symbol' => '$',
                'precision' => 2,
                'type' => Currency::TYPE_FIAT,
            ],
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'precision' => 2,
                'type' => Currency::TYPE_FIAT,
            ],
            [
                'code' => 'ETH',
                'name' => 'Ethereum',
                'symbol' => 'ETH',
                'precision' => 8,
                'type' => Currency::TYPE_CRYPTO,
            ],
            [
                'code' => 'BTC',
                'name' => 'Bitcoin',
                'symbol' => 'BTC',
                'precision' => 8,
                'type' => Currency::TYPE_CRYPTO,
            ],
            [
                'code' => 'Dash',
                'name' => 'Dash',
                'symbol' => 'Dash',
                'precision' => 8,
                'type' => Currency::TYPE_CRYPTO,
            ],
            [
                'code' => 'USDT',
                'name' => 'Tether',
                'symbol' => 'USDT',
                'precision' => 2,
                'type' => Currency::TYPE_CRYPTO,
            ],
            [
                'code' => 'BCH',
                'name' => 'Bitcoin Cash',
                'symbol' => 'BCH',
                'precision' => 8,
                'type' => Currency::TYPE_CRYPTO,
            ],
            [
                'code' => 'LTC',
                'name' => 'Litecoin',
                'symbol' => 'LTC',
                'precision' => 8,
                'type' => Currency::TYPE_CRYPTO,
            ],
        ];

        foreach ($data as $row) {
            $currency = (new Currency())
                ->setCode($row['code'])
                ->setName($row['name'])
                ->setSymbol($row['symbol'])
                ->setPrecision($row['precision'])
                ->setType($row['type'])
            ;

            $manager->persist($currency);
        }


        $manager->flush();
    }
}

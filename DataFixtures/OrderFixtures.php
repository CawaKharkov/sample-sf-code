<?php

namespace App\DataFixtures;

use App\Entity\Direction;
use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $users = $manager->getRepository(User::class)->findAll();
        $directions = $manager->getRepository(Direction::class)->findAll();

        $types = [
            Order::TYPE_BY_MARKET_SELL,
            Order::TYPE_BY_MARKET_BUY,
            Order::TYPE_BY_LIMIT_SELL,
            Order::TYPE_BY_LIMIT_BUY,
        ];

        /*for ($i=0; $i<10; $i++) {
            $order = (new Order())
                ->setAmount(rand(10, 100))
                ->setDirection($directions[array_rand($directions)])
                ->setUser($users[array_rand($users)])
                ->setType($types[array_rand($types)])
            ;

            if (!in_array($order->getType(), [Order::TYPE_BY_MARKET_SELL, Order::TYPE_BY_MARKET_BUY])) {
                $order->setPrice(rand(500, 1000));
            }

            $manager->persist($order);
        }*/

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
        return [
            UserFixtures::class,
            CurrencyFixtures::class,
            DirectionFixtures::class,
            UserAccountFixtures::class
        ];
    }
}

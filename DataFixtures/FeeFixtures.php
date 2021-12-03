<?php

namespace App\DataFixtures;

use App\Entity\Fee;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class FeeFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $fees = [];

        $fees[] = (new Fee())
            ->setAmount(0)
            ->setTakerFee(0.3)
            ->setMakerFee(0.2)
            ->setType(Fee::TYPE_ORDER);

        $fees[] = (new Fee())
            ->setAmount(1000)
            ->setTakerFee(0.3)
            ->setMakerFee(0.2)
            ->setType(Fee::TYPE_ORDER);

        $fees[] = (new Fee())
            ->setAmount(500000)
            ->setTakerFee(0.150)
            ->setMakerFee(0.050)
            ->setType(Fee::TYPE_ORDER);

        $fees[] = (new Fee())
            ->setAmount(100000)
            ->setTakerFee(0.2)
            ->setMakerFee(0.1)
            ->setType(Fee::TYPE_DEPOSIT);

        $fees[] = (new Fee())
            ->setAmount(500000)
            ->setTakerFee(0.4)
            ->setMakerFee(0.3)
            ->setType(Fee::TYPE_DEPOSIT);

        $fees[] = (new Fee())
            ->setAmount(0)
            ->setTakerFee(0.3)
            ->setMakerFee(0.2)
            ->setType(Fee::TYPE_WITHDRAWAL);

        foreach ($fees as $fee) {
            $manager->persist($fee);
        }

        $manager->flush();
    }
}
